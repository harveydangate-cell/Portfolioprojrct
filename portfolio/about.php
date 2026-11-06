<?php
/* ============================================================
   about.php — ABOUT
   ============================================================ */

$pageTitle  = 'About';
$activePage = 'about';
require __DIR__ . '/includes/header.php';

$tools    = [
    ['name' => 'VS Code',          'icon' => 'visualstudiocode'],
    ['name' => 'IntelliJ IDEA',    'icon' => 'intellijidea'],
    ['name' => 'GitHub Pages',     'icon' => 'githubpages'],
    ['name' => 'XAMPP',            'icon' => 'xampp'],
    ['name' => 'Git',              'icon' => 'git'],
    ['name' => 'Chrome DevTools',  'icon' => 'googlechrome'],
    ['name' => 'Figma',            'icon' => 'figma'],
];
$learning = ['Java collections', 'Spring Boot basics', 'PHP OOP patterns', 'DSA practice'];
?>

<!-- ============ PAGE HEAD ============ -->
<section class="page-head">
  <p class="ghost-num" aria-hidden="true">02</p>
  <div class="wrap">
    <p class="eyebrow reveal">About</p>
    <h1 class="reveal">The human<br>behind the <span class="hl">markup</span>.</h1>
    <p class="lead reveal">
      Two columns on a laptop, one column on a phone — same content, no broken text.
      Here's who I am and the tools I use to build useful things.
    </p>
  </div>
</section>

<!-- ============ 01 · ABOUT ============ -->
<section class="section">
  <div class="wrap about__grid">

    <!-- Photo slot: overwrite assets/img/photo.jpg with your own photo (see assets/img/README.md) -->
    <figure class="portrait reveal">
      <img class="portrait__img" src="assets/img/photo.png" alt="John Kim Harvey Dangate"
           onerror="this.remove()">
      <span class="portrait__mono" aria-hidden="true">JD</span>
      <figcaption class="portrait__cap">John K. H. Dangate · 2026</figcaption>
    </figure>

    <div class="bio reveal">
      <p class="lead">
        I'm <strong>John Kim Harvey Dangate</strong> — 23, a BSIT 3-6 student at
        Pamantasan ng Lungsod ng Valenzuela who learns by building things.
      </p>
      <p>
        It started with Java. I was teaching myself the language and bouncing between
        ten tabs to understand a single concept, so I started cleaning up my own notes —
        simpler lessons, runnable examples, questions to check myself. That turned into
        <strong>JavaLearn Hub</strong>, a free tutorial site where beginners get lessons,
        quizzes and coding exercises in one place. Shipping it taught me more about
        structure, clarity and real users than any textbook did.
      </p>
      <p>
        Since then I've been building the same way: small, useful, mobile-first. Outside
        the editor I'm rewriting quiz questions until they're fair, sketching layouts I
        never follow, and explaining to friends why their website “works on my machine”.
      </p>

      <dl class="facts">
        <div class="facts__row"><dt>Name</dt><dd>John Kim Harvey Dangate</dd></div>
        <div class="facts__row"><dt>Age</dt><dd>23</dd></div>
        <div class="facts__row"><dt>Course</dt><dd>BSIT · Section 3-6</dd></div>
        <div class="facts__row"><dt>School</dt><dd>Pamantasan ng Lungsod ng Valenzuela</dd></div>
        <div class="facts__row"><dt>Looking for</dt><dd>Internship · Junior role</dd></div>
      </dl>
    </div>

  </div>
</section>

<!-- ============ 02 · TOOLS + NOW LEARNING ============ -->
<section class="section section--tight-top">
  <div class="wrap tools__grid">

    <div class="panel reveal">
      <h3>Tools I use</h3>
      <div class="tag-row">
        <?php foreach ($tools as $tool): ?>
          <span class="tag tool-chip">
            <img class="tool-chip__logo"
                 src="https://cdn.simpleicons.org/<?= rawurlencode($tool['icon']) ?>"
                 alt="" width="16" height="16" loading="lazy" decoding="async">
            <?= htmlspecialchars($tool['name']) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="panel reveal">
      <h3>Now learning</h3>
      <div class="tag-row">
        <?php foreach ($learning as $item): ?>
          <span class="tag"><?= htmlspecialchars($item) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="panel panel--marker reveal">
      <p class="marker-note">"The best skill is knowing how to find the answer at 2 AM."</p>
      <a class="btn btn--ghost" href="projects.php">See it applied <span aria-hidden="true">→</span></a>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
