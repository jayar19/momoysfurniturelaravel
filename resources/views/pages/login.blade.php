<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - MOMOY'S Furniture</title>
    
    <link rel="stylesheet" href="/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* [Keep your existing CSS styles from the previous version here] */
        .auth-tabs { display: flex; border-radius: 999px; background: rgba(255,255,255,0.15); padding: 4px; margin-bottom: 2rem; gap: 4px; }
        .auth-tab { flex: 1; padding: 0.6rem 1rem; border: none; border-radius: 999px; font-size: 0.95rem; font-weight: 600; cursor: pointer; background: transparent; color: rgba(255,255,255,0.7); transition: all 0.25s; font-family: 'DM Sans', sans-serif; }
        .auth-tab.active { background: #fff; color: #1a1a1a; }
        .auth-panel { display: none; animation: fadeUp 0.3s ease both; }
        .auth-panel.active { display: block; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .auth-title { text-align: center; margin-bottom: 1.5rem; color: #fff; font-family: 'Noto Serif Display', serif; font-size: 1.4rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 0.75rem 1rem; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #fff; border-radius: 8px; box-sizing: border-box; }
        .auth-submit { width: 100%; padding: 0.85rem; background: #fff; color: #1a1a1a; border: none; border-radius: 999px; font-weight: 700; cursor: pointer; margin-top: 0.5rem; }
        .auth-submit:hover:not(:disabled) { background: #f1c40f; }
        .auth-message { display: none; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center; }
        .auth-message.success { background: rgba(39,174,96,0.25); color: #afffcb; border: 1px solid rgba(39,174,96,0.4); }
        .auth-message.error { background: rgba(231,76,60,0.25); color: #ffbdbd; border: 1px solid rgba(231,76,60,0.4); }
        .formcontainer { min-height: calc(100vh - 75px); display: flex; align-items: center; justify-content: center; }
        .form-container { width: 100%; max-width: 420px; padding: 0 1rem; }
    </style>
</head>
<body class="formbody">

    <div class="formcontainer">
        <div class="form-container">
            <div class="auth-tabs">
                <button class="auth-tab active" id="tab-login" onclick="switchTab('login')">Login</button>
                <button class="auth-tab" id="tab-register" onclick="switchTab('register')">Sign Up</button>
            </div>

            <div class="auth-message" id="auth-message"></div>

            <!-- Login Panel -->
            <div class="auth-panel active" id="panel-login">
                <h2 class="auth-title">Welcome Back</h2>
                <form id="login-form">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="email" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="password" required placeholder="Enter password">
                    </div>
                    <button type="submit" class="auth-submit">Login</button>
                </form>
            </div>

            <!-- Register Panel -->
            <div class="auth-panel" id="panel-register">
                <h2 class="auth-title">Create an Account</h2>
                <form id="register-form">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="reg-fullName" placeholder="Ernesto D. Aninon Jr">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="reg-email" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="reg-password" required placeholder="Min. 6 characters" minlength="6">
                    </div>
                    <button type="submit" class="auth-submit">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <script>
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
            el.className = `auth-message ${type}`;
            el.style.display = 'block';
        }

        function hideMessage() {
            document.getElementById('auth-message').style.display = 'none';
        }

        // --- AUTHENTICATION LOGIC ---

        // Login Submit
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('.auth-submit');
            btn.disabled = true;
            btn.textContent = 'Verifying...';
            hideMessage();

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        email: document.getElementById('email').value,
                        password: document.getElementById('password').value
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    // Your controller returns 'error' key for invalid credentials
                    throw new Error(data.error || (data.errors ? Object.values(data.errors).flat()[0] : 'Login failed'));
                }

                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));

                showAuthMessage('Login successful! Redirecting...', 'success');
                setTimeout(() => window.location.href = '/products', 1000);

            } catch (err) {
                showAuthMessage(err.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Login';
            }
        });

        // Register Submit
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('.auth-submit');
            btn.disabled = true;
            btn.textContent = 'Registering...';
            hideMessage();

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        fullName: document.getElementById('reg-fullName').value, // Matches your Controller $data
                        email: document.getElementById('reg-email').value,
                        password: document.getElementById('reg-password').value
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    // Laravel standard validation uses 'errors' object
                    const errorMsg = data.errors ? Object.values(data.errors).flat()[0] : (data.error || 'Registration failed');
                    throw new Error(errorMsg);
                }

                // Your controller returns sessionPayload (token + user) on register too
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));

                showAuthMessage('Account created! Welcome.', 'success');
                setTimeout(() => window.location.href = '/products', 1000);

            } catch (err) {
                showAuthMessage(err.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Create Account';
            }
        });
    </script>
</body>
</html>