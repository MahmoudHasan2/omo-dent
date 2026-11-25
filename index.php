<?php
require_once __DIR__ . '/includes/functions.php';
$c = load_site_content();
$site = $c['site'];
$h = $c['home'];
$pdf = $c['pdf_downloads'];
$page_title = $site['practice_name'] . ' – ' . $site['practice_tagline'];
$page_desc = $h['hero_text'];

// Split the hero heading so the last word/phrase can be highlighted in teal,
// matching the reference designs' two-tone headline style.
$titleWords = explode(' ', $h['hero_title']);
$titleAccent = array_pop($titleWords);
$titleRest = implode(' ', $titleWords);
?>
<!DOCTYPE html>
<html <?php echo html_attrs(); ?>>
<head>
<meta charset="UTF-8">
<link rel="icon" type="<?php echo favicon_mime($c['site']['favicon'] ?? 'pngegg.png'); ?>" href="<?php echo e($c['site']['favicon'] ?? 'pngegg.png'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?></title>
<meta name="description" content="<?php echo e($page_desc); ?>">

<!-- ===== LOADING SCREEN STYLES (inline so it shows instantly) ===== -->
<style>
/* ---- LOADER ---- */
.site-loader {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    transition: opacity 0.9s ease, visibility 0.9s ease;
}
.site-loader.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}
.site-loader-content {
    text-align: center;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}

/* ---- LOGO ---- */
.site-loader-logo {
    width: 220px;
    height: 220px;
    margin: 0 auto 8px;
    opacity: 0;
    transform: scale(0.6);
    animation: logoReveal 1s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
.site-loader-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 8px 30px rgba(11, 60, 93, 0.18));
}
@keyframes logoReveal {
    0% { opacity: 0; transform: scale(0.6); }
    60% { opacity: 1; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
}

/* ---- TAGLINE ---- */
.site-loader-tagline {
    font-family: 'Poppins', sans-serif;
    font-size: 1.05rem;
    font-weight: 600;
    color: #0B3C5D;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 10px auto 28px;
    opacity: 0;
    transform: translateY(20px);
    animation: taglineFlash 0.8s ease-out 0.7s forwards;
    position: relative;
}
/* glow flash behind tagline */
.site-loader-tagline::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120%;
    height: 160%;
    background: radial-gradient(ellipse, rgba(46, 139, 87, 0.15) 0%, transparent 70%);
    border-radius: 50%;
    opacity: 0;
    animation: glowFlash 0.6s ease-out 0.9s forwards;
    pointer-events: none;
    z-index: -1;
}
@keyframes taglineFlash {
    0% { opacity: 0; transform: translateY(20px); letter-spacing: 8px; }
    100% { opacity: 1; transform: translateY(0); letter-spacing: 3px; }
}
@keyframes glowFlash {
    0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
    50% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
    100% { opacity: 0; transform: translate(-50%, -50%) scale(1.5); }
}

/* ---- PROGRESS BAR ---- */
.site-loader-bar {
    width: 200px;
    height: 4px;
    background: rgba(11, 60, 93, 0.08);
    margin: 0 auto;
    border-radius: 4px;
    overflow: hidden;
    opacity: 0;
    animation: barAppear 0.5s ease 1.1s forwards;
}
.site-loader-fill {
    width: 0%;
    height: 100%;
    background: linear-gradient(90deg, #0B3C5D, #2E8B57, #0B3C5D);
    background-size: 200% auto;
    border-radius: 4px;
    animation: loaderFill 1.6s ease-in-out 1.3s forwards, barShimmer 2s linear 1.3s infinite;
}
@keyframes barAppear {
    to { opacity: 1; }
}
@keyframes loaderFill {
    to { width: 100%; }
}
@keyframes barShimmer {
    0% { background-position: 200% center; }
    100% { background-position: -200% center; }
}

/* ---- PRACTICE NAME (optional, below tagline) ---- */
.site-loader-name {
    font-family: 'Poppins', sans-serif;
    font-size: 1.6rem;
    font-weight: 800;
    color: #0B3C5D;
    letter-spacing: 6px;
    text-transform: uppercase;
    margin-bottom: 6px;
    opacity: 0;
    transform: translateY(15px);
    animation: nameReveal 0.7s ease-out 0.5s forwards;
}
@keyframes nameReveal {
    to { opacity: 1; transform: translateY(0); }
}
</style>

<link rel="stylesheet" href="assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/assets/css/style.css'); ?>">
<?php echo color_overrides_style($c['site']['colors'] ?? []); ?>
</head>
<body>

<!-- ===== LOADING SCREEN ===== -->

<?php if (!empty($site['loader_enabled'])): ?>
<div class="site-loader" id="siteLoader">
    <div class="site-loader-content">
        <div class="site-loader-logo">
            <img src="uploads/Element_2_8197e005.png" alt="<?php echo e($site['practice_name']); ?>">
        </div>
        <div class="site-loader-name"><?php echo e($site['practice_name']); ?></div>
        <div class="site-loader-tagline"><?php echo x($site['practice_tagline']); ?></div>
        <div class="site-loader-bar"><div class="site-loader-fill"></div></div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/header.php'; ?>

<!-- Hero -->
<section class="hero">
  <div class="deco-blob b1"></div>
  <div class="deco-blob b2"></div>
  <div class="container">
    <div class="hero-grid">
      <div class="reveal-left in-view">
        <span class="eyebrow"><?php echo x($h['welcome_title']); ?></span>
        <h1><?php echo x($titleRest); ?> <span class="accent-word"><?php echo x($titleAccent); ?></span></h1>
        <p style="font-size:1.1rem;max-width:56ch;"><?php echo x($h['hero_text']); ?></p>
        <div class="hero-actions" style="margin:1.75rem 0;">
          <a href="https://example.com/booking" class="btn"><?php echo x($h['hero_button']); ?> <?php echo icon_svg('arrow-right', 20); ?></a>
          <a href="#pdf-modal" id="pdf-modal-trigger" class="btn btn-outline">
            <?php echo icon_svg('download', 20); ?> <?php echo x($pdf['button_text']); ?>
          </a>
        </div>
        <div class="hero-bullets">
          <?php foreach ($h['features'] as $f): ?>
            <span><span class="dot"></span> <?php echo x($f['title']); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="reveal-right in-view">
        <div class="hero-photo-frame">
          <svg class="hero-sparkle" style="top:-1.2rem;right:1rem;" width="30" height="30" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.6 5.2L19 9l-5.4 1.8L12 16l-1.6-5.2L5 9l5.4-1.8L12 2z"/></svg>
          <svg class="hero-sparkle" style="bottom:2rem;right:-.8rem;" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.6 5.2L19 9l-5.4 1.8L12 16l-1.6-5.2L5 9l5.4-1.8L12 2z"/></svg>
          <div class="hero-img-wrap">
            <img src="<?php echo e($h['hero_image']); ?>" alt="<?php echo e($site['practice_name']); ?> — <?php echo e(ui('hero_img_alt')); ?>">
          </div>
          <div class="hero-stat-badge">
            <div class="stat-num"><?php echo count($c['leistungen']['services']); ?>+</div>
            <div class="stat-label"><?php echo x(ui('stat_services')); ?></div>
          </div>
          <div class="hero-photo-badge">
            <div class="icon-circle">
              <?php echo icon_svg('users', 20); ?>
            </div>
            <div>
              <strong><?php echo e($site['doctor_name']); ?></strong>
              <span><?php echo x($site['doctor_title']); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Marquee ticker (Leistungen) -->
<div class="marquee-bar">
  <div class="marquee-track">
    <?php
      $svcTitles = array_map(function ($s) { return $s['title']; }, $c['leistungen']['services']);
      // duplicate the list so the CSS animation (-50%) loops seamlessly
      $marqueeItems = array_merge($svcTitles, $svcTitles);
      foreach ($marqueeItems as $title): ?>
        <span><?php echo x($title); ?></span>
    <?php endforeach; ?>
  </div>
</div>

<!-- PDF-Download Modal -->
<div class="modal-overlay" id="pdf-modal">
  <div class="modal-box">
    <a href="#" class="modal-close" id="pdf-modal-close" aria-label="<?php echo e(ui('close')); ?>"><?php echo icon_svg('x', 20); ?></a>
    <h3 style="margin-bottom:1.5rem;"><?php echo x(ui('pdf_modal_title')); ?></h3>
    <div class="pdf-grid">
      <?php foreach ($pdf['items'] as $item): ?>
        <?php $hasFile = !empty($item['file']); ?>
        <?php if ($hasFile): ?>
          <a class="pdf-item" href="<?php echo e($item['file']); ?>" download>
            <span class="pdf-icon"><?php echo icon_svg($item['icon'] ?: 'file-text', 34); ?></span>
            <span class="pdf-name"><?php echo x($item['name']); ?></span>
          </a>
        <?php else: ?>
          <span class="pdf-item pdf-item-disabled" title="<?php echo e(ui('pdf_not_uploaded')); ?>">
            <span class="pdf-icon"><?php echo icon_svg($item['icon'] ?: 'file-text', 34); ?></span>
            <span class="pdf-name"><?php echo x($item['name']); ?></span>
            <span class="pdf-soon"><?php echo x(ui('pdf_soon')); ?></span>
          </span>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Philosophy / intro section -->
<section class="section section-tight" style="background:#fff;">
  <div class="container text-center reveal in-view" style="max-width:760px;margin:0 auto;">
    <span class="eyebrow" style="justify-content:center;"><?php echo x($h['intro_eyebrow']); ?></span>
    <h2><?php echo x($h['intro_title']); ?></h2>
    <?php foreach ($h['intro_paragraphs'] as $ip): ?>
      <p style="font-size:1.03rem;"><?php echo x($ip); ?></p>
    <?php endforeach; ?>
    <p style="font-weight:700;color:var(--blue);font-family:'Poppins',sans-serif;font-size:1.05rem;margin-top:1.5rem;"><?php echo x($h['intro_closing']); ?></p>
  </div>
</section>

<!-- Features (dark strip) -->
<section class="section section-tight section-dark">
  <div class="deco-blob" style="width:20rem;height:20rem;top:-6rem;left:-4rem;"></div>
  <div class="container" style="position:relative;z-index:1;">
    <div class="grid-3">
      <?php foreach ($h['features'] as $f): ?>
        <div class="feature-card reveal in-view">
          <div class="icon-circle"><?php echo icon_svg($f['icon'], 28); ?></div>
          <h3><?php echo x($f['title']); ?></h3>
          <p><?php echo x($f['desc']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- About preview ("Where Innovation Meets Compassion" style) -->
<section class="section" style="background:#fff;">
  <div class="container">
    <div class="grid-2">
      <div class="reveal-left in-view">
        <div class="about-photo-wrap">
          <div class="doctor-photo-wrap" style="aspect-ratio:1/1;">
            <?php if (!empty($c['ueber_mich']['photo'])): ?>
              <img src="<?php echo e($c['ueber_mich']['photo']); ?>" alt="<?php echo e($site['doctor_name']); ?>">
            <?php else: ?>
              <div class="doctor-photo-placeholder">
                <?php echo icon_svg('users', 48); ?>
                <span style="font-weight:600;"><?php echo e($site['doctor_name']); ?>, <?php echo x($site['doctor_title']); ?></span>
              </div>
            <?php endif; ?>
          </div>
          <div class="about-stat-stack">
            <div class="about-stat-card">
              <div class="icon-circle alt" style="width:42px;height:42px;"><?php echo icon_svg('award', 18); ?></div>
              <div>
                <div class="stat-num"><?php echo count($c['ueber_mich']['credentials']); ?></div>
                <div class="stat-label"><?php echo x(ui('stat_qualifications')); ?></div>
              </div>
            </div>
            <div class="about-stat-card">
              <div class="icon-circle" style="width:42px;height:42px;"><?php echo icon_svg('heart', 18); ?></div>
              <div>
                <div class="stat-num" dir="ltr">1:1</div>
                <div class="stat-label"><?php echo x(ui('stat_personal')); ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="reveal-right in-view" style="padding-inline-start:1.5rem;">
        <span class="eyebrow"><?php echo x(ui('about_eyebrow')); ?></span>
        <h2><?php echo x($h['about_preview_title']); ?></h2>
        <p style="font-size:1.05rem;"><?php echo x($h['about_preview_text']); ?></p>
        <a href="ueber-mich.php" class="btn btn-outline" style="margin-top:1rem;"><?php echo x($h['about_preview_button']); ?> <?php echo icon_svg('arrow-right', 18); ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Services preview (icon-outline quick grid, like "Comprehensive Care for Every Smile") -->
<section class="section" style="background:var(--bg);position:relative;overflow:hidden;">
  <div class="tooth-pattern-bg"></div>
  <div class="container" style="position:relative;z-index:1;">
    <div class="reveal in-view" style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:1.5rem;margin-bottom:2.75rem;">
      <div>
        <span class="eyebrow" style="margin-bottom:.6rem;"><?php echo x(ui('services_eyebrow')); ?></span>
        <h2 style="margin-bottom:0;"><?php echo x($h['services_title']); ?></h2>
      </div>
      <a href="leistungen.php" style="display:inline-flex;align-items:center;gap:.4rem;font-weight:700;color:var(--teal);font-size:.92rem;"><?php echo x($h['services_link_text']); ?> <?php echo icon_svg('arrow-right', 16); ?></a>
    </div>
    <p style="font-size:1.05rem;max-width:60ch;margin-bottom:2.5rem;"><?php echo x($h['services_subtitle']); ?></p>

    <div class="grid-3">
      <?php foreach ($c['leistungen']['services'] as $si => $svc): ?>
        <a href="leistung-detail.php?s=<?php echo $si; ?>" class="quick-service reveal in-view" style="display:block;">
          <div class="icon-circle-outline"><?php echo icon_svg('sparkles', 24); ?></div>
          <h3><?php echo x($svc['title']); ?></h3>
          <?php if (!empty($svc['summary'])): ?>
            <p style="font-size:.88rem;margin:0;"><?php echo x($svc['summary']); ?></p>
          <?php endif; ?>
          <span class="qs-arrow"><?php echo x(ui('read_more')); ?> <?php echo icon_svg('arrow-right', 14); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA Banner -->
<section class="section-tight" style="background:var(--bg);">
  <div class="container">
    <div class="cta-banner reveal in-view">
      <div class="cta-banner-text">
        <?php if (!empty($c['ueber_mich']['photo'])): ?>
          <div class="cta-banner-photo"><img src="<?php echo e($c['ueber_mich']['photo']); ?>" alt="<?php echo e($site['doctor_name']); ?>"></div>
        <?php endif; ?>
        <div>
          <h3><?php echo x($site['footer_tagline']); ?></h3>
          <span class="cta-phone"><?php echo icon_svg('phone', 18); ?> <a href="<?php echo e(tel_href($site['phone'])); ?>" style="color:inherit;text-decoration:none;"><?php echo ltr($site['phone']); ?></a></span>
          <?php if (!empty($site['fax'])): ?><span class="cta-phone contact-fax"><?php echo icon_svg('printer', 16); ?> <?php echo x(ui('label_fax')); ?>: <?php echo ltr($site['fax']); ?></span><?php endif; ?>
        </div>
      </div>
      <a href="#kontakt" class="btn"><?php echo x($h['hero_button']); ?> <?php echo icon_svg('arrow-right', 20); ?></a>
    </div>
  </div>
</section>

<!-- Contact -->
<section id="kontakt" class="section" style="background:var(--bg);scroll-margin-top:5.5rem;position:relative;overflow:hidden;padding-top:1.5rem;">
  <div class="deco-blob" style="width:22rem;height:22rem;background:var(--teal);top:-4rem;right:-6rem;opacity:.2;"></div>
  <div class="container" style="position:relative;z-index:1;">
    <div class="text-center reveal in-view" style="max-width:640px;margin:0 auto 3.5rem;">
      <h2><?php echo x($h['contact_title']); ?></h2>
      <p style="font-size:1.05rem;"><?php echo x($h['contact_subtitle']); ?></p>
    </div>

    <div class="grid-2">
      <div class="reveal-left in-view">
        <div class="info-card">
          <h3><?php echo x($h['contact_info_title']); ?></h3>
          <div style="margin-top:1.5rem;">
            <div class="info-row">
              <div class="icon-circle" style="width:52px;height:52px;"><?php echo icon_svg('map-pin', 22); ?></div>
              <div>
                <h4 style="margin-bottom:.25rem;font-size:1.02rem;color:var(--text);"><?php echo x(ui('label_address')); ?></h4>
                <p style="margin:0;"><?php echo ltr($site['address_line1']); ?><br><?php echo ltr($site['address_line2']); ?><br><?php echo x($site['address_line3']); ?></p>
              </div>
            </div>
            <div class="info-row">
              <div class="icon-circle alt" style="width:52px;height:52px;"><?php echo icon_svg('phone', 22); ?></div>
              <div>
                <h4 style="margin-bottom:.25rem;font-size:1.02rem;color:var(--text);"><?php echo x(ui('label_phone')); ?></h4>
                <p style="margin:0;"><a href="<?php echo e(tel_href($site['phone'])); ?>" style="color:inherit;text-decoration:none;"><?php echo ltr($site['phone']); ?></a></p>
                <?php if (!empty($site['fax'])): ?>
                  <h4 style="margin:.8rem 0 .25rem;font-size:1.02rem;color:var(--text);"><?php echo x(ui('label_fax')); ?></h4>
                  <p style="margin:0;"><?php echo ltr($site['fax']); ?></p>
                <?php endif; ?>
              </div>
            </div>
            <div class="info-row">
              <div class="icon-circle" style="width:52px;height:52px;"><?php echo icon_svg('mail', 22); ?></div>
              <div>
                <h4 style="margin-bottom:.25rem;font-size:1.02rem;color:var(--text);"><?php echo x(ui('label_email')); ?></h4>
                <p style="margin:0;"><a href="mailto:<?php echo e($site['email']); ?>" style="color:inherit;text-decoration:none;"><?php echo ltr($site['email']); ?></a></p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="reveal-right in-view">
        <!-- Google Maps erst nach Klick laden (Zwei-Klick-Lösung) -->
        <div class="map-wrap map-consent" style="margin-bottom:2.5rem;" data-src="<?php echo e(map_embed_url($site['map_embed'])); ?>" data-title="<?php echo e(ui('map_title')); ?>">
          <div class="map-consent-box">
            <?php echo icon_svg('map-pin', 28); ?>
            <p><?php echo x(ui('map_consent_text')); ?> <a href="datenschutz.php"><?php echo x(ui('footer_privacy')); ?></a></p>
            <button type="button" class="btn map-consent-btn"><?php echo x(ui('map_consent_btn')); ?></button>
            <a class="map-consent-link" href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo e(urlencode($site['address_line1'] . ', ' . $site['address_line2'])); ?>" target="_blank" rel="noopener"><?php echo x(ui('map_open_link')); ?></a>
          </div>
        </div>

        <div class="form-card">
          <h3 style="margin-bottom:.4rem;"><?php echo x($h['form_title']); ?></h3>
          <p style="font-size:.9rem;margin-bottom:1.5rem;"><?php echo x($h['form_subtitle']); ?></p>

          <form id="contact-form" method="post" action="send_mail.php" enctype="multipart/form-data" novalidate>
            <div class="field">
              <label><?php echo x(ui('form_name')); ?> <span class="required">*</span></label>
              <div class="grid-name">
                <div>
                  <input type="text" id="firstName" name="firstName" placeholder="<?php echo e(ui('form_first')); ?>" required>
                </div>
                <div>
                  <input type="text" id="lastName" name="lastName" placeholder="<?php echo e(ui('form_last')); ?>" required>
                </div>
              </div>
            </div>

            <div class="field">
              <label for="phone"><?php echo x(ui('form_phone')); ?> <span class="required">*</span></label>
              <input type="tel" id="phone" name="phone" placeholder="0 59 71 / 12 34 56" dir="ltr" required>
            </div>

            <div class="field">
              <label for="email"><?php echo x(ui('form_email')); ?> <span class="required">*</span></label>
              <input type="email" id="email" name="email" placeholder="<?php echo e(ui('form_email_ph')); ?>" dir="ltr" required>
            </div>

            <div class="field">
              <label for="isPatient"><?php echo x(ui('form_patient')); ?> <span class="required">*</span></label>
              <select id="isPatient" name="isPatient" required>
                <option value="" disabled selected><?php echo e(ui('form_select')); ?></option>
                <option value="Ja, ich bin bereits Patient"><?php echo e(ui('form_yes')); ?></option>
                <option value="Nein, ich bin noch kein Patient"><?php echo e(ui('form_no')); ?></option>
              </select>
            </div>

            <div class="field">
              <label><?php echo x(ui('form_file')); ?></label>
              <p class="hint"><?php echo x(ui('form_file_hint')); ?></p>
              <input type="file" id="fileUpload" name="attachments[]" multiple style="display:none;">
              <label for="fileUpload" class="file-drop">
                <?php echo icon_svg('upload', 20); ?>
                <span style="font-size:.9rem;margin-top:.4rem;"><?php echo x(ui('form_file_add')); ?></span>
              </label>
              <div id="file-list" class="file-list"></div>
            </div>

            <div class="field">
              <label for="message"><?php echo x(ui('form_message')); ?> <span class="required">*</span></label>
              <textarea id="message" name="message" rows="4" placeholder="<?php echo e(ui('form_message_ph')); ?>" required></textarea>
            </div>

            <p class="hint form-privacy"><?php echo x(ui('form_privacy')); ?> <a href="datenschutz.php" target="_blank"><?php echo x(ui('form_privacy_link')); ?></a>.</p>

            <button type="submit" class="btn btn-block"><?php echo x(ui('form_submit')); ?></button>
            <div id="form-msg" class="form-msg"></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

<!-- ===== LOADING SCREEN SCRIPT ===== -->
<?php if (!empty($site['loader_enabled'])): ?>
<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        var loader = document.getElementById('siteLoader');
        if (loader) loader.classList.add('hidden');
    }, 2800);
});
</script>
<?php endif; ?>
</body>
</html>