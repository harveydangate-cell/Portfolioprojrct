<?php
/* ============================================================
   includes/header.php — loaded at the TOP of every page
   ------------------------------------------------------------
   Why this file exists: the navigation, <head> and logo are
   written ONCE here, so index / about / projects can never
   drift apart. Edit the two config lines below and the whole
   site updates at once.

   Each page must set $pageTitle and $activePage BEFORE
   requiring this file, e.g.:
       $pageTitle  = 'Home';
       $activePage = 'home';
       require __DIR__ . '/includes/header.php';
   ============================================================ */

/* ---------- 1. Site config (edit these two lines) ---------- */
$siteName = 'John Kim Harvey Dangate'; /* full name — nav hides it on tiny screens */
$initials = 'JD';                      /* 2-letter logo badge                      */

/* ---------- 2. Navigation: one array drives EVERY page ------ */
$nav = [
    'home'     => ['label' => 'Home',     'href' => 'index.php'],
    'about'    => ['label' => 'About',    'href' => 'about.php'],
    'projects'   => ['label' => 'Projects',   'href' => 'projects.php'],
    'playground' => ['label' => 'Playground', 'href' => 'playground.php'],
];

/* ---------- 3. Fallbacks if a page forgot to set them ------- */
$pageTitle  = $pageTitle  ?? 'Home';
$activePage = $activePage ?? 'home';
$fullTitle  = $pageTitle . ' · ' . $siteName;
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($fullTitle) ?></title>
<meta name="description" content="Portfolio of <?= htmlspecialchars($siteName) ?> — BSIT 3-6 student at Pamantasan ng Lungsod ng Valenzuela, creator of JavaLearn Hub.">
<meta name="theme-color" content="#0c0c0c">
<script>
  /* Swap no-js → js (CSS only hides animations when .js exists)
     and restore the saved dark/light theme before first paint. */
  document.documentElement.classList.replace('no-js', 'js');
  document.documentElement.dataset.theme = localStorage.getItem('theme') || 'dark';
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800;900&family=Bungee&family=Permanent+Marker&display=swap">
<link rel="stylesheet" href="assets/css/style.css?v=23">
</head>
<body>

<a class="skip-link" href="#main">Skip to content</a>
<div class="grain" aria-hidden="true"></div>
<div class="progress" aria-hidden="true"></div>
<canvas class="scroll-thread" aria-hidden="true"></canvas>

<header class="header" id="top">
  <div class="wrap header__inner">

    <!-- Logo: same on every page -->
    <a class="logo" href="index.php" aria-label="<?= htmlspecialchars($siteName) ?> — home">
      <span class="logo__mark"><?= htmlspecialchars($initials) ?></span>
      <span class="logo__name"><?= htmlspecialchars($siteName) ?></span>
    </a>

    <!-- Mobile menu button (hidden on desktop by CSS) -->
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
      <span class="nav-toggle__box" aria-hidden="true"><i></i><i></i></span>
      <span class="nav-toggle__label">Menu</span>
    </button>

    <!-- Nav: rendered from the $nav array, active link from $activePage -->
    <nav class="nav" id="site-nav" aria-label="Main">
      <ul class="nav__list">
        <?php foreach ($nav as $key => $item):
            $isActive = $activePage === $key;
            $linkAttr = $isActive ? ' class="is-active" aria-current="page"' : '';
        ?>
          <li>
            <a href="<?= htmlspecialchars($item['href']) ?>"<?= $linkAttr ?>>
              <?= htmlspecialchars($item['label']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <button class="theme-toggle" type="button" aria-pressed="false" aria-label="Switch between dark and light theme">
        <span aria-hidden="true">◐</span>
      </button>
    </nav>

  </div>
</header>

<main id="main">
