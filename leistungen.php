<?php
require_once __DIR__ . '/includes/functions.php';
$c = load_site_content();
$l = $c['leistungen'];
$page_title = $c['nav']['leistungen'] . ' - ' . $c['site']['practice_name'];
?>
<!DOCTYPE html>
<html <?php echo html_attrs(); ?>>
<head>
<meta charset="UTF-8">
<link rel="icon" type="<?php echo favicon_mime($c['site']['favicon'] ?? 'pngegg.png'); ?>" href="<?php echo e($c['site']['favicon'] ?? 'pngegg.png'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?></title>
<meta name="description" content="<?php echo e(ui('meta_services')); ?>">
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/style.css'); ?>">
<?php echo color_overrides_style($c['site']['colors'] ?? []); ?>
</head>
<body>

<?php require __DIR__ . '/includes/header.php'; ?>

<section class="section section-tight" style="background:var(--grad-bg);position:relative;overflow:hidden;">
  <div class="deco-blob" style="width:20rem;height:20rem;background:var(--teal);top:-4rem;right:-4rem;"></div>
  <div class="tooth-pattern-bg" style="opacity:.5;"></div>
  <div class="container text-center reveal in-view" style="max-width:820px;margin:0 auto;position:relative;z-index:1;">
    <span class="hero-badge"><?php echo icon_svg('sparkles', 16); ?> <?php echo x($c['nav']['leistungen']); ?></span>
    <h1><?php echo x($l['hero_title']); ?></h1>
    <p style="font-size:1.1rem;"><?php echo x($l['hero_text']); ?></p>
  </div>
</section>

<section class="section" style="background:#fff;">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($l['services'] as $si => $service): ?>
        <div class="service-card reveal in-view">
          <div class="service-img">
            <img src="<?php echo e($service['image']); ?>" alt="<?php echo e($service['title']); ?>" loading="lazy">
          </div>
          <h3 style="margin-bottom:.4rem;"><?php echo x($service['title']); ?></h3>
          <?php if (!empty($service['subtitle'])): ?>
            <p style="font-size:.88rem;color:var(--teal);font-weight:600;margin-bottom:.9rem;"><?php echo x($service['subtitle']); ?></p>
          <?php endif; ?>
          <div>
            <?php foreach ($service['faqs'] as $faq): if (trim($faq['q']) === '') continue; ?>
              <div class="faq-item">
                <button type="button" class="faq-q">
                  <span><?php echo x($faq['q']); ?></span>
                  <?php echo icon_svg('chevron-down', 16); ?>
                </button>
                <div class="faq-a"><div class="faq-a-inner"><?php echo x($faq['a']); ?></div></div>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="leistung-detail.php?s=<?php echo (int)$si; ?>" class="btn btn-outline" style="margin-top:1.1rem;width:100%;justify-content:center;padding:.65rem;font-size:.85rem;"><?php echo x(ui('read_more')); ?> <?php echo icon_svg('arrow-right', 16); ?></a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--bg);">
  <div class="container grid-2">
    <div class="reveal-left in-view" style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-hover);">
      <img src="<?php echo e($l['tech_image']); ?>" alt="<?php echo e($l['tech_title']); ?>">
    </div>
    <div class="reveal-right in-view">
      <?php if (!empty($l['tech_image_secondary'])): ?>
        <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-hover);margin-bottom:2rem;">
          <img src="<?php echo e($l['tech_image_secondary']); ?>" alt="<?php echo e($l['tech_title']); ?>">
        </div>
      <?php endif; ?>
      <h2><?php echo x($l['tech_title']); ?></h2>
      <?php foreach ($l['tech_paragraphs'] as $p): ?>
        <p><?php echo x($p); ?></p>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--bg);">
  <div class="container">
    <div class="cta-banner reveal in-view">
      <div class="cta-banner-text">
        <div>
          <h3><?php echo x($c['site']['footer_tagline']); ?></h3>
          <span class="cta-phone"><?php echo icon_svg('phone', 18); ?> <a href="<?php echo e(tel_href($c['site']['phone'])); ?>" style="color:inherit;text-decoration:none;"><?php echo ltr($c['site']['phone']); ?></a></span>
          <?php if (!empty($c['site']['fax'])): ?><span class="cta-phone contact-fax"><?php echo icon_svg('printer', 16); ?> <?php echo x(ui('label_fax')); ?>: <?php echo ltr($c['site']['fax']); ?></span><?php endif; ?>
        </div>
      </div>
      <a href="index.php#kontakt" class="btn"><?php echo x($c['home']['hero_button']); ?> <?php echo icon_svg('arrow-right', 20); ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
