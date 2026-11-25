<?php
require_once __DIR__ . '/functions.php';
$c = load_content();
$site = $c['site'];
?>
<div class="loader" id="loader">
  <div class="loader-content">
    <div class="loader-logo">
      <?php if (!empty($site['logo_image'])): ?>
        <img src="<?php echo e($site['logo_image']); ?>" alt="<?php echo e($site['practice_name']); ?>" style="width:100%;height:100%;object-fit:contain;">
      <?php else: ?>
        <svg viewBox="0 0 200 200" aria-hidden="true">
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
      <?php endif; ?>
    </div>
    <div class="loader-text"><?php echo e($site['practice_name']); ?></div>
    <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  </div>
</div>
<noscript><style>.loader { display: none !important; }</style></noscript>