(() => {
  'use strict';

  /**
   * Requirement:
   * - Depending on selected value of "Type" field, display a different set of fields.
   * - Only those fields whose `name` attribute contains the selected value should be displayed.
   *
   * Notes:
   * - We hide and disable non-matching fields to avoid accidental submission.
   * - We try to hide a reasonable "row/container" around each field, not just the input itself.
   */

  const SELECTOR_FIELDS = 'input[name], select[name], textarea[name]';

  function isUsableField(el) {
    if (!el || el.nodeType !== 1) return false;
    if (el.tagName === 'INPUT') {
      const t = (el.getAttribute('type') || '').toLowerCase();
      if (t === 'hidden') return false;
      if (t === 'submit' || t === 'button' || t === 'reset') return false;
      if (t === 'image') return false;
    }
    if (el.hasAttribute('data-keep-visible')) return false;
    return true;
  }

  function findTypeSelect() {
    const candidates = Array.from(document.querySelectorAll('select'));
    const byAttr = candidates.find((s) => {
      const n = (s.getAttribute('name') || '').toLowerCase();
      const i = (s.getAttribute('id') || '').toLowerCase();
      return n === 'type' || i === 'type' || n.includes('type') || i.includes('type');
    });
    if (byAttr) return byAttr;

    // Fallback: look for a label with "type" pointing to a select.
    const labels = Array.from(document.querySelectorAll('label'));
    for (const label of labels) {
      const txt = (label.textContent || '').trim().toLowerCase();
      if (!txt) continue;
      if (!txt.includes('type') && !txt.includes('тип')) continue;
      const forId = label.getAttribute('for');
      if (forId) {
        const linked = document.getElementById(forId);
        if (linked && linked.tagName === 'SELECT') return linked;
      }
      const inside = label.querySelector('select');
      if (inside) return inside;
    }

    return null;
  }

  function getFieldContainer(field) {
    // Prefer common wrappers.
    const selectors = [
      '.form-group',
      '.field',
      '.form-row',
      '.row',
      '.input-group',
      'tr',
      'li',
      'p',
      'div',
    ];
    for (const sel of selectors) {
      const container = field.closest(sel);
      if (container && container !== document.body && container !== document.documentElement) {
        return container;
      }
    }
    return field;
  }

  function setVisible(container, visible) {
    if (visible) {
      container.style.removeProperty('display');
      container.hidden = false;
      container.setAttribute('aria-hidden', 'false');
    } else {
      container.style.display = 'none';
      container.hidden = true;
      container.setAttribute('aria-hidden', 'true');
    }
  }

  function toggleFields(typeSelect) {
    const selected = String(typeSelect.value ?? '').trim();

    // If nothing is selected, show everything (safe default).
    if (!selected) {
      const fields = Array.from(document.querySelectorAll(SELECTOR_FIELDS)).filter(isUsableField);
      for (const field of fields) {
        field.disabled = false;
        setVisible(getFieldContainer(field), true);
      }
      return;
    }

    const selectedLower = selected.toLowerCase();
    const allFields = Array.from(document.querySelectorAll(SELECTOR_FIELDS)).filter(isUsableField);

    for (const field of allFields) {
      // Never hide the type selector itself.
      if (field === typeSelect) continue;

      const name = String(field.getAttribute('name') || '');
      const matches = name.toLowerCase().includes(selectedLower);

      field.disabled = !matches;
      setVisible(getFieldContainer(field), matches);
    }
  }

  function init() {
    const typeSelect = findTypeSelect();
    if (!typeSelect) {
      // Fail quietly; nothing to do.
      return;
    }

    // Initial state.
    toggleFields(typeSelect);

    // Update on user changes.
    typeSelect.addEventListener('change', () => toggleFields(typeSelect));

    // In some pages select value is set programmatically.
    const mo = new MutationObserver(() => toggleFields(typeSelect));
    mo.observe(typeSelect, { attributes: true, attributeFilter: ['value'] });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();

