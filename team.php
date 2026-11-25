<?php
require_once __DIR__ . '/includes/functions.php';
$c = load_site_content();
$t = $c['team'];
$site = $c['site'];
$page_title = $t['hero_title'] . ' - ' . $site['practice_name'];
?>
<!DOCTYPE html>
<html <?php echo html_attrs(); ?>>
<head>
<meta charset="UTF-8">
<link rel="icon" type="<?php echo favicon_mime($c['site']['favicon'] ?? 'pngegg.png'); ?>" href="<?php echo e($c['site']['favicon'] ?? 'pngegg.png'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?></title>
<meta name="description" content="<?php echo e(ui('meta_team')); ?>">
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/style.css'); ?>">
<?php echo color_overrides_style($c['site']['colors'] ?? []); ?>
</head>
<body>

<?php require __DIR__ . '/includes/header.php'; ?>

<section class="section section-tight" style="background:var(--grad-bg);position:relative;overflow:hidden;">
  <div class="deco-blob" style="width:20rem;height:20rem;background:var(--teal);top:-4rem;right:-4rem;"></div>
  <div class="tooth-pattern-bg" style="opacity:.5;"></div>
  <div class="container text-center reveal in-view" style="max-width:760px;margin:0 auto;position:relative;z-index:1;">
    <span class="hero-badge"><?php echo icon_svg('users', 16); ?> <?php echo x($c['nav']['team']); ?></span>
    <h1><?php echo x($t['hero_title']); ?></h1>
    <?php if (!empty($t['hero_text'])): ?>
      <p style="font-size:1.1rem;"><?php echo x($t['hero_text']); ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section" style="background:#fff;">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($t['members'] as $m): ?>
        <?php $displayName = !empty($m['name']) ? $m['name'] : $m['role']; ?>
        <div class="team-card reveal in-view">
          <div class="img-wrap" style="aspect-ratio:4/5;">
            <?php if (!empty($m['photo'])): ?>
              <img src="<?php echo e($m['photo']); ?>" alt="<?php echo e($displayName); ?>" loading="lazy">
            <?php else: ?>
              <div class="doctor-photo-placeholder" style="height:100%;">
                <?php echo icon_svg('users', 40); ?>
                <span style="font-weight:600;text-align:center;padding:0 1rem;"><?php echo x($displayName); ?></span>
              </div>
            <?php endif; ?>
          </div>
          <div class="body">
            <?php if (!empty($m['name'])): ?>
              <h3 style="margin-bottom:.2rem;"><?php echo e($m['name']); ?></h3>
              <?php if (!empty($m['role'])): ?><p class="stat-label" style="color:var(--teal);font-weight:700;margin-bottom:.6rem;"><?php echo x($m['role']); ?></p><?php endif; ?>
            <?php else: ?>
              <h3 style="margin-bottom:.6rem;font-size:1rem;"><?php echo x($m['role']); ?></h3>
            <?php endif; ?>
            <?php if (!empty($m['description'])): ?>
              <p style="font-size:.92rem;margin:0;"><?php echo x($m['description']); ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--bg);">
  <div class="container">
    <div class="cta-banner reveal in-view">
      <div class="cta-banner-text">
        <?php if (!empty($c['ueber_mich']['photo'])): ?>
          <div class="cta-banner-photo"><img src="<?php echo e($c['ueber_mich']['photo']); ?>" alt="<?php echo e($c['site']['doctor_name']); ?>"></div>
        <?php endif; ?>
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
