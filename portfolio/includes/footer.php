<?php
/* ============================================================
   includes/footer.php — loaded at the BOTTOM of every page.
   $nav, $initials and $siteName were defined in header.php,
   which always runs first — so the footer menu is literally
   the same array as the header menu. One source of truth.
   ============================================================ */
?>
</main>

<footer class="footer">
  <div class="wrap footer__grid">

    <div class="footer__brand">
      <a class="logo" href="index.php">
        <span class="logo__mark"><?= htmlspecialchars($initials) ?></span>
        <span class="logo__name"><?= htmlspecialchars($siteName) ?></span>
      </a>
      <p class="footer__blurb">I build responsive, no-fuss websites with PHP, CSS and a stubborn attention to detail.</p>
    </div>

    <nav class="footer__col" aria-label="Footer">
      <p class="footer__label">Pages</p>
      <ul>
        <?php foreach ($nav as $item): ?>
          <li><a href="<?= htmlspecialchars($item['href']) ?>"><?= htmlspecialchars($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="footer__col">
      <p class="footer__label">Say hello</p>
      <ul>
        <li><a href="mailto:hello@example.com">harveydangate@gmail.com</a></li> <!-- ✏️ your email -->
        <li><a href="https://github.com/harveydangate-cell" target="_blank" rel="noopener">GitHub</a></li> <!-- ✏️ your GitHub -->
      </ul>
    </div>

  </div>

  <div class="wrap footer__bottom">
    <p>© <?= date('Y') ?> <?= htmlspecialchars($siteName) ?> · All rights reserved.</p>
    <p class="footer__note">Hand-built with PHP — no frameworks, no page builders.</p>
  </div>
</footer>

<script src="assets/js/main.js?v=17" defer></script>
</body>
</html>
