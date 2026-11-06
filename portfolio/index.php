<?php
/* ============================================================
   index.php — HOME / interactive introduction
   ------------------------------------------------------------
   1. Tell the shared header who we are + which link to mark.
   2. Define small data arrays (roles, marquee, featured card)
      so the HTML below stays readable.
   3. require the header → render content → require the footer.
   ============================================================ */

$pageTitle  = 'Home';
$activePage = 'home';
require __DIR__ . '/includes/header.php';

/* Words the typewriter cycles through (rendered as JSON below) */
$roles = ['JavaLearn Hub creator', 'BSIT 3-6 student', 'self-taught developer', 'problem solver'];

/* Big scrolling strip under the hero */
$marqueeItems = [
    'Java', 'PHP', 'JavaLearn Hub', 'HTML & CSS',
    'JavaScript', 'MySQL', 'GitHub', 'Design · Build · Learn',
];

/* Featured project — full write-up lives in projects.php */
$featured = [
    'year'  => '2026',
    'title' => 'JavaLearn Hub',
    'text'  => 'A free beginner Java tutorial — lessons, quizzes and coding exercises in one place, live at javalearn.site.',
    'tags'  => ['HTML', 'CSS', 'JavaScript'],
];
?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="wrap hero__grid">
    <div class="hero__main">
      <div class="local-clock local-clock--hero reveal" aria-label="Your local date and time">
        <div class="hero__drift" aria-hidden="true">
          <span class="hero__drift-orbit"></span>
          <span class="hero__drift-core"></span>
        </div>
        <div class="local-clock__dial" aria-hidden="true">
          <span class="local-clock__hand local-clock__hand--hour"></span>
          <span class="local-clock__hand local-clock__hand--minute"></span>
          <span class="local-clock__hand local-clock__hand--second"></span>
          <span class="local-clock__pin"></span>
        </div>
        <div class="local-clock__readout">
          <span class="local-clock__label">Your local time</span>
          <time id="local-time" class="local-clock__time">--:--:--</time>
          <time id="local-date" class="local-clock__date">Finding date…</time>
          <span id="local-timezone" class="local-clock__zone">Finding timezone…</span>
        </div>
      </div>

      <p class="eyebrow hero__availability reveal"><span class="pulse" aria-hidden="true"></span> Available for internships · 2026</p>
      <h1 class="hero__title reveal">
        <span class="hero__hi">Hi, I'm</span>
        <span class="hero__name">John Kim Harvey <span class="hl">Dangate.</span></span>
      </h1>
      <p class="hero__role reveal">
        <span class="type"
              data-roles="<?= htmlspecialchars(json_encode($roles), ENT_QUOTES) ?>"></span><span class="caret" aria-hidden="true"></span>
      </p>
      <p class="hero__lead reveal">
        BSIT student and builder of <strong>JavaLearn Hub</strong>. I turn useful
        ideas into clear, working experiences for the web.
      </p>
      <div class="hero__actions reveal">
        <a class="btn" href="projects.php">Explore my work <span aria-hidden="true">↗</span></a>
        <a class="btn btn--ghost" href="about.php">About me</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ MARQUEE ============ -->
<div class="marquee" aria-hidden="true">
  <div class="marquee__track">
    <?php for ($g = 0; $g < 2; $g++): ?>
      <div class="marquee__group">
        <?php foreach ($marqueeItems as $item): ?>
          <span><?= htmlspecialchars($item) ?></span>
          <i>•</i>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>

<!-- ============ 01 · INTRODUCTION ============ -->
<section class="section">
  <div class="wrap">
    <header class="section__head intro__head reveal">
      <p class="section__num">01 — Introduction</p>
      <h2 class="display">I learn by<br>building things.</h2>
      <p class="section__lead">
        Short version: a student developer who fell for the web the first time a
        <strong>&lt;div&gt;</strong> did exactly what I told it to.
      </p>
      <a class="intro__portrait" href="about.php" aria-label="More about John Kim Harvey Dangate">
        <img src="assets/img/photo.png" alt="John Kim Harvey Dangate">
        <span>John K. H. Dangate <b aria-hidden="true">↗</b></span>
      </a>
    </header>

    <div class="intro__grid">
      <div class="intro__statement reveal">
        <p>
          I'm <strong>John Kim Harvey Dangate</strong> — 23, third-year BS Information
          Technology (Section 3-6) at Pamantasan ng Lungsod ng Valenzuela. I taught
          myself Java, got tired of ten browser tabs for one concept, and built
          <strong>JavaLearn Hub</strong> instead: lessons, quizzes and coding
          exercises in one free place. That project turned “I should learn to code”
          into “I ship things” — this portfolio is build number two.
        </p>
        <p>
          I also worked on <strong>WinSoles</strong>, a marketplace for browsing and
          selling authentic deadstock sneakers, with product search and market details
          like current bids, asks, and recent sales.
        </p>
      </div>

      <dl class="facts reveal">
        <div class="facts__row"><dt>Course</dt><dd>BS Information Technology · 3-6</dd></div>
        <div class="facts__row"><dt>School</dt><dd>Pamantasan ng Lungsod ng Valenzuela</dd></div>
        <div class="facts__row"><dt>Age</dt><dd>23</dd></div>
        <div class="facts__row"><dt>Focus</dt><dd>Java · Web</dd></div>
        <div class="facts__row"><dt>Available</dt><dd>Internships · Freelance</dd></div>
      </dl>
    </div>
  </div>
</section>

<!-- ============ 02 · FEATURED PROJECT (inverted band) ============ -->
<section class="section band">
  <div class="wrap">
    <header class="section__head reveal">
      <p class="section__num">02 — Featured project</p>
      <h2 class="display">One project,<br>done properly.</h2>
      <p class="section__lead">
        I'd rather ship <em>one</em> thing I can defend in a viva than five I can't.
      </p>
    </header>

    <article class="feature reveal">
      <p class="feature__num" aria-hidden="true">01</p>
      <div class="feature__body">
        <div class="tag-row">
          <span class="tag"><?= htmlspecialchars($featured['year']) ?></span>
          <?php foreach ($featured['tags'] as $tag): ?>
            <span class="tag"><?= htmlspecialchars($tag) ?></span>
          <?php endforeach; ?>
        </div>
        <h3 class="feature__title"><?= htmlspecialchars($featured['title']) ?></h3>
        <p class="feature__text"><?= htmlspecialchars($featured['text']) ?></p>
        <a class="btn" href="projects.php">Read the case study <span aria-hidden="true">→</span></a>
      </div>
    </article>
  </div>
</section>

<!-- ============ CTA ============ -->
<section class="section cta">
  <div class="wrap cta__inner reveal">
    <h2 class="display">Got a project<br>in mind?</h2>
    <div class="cta__actions">
      <a class="btn" href="mailto:hello@example.com">harveydangate@gmail.com</a> <!-- ✏️ your email -->
      <a class="btn btn--ghost" href="about.php">See my skills</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
