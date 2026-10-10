// Photo slideshow for project pages
document.querySelectorAll('[data-slides]').forEach(function (box) {
  var imgs = box.querySelectorAll('img'), i = 0, count = box.querySelector('[data-count]');
  function show(n) {
    i = (n + imgs.length) % imgs.length;
    imgs.forEach(function (im, k) { im.hidden = k !== i; });
    if (count) count.textContent = (i + 1) + ' / ' + imgs.length;
  }
  var p = box.querySelector('[data-prev]'), n = box.querySelector('[data-next]');
  if (p) p.addEventListener('click', function () { show(i - 1); });
  if (n) n.addEventListener('click', function () { show(i + 1); });
});

// Home page slideshow: photo, video and YouTube slides, auto-play, arrows, dots, swipe, pause
(function () {
  var box = document.querySelector('[data-hero]');
  if (!box) return;
  var slides = [].slice.call(box.querySelectorAll('.hs-slide')), dots = [].slice.call(box.querySelectorAll('.hs-dots button'));
  var cur = 0, timer = null, paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches, pauseBtn = box.querySelector('[data-pause]');
  function media(i, on) {
    var s = slides[i], v = s.querySelector('video'), y = s.querySelector('.hs-yt');
    if (v) { if (on) { v.currentTime = 0; var p = v.play(); if (p && p.catch) p.catch(function () {}); } else v.pause(); }
    if (y) {
      if (on && !y.firstChild) {
        var f = document.createElement('iframe'), id = y.getAttribute('data-yt');
        f.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&mute=1&controls=0&loop=1&playlist=' + id + '&playsinline=1&rel=0&modestbranding=1';
        f.allow = 'autoplay; encrypted-media'; f.title = ''; f.tabIndex = -1; y.appendChild(f);
      } else if (!on) y.innerHTML = '';
    }
  }
  function go(n) {
    if (n === cur && slides[cur].classList.contains('is-active')) { schedule(); return; }
    media(cur, false); slides[cur].classList.remove('is-active');
    cur = (n + slides.length) % slides.length;
    slides[cur].classList.add('is-active'); media(cur, true);
    dots.forEach(function (d, k) { if (k === cur) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current'); });
    schedule();
  }
  function schedule() {
    clearTimeout(timer);
    if (paused || slides.length < 2) return;
    timer = setTimeout(function () { go(cur + 1); }, slides[cur].getAttribute('data-type') === 'image' ? 6000 : 12000);
  }
  function setPaused(p) {
    paused = p; if (pauseBtn) { pauseBtn.textContent = p ? 'Play' : 'Pause'; pauseBtn.setAttribute('aria-label', p ? 'Play slideshow' : 'Pause slideshow'); }
    schedule();
  }
  box.addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b) return;
    if (b.hasAttribute('data-dir')) go(cur + parseInt(b.getAttribute('data-dir'), 10));
    else if (b.hasAttribute('data-go')) go(parseInt(b.getAttribute('data-go'), 10));
    else if (b.hasAttribute('data-pause')) setPaused(!paused);
  });
  box.addEventListener('keydown', function (e) { if (e.key === 'ArrowLeft') go(cur - 1); if (e.key === 'ArrowRight') go(cur + 1); });
  var x0 = null;
  box.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  box.addEventListener('touchend', function (e) { if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 50) go(cur + (dx < 0 ? 1 : -1)); x0 = null; });
  box.addEventListener('mouseenter', function () { clearTimeout(timer); });
  box.addEventListener('mouseleave', schedule);
  setPaused(paused); media(0, true); schedule();
})();


// Mobile menu: opens and closes the collapsed navigation
(function () {
  var btn = document.querySelector('.nav-toggle'), menu = document.getElementById('site-menu');
  if (!btn || !menu) return;
  function set(open) { menu.classList.toggle('open', open); btn.setAttribute('aria-expanded', open ? 'true' : 'false'); btn.setAttribute('aria-label', open ? 'Close menu' : 'Menu'); }
  btn.addEventListener('click', function (e) { e.stopPropagation(); set(btn.getAttribute('aria-expanded') !== 'true'); });
  menu.addEventListener('click', function (e) { if (e.target.closest('a')) set(false); });
  document.addEventListener('click', function (e) { if (!menu.contains(e.target) && !btn.contains(e.target)) set(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { set(false); btn.focus(); } });
  window.addEventListener('resize', function () { if (window.innerWidth > 960) set(false); });
})();