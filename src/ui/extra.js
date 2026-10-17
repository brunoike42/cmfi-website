/* Dropdown menu + home page slideshows. Plain JavaScript, no libraries. */
(function () {
  // ----- Dropdown menu: tap/click the arrow to open; Esc or a click elsewhere closes -----
  var menu = document.getElementById('site-menu');
  if (menu) {
    var closeAll = function (except) {
      menu.querySelectorAll('li.open').forEach(function (li) {
        if (except && li.contains(except)) return;
        li.classList.remove('open');
        var b = li.querySelector(':scope > .dd-toggle, :scope > .dd-parent'); if (b) b.setAttribute('aria-expanded', 'false');
      });
    };
    menu.addEventListener('click', function (ev) {
      var btn = ev.target.closest('.dd-toggle, .dd-parent'); if (!btn || !menu.contains(btn)) return;
      var li = btn.parentNode, willOpen = !li.classList.contains('open');
      closeAll(willOpen ? li : null);
      li.classList.toggle('open', willOpen); btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
    document.addEventListener('click', function (ev) { if (!menu.contains(ev.target)) closeAll(); });
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') closeAll(); });
  }

  // ----- Slideshows (About us, Projects, More to discover) -----
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-xc]').forEach(function (root) {
    var vp = root.querySelector('.xc-viewport'), tr = root.querySelector('.xc-track'), cards = tr.children,
        dotsBox = root.querySelector('.xc-dots'), pauseBtn = root.querySelector('.xc-pause'),
        timer = null, userPaused = false, hover = false, visible = true, ticking = false;

    function gap() { return parseFloat(getComputedStyle(tr).columnGap) || parseFloat(getComputedStyle(tr).gap) || 0; }
    function step() { return cards.length ? cards[0].getBoundingClientRect().width + gap() : 0; }
    function perView() { var s = step(); return s ? Math.max(1, Math.round((vp.clientWidth + gap()) / s)) : 1; }
    function last() { return Math.max(0, cards.length - perView()); }
    function index() { var s = step(); return s ? Math.min(last(), Math.round(vp.scrollLeft / s)) : 0; }
    function go(i) { i = Math.max(0, Math.min(last(), i)); vp.scrollTo({ left: i * step(), behavior: reduce ? 'auto' : 'smooth' }); }
    function next() { var i = index(); go(i >= last() ? 0 : i + 1); }
    function prev() { var i = index(); go(i <= 0 ? last() : i - 1); }

    function buildDots() {
      var n = last() + 1; dotsBox.innerHTML = '';
      root.classList.toggle('is-static', n < 2);
      for (var i = 0; i < n; i++) {
        var b = document.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
        (function (k) { b.addEventListener('click', function () { go(k); restart(); }); })(i);
        dotsBox.appendChild(b);
      }
      markDot();
    }
    function markDot() {
      var i = index(), d = dotsBox.children;
      for (var k = 0; k < d.length; k++) k === i ? d[k].setAttribute('aria-current', 'true') : d[k].removeAttribute('aria-current');
    }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function restart() { stop(); if (!reduce && !userPaused && !hover && visible && !root.classList.contains('is-static')) timer = setInterval(next, 5000); }

    root.querySelector('.xc-next').addEventListener('click', function () { next(); restart(); });
    root.querySelector('.xc-prev').addEventListener('click', function () { prev(); restart(); });
    vp.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(function () { ticking = false; markDot(); }); } }, { passive: true });
    vp.addEventListener('keydown', function (ev) { if (ev.key === 'ArrowRight') { next(); restart(); } else if (ev.key === 'ArrowLeft') { prev(); restart(); } });
    root.addEventListener('mouseenter', function () { hover = true; stop(); });
    root.addEventListener('mouseleave', function () { hover = false; restart(); });
    root.addEventListener('focusin', function () { hover = true; stop(); });
    root.addEventListener('focusout', function () { hover = false; restart(); });
    root.addEventListener('touchstart', function () { hover = true; stop(); }, { passive: true });
    root.addEventListener('touchend', function () { setTimeout(function () { hover = false; restart(); }, 4000); }, { passive: true });
    if (pauseBtn) pauseBtn.addEventListener('click', function () {
      userPaused = !userPaused; pauseBtn.textContent = userPaused ? 'Play' : 'Pause';
      pauseBtn.setAttribute('aria-label', userPaused ? 'Play slideshow' : 'Pause slideshow'); restart();
    });
    if ('IntersectionObserver' in window) new IntersectionObserver(function (en) { visible = en[0].isIntersecting; restart(); }).observe(root);
    window.addEventListener('resize', function () { buildDots(); });
    buildDots(); restart();
  });
})();
