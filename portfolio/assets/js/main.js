/* ============================================================
   assets/js/main.js — all interactivity, zero dependencies
   1. Mobile menu        4. Reveal-on-scroll (IntersectionObserver)
   2. Dark/light theme   5. Typewriter role text
   3. Scroll progress bar
   ============================================================ */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- 1. Mobile menu ---------- */
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('site-nav');

  function setNav(open) {
    if (!toggle || !nav) return;
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
  }

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      setNav(!nav.classList.contains('is-open'));
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        setNav(false);
        toggle.focus();
      }
    });
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () { setNav(false); });
    });
  }

  /* ---------- 2. Dark ↔ light blue-lavender themes ---------- */
  var themeBtn = document.querySelector('.theme-toggle');
  var themeColor = document.querySelector('meta[name="theme-color"]');

  function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    if (themeBtn) themeBtn.setAttribute('aria-pressed', String(theme === 'light'));
    if (themeColor) themeColor.setAttribute('content', theme === 'light' ? '#ede8f5' : '#0c0c0c');
  }

  applyTheme(document.documentElement.dataset.theme || 'dark');

  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
      try { localStorage.setItem('theme', next); } catch (e) { /* private mode */ }
      applyTheme(next);
    });
  }

  /* ---------- 2b. Local analog and digital clock on the homepage ---------- */
  var localTime = document.getElementById('local-time');
  var localDate = document.getElementById('local-date');
  var localTimezone = document.getElementById('local-timezone');
  var clockDial = document.querySelector('.local-clock__dial');

  if (localTime && clockDial) {
    var timeFormatter = new Intl.DateTimeFormat(undefined, {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hourCycle: 'h23'
    });
    var dateFormatter = new Intl.DateTimeFormat(undefined, {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });

    function updateLocalClock() {
      var now = new Date();
      var seconds = now.getSeconds() + now.getMilliseconds() / 1000;
      var minutes = now.getMinutes() + seconds / 60;
      var hours = (now.getHours() % 12) + minutes / 60;

      localTime.textContent = timeFormatter.format(now);
      localTime.dateTime = now.toISOString();
      if (localDate) {
        localDate.textContent = dateFormatter.format(now);
        localDate.dateTime = now.toISOString();
      }
      clockDial.style.setProperty('--second-angle', (seconds * 6) + 'deg');
      clockDial.style.setProperty('--minute-angle', (minutes * 6) + 'deg');
      clockDial.style.setProperty('--hour-angle', (hours * 30) + 'deg');
    }

    if (localTimezone) {
      var zoneParts = new Intl.DateTimeFormat(undefined, { timeZoneName: 'short' }).formatToParts(new Date());
      var zone = zoneParts.find(function (part) { return part.type === 'timeZoneName'; });
      localTimezone.textContent = zone ? zone.value : 'Local time';
    }

    updateLocalClock();
    window.setInterval(updateLocalClock, 1000);
  }

  /* ---------- 2c. Pointer-reactive blue orbit in the homepage hero ---------- */
  var hero = document.querySelector('.hero');
  var drift = document.querySelector('.hero__drift');

  if (hero && drift && !reduceMotion) {
    hero.addEventListener('pointermove', function (event) {
      if (event.pointerType === 'touch') return;

      var bounds = hero.getBoundingClientRect();
      var offsetX = (event.clientX - bounds.left - bounds.width / 2) * .28;
      var offsetY = (event.clientY - bounds.top - bounds.height / 2) * .24;
      drift.style.setProperty('--drift-offset-x', Math.max(-180, Math.min(180, offsetX)).toFixed(1) + 'px');
      drift.style.setProperty('--drift-offset-y', Math.max(-110, Math.min(110, offsetY)).toFixed(1) + 'px');
      drift.style.setProperty('--drift-scale', '1.14');
    });

    hero.addEventListener('pointerleave', function () {
      drift.style.setProperty('--drift-offset-x', '0px');
      drift.style.setProperty('--drift-offset-y', '0px');
      drift.style.setProperty('--drift-scale', '1');
    });
  }

  /* ---------- 3. Scroll progress bar + header state ---------- */
  var progress = document.querySelector('.progress');
  var threadCanvas = document.querySelector('.scroll-thread');
  var threadContext = threadCanvas ? threadCanvas.getContext('2d') : null;
  var header = document.querySelector('.header');
  var threadPoints = [];
  var threadLength = 0;
  var threadWidth = 0;
  var threadHeight = 0;
  var threadColor = getComputedStyle(document.documentElement).getPropertyValue('--blue').trim() || '#7091e6';
  var threadStart = [.88, .10];
  var threadCurves = [
    [[.96, .16], [.89, .22], [.78, .31]],
    [[.67, .37], [.57, .39], [.66, .48]],
    [[.75, .57], [.92, .57], [.82, .66]],
    [[.72, .75], [.54, .76], [.61, .86]],
    [[.68, .96], [.84, .99], [.71, 1.12]]
  ];

  function resizeThread() {
    if (!threadCanvas || !threadContext || reduceMotion) return;

    var pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
    threadWidth = window.innerWidth;
    threadHeight = window.innerHeight;
    threadCanvas.width = Math.round(threadWidth * pixelRatio);
    threadCanvas.height = Math.round(threadHeight * pixelRatio);
    threadContext.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

    var previous = { x: threadStart[0] * threadWidth, y: threadStart[1] * threadHeight };
    threadPoints = [{ x: previous.x, y: previous.y, distance: 0 }];
    threadLength = 0;

    threadCurves.forEach(function (curve) {
      var control1 = { x: curve[0][0] * threadWidth, y: curve[0][1] * threadHeight };
      var control2 = { x: curve[1][0] * threadWidth, y: curve[1][1] * threadHeight };
      var end = { x: curve[2][0] * threadWidth, y: curve[2][1] * threadHeight };

      for (var step = 1; step <= 40; step++) {
        var t = step / 40;
        var inverse = 1 - t;
        var point = {
          x: inverse * inverse * inverse * previous.x
            + 3 * inverse * inverse * t * control1.x
            + 3 * inverse * t * t * control2.x
            + t * t * t * end.x,
          y: inverse * inverse * inverse * previous.y
            + 3 * inverse * inverse * t * control1.y
            + 3 * inverse * t * t * control2.y
            + t * t * t * end.y
        };
        var lastPoint = threadPoints[threadPoints.length - 1];
        var deltaX = point.x - lastPoint.x;
        var deltaY = point.y - lastPoint.y;
        threadLength += Math.sqrt(deltaX * deltaX + deltaY * deltaY);
        threadPoints.push({ x: point.x, y: point.y, distance: threadLength });
      }

      previous = end;
    });
  }

  function drawThread(scrollRatio) {
    if (!threadContext || !threadLength || reduceMotion) return;

    var targetDistance = threadLength * (.16 + .84 * scrollRatio);
    var endpoint = threadPoints[0];
    threadContext.clearRect(0, 0, threadWidth, threadHeight);
    threadContext.beginPath();
    threadContext.moveTo(endpoint.x, endpoint.y);

    for (var i = 1; i < threadPoints.length; i++) {
      var point = threadPoints[i];
      if (point.distance >= targetDistance) {
        var previous = threadPoints[i - 1];
        var segmentLength = point.distance - previous.distance;
        var segmentProgress = segmentLength
          ? (targetDistance - previous.distance) / segmentLength
          : 0;
        endpoint = {
          x: previous.x + (point.x - previous.x) * segmentProgress,
          y: previous.y + (point.y - previous.y) * segmentProgress
        };
        threadContext.lineTo(endpoint.x, endpoint.y);
        break;
      }
      threadContext.lineTo(point.x, point.y);
      endpoint = point;
    }

    threadContext.strokeStyle = threadColor;
    threadContext.lineWidth = 5;
    threadContext.lineCap = 'round';
    threadContext.lineJoin = 'round';
    threadContext.shadowColor = threadContext.strokeStyle;
    threadContext.shadowBlur = 16;
    threadContext.globalAlpha = .95;
    threadContext.stroke();
    threadContext.shadowBlur = 0;

    threadContext.beginPath();
    threadContext.arc(endpoint.x, endpoint.y, 9, 0, Math.PI * 2);
    threadContext.strokeStyle = threadColor;
    threadContext.lineWidth = 1;
    threadContext.globalAlpha = .38;
    threadContext.stroke();
    threadContext.beginPath();
    threadContext.arc(endpoint.x, endpoint.y, 4.5, 0, Math.PI * 2);
    threadContext.fillStyle = threadColor;
    threadContext.globalAlpha = .95;
    threadContext.fill();
    threadContext.globalAlpha = 1;
  }

  function onScroll() {
    var max = document.documentElement.scrollHeight - window.innerHeight;
    var scrollRatio = max > 0 ? Math.max(0, Math.min(1, window.scrollY / max)) : 0;
    if (progress) {
      progress.style.setProperty('--p', scrollRatio.toFixed(3));
    }
    drawThread(scrollRatio);
    if (header) header.classList.toggle('is-scrolled', window.scrollY > 8);
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', function () {
    resizeThread();
    onScroll();
  });
  resizeThread();
  onScroll();

  /* ---------- 4. Reveal on scroll (staggered per group) ---------- */
  var revealEls = Array.prototype.slice.call(document.querySelectorAll('.reveal'));

  if (revealEls.length) {
    if ('IntersectionObserver' in window && !reduceMotion) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          /* Stagger siblings that share a parent for a nicer cascade */
          var siblings = Array.prototype.filter.call(el.parentElement.children, function (c) {
            return c.classList && c.classList.contains('reveal');
          });
          var index = Math.min(siblings.indexOf(el), 6);
          el.style.setProperty('--d', (index * 0.07).toFixed(2) + 's');
          el.classList.add('is-visible');
          io.unobserve(el);
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

      revealEls.forEach(function (el) { io.observe(el); });
    } else {
      /* No IO support or user prefers reduced motion: show everything */
      revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    }
  }

  /* ---------- 5. Typewriter roles (roles come from PHP) ---------- */
  var typeEl = document.querySelector('.type');

  if (typeEl) {
    var roles = [];
    try { roles = JSON.parse(typeEl.dataset.roles || '[]'); } catch (e) { roles = []; }

    if (roles.length === 1 || reduceMotion) {
      typeEl.textContent = roles[0] || '';
    } else if (roles.length > 1) {
      var r = 0;      /* which role   */
      var c = 0;      /* char count   */
      var deleting = false;

      (function tick() {
        var word = roles[r];
        c += deleting ? -1 : 1;
        typeEl.textContent = word.slice(0, c);

        var delay = deleting ? 45 : 95;
        if (!deleting && c === word.length) {
          delay = 1600;      /* pause on the full word */
          deleting = true;
        } else if (deleting && c === 0) {
          deleting = false;
          r = (r + 1) % roles.length;
          delay = 320;
        }
        window.setTimeout(tick, delay);
      })();
    }
  }

  /* ---------- 6. Playground: all tools (AJAX, no reload) ---------- */
  var toolForms = document.querySelectorAll('.tool-form');

  function postToTool(form, body, out, stat, btn, btnHtml) {
    if (btn) { btn.disabled = true; btn.textContent = 'Working…'; }

    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (out) {
          out.classList.remove('is-empty');
          out.innerHTML = data.html || '';
        }
        if (stat) { stat.textContent = data.ok ? 'Done — result below.' : 'No result.'; }
      })
      .catch(function () {
        if (out) {
          out.classList.remove('is-empty');
          out.innerHTML = '<p class="tool-error">The request failed — check your connection and try again.</p>';
        }
        if (stat) { stat.textContent = 'Request failed.'; }
      })
      .then(function () {
        if (btn) { btn.disabled = false; btn.innerHTML = btnHtml; }
      });
  }

  /* SHA-256 in the browser: PHP only ever sees the hash + length */
  function sha256Hex(text) {
    var data = new TextEncoder().encode(text);
    return window.crypto.subtle.digest('SHA-256', data).then(function (buf) {
      var bytes = new Uint8Array(buf);
      var hex = '';
      for (var i = 0; i < bytes.length; i++) {
        hex += ('0' + bytes[i].toString(16)).slice(-2);
      }
      return hex;
    });
  }

  function submitTool(form) {
    var kind    = form.getAttribute('data-tool') || 'domain';
    var out     = document.getElementById('tool-' + kind + '-result');
    var stat    = form.querySelector('.tool-status');
    var btn     = form.querySelector('.tool-btn');
    var btnHtml = btn ? btn.innerHTML : '';
    var field   = form.querySelector('.tool-input');

    /* Each tool posts only what its PHP handler needs */
    if (kind === 'password') {
      var pw = field ? field.value : '';
      if (!pw) { if (field) { field.focus(); } return; }
      if (stat) { stat.textContent = 'Hashing locally…'; }

      if (!(window.crypto && window.crypto.subtle)) {
        if (stat) { stat.textContent = 'This browser has no Web Crypto — submit with Enter instead.'; }
        return;
      }

      sha256Hex(pw)
        .then(function (hash) {
          return postToTool(form, 'tool=password&hash=' + hash + '&length=' + pw.length,
                            out, stat, false, btnHtml);
        })
        .catch(function () {
          if (stat) { stat.textContent = 'Could not hash locally — try another browser.'; }
          if (btn) { btn.disabled = false; btn.innerHTML = btnHtml; }
        });
      return;
    }

    var value = field ? field.value.trim() : '';
    if (!value) { if (field) { field.focus(); } return; }

    var body;
    if (kind === 'http') {
      body = 'tool=http&url=' + encodeURIComponent(value);
      if (stat) { stat.textContent = 'Asking ' + value + '…'; }
    } else if (kind === 'text') {
      body = 'tool=text&text=' + encodeURIComponent(value);
      if (stat) { stat.textContent = 'Converting…'; }
    } else {
      body = 'tool=domain&domain=' + encodeURIComponent(value);
      if (stat) { stat.textContent = 'Checking ' + value + '…'; }
    }

    postToTool(form, body, out, stat, btn, btnHtml);
  }

  Array.prototype.forEach.call(toolForms, function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      /* Let the browser show its own "required" bubble first */
      if (!form.checkValidity()) {
        if (form.reportValidity) { form.reportValidity(); }
        return;
      }
      submitTool(form);
    });

    /* Quick-fill chips: fill the field, then run the tool */
    Array.prototype.forEach.call(form.querySelectorAll('.chip'), function (chip) {
      chip.addEventListener('click', function () {
        var input = form.querySelector('.tool-input');
        if (input) {
          input.value = chip.getAttribute('data-fill') || '';
        }
        if (form.requestSubmit) {
          form.requestSubmit();
        } else {
          form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
      });
    });
  });
})();
