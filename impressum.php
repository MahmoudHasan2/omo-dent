<?php
require_once __DIR__ . '/includes/functions.php';
$c = load_site_content();
$im = $c['impressum'];
$page_title = $im['title'] . ' - ' . $c['site']['practice_name'];
?>
<!DOCTYPE html>
<html <?php echo html_attrs(); ?>>
<head>
<meta charset="UTF-8">
<link rel="icon" type="<?php echo favicon_mime($c['site']['favicon'] ?? 'pngegg.png'); ?>" href="<?php echo e($c['site']['favicon'] ?? 'pngegg.png'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?></title>
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/style.css'); ?>">
<?php echo color_overrides_style($c['site']['colors'] ?? []); ?>
</head>
<body>
<?php require __DIR__ . '/includes/header.php'; ?>
<section class="section section-tight" style="background:var(--grad-bg);">
  <div class="container text-center reveal in-view"><h1><?php echo x($im['title']); ?></h1></div>
</section>
<section class="section" style="background:#fff;">
  <div class="container legal-content reveal in-view" style="max-width:820px;margin:0 auto;">
    <?php echo render_blocks($im['blocks']); ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
