<?php
require_once __DIR__ . '/includes/functions.php';
$c = load_site_content();
$u = $c['ueber_mich'];
$site = $c['site'];
$page_title = $c['nav']['ueber_mich'] . ' - ' . $site['practice_name'];
?>
<!DOCTYPE html>
<html <?php echo html_attrs(); ?>>
<head>
<meta charset="UTF-8">
<link rel="icon" type="<?php echo favicon_mime($c['site']['favicon'] ?? 'pngegg.png'); ?>" href="<?php echo e($c['site']['favicon'] ?? 'pngegg.png'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?></title>
<meta name="description" content="<?php echo e(ui('meta_about', ['doctor' => $site['doctor_name']])); ?>">
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/style.css'); ?>">
<?php echo color_overrides_style($c['site']['colors'] ?? []); ?>
</head>
<body>

<?php require __DIR__ . '/includes/header.php'; ?>

<section class="section section-tight" style="background:var(--grad-bg);">
  <div class="container text-center reveal in-view">
    <span class="hero-badge"><?php echo icon_svg('sparkles', 16); ?> <?php echo x($u['intro_title']); ?></span>
    <h1><?php echo x($u['hero_title']); ?></h1>
  </div>
</section>

<section class="section" style="background:#fff;">
  <div class="container grid-2">
    <div class="reveal-left in-view">
      <div class="doctor-photo-wrap">
        <?php if (!empty($u['photo'])): ?>
          <img src="<?php echo e($u['photo']); ?>" alt="<?php echo e($site['doctor_name']); ?>">
        <?php else: ?>
          <div class="doctor-photo-placeholder">
            <?php echo icon_svg('users', 64); ?>
            <span style="font-weight:700;font-size:1.1rem;color:var(--blue);"><?php echo e($site['doctor_name']); ?></span>
            <span style="font-size:.85rem;"><?php echo x($site['doctor_title']); ?></span>
          </div>
        <?php endif; ?>
      </div>

      <div style="margin-top:2rem;">
        <h3 style="font-size:1.1rem;"><?php echo x($u['credentials_title']); ?></h3>
        <ul class="credential-list">
          <?php foreach ($u['credentials'] as $cred): ?>
            <li><?php echo icon_svg('award', 20); ?> <span><?php echo x($cred); ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="reveal-right in-view">
      <h2 style="font-size:1.1rem;color:var(--teal);text-transform:uppercase;letter-spacing:.05em;"><?php echo e($site['doctor_name']); ?> — <?php echo x($site['doctor_title']); ?></h2>
      <?php foreach ($u['paragraphs'] as $p): ?>
        <p style="font-size:1.02rem;"><?php echo x($p); ?></p>
      <?php endforeach; ?>

      <?php if (trim($u['quote']) !== ''): ?>
      <div class="quote-box">
        <p><?php echo is_rtl_lang(current_lang()) ? '«' . x($u['quote']) . '»' : '"' . x($u['quote']) . '"'; ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--bg);">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($u['features'] as $f): ?>
        <div class="feature-card reveal in-view">
          <div class="icon-circle alt"><?php echo icon_svg($f['icon'] ?? 'sparkles', 28); ?></div>
          <h3><?php echo x($f['title']); ?></h3>
          <p><?php echo x($f['desc']); ?></p>
        </div>
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
