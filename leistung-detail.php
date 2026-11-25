<?php
require_once __DIR__ . '/includes/functions.php';
$c = load_site_content();
$l = $c['leistungen'];
$services = $l['services'];
$s = isset($_GET['s']) ? (int)$_GET['s'] : -1;
if ($s < 0 || !isset($services[$s])) {
  header('Location: leistungen.php');
  exit;
}
$service = $services[$s];
$gallery = $service['gallery'] ?? [];
$faqs = array_values(array_filter($service['faqs'] ?? [], function ($f) { return trim($f['q']) !== ''; }));
$page_title = $service['title'] . ' - ' . $c['site']['practice_name'];
$page_desc = $service['summary'] !== '' ? $service['summary'] : $service['title'];
?>
<!DOCTYPE html>
<html <?php echo html_attrs(); ?>>
<head>
<meta charset="UTF-8">
<link rel="icon" type="<?php echo favicon_mime($c['site']['favicon'] ?? 'pngegg.png'); ?>" href="<?php echo e($c['site']['favicon'] ?? 'pngegg.png'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?></title>
<meta name="description" content="<?php echo e($page_desc); ?>">
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/style.css'); ?>">
<?php echo color_overrides_style($c['site']['colors'] ?? []); ?>
</head>
<body>
<?php require __DIR__ . '/includes/header.php'; ?>

<section class="section section-tight" style="background:var(--grad-bg);">
  <div class="container">
    <a href="leistungen.php" style="display:inline-flex;align-items:center;gap:.4rem;font-size:.9rem;color:var(--text-2);text-decoration:none;margin-bottom:1.5rem;font-weight:600;">
      <?php echo icon_svg('arrow-left', 16); ?>
      <?php echo x(ui('back_services')); ?>
    </a>
    <div class="text-center reveal in-view" style="max-width:820px;margin:0 auto;">
      <h1><?php echo x($service['title']); ?></h1>
      <?php if (!empty($service['subtitle'])): ?>
        <p style="font-size:1.15rem;color:var(--teal);font-weight:600;"><?php echo x($service['subtitle']); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if (!empty($service['what_is']) || !empty($service['approach'])): ?>
<section class="section" style="background:#fff;">
  <div class="container" style="max-width:760px;margin:0 auto;">
    <?php if (!empty($service['what_is'])): ?>
      <h2 style="font-size:1.4rem;"><?php echo x(ui('what_is', ['title' => $service['title']])); ?></h2>
      <p style="font-size:1.03rem;margin-bottom:2rem;"><?php echo x($service['what_is']); ?></p>
    <?php endif; ?>
    <?php if (!empty($service['approach'])): ?>
      <h2 style="font-size:1.4rem;"><?php echo x(ui('our_approach')); ?></h2>
      <p style="font-size:1.03rem;"><?php echo x($service['approach']); ?></p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($gallery)): ?>
<section class="section" style="background:var(--bg);">
  <div class="container">
    <div class="text-center reveal in-view" style="max-width:640px;margin:0 auto 3rem;">
      <h2><?php echo x(ui('gallery_title')); ?></h2>
      <p><?php echo x(ui('gallery_sub', ['title' => $service['title']])); ?></p>
    </div>
    <div class="grid-3">
      <?php foreach ($gallery as $img): ?>
        <div class="team-card reveal in-view">
          <div class="img-wrap" style="aspect-ratio:4/3;">
            <img src="<?php echo e($img['image']); ?>" alt="<?php echo e($service['title']); ?>" loading="lazy">
          </div>
          <?php if (!empty($img['caption'])): ?>
            <div class="body">
              <p style="font-size:.92rem;margin:0;"><?php echo x($img['caption']); ?></p>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($faqs)): ?>
<section class="section" style="background:#fff;">
  <div class="container" style="max-width:760px;margin:0 auto;">
    <h2 style="font-size:1.4rem;margin-bottom:1.2rem;"><?php echo x(ui('faq_title')); ?></h2>
    <?php foreach ($faqs as $faq): ?>
      <div class="faq-item">
        <button type="button" class="faq-q">
          <span><?php echo x($faq['q']); ?></span>
          <?php echo icon_svg('chevron-down', 16); ?>
        </button>
        <div class="faq-a"><div class="faq-a-inner"><?php echo x($faq['a']); ?></div></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

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
