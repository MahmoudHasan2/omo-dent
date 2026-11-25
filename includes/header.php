<?php
require_once __DIR__ . '/functions.php';
$c = load_site_content();
$site = $c['site'];
$nav = $c['nav'];
?>
<header class="site-header">

  <div class="container">
    <div class="nav-wrap">
      <a href="index.php" class="logo-lockup" aria-label="<?php echo e($site['practice_name']); ?>">
        <?php if (!empty($site['logo_image'])): ?>
          <img class="logo-image-custom" src="<?php echo e($site['logo_image']); ?>" alt="<?php echo e($site['practice_name']); ?>">
        <?php else: ?>
          <svg class="logo-icon" width="46" height="46" viewBox="0 0 200 200" aria-hidden="true">
            <path d="M 92,80 C 78,55 55,32 35,32 C 20,32 15,42 22,50 C 28,57 42,56 55,48"
                  fill="none" stroke="<?php echo e($site['colors']['primary_blue']); ?>" stroke-width="8.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M 108,80 C 122,55 145,32 165,32 C 180,32 185,42 178,50 C 172,57 158,56 145,48"
                  fill="none" stroke="<?php echo e($site['colors']['primary_teal']); ?>" stroke-width="8.5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="100" cy="82" r="12.5" fill="<?php echo e($site['colors']['primary_blue']); ?>"/>
            <path d="M 38,100 C 54,124 78,128 100,112 C 122,128 146,124 162,100"
                  fill="none" stroke="<?php echo e($site['colors']['primary_blue']); ?>" stroke-width="8.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M 82,114 C 74,130 68,150 72,172 C 82,154 90,136 96,120"
                  fill="none" stroke="<?php echo e($site['colors']['primary_blue']); ?>" stroke-width="8.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M 118,114 C 126,130 132,150 128,172 C 118,154 110,136 104,120"
                  fill="none" stroke="<?php echo e($site['colors']['primary_teal']); ?>" stroke-width="8.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span class="logo-wordmark">
            <span class="word"><span class="o1"><?php echo e($site['logo_text_1']); ?></span><span class="o2"><?php echo e($site['logo_text_2']); ?></span></span>
            <span class="tagline"><?php echo x($site['practice_tagline']); ?></span>
          </span>
        <?php endif; ?>
      </a>

      <nav class="main-nav">
        <a href="index.php" class="<?php echo nav_active('index.php'); ?>"><?php echo x($nav['home']); ?></a>
        <a href="leistungen.php" class="<?php echo nav_active('leistungen.php'); ?>"><?php echo x($nav['leistungen']); ?></a>
        <a href="ueber-mich.php" class="<?php echo nav_active('ueber-mich.php'); ?>"><?php echo x($nav['ueber_mich']); ?></a>
        <a href="team.php" class="<?php echo nav_active('team.php'); ?>"><?php echo x($nav['team']); ?></a>
        <a href="index.php#kontakt" class="btn" style="padding:.7rem 1.4rem;font-size:.88rem;"><?php echo icon_svg('phone', 16); ?> <?php echo x(ui('nav_appointment')); ?></a>
      </nav>

      <div class="lang-switcher">
        <?php $curLang = current_lang(); foreach (switcher_langs() as $code => $label): ?>
          <a href="<?php echo e(lang_url($code)); ?>" class="lang-btn <?php echo $curLang === $code ? 'active' : ''; ?>" hreflang="<?php echo e($code); ?>"><?php echo e($label); ?></a>
        <?php endforeach; ?>
      </div>

      <button class="mobile-toggle" aria-label="<?php echo e(ui('menu_toggle')); ?>">
        <?php echo icon_svg('menu', 24); ?>
      </button>
    </div>

    <div class="mobile-nav">
      <a href="index.php" class="<?php echo nav_active('index.php'); ?>"><?php echo x($nav['home']); ?></a>
      <a href="leistungen.php" class="<?php echo nav_active('leistungen.php'); ?>"><?php echo x($nav['leistungen']); ?></a>
      <a href="ueber-mich.php" class="<?php echo nav_active('ueber-mich.php'); ?>"><?php echo x($nav['ueber_mich']); ?></a>
      <a href="team.php" class="<?php echo nav_active('team.php'); ?>"><?php echo x($nav['team']); ?></a>
      <a href="index.php#kontakt"><?php echo e($site['practice_name']); ?> — <?php echo x(ui('nav_appointment')); ?></a>
    </div>
  </div>
</header>
