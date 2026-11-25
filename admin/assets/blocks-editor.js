/**
 * Abschnitts-Editor für Impressum & Datenschutz (ohne HTML).
 * Abschnitt = Überschrift + beliebig viele Zeilen.
 *  - Enter in einer Zeile        → neue Zeile darunter
 *  - Shift+Enter                  → Zeilenumbruch innerhalb derselben Zeile
 *  - Rücktaste in leerer Zeile    → Zeile löschen
 *  - Mehrzeiligen Text einfügen   → wird automatisch auf mehrere Zeilen verteilt
 * Das Ergebnis wird als JSON in das versteckte <textarea class="blocks-json"> geschrieben.
 */
(function () {
  function el(tag, attrs, children) {
    var n = document.createElement(tag);
    if (attrs) Object.keys(attrs).forEach(function (k) {
      if (k === 'text') n.textContent = attrs[k];
      else if (k === 'className') n.className = attrs[k];
      else n.setAttribute(k, attrs[k]);
    });
    (children || []).forEach(function (c) { if (c) n.appendChild(c); });
    return n;
  }

  function autoGrow(ta) {
    ta.style.height = 'auto';
    ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
  }

  function BlocksEditor(root, store) {
    this.root = root;
    this.store = store;
    this.dir = root.dataset.dir || 'ltr';
    var data = [];
    try { data = JSON.parse(store.value || '[]'); } catch (e) { data = []; }
    if (!Array.isArray(data)) data = [];
    this.root.innerHTML = '';
    this.list = el('div', { className: 'blk-list' });
    this.root.appendChild(this.list);
    var self = this;
    var addBlock = el('button', { type: 'button', className: 'btn btn-outline btn-sm blk-add-block', text: '+ Abschnitt hinzufügen' });
    addBlock.addEventListener('click', function () {
      var b = self.addBlock({ level: 'h2', title: '', lines: [''] });
      b.querySelector('.blk-title').focus();
      self.sync();
    });
    this.root.appendChild(addBlock);
    data.forEach(function (b) { self.addBlock(b); });
    if (!data.length) this.root.classList.add('is-empty');
    this.sync();
    root._editor = this;
  }

  BlocksEditor.prototype.setData = function (data) {
    this.list.innerHTML = '';
    var self = this;
    (data || []).forEach(function (b) { self.addBlock(b); });
    this.sync();
  };

  BlocksEditor.prototype.addBlock = function (b, after) {
    var self = this;
    var card = el('div', { className: 'blk' });

    var level = el('select', { className: 'blk-level', title: 'Art der Überschrift' });
    level.appendChild(el('option', { value: 'h2', text: 'Große Überschrift' }));
    level.appendChild(el('option', { value: 'h3', text: 'Kleine Überschrift' }));
    level.value = b.level === 'h3' ? 'h3' : 'h2';

    var title = el('input', { type: 'text', className: 'blk-title', placeholder: 'Überschrift (optional)', dir: this.dir });
    title.value = b.title || '';
    title.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var first = card.querySelector('.blk-line');
        if (!first) first = self.addLine(card, '', null);
        first.focus();
      }
    });

    var up = el('button', { type: 'button', className: 'blk-btn', title: 'Abschnitt nach oben', text: '↑' });
    var down = el('button', { type: 'button', className: 'blk-btn', title: 'Abschnitt nach unten', text: '↓' });
    var del = el('button', { type: 'button', className: 'blk-btn blk-del', title: 'Abschnitt löschen', text: '✕' });
    up.addEventListener('click', function () { if (card.previousElementSibling) { card.parentNode.insertBefore(card, card.previousElementSibling); self.sync(); } });
    down.addEventListener('click', function () { if (card.nextElementSibling) { card.parentNode.insertBefore(card.nextElementSibling, card); self.sync(); } });
    del.addEventListener('click', function () { if (confirm('Diesen Abschnitt komplett löschen?')) { card.remove(); self.sync(); } });

    var head = el('div', { className: 'blk-head' }, [level, title, el('div', { className: 'blk-tools' }, [up, down, del])]);
    var lines = el('div', { className: 'blk-lines' });
    var addLine = el('button', { type: 'button', className: 'btn btn-outline btn-sm blk-add-line', text: '+ Zeile hinzufügen' });
    addLine.addEventListener('click', function () {
      var ta = self.addLine(card, '', null);
      ta.focus();
      self.sync();
    });

    card.appendChild(head);
    card.appendChild(lines);
    card.appendChild(addLine);
    if (after && after.nextSibling) this.list.insertBefore(card, after.nextSibling);
    else this.list.appendChild(card);

    (b.lines && b.lines.length ? b.lines : []).forEach(function (l) { self.addLine(card, l, null); });

    card.addEventListener('input', function () { self.sync(); });
    card.addEventListener('change', function () { self.sync(); });
    this.root.classList.remove('is-empty');
    return card;
  };

  BlocksEditor.prototype.addLine = function (card, text, afterRow) {
    var self = this;
    var linesBox = card.querySelector('.blk-lines');
    var ta = el('textarea', { className: 'blk-line', rows: '1', dir: this.dir, placeholder: 'Text dieser Zeile … (leer = Abstand)' });
    ta.value = text || '';
    var up = el('button', { type: 'button', className: 'blk-btn', title: 'Zeile nach oben', text: '↑' });
    var down = el('button', { type: 'button', className: 'blk-btn', title: 'Zeile nach unten', text: '↓' });
    var del = el('button', { type: 'button', className: 'blk-btn blk-del', title: 'Zeile löschen', text: '✕' });
    var row = el('div', { className: 'blk-row' }, [el('span', { className: 'blk-bullet', text: '•' }), ta, el('div', { className: 'blk-tools' }, [up, down, del])]);

    up.addEventListener('click', function () { if (row.previousElementSibling) { linesBox.insertBefore(row, row.previousElementSibling); self.sync(); } });
    down.addEventListener('click', function () { if (row.nextElementSibling) { linesBox.insertBefore(row.nextElementSibling, row); self.sync(); } });
    del.addEventListener('click', function () { row.remove(); self.sync(); });

    ta.addEventListener('input', function () { autoGrow(ta); row.classList.toggle('is-blank', ta.value.trim() === ''); });
    ta.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        var n = self.addLine(card, '', row);
        n.focus();
        self.sync();
      } else if (e.key === 'Backspace' && ta.value === '' && linesBox.children.length > 1) {
        e.preventDefault();
        var prev = row.previousElementSibling || row.nextElementSibling;
        row.remove();
        if (prev) { var p = prev.querySelector('textarea'); p.focus(); p.selectionStart = p.selectionEnd = p.value.length; }
        self.sync();
      }
    });
    ta.addEventListener('paste', function (e) {
      var txt = (e.clipboardData || window.clipboardData).getData('text');
      if (!txt || txt.indexOf('\n') === -1) return;
      e.preventDefault();
      var parts = txt.replace(/\r\n?/g, '\n').split('\n');
      var start = ta.value.slice(0, ta.selectionStart), end = ta.value.slice(ta.selectionEnd);
      ta.value = start + parts[0];
      autoGrow(ta);
      var last = row, lastTa = ta;
      for (var i = 1; i < parts.length; i++) {
        lastTa = self.addLine(card, parts[i] + (i === parts.length - 1 ? end : ''), last);
        last = lastTa.closest('.blk-row');
      }
      lastTa.focus();
      self.sync();
    });

    if (afterRow && afterRow.nextSibling) linesBox.insertBefore(row, afterRow.nextSibling);
    else if (afterRow) linesBox.appendChild(row);
    else linesBox.appendChild(row);
    row.classList.toggle('is-blank', ta.value.trim() === '');
    setTimeout(function () { autoGrow(ta); }, 0);
    return ta;
  };

  BlocksEditor.prototype.getData = function () {
    var out = [];
    this.list.querySelectorAll('.blk').forEach(function (card) {
      var lines = [];
      card.querySelectorAll('.blk-line').forEach(function (t) { lines.push(t.value); });
      out.push({ level: card.querySelector('.blk-level').value, title: card.querySelector('.blk-title').value, lines: lines });
    });
    return out;
  };

  BlocksEditor.prototype.sync = function () {
    this.store.value = JSON.stringify(this.getData());
    this.store.dispatchEvent(new Event('change', { bubbles: true }));
  };

  function initAll() {
    document.querySelectorAll('.blocks-editor').forEach(function (root) {
      if (root._editor) return;
      var store = root.nextElementSibling;
      while (store && !store.classList.contains('blocks-json')) store = store.nextElementSibling;
      if (!store) return;
      new BlocksEditor(root, store);
    });

    // „Deutsche Struktur übernehmen" (Übersetzungsseite)
    document.querySelectorAll('[data-copy-blocks]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = document.getElementById(btn.dataset.copyBlocks);
        if (!target || !target._editor) return;
        if (target._editor.getData().length && !confirm('Vorhandene Übersetzung durch den deutschen Text ersetzen?')) return;
        var data = [];
        try { data = JSON.parse(btn.dataset.source || '[]'); } catch (e) {}
        target._editor.setData(data);
      });
    });

    // vor dem Absenden sicher synchronisieren
    document.querySelectorAll('form').forEach(function (f) {
      f.addEventListener('submit', function () {
        f.querySelectorAll('.blocks-editor').forEach(function (r) { if (r._editor) r._editor.sync(); });
      }, true);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);
  else initAll();
  window.BlocksEditor = BlocksEditor;
})();
