/* POINTU front-end — no dependencies (jQuery only used if WooCommerce loaded it). */
(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  var nav = $('#pt-nav'), burger = $('.pt-burger'), overlay = $('.pt-overlay'), search = $('#pt-search');

  function closeAll() {
    if (nav) { nav.classList.remove('is-open'); }
    if (burger) { burger.setAttribute('aria-expanded', 'false'); }
    if (search) { search.hidden = true; }
    if (overlay) { overlay.hidden = true; }
    document.body.style.overflow = '';
  }
  if (burger && nav) {
    burger.addEventListener('click', function () {
      nav.classList.add('is-open'); burger.setAttribute('aria-expanded', 'true');
      overlay.hidden = false; document.body.style.overflow = 'hidden';
    });
  }
  $$('.pt-nav-close, .pt-search-close').forEach(function (b) { b.addEventListener('click', closeAll); });
  if (overlay) { overlay.addEventListener('click', closeAll); }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeAll(); } });

  var so = $('.pt-search-open');
  if (so && search) {
    so.addEventListener('click', function () {
      var open = search.hidden;
      closeAll();
      if (open) { search.hidden = false; overlay.hidden = false; var i = $('input[type=search]', search); if (i) { i.focus(); } }
    });
  }

  $$('.pt-sub-toggle').forEach(function (t) {
    t.addEventListener('click', function () {
      var li = t.parentElement, open = li.classList.toggle('is-open');
      t.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  /* Shop price filter: one select drives min_price / max_price */
  window.pointuPrice = function (sel) {
    var f = sel.form, min = f.querySelector('[name=min_price]'), max = f.querySelector('[name=max_price]');
    if (sel.value) {
      var p = sel.value.split('-'); min.value = p[0]; max.value = p[1]; min.disabled = max.disabled = false;
    } else { min.disabled = max.disabled = true; }
    f.submit();
  };

  /* ---------- Variation swatches (buttons instead of dropdowns) ---------- */
  var COLORS = {
    black: '#1d1a1b', noir: '#1d1a1b', white: '#fff', ivory: '#f6f0e4', cream: '#f3e9d8', nude: '#e3c3ad', beige: '#d9c3a5',
    camel: '#b88a5a', tan: '#c08a5b', brown: '#6b4630', chocolate: '#4a2c20', burgundy: '#6e2d38', bordeaux: '#5a1f2b', wine: '#5a1f2b',
    red: '#b2232f', rose: '#ead2cd', blush: '#f2d4cf', pink: '#e8a7b5', fuchsia: '#c2185b', silver: '#c9c9cc', gold: '#c9a35b',
    champagne: '#e8d5b0', navy: '#1f2a44', blue: '#3b5ba5', green: '#3f6b4f', emerald: '#1f6b4f', olive: '#6f6b3a', grey: '#8f8c8c', gray: '#8f8c8c', taupe: '#8b7d72'
  };
  function swatchify(form) {
    var table = $('.variations', form);
    if (!table || table.classList.contains('pt-swatched')) { return; }
    table.classList.add('pt-swatched');
    $$('select', table).forEach(function (sel) {
      var wrap = document.createElement('div'); wrap.className = 'pt-sw';
      var isColor = /colou?r/i.test(sel.name);
      $$('option', sel).forEach(function (o) {
        if (!o.value) { return; }
        var b = document.createElement('button'); b.type = 'button'; b.dataset.value = o.value;
        if (isColor) {
          var key = o.value.toLowerCase().split('-')[0], c = COLORS[key];
          if (c) { var i = document.createElement('i'); i.style.background = c; b.appendChild(i); }
        }
        b.appendChild(document.createTextNode(o.textContent));
        b.addEventListener('click', function () {
          sel.value = sel.value === o.value ? '' : o.value;
          if (window.jQuery) { window.jQuery(sel).trigger('change'); } else { sel.dispatchEvent(new Event('change', { bubbles: true })); }
          sync();
        });
        wrap.appendChild(b);
      });
      sel.parentNode.insertBefore(wrap, sel);
      function sync() {
        var avail = {}; $$('option', sel).forEach(function (o) { if (o.value) { avail[o.value] = !o.disabled; } });
        $$('button', wrap).forEach(function (b) {
          b.classList.toggle('is-on', b.dataset.value === sel.value);
          b.disabled = avail[b.dataset.value] === false;
        });
      }
      sel.addEventListener('change', sync);
      if (window.jQuery) { window.jQuery(form).on('woocommerce_update_variation_values reset_data', function () { setTimeout(sync, 0); }); }
      sync();
    });
  }

  /* ---------- WhatsApp message carries the chosen options ---------- */
  function updateWA(form) {
    var a = $('[data-pt-wa]'); if (!a) { return; }
    var opts = [];
    if (form) {
      $$('.variations select', form).forEach(function (s) {
        if (s.value) {
          var label = (form.querySelector('label[for="' + s.id + '"]') || {}).textContent || s.name.replace('attribute_pa_', '').replace('attribute_', '');
          var txt = s.options[s.selectedIndex].textContent;
          opts.push(label.trim() + ': ' + txt.trim());
        }
      });
    }
    var qty = form && form.querySelector('input.qty');
    var msg = a.dataset.intro + '\n' + a.dataset.name + (opts.length ? '\n' + opts.join(' | ') : '') + (qty && +qty.value > 1 ? '\nQty: ' + qty.value : '') + '\n' + a.dataset.url;
    a.href = 'https://wa.me/' + (window.POINTU ? POINTU.whatsapp : '') + '?text=' + encodeURIComponent(msg);
  }

  function initProduct() {
    $$('form.variations_form').forEach(function (f) {
      swatchify(f);
      f.addEventListener('change', function () { updateWA(f); });
      if (window.jQuery) { window.jQuery(f).on('found_variation reset_data', function () { updateWA(f); }); }
    });
    var cart = $('form.cart'); if (cart) { cart.addEventListener('input', function () { updateWA(cart); }); }
  }
  if (document.readyState !== 'loading') { initProduct(); } else { document.addEventListener('DOMContentLoaded', initProduct); }
})();

/* Cart: update totals automatically when quantity changes (no "Update cart" button) */
(function () {
  if (!document.body.classList.contains('woocommerce-cart')) { return; }
  var t;
  document.addEventListener('change', function (e) {
    if (!e.target.matches('.woocommerce-cart-form .qty')) { return; }
    clearTimeout(t);
    t = setTimeout(function () {
      var b = document.querySelector('.woocommerce-cart-form [name=update_cart]');
      if (b) { b.disabled = false; b.click(); }
    }, 400);
  });
})();

/* Checkout: inline coupon inside "Your order" */
(function () {
  if (!document.body.classList.contains('woocommerce-checkout')) { return; }
  document.addEventListener('click', function (e) {
    var tg = e.target.closest('.pt-co-coupon__toggle');
    if (tg) {
      var row = tg.parentNode.querySelector('.pt-co-coupon__row');
      row.hidden = !row.hidden; tg.setAttribute('aria-expanded', row.hidden ? 'false' : 'true');
      if (!row.hidden) { row.querySelector('input').focus(); }
      return;
    }
    var ap = e.target.closest('.pt-co-coupon__apply');
    if (!ap || !window.jQuery || !window.wc_checkout_params) { return; }
    var box = ap.closest('.pt-co-coupon'), code = box.querySelector('input').value.trim(), msg = box.querySelector('.pt-co-coupon__msg');
    if (!code) { return; }
    ap.disabled = true;
    jQuery.post(wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
      { security: wc_checkout_params.apply_coupon_nonce, coupon_code: code },
      function (html) {
        ap.disabled = false; msg.innerHTML = html;
        jQuery(document.body).trigger('update_checkout', { update_shipping_method: false });
      });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.matches('.pt-co-coupon__input')) { e.preventDefault(); e.target.parentNode.querySelector('.pt-co-coupon__apply').click(); }
  });
})();

/* Quantity stepper: − / + buttons around every WooCommerce quantity field */
(function () {
  function build(root) {
    (root || document).querySelectorAll('.quantity:not(.pt-stepper) input.qty').forEach(function (inp) {
      if (inp.type === 'hidden') { return; }
      var q = inp.closest('.quantity'); q.classList.add('pt-stepper');
      var minus = document.createElement('button'), plus = document.createElement('button');
      minus.type = plus.type = 'button'; minus.className = plus.className = 'pt-step';
      minus.textContent = '−'; plus.textContent = '+';
      minus.setAttribute('aria-label', 'Decrease quantity'); plus.setAttribute('aria-label', 'Increase quantity');
      inp.parentNode.insertBefore(minus, inp); inp.parentNode.insertBefore(plus, inp.nextSibling);
      function lim() {
        var v = parseFloat(inp.value) || 0, mn = parseFloat(inp.min) || 0, mx = parseFloat(inp.max);
        minus.disabled = v <= Math.max(mn, inp.closest('.woocommerce-cart-form') ? 0 : 1);
        plus.disabled = !isNaN(mx) && mx > 0 && v >= mx;
      }
      function step(d) {
        var st = parseFloat(inp.step) || 1, v = (parseFloat(inp.value) || 0) + d * st;
        var mn = parseFloat(inp.min); if (!isNaN(mn) && v < mn) { v = mn; }
        var mx = parseFloat(inp.max); if (!isNaN(mx) && mx > 0 && v > mx) { v = mx; }
        inp.value = v; lim();
        inp.dispatchEvent(new Event('input', { bubbles: true }));
        inp.dispatchEvent(new Event('change', { bubbles: true }));
        if (window.jQuery) { jQuery(inp).trigger('change'); }
      }
      minus.addEventListener('click', function () { step(-1); });
      plus.addEventListener('click', function () { step(1); });
      inp.addEventListener('input', lim); lim();
    });
  }
  if (document.readyState !== 'loading') { build(); } else { document.addEventListener('DOMContentLoaded', function () { build(); }); }
  if (window.jQuery) { jQuery(document.body).on('updated_cart_totals updated_wc_div', function () { build(); }); }
})();
