<?php
/* ============================================================
   playground.php — PLAYGROUND: little tools, no accounts
   ------------------------------------------------------------
   01. Domain Age Check   — registration date, age, expiry
   02. HTTP Status Lookup — does a URL respond, how fast, redirects
   03. Password Strength  — length/entropy estimate, never sent
   04. Text Case Lab      — every case style, plus a word count

   Every tool posts to this same page. The PHP builds ONE html
   fragment per tool and returns it either as JSON (JS on) or
   inside the page (JS off), so markup is never duplicated.
   Everything except tool 01 runs entirely on this server —
   the password is hashed in the browser and only the hash is
   ever posted.
   ============================================================ */

$pageTitle  = 'Playground';
$activePage = 'playground';

/* ============================================================
   TOOL 01 — DOMAIN AGE: helpers
   ============================================================ */

/* Accepts a pasted URL ("https://www.site.com/page") and
   returns a clean host ("site.com"). */
function normalize_domain(string $input): string {
    $host = strtolower(trim($input));
    $host = preg_replace('#^[a-z][a-z0-9+.\-]*://#i', '', $host);   // strip scheme
    $host = explode('/', $host)[0];
    $host = explode('?', $host)[0];
    $host = explode('#', $host)[0];
    $at    = explode('@', $host);
    $host  = end($at);                                              // strip user@pass
    $host  = explode(':', $host)[0];                                // strip :port
    $host  = preg_replace('#^www\.#', '', $host);                   // strip www.
    return trim($host, '. ');
}

/* Read one event date (registration / expiration) from RDAP JSON. */
function rdap_event(array $data, string $action): ?string {
    foreach ($data['events'] ?? [] as $event) {
        if (($event['eventAction'] ?? '') === $action && !empty($event['eventDate'])) {
            return (string) $event['eventDate'];
        }
    }
    return null;
}

/* Pull the registrar name out of RDAP's jCard structure. */
function rdap_registrar(array $data): ?string {
    foreach ($data['entities'] ?? [] as $entity) {
        if (!in_array('registrar', $entity['roles'] ?? [], true)) {
            continue;
        }
        foreach ($entity['vcardArray'][1] ?? [] as $item) {
            if (is_array($item) && ($item[0] ?? '') === 'fn') {
                return (string) ($item[3] ?? '');
            }
        }
    }
    return null;
}

/* Step 1: RDAP lookup over HTTPS. Returns null on failure.
   Prefers the curl extension (works even when the PHP build's
   TLS/CA setup is broken), falls back to file_get_contents. */
function fetch_rdap(string $domain): ?array {
    $url     = 'https://rdap.org/domain/' . rawurlencode($domain);
    $headers = 'Accept: application/json';
    $ua      = 'Mozilla/5.0 (compatible; DomainAgeCheck/1.0; portfolio script)';
    $body    = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [$headers],
            CURLOPT_USERAGENT      => $ua,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw !== false && $code >= 200 && $code < 400) {
            $body = (string) $raw;
        }
    } elseif (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'method'          => 'GET',
                'timeout'         => 10,
                'header'          => $headers . "\r\nUser-Agent: " . $ua . "\r\n",
                'follow_location' => 1,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw !== false) {
            $body = $raw;
        }
    }

    if ($body === null) {
        return null;
    }
    $data = json_decode($body, true);
    return (is_array($data) && isset($data['objectClassName'])) ? $data : null;
}

/* Send one query to a WHOIS server (port 43). */
function whois_raw(string $server, string $query, int $timeout = 8): ?string {
    $fp = @fsockopen($server, 43, $errno, $errstr, $timeout);
    if (!$fp) {
        return null;
    }
    stream_set_timeout($fp, $timeout);
    @fwrite($fp, $query . "\r\n");
    $out = '';
    while (!feof($fp)) {
        $line = fgets($fp, 1024);
        if ($line === false) {
            break;
        }
        $out .= $line;
        if (strlen($out) > 65536) {
            break;
        }
    }
    fclose($fp);
    return $out === '' ? null : $out;
}

/* Step 2: WHOIS fallback — IANA tells us which server to ask. */
function whois_lookup(string $domain): ?array {
    $dot = strrchr($domain, '.');
    $tld = $dot ? ltrim($dot, '.') : '';
    if ($tld === '') {
        return null;
    }
    $iana = whois_raw('whois.iana.org', $tld);
    if (!$iana || !preg_match('/^refer:\s*([^\s]+)/mi', $iana, $m)) {
        return null;
    }
    $raw = whois_raw($m[1], 'domain ' . $domain);
    if (!$raw) {
        return null;
    }
    $created = $expires = $registrar = null;
    if (preg_match('/^(?:Creation Date|Created Date|Registration Date|Registered on|created|Domain Create Date):\s*(.+)$/mi', $raw, $m)) {
        $created = trim($m[1]);
    }
    if (preg_match('/^(?:Registry Expiry Date|Expiry Date|Expiration Date|paid-till):\s*(.+)$/mi', $raw, $m)) {
        $expires = trim($m[1]);
    }
    if (preg_match('/^Registrar:\s*(.+)$/mi', $raw, $m)) {
        $registrar = trim($m[1]);
    }
    if (!$created) {
        return null;
    }
    return ['created' => $created, 'expires' => $expires, 'registrar' => $registrar];
}

/* Turn the many date formats WHOIS/RDAP use into one DateTime. */
function parse_date(?string $raw): ?DateTimeImmutable {
    if (!$raw) {
        return null;
    }
    $raw = preg_replace('/\(.*?\)/', '', $raw);        // strip "(UTC)" style notes
    $raw = trim(str_replace('.', '-', $raw));          // "2010.05.12" → "2010-05-12"
    if ($raw === '') {
        return null;
    }
    try {
        return new DateTimeImmutable($raw);
    } catch (Exception $e) {
        return null;
    }
}

/* One entry point: RDAP first, WHOIS second, empty array = failed. */
function lookup_domain(string $domain): array {
    $data = fetch_rdap($domain);
    if ($data) {
        $created = parse_date(rdap_event($data, 'registration'));
        if ($created) {
            return [
                'created'   => $created,
                'expires'   => parse_date(rdap_event($data, 'expiration')),
                'registrar' => rdap_registrar($data),
                'source'    => 'RDAP',
            ];
        }
    }

    $whois = whois_lookup($domain);
    if ($whois) {
        $created = parse_date($whois['created']);
        if ($created) {
            return [
                'created'   => $created,
                'expires'   => parse_date($whois['expires']),
                'registrar' => $whois['registrar'],
                'source'    => 'WHOIS',
            ];
        }
    }

    return [];
}

/* ============================================================
   SHARED MARKUP HELPERS
   ============================================================ */

function error_box(string $msg): string {
    return '<p class="tool-error">' . htmlspecialchars($msg) . '</p>';
}

function fact_row(string $key, string $value): string {
    return '<div class="facts__row"><dt>' . htmlspecialchars($key) . '</dt><dd>'
         . htmlspecialchars($value) . '</dd></div>';
}

/* Big headline number(s): [['n' => 5, 'u' => 'months'], …] */
function big_age(array $pairs): string {
    $out = '';
    foreach ($pairs as $i => $pair) {
        $cls  = $i === 0 ? ' class="hl"' : '';
        $out .= '<span><b' . $cls . '>' . (int) $pair['n'] . '</b> <i>'
              . htmlspecialchars($pair['u']) . '</i></span>';
    }
    return '<p class="result__age">' . $out . '</p>';
}

/* Meter bar 0–100 with a caption underneath. */
function meter(int $pct, string $caption, string $tone = ''): string {
    $pct = max(0, min(100, $pct));
    return '<div class="term">'
         . '<div class="term__bar"><span class="term__fill' . $tone . '" style="width: ' . $pct . '%"></span></div>'
         . '<p class="term__cap">' . htmlspecialchars($caption) . '</p>'
         . '</div>';
}

/* ============================================================
   TOOL 01 — DOMAIN AGE: build result html
   ============================================================ */
function tool_domain(string $raw): array {
    $domain = normalize_domain($raw);
    $valid  = $domain !== ''
        && strpos($domain, '.') !== false
        && filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

    if (!$valid) {
        return [false, error_box('That doesn’t look like a domain — try something like javalearn.site.')];
    }

    $info = lookup_domain($domain);

    if (!$info) {
        return [false, error_box(
            'Couldn’t find a registration record for ' . $domain . ' — either its '
            . 'extension isn’t in the public records, or this server can’t reach '
            . 'them right now (some school networks block port 43).'
        )];
    }

    $created = $info['created'];
    $expires = $info['expires'];
    $now     = new DateTimeImmutable('today');

    if ($created > $now) {
        return [false, error_box('The registration date for ' . $domain . ' looks like it’s in the future — that record can’t be trusted.')];
    }

    $age   = $created->diff($now);
    $pairs = [];
    if ($age->y > 0) {
        $pairs[] = ['n' => $age->y, 'u' => $age->y === 1 ? 'year' : 'years'];
    }
    if ($age->m > 0) {
        $pairs[] = ['n' => $age->m, 'u' => $age->m === 1 ? 'month' : 'months'];
    }
    $pairs[] = ['n' => $age->d, 'u' => $age->d === 1 ? 'day' : 'days'];

    $html  = '<p class="result__domain">' . htmlspecialchars($domain) . '</p>';
    $html .= big_age($pairs);

    if ($expires && $expires < $now) {
        $html .= '<p class="tool-error">Expired — this domain’s registration lapsed on '
               . htmlspecialchars(date('M j, Y', $expires->getTimestamp())) . '.</p>';
    }

    $rows  = fact_row('Registered', date('M j, Y', $created->getTimestamp()));
    $rows .= $expires ? fact_row('Expires', date('M j, Y', $expires->getTimestamp())) : '';
    $rows .= $info['registrar'] ? fact_row('Registrar', $info['registrar']) : '';
    $rows .= fact_row('Age in days', number_format($age->days));
    $rows .= fact_row('Source', $info['source']);

    $html .= '<dl class="facts">' . $rows . '</dl>';

    if ($expires && $expires > $created) {
        $termDays = $created->diff($expires)->days;
        $usedDays = $created->diff($now)->days;
        if ($termDays > 0) {
            $pct   = (int) round($usedDays / $termDays * 100);
            $html .= meter($pct, $pct . '% of the current registration term has passed');
        }
    }

    return [true, $html];
}

/* ============================================================
   TOOL 02 — HTTP STATUS: is this URL alive, how fast, redirects
   ============================================================ */
function tool_http(string $raw): array {
    $url = trim($raw);
    if ($url === '') {
        return [false, error_box('Paste a URL first — e.g. https://javalearn.site.')];
    }
    if (!preg_match('#^[a-z][a-z0-9+.\-]*://#i', $url)) {
        $url = 'https://' . $url;
    }

    $parts = parse_url($url);
    $host  = strtolower($parts['host'] ?? '');
    $valid = $host !== '' && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    if (!$valid || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
        return [false, error_box('That doesn’t look like a web address — try https://example.com.')];
    }

    $ua = 'Mozilla/5.0 (compatible; PortfolioStatusCheck/1.0)';

    if (!function_exists('curl_init')) {
        /* No curl extension: at least confirm the host answers on 443/80 */
        $port = ($parts['scheme'] ?? '') === 'https' ? 443 : 80;
        $fp   = @fsockopen($host, $port, $errno, $errstr, 6);
        if (!$fp) {
            return [false, error_box('Couldn’t connect to ' . $host . ' on port ' . $port . '.')];
        }
        fclose($fp);
        return [true, '<p class="result__domain">' . htmlspecialchars($url) . '</p>'
                   . '<dl class="facts">'
                   . fact_row('Host reachable', 'yes, port ' . $port . ' is open')
                   . fact_row('Note', 'the curl extension is off, so no HTTP status')
                   . '</dl>'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_NOBODY         => true,
        CURLOPT_USERAGENT      => $ua,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    curl_exec($ch);

    $code    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $lookup  = (float) curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME);
    $connect = (float) curl_getinfo($ch, CURLINFO_CONNECT_TIME);
    $total   = (float) curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $final   = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $redirects = (int) curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
    $scheme  = strtolower((string) curl_getinfo($ch, CURLINFO_SCHEME));
    $err     = curl_error($ch);
    curl_close($ch);

    if ($code === 0) {
        /* Certificate failures are a real finding, not a dead server —
           say so plainly, because that is exactly what visitors see. */
        $sslProblem = (bool) preg_match('/certificate|SSL|TLS/i', $err);
        if ($sslProblem) {
            return [false, error_box(
                $host . ' answered, but its HTTPS certificate is not valid for this '
                . 'address — so browsers show a security warning. That usually means '
                . 'the certificate hasn’t been issued (or renewed) for this exact domain yet.'
            )];
        }
        return [false, error_box('No response from ' . $host . ' — ' . ($err !== '' ? $err : 'the server didn’t answer').'.')];
    }

    /* Group the status so the colour-free design can still say "good/bad" */
    if ($code < 300) {
        $verdict = 'Fine — the page is being served.';
        $tone    = '';
    } elseif ($code < 400) {
        $verdict = 'Redirect — the page points somewhere else.';
        $tone    = '';
    } elseif ($code < 500) {
        $verdict = 'Problem — the request was understood but refused.';
        $tone    = ' is-bad';
    } else {
        $verdict = 'Server error — the page is down.';
        $tone    = ' is-bad';
    }

    $clean = $final !== '' ? $final : $url;

    $html  = '<p class="result__domain">' . htmlspecialchars($clean) . '</p>';
    $html .= '<p class="result__age"><span><b' . ($code < 400 ? ' class="hl"' : '') . '>'
           . $code . '</b> <i>HTTP status</i></span></p>';
    $html .= '<p class="result__verdict">' . htmlspecialchars($verdict) . '</p>';

    $rows  = fact_row('Response time', $total > 0 ? round($total * 1000) . ' ms' : 'n/a');
    $rows .= fact_row('DNS lookup', round($lookup * 1000) . ' ms');
    $rows .= fact_row('TLS connect', round($connect * 1000) . ' ms');
    $rows .= fact_row('Encrypted', $scheme === 'https' ? 'yes (HTTPS)' : 'no (plain HTTP)');
    $rows .= fact_row('Redirects', $redirects === 0 ? 'none' : $redirects);
    $html .= '<dl class="facts">' . $rows . '</dl>';

    /* Speed meter: 300 ms = instant, 3000 ms = slow */
    $pct = (int) round(min(100, $total * 1000 / 3000 * 100));
    $html .= meter($pct, round($total * 1000) . ' ms to first byte — '
                  . ($pct < 34 ? 'fast' : ($pct < 70 ? 'average' : 'slow')), $tone);

    return [true, $html];
}

/* ============================================================
   TOOL 03 — PASSWORD STRENGTH (hash arrives, never the password)
   ============================================================ */
function tool_password(string $hash, int $length): array {
    if (!preg_match('/^[a-f0-9]{64}$/', $hash) || $length < 1 || $length > 200) {
        return [false, error_box('Type a password first — it’s measured as you type.')];
    }

    /* Estimate the character pool from the SHA-256 hash's bytes.
       This is a heuristic, not a claim: the browser never sends
       the password, so the pool is guessed from the hash only. */
    $bytes = array_values(unpack('C*', hex2bin($hash)));
    $seen  = count(array_unique($bytes));
    $lower = $upper = $digit = false;
    foreach ($bytes as $byte) {
        if ($byte >= 0x61 && $byte <= 0x7a) { $lower = true; }
        if ($byte >= 0x41 && $byte <= 0x5a) { $upper = true; }
        if ($byte >= 0x30 && $byte <= 0x39) { $digit = true; }
    }

    $pool  = 26 + ($lower ? 26 : 0) + ($upper ? 26 : 0) + ($digit ? 10 : 0);
    $pool += 33;                                    // symbols are always plausible
    $bits  = $length * log($pool, 2);

    if ($bits < 40) {
        $verdict = 'Weak';
        $pct     = max(6, (int) round($bits / 128 * 100));
        $tone    = ' is-bad';
        $advice  = 'Under 40 bits of entropy — guessing tools break this quickly.';
    } elseif ($bits < 70) {
        $verdict = 'Okay';
        $pct     = (int) round($bits / 128 * 100);
        $tone    = '';
        $advice  = 'Decent, but a longer passphrase would be safer.';
    } elseif ($bits < 100) {
        $verdict = 'Strong';
        $pct     = (int) round($bits / 128 * 100);
        $tone    = '';
        $advice  = 'Good length — that’s a lot of guessing.';
    } else {
        $verdict = 'Very strong';
        $pct     = 100;
        $tone    = '';
        $advice  = 'Excellent — long enough to be impractical to brute-force.';
    }

    $html  = '<p class="result__domain">password strength · ' . $length . ' characters</p>';
    $html .= '<p class="result__age"><span><b' . ($bits >= 70 ? ' class="hl"' : '') . '>'
           . $verdict . '</b> <i>' . (int) round($bits) . ' bits estimated</i></span></p>';
    $sets = [];
    if ($lower) { $sets[] = 'lowercase'; }
    if ($upper) { $sets[] = 'uppercase'; }
    if ($digit) { $sets[] = 'digits'; }

    $html .= '<dl class="facts">'
           . fact_row('Length', $length . ' characters')
           . fact_row('Variety detected', $seen . ' distinct values in the hash')
           . fact_row('Letter types', $sets ? implode(' + ', $sets) : 'symbols only')
           . fact_row('Private', 'the password never leaves your browser')
           . '</dl>';
    $html .= meter($pct, $verdict . ' — ' . $advice, $tone);

    return [true, $html];
}

/* ============================================================
   TOOL 04 — TEXT CASE LAB
   ============================================================ */
function tool_text(string $text): array {
    $text = trim($text);
    if ($text === '') {
        return [false, error_box('Paste some text and it’ll be converted every way at once.')];
    }
    if (strlen($text) > 4000) {
        return [false, error_box('That’s a lot of text — keep it under 4,000 characters.')];
    }

    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    if (!$words) {
        $words = [];
    }

    $cases = [
        'lowercase'    => mb_strtolower($text),
        'UPPERCASE'    => mb_strtoupper($text),
        'Title Case'   => mb_convert_case($text, MB_CASE_TITLE),
        'Sentence case'=> mb_strtoupper(mb_substr($text, 0, 1)) . mb_strtolower(mb_substr($text, 1)),
        'kebab-case'   => implode('-', array_map('mb_strtolower', $words)),
        'snake_case'   => implode('_', array_map('mb_strtolower', $words)),
        'dot.case'     => implode('.', array_map('mb_strtolower', $words)),
        'camelCase'    => lcfirst(str_replace(' ', '', ucwords(mb_strtolower($text)))),
        'PascalCase'   => str_replace(' ', '', ucwords(mb_strtolower($text))),
        'a11y-slug'    => trim(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($text)), '-'),
    ];

    $totalWords = count($words);
    $chars      = mb_strlen($text);
    $sentences  = max(1, preg_match_all('/[.!?]+/', $text));
    $readMin    = max(1, (int) ceil($totalWords / 200));

    $html  = '<p class="result__domain">text case lab · ' . $chars . ' characters</p>';
    $html .= '<p class="result__age"><span><b class="hl">' . $totalWords . '</b> <i>'
           . ($totalWords === 1 ? 'word' : 'words') . '</i></span>'
           . '<span><b>' . $sentences . '</b> <i>sentences</i></span></p>';

    $body = '';
    foreach ($cases as $label => $value) {
        if (mb_strlen($value) > 240) {
            $value = mb_substr($value, 0, 240) . '…';
        }
        $body .= '<div class="case-row"><span class="case-row__label">' . htmlspecialchars($label)
               . '</span><code class="case-row__val">' . ($value === '' ? '—' : htmlspecialchars($value)) . '</code></div>';
    }

    $html .= '<dl class="facts">'
           . fact_row('Words', $totalWords)
           . fact_row('Characters', $chars)
           . fact_row('Sentences', $sentences)
           . fact_row('Reading time', $readMin . ' min (200 wpm)')
           . '</dl>';
    $html .= '<div class="case-list">' . $body . '</div>';

    return [true, $html];
}

/* ============================================================
   ROUTE THE POST — before the header, so JSON can exit clean
   ============================================================ */
$tool    = (string) ($_POST['tool'] ?? '');
if ($tool === '') {
    $tool = 'domain';          /* bare POSTs (no-JS, old links) default to tool 01 */
}
$isPost  = $_SERVER['REQUEST_METHOD'] === 'POST';
$isAjax  = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
$ok      = false;
$html    = '';
$echoed  = [
    'domain'   => (string) ($_POST['domain'] ?? ''),
    'url'      => (string) ($_POST['url'] ?? ''),
    'text'     => (string) ($_POST['text'] ?? ''),
    'length'   => (int) ($_POST['length'] ?? 0),
];

if ($isPost) {
    switch ($tool) {
        case 'http':
            [$ok, $html] = tool_http($echoed['url']);
            break;
        case 'password':
            [$ok, $html] = tool_password((string) ($_POST['hash'] ?? ''), $echoed['length']);
            break;
        case 'text':
            [$ok, $html] = tool_text($echoed['text']);
            break;
        default:
            [$ok, $html] = tool_domain($echoed['domain']);
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'html' => $html]);
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<!-- ============ PAGE HEAD ============ -->
<section class="page-head">
  <p class="ghost-num" aria-hidden="true">04</p>
  <div class="wrap">
    <p class="eyebrow reveal">Playground</p>
    <h1 class="reveal">Small tools,<br><span class="hl">no sign-up</span>.</h1>
    <p class="lead reveal">
      Four little things I built because I wanted them — no accounts, no API keys,
      no tracking. Nothing you type is stored; your password never even leaves your browser.
    </p>
  </div>
</section>

<!-- ============ TOOLS ============ -->
<section class="section section--tight-top">
  <div class="wrap">

    <!-- ---------- 01. DOMAIN AGE ---------- -->
    <article class="tool reveal">
      <header class="tool__head">
        <div>
          <p class="section__num">Tool 01</p>
          <h2 class="tool__title">Domain Age Check</h2>
          <p class="tool__sub">Instant registration &amp; expiry check — any domain, no signup.</p>
        </div>
        <span class="tag">Live</span>
      </header>

      <form class="tool-form" action="playground.php" method="post" data-tool="domain">
        <input type="hidden" name="tool" value="domain">
        <label class="tool-label" for="domain">Domain</label>
        <div class="tool-form__row">
          <input class="tool-input" type="text" id="domain" name="domain"
                 placeholder="javalearn.site"
                 value="<?= htmlspecialchars($echoed['domain']) ?>"
                 autocomplete="off" autocapitalize="off" spellcheck="false"
                 inputmode="url" required>
          <button class="btn tool-btn" type="submit">Check age <span aria-hidden="true">→</span></button>
        </div>
        <p class="tool-hint">
          Try:
          <button type="button" class="chip" data-fill="javalearn.site">javalearn.site</button>
          <button type="button" class="chip" data-fill="github.com">github.com</button>
          <button type="button" class="chip" data-fill="stackoverflow.com">stackoverflow.com</button>
        </p>
      </form>

      <p class="tool-status" role="status" aria-live="polite"></p>
      <div class="result<?= $isPost && $tool === 'domain' ? '' : ' is-empty' ?>" id="tool-domain-result">
        <?php if ($isPost && $tool === 'domain'): ?>
          <?= $html ?>
        <?php else: ?>
          <p class="result__empty">Results land here — enter a domain above.</p>
        <?php endif; ?>
      </div>
    </article>

    <!-- ---------- 02. HTTP STATUS ---------- -->
    <article class="tool reveal">
      <header class="tool__head">
        <div>
          <p class="section__num">Tool 02</p>
          <h2 class="tool__title">HTTP Status Lookup</h2>
          <p class="tool__sub">Is a URL alive? Status code, redirects and how fast it answers.</p>
        </div>
        <span class="tag">Live</span>
      </header>

      <form class="tool-form" action="playground.php" method="post" data-tool="http">
        <input type="hidden" name="tool" value="http">
        <label class="tool-label" for="url">URL</label>
        <div class="tool-form__row">
          <input class="tool-input" type="text" id="url" name="url"
                 placeholder="https://javalearn.site"
                 value="<?= htmlspecialchars($echoed['url']) ?>"
                 autocomplete="off" autocapitalize="off" spellcheck="false"
                 inputmode="url" required>
          <button class="btn tool-btn" type="submit">Check status <span aria-hidden="true">→</span></button>
        </div>
        <p class="tool-hint">
          Try:
          <button type="button" class="chip" data-fill="https://javalearn.site">javalearn.site</button>
          <button type="button" class="chip" data-fill="https://github.com">github.com</button>
          <button type="button" class="chip" data-fill="https://httpstat.us/404">httpstat.us/404</button>
        </p>
      </form>

      <p class="tool-status" role="status" aria-live="polite"></p>
      <div class="result<?= $isPost && $tool === 'http' ? '' : ' is-empty' ?>" id="tool-http-result">
        <?php if ($isPost && $tool === 'http'): ?>
          <?= $html ?>
        <?php else: ?>
          <p class="result__empty">Results land here — paste a URL above.</p>
        <?php endif; ?>
      </div>
    </article>

    <!-- ---------- 03. PASSWORD STRENGTH ---------- -->
    <article class="tool reveal">
      <header class="tool__head">
        <div>
          <p class="section__num">Tool 03</p>
          <h2 class="tool__title">Password Strength</h2>
          <p class="tool__sub">Length and entropy estimate — the password itself never leaves your browser.</p>
        </div>
        <span class="tag">Private</span>
      </header>

      <form class="tool-form" action="playground.php" method="post" data-tool="password">
        <input type="hidden" name="tool" value="password">
        <label class="tool-label" for="pw">Password</label>
        <div class="tool-form__row">
          <input class="tool-input" type="password" id="pw" name="pw"
                 placeholder="type something long"
                 autocomplete="new-password" spellcheck="false" required>
          <button class="btn tool-btn" type="submit">Measure <span aria-hidden="true">→</span></button>
        </div>
        <p class="tool-hint">
          The browser hashes what you type (SHA-256) and posts only the hash and the
          length — never the password. Use a fake one if you like.
        </p>
      </form>

      <p class="tool-status" role="status" aria-live="polite"></p>
      <div class="result<?= $isPost && $tool === 'password' ? '' : ' is-empty' ?>" id="tool-password-result">
        <?php if ($isPost && $tool === 'password'): ?>
          <?= $html ?>
        <?php else: ?>
          <p class="result__empty">Results land here — start typing a password.</p>
        <?php endif; ?>
      </div>
    </article>

    <!-- ---------- 04. TEXT CASE LAB ---------- -->
    <article class="tool reveal">
      <header class="tool__head">
        <div>
          <p class="section__num">Tool 04</p>
          <h2 class="tool__title">Text Case Lab</h2>
          <p class="tool__sub">Ten case styles plus a word count — handy for titles, slugs and code.</p>
        </div>
        <span class="tag">Live</span>
      </header>

      <form class="tool-form" action="playground.php" method="post" data-tool="text">
        <input type="hidden" name="tool" value="text">
        <label class="tool-label" for="text">Text</label>
        <textarea class="tool-input tool-textarea" id="text" name="text" rows="4"
                  placeholder="Paste a sentence or a title…" required><?= htmlspecialchars($echoed['text']) ?></textarea>
        <div class="tool-form__row">
          <button class="btn tool-btn" type="submit">Convert <span aria-hidden="true">→</span></button>
        </div>
        <p class="tool-hint">
          Try:
          <button type="button" class="chip" data-fill="JavaScript variables and data types">a lesson title</button>
          <button type="button" class="chip" data-fill="How To Write Clean Code">a blog title</button>
        </p>
      </form>

      <p class="tool-status" role="status" aria-live="polite"></p>
      <div class="result<?= $isPost && $tool === 'text' ? '' : ' is-empty' ?>" id="tool-text-result">
        <?php if ($isPost && $tool === 'text'): ?>
          <?= $html ?>
        <?php else: ?>
          <p class="result__empty">Results land here — paste some text above.</p>
        <?php endif; ?>
      </div>
    </article>

    <div class="tool-notes">
      <div class="panel tool-notes__about reveal">
        <h3>How these work</h3>
        <p>
          Everything runs on <strong>PHP on this server</strong> — no third-party APIs,
          no keys, no database. Tool 01 talks to <strong>RDAP</strong> then
          <strong>WHOIS</strong>; tool 02 does a real cURL request and reports the
          status code and timing; tool 03 hashes your password in the browser with
          the Web Crypto API; tool 04 is pure PHP string functions.
        </p>
      </div>

      <!--<div class="panel panel--marker reveal">
        <p class="marker-note">Inspired by the playground over at nader.io — the ideas, rebuilt my way.</p>
        <a class="btn btn--ghost" href="https://nader.io/playground/" target="_blank" rel="noopener">
          See the original <span aria-hidden="true">↗</span>
        </a>
      </div>
    </div>
        --> 
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
