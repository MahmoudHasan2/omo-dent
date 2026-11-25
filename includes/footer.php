<?php
require_once __DIR__ . '/functions.php';
$c = load_site_content();
$site = $c['site'];
?>
<div class="wave-divider" style="margin-top:-2px;">
  <svg viewBox="0 0 1440 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0,40 C240,90 480,0 720,20 C960,40 1200,90 1440,30 L1440,70 L0,70 Z" fill="<?php echo e($site['colors']['primary_blue']); ?>"/>
  </svg>
</div>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div style="display:flex;flex-direction:column;line-height:1;margin-bottom:1rem;">
          <span style="font-family:'Poppins',sans-serif;font-weight:800;font-size:1.5rem;color:#fff;" dir="ltr">
            <?php echo e($site['logo_text_1']); ?><span style="color:<?php echo e($site['colors']['accent_green']); ?>;"><?php echo e($site['logo_text_2']); ?></span>
          </span>
          <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:.65rem;letter-spacing:.15em;color:rgba(255,255,255,.65);margin-top:.35rem;">
            <?php echo x($site['practice_tagline']); ?>
          </span>
        </div>
        <p style="font-size:.9rem;color:rgba(255,255,255,.75);"><?php echo x($site['footer_tagline']); ?></p>
      </div>

      <div>
        <h4><?php echo x(ui('footer_contact')); ?></h4>
        <div class="footer-contact-row">
          <?php echo icon_svg('map-pin', 18); ?>
          <span><?php echo ltr($site['address_line1']); ?><br><?php echo ltr($site['address_line2']); ?><br><?php echo x($site['address_line3']); ?></span>
        </div>
        <div class="footer-contact-row">
          <?php echo icon_svg('phone', 18); ?>
          <span><a href="<?php echo e(tel_href($site['phone'])); ?>" style="color:inherit;text-decoration:none;"><?php echo ltr($site['phone']); ?></a></span>
        </div>
        <?php if (!empty($site['fax'])): ?>
        <div class="footer-contact-row">
          <?php echo icon_svg('printer', 18); ?>
          <span><?php echo x(ui('label_fax')); ?>: <?php echo ltr($site['fax']); ?></span>
        </div>
        <?php endif; ?>
        <div class="footer-contact-row">
          <?php echo icon_svg('mail', 18); ?>
          <span><a href="mailto:<?php echo e($site['email']); ?>" style="color:inherit;text-decoration:none;"><?php echo ltr($site['email']); ?></a></span>
        </div>
      </div>

      <div>
        <h4><?php echo x(ui('footer_hours')); ?></h4>
        <div class="footer-contact-row">
          <?php echo icon_svg('clock', 18); ?>
          <span>
            <?php echo x($site['hours_line1']); ?><br>
            <?php echo x($site['hours_line2']); ?><br>
            <span style="color:rgba(255,255,255,.55);"><?php echo x($site['hours_line3']); ?></span>
          </span>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p><?php echo x($site['copyright']); ?></p>
      <div class="footer-links">
        <a href="datenschutz.php"><?php echo x(ui('footer_privacy')); ?></a>
        <a href="impressum.php"><?php echo x(ui('footer_imprint')); ?></a>
      </div>
    </div>
  </div>
</footer>

<div id="toast-wrap"></div>
<script>
window.OMO_LANG = <?php echo json_encode(current_lang()); ?>;
window.OMO_I18N = <?php echo json_encode([
  'sending'  => ui('msg_sending'),
  'success'  => ui('msg_success'),
  'required' => ui('msg_required'),
  'email'    => ui('msg_email'),
  'error'    => ui('msg_error'),
  'submit'   => ui('form_submit'),
  'fileAdd'  => ui('form_file_add'),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
</script>
<script src="assets/js/main.js?v=<?php echo @filemtime(__DIR__ . '/../assets/js/main.js'); ?>"></script>
</body>
</html>
