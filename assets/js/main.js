document.addEventListener('DOMContentLoaded', function () {

  /* ---------- Mobile menu ---------- */
  var toggle = document.querySelector('.mobile-toggle');
  var mobileNav = document.querySelector('.mobile-nav');
  if (toggle && mobileNav) {
    toggle.addEventListener('click', function () {
      mobileNav.classList.toggle('open');
      var open = mobileNav.classList.contains('open');
      toggle.innerHTML = open
        ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
        : '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';
    });
    mobileNav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { mobileNav.classList.remove('open'); });
    });
  }

  /* ---------- Reveal on scroll ---------- */
  var revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in-view'); });
  }

  /* ---------- FAQ accordion ---------- */
  document.querySelectorAll('.faq-q').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      item.classList.toggle('open');
    });
  });

  /* ---------- Smooth scroll for #anchor links ---------- */
  document.querySelectorAll('a[href*="#"]').forEach(function (a) {
    var href = a.getAttribute('href');
    var hashIndex = href.indexOf('#');
    if (hashIndex === -1) return;
    var hash = href.substring(hashIndex);
    if (hash.length <= 1) return;
    var target = document.querySelector(hash);
    // Modal triggers must NOT be intercepted — they rely on native browser
    // anchor navigation (CSS :target) to open.
    if (target && target.classList.contains('modal-overlay')) return;
    var pagePart = href.substring(0, hashIndex);
    var samePage = pagePart === '' || pagePart === window.location.pathname.split('/').pop() || pagePart === './';
    if (samePage) {
      a.addEventListener('click', function (e) {
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    }
  });

  /* ---------- Toast helper ---------- */
  window.showToast = function (message, type) {
    var wrap = document.getElementById('toast-wrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.id = 'toast-wrap';
      document.body.appendChild(wrap);
    }
    var t = document.createElement('div');
    t.className = 'toast ' + (type || 'success');
    t.textContent = message;
    wrap.appendChild(t);
    setTimeout(function () {
      t.style.opacity = '0';
      t.style.transition = 'opacity .3s ease';
      setTimeout(function () { t.remove(); }, 300);
    }, 5000);
  };

  /* ---------- PDF-Download Modal ---------- */
  /* Öffnen/Schließen funktioniert bereits rein über CSS (:target). Der
     folgende Code ist nur eine optionale Komfort-Erweiterung. */
  try {
    var pdfModal = document.getElementById('pdf-modal');
    if (pdfModal) {
      var clearPdfHash = function () {
        if (window.location.hash === '#pdf-modal') {
          history.replaceState(null, '', window.location.pathname + window.location.search);
        }
      };
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') clearPdfHash();
      });
      pdfModal.addEventListener('click', function (e) {
        if (e.target === pdfModal) clearPdfHash();
      });
    }
  } catch (err) {
    console.error('PDF-Modal Komfort-Erweiterung fehlgeschlagen:', err);
  }
  
  

  /* ---------- Google Maps erst nach Klick laden ---------- */
  document.querySelectorAll('.map-consent').forEach(function (wrap) {
    var btn = wrap.querySelector('.map-consent-btn');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var iframe = document.createElement('iframe');
      iframe.src = wrap.getAttribute('data-src');
      iframe.title = wrap.getAttribute('data-title') || '';
      iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
      wrap.innerHTML = '';
      wrap.classList.remove('map-consent');
      wrap.appendChild(iframe);
    });
  });

  /* ---------- Contact form (file upload UI + AJAX submit) ---------- */
  var form = document.getElementById('contact-form');
  if (form) {
    var fileInput = document.getElementById('fileUpload');
    var fileListEl = document.getElementById('file-list');
    var dt = new DataTransfer();

    if (fileInput) {
      fileInput.addEventListener('change', function () {
        Array.from(fileInput.files).forEach(function (f) { dt.items.add(f); });
        fileInput.files = dt.files;
        renderFiles();
      });
    }

    function renderFiles() {
      if (!fileListEl) return;
      fileListEl.innerHTML = '';
      Array.from(dt.files).forEach(function (file, index) {
        var chip = document.createElement('div');
        chip.className = 'file-chip';
        var name = document.createElement('span');
        name.textContent = file.name;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '&times;';
        btn.addEventListener('click', function () {
          var newDt = new DataTransfer();
          Array.from(dt.files).forEach(function (f, i) {
            if (i !== index) newDt.items.add(f);
          });
          dt = newDt;
          fileInput.files = dt.files;
          renderFiles();
        });
        chip.appendChild(name);
        chip.appendChild(btn);
        fileListEl.appendChild(chip);
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var submitBtn = form.querySelector('button[type=submit]');
      var msgBox = document.getElementById('form-msg');
      var originalLabel = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Wird gesendet...';
      if (msgBox) { msgBox.className = 'form-msg'; msgBox.textContent = ''; }

      var formData = new FormData(form);

      fetch('send_mail.php', { method: 'POST', body: formData })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            if (msgBox) {
              msgBox.className = 'form-msg success';
              msgBox.textContent = 'Ihre Anfrage wurde erfolgreich gesendet. Wir melden uns in Kürze bei Ihnen.';
            }
            showToast('Ihre Nachricht wurde erfolgreich gesendet!', 'success');
            form.reset();
            dt = new DataTransfer();
            renderFiles();
          } else {
            if (msgBox) {
              msgBox.className = 'form-msg error';
              msgBox.textContent = data.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später noch einmal.';
            }
            showToast('Ein Fehler ist aufgetreten.', 'error');
          }
        })
        .catch(function () {
          if (msgBox) {
            msgBox.className = 'form-msg error';
            msgBox.textContent = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später noch einmal.';
          }
          showToast('Ein Fehler ist aufgetreten.', 'error');
        })
        .finally(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = originalLabel;
        });
    });
  }

});

