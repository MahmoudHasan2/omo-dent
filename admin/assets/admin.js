document.addEventListener('DOMContentLoaded', function () {

  /* ---------- Tabs ---------- */
  var tabBtns = document.querySelectorAll('.tab-btn');
  var panels = document.querySelectorAll('.panel');
  tabBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabBtns.forEach(function (b) { b.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
    });
  });

  /* ---------- Image upload buttons ---------- */
  document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('upload-input')) return;
    var input = e.target;
    var file = input.files[0];
    if (!file) return;
    var targetImg = document.getElementById(input.dataset.target);
    var textInput = input.closest('.upload-row').querySelector('input[type=text]');

    var fd = new FormData();
    fd.append('image', file);

    fetch('upload_image.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          if (targetImg) targetImg.src = data.url;
          if (textInput) textInput.value = data.url;
        } else {
          alert(data.message || 'Upload fehlgeschlagen.');
        }
      })
      .catch(function () { alert('Upload fehlgeschlagen.'); });
  });

  /* ---------- PDF upload buttons ---------- */
  document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('upload-pdf-input')) return;
    var input = e.target;
    var file = input.files[0];
    if (!file) return;
    var targetInput = document.getElementById(input.dataset.target);
    var statusEl = document.getElementById(input.dataset.status);

    var fd = new FormData();
    fd.append('pdf', file);

    if (statusEl) statusEl.textContent = 'Wird hochgeladen...';

    fetch('upload_pdf.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          if (targetInput) targetInput.value = data.url;
          if (statusEl) statusEl.textContent = '📄 ' + data.name;
        } else {
          if (statusEl) statusEl.textContent = 'Noch keine Datei hochgeladen';
          alert(data.message || 'Upload fehlgeschlagen.');
        }
      })
      .catch(function () {
        if (statusEl) statusEl.textContent = 'Noch keine Datei hochgeladen';
        alert('Upload fehlgeschlagen.');
      });
  });

  /* ---------- Add / remove repeatable items ---------- */
  function addFromTemplate(tplId, containerId) {
    var tpl = document.getElementById(tplId);
    var container = document.getElementById(containerId);
    var idx = parseInt(container.dataset.nextIndex, 10) || 0;
    var html = tpl.innerHTML.split('__IDX__').join(idx);
    var wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    var node = wrapper.firstElementChild;
    var addBtn = container.querySelector(':scope > button');
    container.insertBefore(node, addBtn);
    container.dataset.nextIndex = idx + 1;
  }

  var addServiceBtn = document.getElementById('add-service');
  if (addServiceBtn) addServiceBtn.addEventListener('click', function () { addFromTemplate('tpl-service', 'services-container'); });

  var addCredentialBtn = document.getElementById('add-credential');
  if (addCredentialBtn) addCredentialBtn.addEventListener('click', function () { addFromTemplate('tpl-credential', 'credentials-container'); });

  var addTeamMemberBtn = document.getElementById('add-team-member');
  if (addTeamMemberBtn) addTeamMemberBtn.addEventListener('click', function () { addFromTemplate('tpl-team-member', 'team-members-container'); });

  var resetLogoBtn = document.getElementById('reset-logo-image');
  if (resetLogoBtn) {
    resetLogoBtn.addEventListener('click', function () {
      var input = document.getElementById('logo-image-input');
      var preview = document.getElementById('preview-logo-image');
      if (input) input.value = '';
      if (preview) preview.src = '';
    });
  }

  /* ---------- Add gallery image / FAQ (nested inside a service) ---------- */
  document.addEventListener('click', function (e) {
    var addGalleryBtn = e.target.closest('.add-gallery-image');
    if (addGalleryBtn) {
      try {
        var container = addGalleryBtn.closest('.gallery-container');
        if (!container) { console.error('Galerie: .gallery-container nicht gefunden.'); return; }
        var sidx = container.dataset.sidx;
        var gidx = parseInt(container.dataset.nextIndex, 10) || 0;
        var tpl = document.getElementById('tpl-gallery-image');
        if (!tpl) { console.error('Galerie: Template #tpl-gallery-image fehlt.'); return; }
        var html = tpl.innerHTML.split('__SIDX__').join(sidx).split('__GIDX__').join(gidx);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        var node = wrapper.firstElementChild;
        container.insertBefore(node, addGalleryBtn);
        container.dataset.nextIndex = gidx + 1;
      } catch (err) {
        console.error('Galerie-Bild hinzufügen fehlgeschlagen:', err);
      }
      return;
    }

    var addFaqBtn = e.target.closest('.add-faq');
    if (addFaqBtn) {
      try {
        var faqContainer = addFaqBtn.closest('.faqs-container');
        if (!faqContainer) { console.error('FAQ: .faqs-container nicht gefunden.'); return; }
        var fsidx = faqContainer.dataset.sidx;
        var ffidx = parseInt(faqContainer.dataset.nextIndex, 10) || 0;
        var faqTpl = document.getElementById('tpl-faq');
        if (!faqTpl) { console.error('FAQ: Template #tpl-faq fehlt.'); return; }
        var faqHtml = faqTpl.innerHTML.split('__SIDX__').join(fsidx).split('__FIDX__').join(ffidx);
        var faqWrapper = document.createElement('div');
        faqWrapper.innerHTML = faqHtml.trim();
        var faqNode = faqWrapper.firstElementChild;
        faqContainer.insertBefore(faqNode, addFaqBtn);
        faqContainer.dataset.nextIndex = ffidx + 1;
      } catch (err) {
        console.error('Frage hinzufügen fehlgeschlagen:', err);
      }
      return;
    }

    if (e.target.classList.contains('remove-item')) {
      var item = e.target.closest('.repeat-item');
      if (item && confirm('Diesen Eintrag wirklich entfernen?')) {
        item.remove();
      }
    }
  });

  /* ---------- Save forms via fetch ---------- */
  document.querySelectorAll('.ajax-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var section = form.dataset.section;
      var fd = new FormData(form);

      var navPayload = null;
      if (section === 'site') {
        navPayload = {};
        form.querySelectorAll('[data-nav-field]').forEach(function (inp) {
          navPayload[inp.dataset.navField] = inp.value;
        });
      }

      fd.append('section', section);

      var statusEl = form.querySelector('.save-status');
      var btn = form.querySelector('button[type=submit]');
      var originalText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Speichern...';

      fetch('save_content.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success && navPayload) {
            var navFd = new FormData();
            navFd.append('section', 'nav');
            Object.keys(navPayload).forEach(function (k) {
              navFd.append('fields[' + k + ']', navPayload[k]);
            });
            return fetch('save_content.php', { method: 'POST', body: navFd }).then(function (r2) { return r2.json(); });
          }
          return data;
        })
        .then(function (finalData) {
          btn.disabled = false;
          btn.textContent = originalText;
          if (finalData.success) {
            statusEl.classList.add('show');
            setTimeout(function () { statusEl.classList.remove('show'); }, 2500);
          } else {
            alert(finalData.message || 'Speichern fehlgeschlagen.');
          }
        })
        .catch(function () {
          btn.disabled = false;
          btn.textContent = originalText;
          alert('Speichern fehlgeschlagen. Bitte versuchen Sie es erneut.');
        });
    });
  });

});
