<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/logo.png">
  <link rel="apple-touch-icon" href="/images/logo.png">
  <title>Login - MOMOY'S Furniture</title>
  <link rel="stylesheet" href="/css/styles.css">
  {{-- CSRF meta tag for AJAX requests --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    .auth-tabs {
      display: flex;
      border-radius: 999px;
      background: rgba(255,255,255,0.15);
      padding: 4px;
      margin-bottom: 2rem;
      gap: 4px;
    }
    .auth-tab {
      flex: 1;
      padding: 0.6rem 1rem;
      border: none;
      border-radius: 999px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      background: transparent;
      color: rgba(255,255,255,0.7);
      transition: background 0.25s, color 0.25s;
      font-family: var(--sans);
    }
    .auth-tab.active {
      background: #fff;
      color: var(--dark);
    }
    .auth-panel {
      display: none;
      animation: fadeUp 0.3s ease both;
    }
    .auth-panel.active { display: block; }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .auth-title {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #fff;
      font-family: var(--serif);
      font-size: 1.4rem;
      font-weight: 700;
    }
    .form-container .form-group label { color: rgba(255,255,255,0.9); }
    .form-container .form-group input {
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.3);
      color: #fff;
      border-radius: 8px;
    }
    .form-container .form-group input::placeholder { color: rgba(255,255,255,0.45); }
    .form-container .form-group input:focus {
      outline: none;
      border-color: rgba(255,255,255,0.7);
      background: rgba(255,255,255,0.22);
    }
    .auth-submit {
      width: 100%;
      padding: 0.85rem;
      background: #fff;
      color: var(--dark);
      border: none;
      border-radius: 999px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
      font-family: var(--sans);
      margin-top: 0.5rem;
    }
    .auth-submit:hover { background: var(--yellow); transform: translateY(-1px); }
    .auth-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
    .auth-message {
      display: none;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      font-size: 0.9rem;
      text-align: center;
    }
    .auth-message.success { background: rgba(39,174,96,0.25); color: #afffcb; border: 1px solid rgba(39,174,96,0.4); }
    .auth-message.error   { background: rgba(231,76,60,0.25);  color: #ffbdbd; border: 1px solid rgba(231,76,60,0.4); }
    .auth-message.show    { display: block; }
    .field-error { color: #ffbdbd; font-size: 0.8rem; margin-top: 0.25rem; display: block; }
    .formcontainer {
      min-height: calc(100vh - 75px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .form-container { width: 100%; max-width: 420px; }
  </style>
</head>
<body class="formbody">

  <nav>
    <div class="nav-container">
      <a href="/" class="nav-brand">
        <img src="/images/logo.png" alt="Momoy's Furniture Logo" class="nav-logo">
        <span>Momoy's Furniture</span>
      </a>
      <ul class="nav-links">
        <li><a href="/products">Products</a></li>
        <li><a href="/offers">Offers</a></li>
        <li><a href="/services">Services</a></li>
      </ul>
    </div>
  </nav>

  <div class="formcontainer">
    <div class="form-container">

      <div class="auth-tabs">
        <button type="button" class="auth-tab {{ $tab === 'login' ? 'active' : '' }}" id="tab-login" onclick="switchTab('login')">Login</button>
        <button type="button" class="auth-tab {{ $tab === 'register' ? 'active' : '' }}" id="tab-register" onclick="switchTab('register')">Sign Up</button>
      </div>

      {{-- Shared flash message (from Laravel session) --}}
      <div class="auth-message {{ session('auth_status') ? 'show ' . session('auth_status') : '' }}" id="auth-message">
        {{ session('auth_message') }}
      </div>

      {{-- ── Login Panel ── --}}
      <div class="auth-panel {{ $tab === 'login' ? 'active' : '' }}" id="panel-login">
        <h2 class="auth-title">Welcome Back</h2>

        {{-- BUG FIX: method="POST" + action + @csrf --}}
        <form id="login-form" method="POST" action="{{ route('auth.login.submit') }}">
          @csrf
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required
                   placeholder="your@email.com"
                   value="{{ old('email') }}">
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
            @error('password') <span class="field-error">{{ $message }}</span> @enderror
          </div>
          <button type="submit" class="auth-submit" id="login-btn">Login</button>
        </form>
      </div>

      {{-- ── Sign Up Panel ── --}}
      <div class="auth-panel {{ $tab === 'register' ? 'active' : '' }}" id="panel-register">
        <h2 class="auth-title">Create an Account</h2>

        {{-- BUG FIX: method="POST" + action + @csrf --}}
        <form id="register-form" method="POST" action="{{ route('auth.register.submit') }}">
          @csrf
          <div class="form-group">
            <label for="reg-fullName">Full Name</label>
            <input type="text" id="reg-fullName" name="fullName" required
                   placeholder="Ernesto D. Aninon Jr"
                   value="{{ old('fullName') }}">
            @error('fullName') <span class="field-error">{{ $message }}</span> @enderror
          </div>
          <div class="form-group">
            <label for="reg-email">Email Address</label>
            <input type="email" id="reg-email" name="email" required
                   placeholder="your@email.com"
                   value="{{ old('email') }}">
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
          </div>
          <div class="form-group">
            <label for="reg-password">Password</label>
            <input type="password" id="reg-password" name="password" required
                   placeholder="At least 6 characters" minlength="6">
            @error('password') <span class="field-error">{{ $message }}</span> @enderror
          </div>
          <button type="submit" class="auth-submit" id="register-btn">Create Account</button>
        </form>
      </div>

    </div>
  </div>

  {{-- REMOVED: firebase-config.js, auth.js (not needed for Laravel auth) --}}
  <script src="/js/mobile-nav.js"></script>

  <script>
    // ── Tab switching ──
    function switchTab(tab) {
      document.getElementById('panel-login').classList.toggle('active', tab === 'login');
      document.getElementById('panel-register').classList.toggle('active', tab === 'register');
      document.getElementById('tab-login').classList.toggle('active', tab === 'login');
      document.getElementById('tab-register').classList.toggle('active', tab === 'register');
      hideMessage();
    }

    function showAuthMessage(msg, type) {
      const el = document.getElementById('auth-message');
      el.textContent = msg;
      el.className = `auth-message show ${type}`;
    }

    function hideMessage() {
      const el = document.getElementById('auth-message');
      el.className = 'auth-message';
      el.textContent = '';
    }

    // Show loading state on submit (UX only — form still does a normal POST)
    document.getElementById('login-form').addEventListener('submit', function () {
      const btn = document.getElementById('login-btn');
      btn.textContent = 'Logging in...';
      btn.disabled = true;
    });

    document.getElementById('register-form').addEventListener('submit', function () {
      const btn = document.getElementById('register-btn');
      btn.textContent = 'Creating account...';
      btn.disabled = true;
    });

    // ── Nav scroll hide ──
    (function () {
      const nav = document.querySelector('nav');
      let lastY = window.scrollY, ticking = false;
      window.addEventListener('scroll', () => {
        if (!ticking) {
          window.requestAnimationFrame(() => {
            const currentY = window.scrollY;
            nav.classList.toggle('nav-hidden', currentY > lastY && currentY > 80);
            if (currentY < lastY) nav.classList.remove('nav-hidden');
            lastY = currentY;
            ticking = false;
          });
          ticking = true;
        }
      }, { passive: true });
    })();
  </script>
</body>
</html>