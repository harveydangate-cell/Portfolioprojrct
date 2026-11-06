<?php
/* ============================================================
   projects.php — PROJECTS
   ------------------------------------------------------------
   The case study is rendered with foreach from $projects.
   Add another array entry to showcase a project.
   ============================================================ */

$pageTitle  = 'Projects';
$activePage = 'projects';
require __DIR__ . '/includes/header.php';

$projects = [
    [
        'num'    => '01',
        'title'  => 'JavaLearn Hub',
        'sub'    => 'Beginner Java tutorial site',
        'image'  => 'assets/img/photos.jpg',
        'image_alt' => 'JavaLearn Hub homepage screenshot',
        'site'   => 'https://javalearn.site',
        'year'   => '2026',                 // ✏️ the year you launched it
        'role'   => 'Research · Lessons · Design · Build',
        'status' => 'Live · javalearn.site',
        'problem' => 'Beginners learning Java usually end up with ten tabs open: a video '
                   . 'here, a PDF there, a forum thread that contradicts both. Concepts '
                   . 'arrive out of order, nobody checks whether you understood the last '
                   . 'one, and most material assumes you already speak programmer.',
        'solution' => 'JavaLearn Hub puts the whole beginner path in one free place: short '
                    . 'lessons in plain language with runnable examples, quizzes that check '
                    . 'you as you go, and coding exercises to actually write Java — no '
                    . 'signup, no paywall, and it works on a phone.',
        'steps'  => [
            ['num' => '01', 'title' => 'Outline',
             'text' => 'Mapped the beginner path — what is Java → variables → operators → … — and picked the topics learners get stuck on first.'],
            ['num' => '02', 'title' => 'Content',
             'text' => 'Wrote each lesson plain and short with examples you can run, then turned common mistakes into quiz questions.'],
            ['num' => '03', 'title' => 'Build',
             'text' => 'Hand-built static pages — HTML, CSS and JavaScript — so lessons load fast and behave on any screen.'],
            ['num' => '04', 'title' => 'Ship & improve',
             'text' => 'Deployed free on GitHub Pages at javalearn.site (custom domain), then kept editing whatever learners found confusing.'],
        ],
        'stats'  => [
            ['value' => '3',        'label' => 'ways to learn: lessons, quizzes, exercises'],
            ['value' => 'Free',     'label' => 'no signup, no paywall — just open the site'],
            ['value' => 'Beginner', 'label' => 'zero experience assumed, from lesson one'],
        ],
        'stack'  => ['HTML', 'CSS', 'JavaScript', 'GitHub Pages', 'Git', 'VS Code'],
        'links'  => [
            ['label' => 'Visit JavaLearn Hub', 'href' => 'https://javalearn.site'],
        ],
    ],
    [
        'num'    => '02',
        'title'  => 'WinSoles',
        'sub'    => 'Authentic deadstock sneaker marketplace',
        'image'  => 'assets/img/winsoles.png',
        'image_alt' => 'WinSoles sneaker marketplace homepage screenshot',
        'site'   => 'https://winsoles.vercel.app',
        'year'   => '2026',
        'role'   => 'Design · Build',
        'status' => 'Live · winsoles.vercel.app',
        'problem' => 'Sneaker shoppers and sellers need a clear place to discover and '
                   . 'trade deadstock shoes, while buyers also need confidence in the '
                   . 'authenticity of what they purchase.',
        'solution' => 'WinSoles presents a sneaker marketplace for browsing and selling '
                    . 'authentic deadstock shoes, with product search and market details '
                    . 'such as bids, asks, and recent sales.',
        'steps'  => [
            ['num' => '01', 'title' => 'Plan',
             'text' => 'Shape the experience around sneaker discovery, marketplace listings, and trust in authenticity.'],
            ['num' => '02', 'title' => 'Design',
             'text' => 'Give the marketplace a clean storefront, clear navigation, and prominent product imagery.'],
            ['num' => '03', 'title' => 'Build',
             'text' => 'Bring browsing, search, and seller entry points together in one marketplace interface.'],
            ['num' => '04', 'title' => 'Launch',
             'text' => 'Publish WinSoles online so visitors can explore the live marketplace.'],
        ],
        'stack'  => ['Sneaker marketplace', 'Product search', 'Responsive design'],
        'links'  => [
            ['label' => 'Visit WinSoles', 'href' => 'https://winsoles.vercel.app'],
        ],
    ],
];
?>

<!-- ============ PAGE HEAD ============ -->
<section class="page-head">
  <p class="ghost-num" aria-hidden="true">03</p>
  <div class="wrap">
    <p class="eyebrow reveal">Projects</p>
    <h1 class="reveal">One project,<br>done <span class="hl">properly</span>.</h1>
    <p class="lead reveal">
      Explore the projects I've built, from beginner-friendly learning tools to a sneaker marketplace.
    </p>
  </div>
</section>

<!-- ============ CASE STUDIES (PHP foreach) ============ -->
<section class="section section--tight-top">
  <div class="wrap">

    <?php foreach ($projects as $project): ?>
      <article class="case">

        <div class="case__top">
          <div class="case__id">
            <p class="case__num"><?= htmlspecialchars($project['num']) ?></p>
            <h2 class="case__title"><?= htmlspecialchars($project['title']) ?></h2>
            <p class="case__sub"><?= htmlspecialchars($project['sub']) ?></p>
          </div>

          <dl class="meta reveal">
            <div class="meta__row"><dt>Year</dt><dd><?= htmlspecialchars($project['year']) ?></dd></div>
            <div class="meta__row"><dt>Role</dt><dd><?= htmlspecialchars($project['role']) ?></dd></div>
            <div class="meta__row"><dt>Status</dt><dd><?= htmlspecialchars($project['status']) ?></dd></div>
          </dl>
        </div>

        <div class="case__shot reveal">
          <figure>
            <a class="case__shot-link" href="<?= htmlspecialchars($project['site']) ?>" target="_blank" rel="noopener"
               aria-label="Visit <?= htmlspecialchars($project['title']) ?>">
              <img src="<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['image_alt']) ?>"
                   onerror="this.closest('figure').remove()">
            </a>
          </figure>
        </div>

        <!-- Problem / solution: 1 column on phone, 2 on desktop -->
        <div class="split">
          <div class="panel reveal">
            <h3>The problem</h3>
            <p><?= htmlspecialchars($project['problem']) ?></p>
          </div>
          <div class="panel reveal">
            <h3>What I built</h3>
            <p><?= htmlspecialchars($project['solution']) ?></p>
          </div>
        </div>

        <!-- Process: 1 column → 2 columns → 4 columns -->
        <h3 class="case__label reveal">Process</h3>
        <div class="process">
          <?php foreach ($project['steps'] as $step): ?>
            <div class="step reveal">
              <p class="step__num"><?= htmlspecialchars($step['num']) ?></p>
              <h4><?= htmlspecialchars($step['title']) ?></h4>
              <p><?= htmlspecialchars($step['text']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Results: 1 column → 3 columns -->
        <?php if (!empty($project['stats'])): ?>
          <h3 class="case__label reveal">Results</h3>
          <div class="stats">
            <?php foreach ($project['stats'] as $stat): ?>
              <div class="stat reveal">
                <b><?= htmlspecialchars($stat['value']) ?></b>
                <span><?= htmlspecialchars($stat['label']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Stack + links -->
        <div class="case__foot reveal">
          <div class="tag-row">
            <?php foreach ($project['stack'] as $tech): ?>
              <span class="tag"><?= htmlspecialchars($tech) ?></span>
            <?php endforeach; ?>
          </div>
          <div class="case__links">
            <?php foreach ($project['links'] as $link): ?>
              <a class="btn btn--ghost" href="<?= htmlspecialchars($link['href']) ?>" target="_blank" rel="noopener">
                <?= htmlspecialchars($link['label']) ?> <span aria-hidden="true">↗</span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

      </article>
    <?php endforeach; ?>

    <!-- ============ GROWING GRID: honest placeholders ============ -->
    <div class="more">
      <article class="more-card more-card--now reveal">
        <p class="section__num">03 — In progress</p>
        <h3>This portfolio</h3>
        <p>
          Build number two: hand-written PHP includes, media queries down to 320px,
          a blue-lavender design system with dark/light themes — and the playground
          tab you can try next.
        </p>
        <div class="tag-row">
          <span class="tag">PHP</span>
          <span class="tag">CSS</span>
          <span class="tag">JavaScript</span>
        </div>
      </article>

      <article class="more-card reveal">
        <p class="section__num">04 — Next up</p>
        <h3>JavaLearn Hub, more of it</h3>
        <p>
          New lessons and quizzes land on JavaLearn Hub one at a time. More projects can
          be showcased by adding another entry to <code>$projects</code> at the top of
          this file.
        </p>
        <p class="marker-note">One strong project beats five I can't defend.</p>
      </article>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
