<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NANOS | Secure Access</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.5);
            --bg: #020617;
            --card: rgba(15, 23, 42, 0.8);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: var(--bg);
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(168, 85, 247, 0.15) 0%, transparent 40%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-main);
            overflow: hidden;
        }

        .container {
            width: 100%;
            max-width: 420px;
            perspective: 1000px;
            padding: 20px;
        }

        .auth-card {
            background: var(--card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .logo-area { text-align: center; margin-bottom: 32px; }
        .logo-icon {
            background: linear-gradient(135deg, var(--primary), #a855f7);
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 10px 20px var(--primary-glow);
        }

        h2 { font-size: 24px; font-weight: 700; margin-bottom: 8px; text-align: center; }
        p.subtitle { color: var(--text-dim); font-size: 14px; text-align: center; margin-bottom: 32px; }

        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
            width: 18px;
        }

        input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: white;
            font-size: 15px;
            transition: 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .otp-display {
            display: none;
            text-align: center;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .otp-display input {
            text-align: center;
            letter-spacing: 12px;
            font-size: 24px;
            padding: 14px;
            font-family: monospace;
        }

        button {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        button:hover { background: #4f46e5; transform: translateY(-1px); }
        button:active { transform: translateY(1px); }

        .footer-action {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
            color: var(--text-dim);
        }

        .footer-action span {
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
        }

        .loader {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .hidden { display: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="auth-card" id="card">
        <div class="logo-area">
            <div class="logo-icon"><i data-lucide="shield-check"></i></div>
            <h2 id="title">Welcome Back</h2>
            <p class="subtitle" id="subtitle">Access the secure NANOS terminal</p>
        </div>

        <div id="form-body">
            <div id="credential-fields">
                <div class="input-group">
                    <i data-lucide="mail"></i>
                    <input type="email" id="email" placeholder="Email Address">
                </div>
                <div class="input-group" id="pass-group">
                    <i data-lucide="lock"></i>
                    <input type="password" id="password" placeholder="Password">
                </div>
            </div>

            <div id="otp-group" class="otp-display">
                <div class="input-group">
                    <input type="text" id="otp" placeholder="000000" maxlength="6">
                </div>
                <p class="subtitle">Check your inbox for the authorization code</p>
            </div>

            <button onclick="handleAuth()" id="mainBtn">
                <span id="btnText">Secure Login</span>
                <div class="loader" id="loader"></div>
            </button>
        </div>

        <div class="footer-action" id="footer">
            Don't have an account? <span onclick="toggleView()" id="toggleBtn">Create one</span>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    let mode = 'login'; 

    function toggleView() {
        const card = document.getElementById('card');
        card.style.transform = 'rotateY(10deg)';
        
        setTimeout(() => {
            if (mode === 'login') {
                mode = 'register';
                document.getElementById('title').innerText = 'New Identity';
                document.getElementById('btnText').innerText = 'Initialize Account';
                document.getElementById('toggleBtn').innerText = 'Sign in here';
                document.getElementById('footer').firstChild.textContent = 'Already protected? ';
            } else {
                mode = 'login';
                document.getElementById('title').innerText = 'Welcome Back';
                document.getElementById('btnText').innerText = 'Secure Login';
                document.getElementById('toggleBtn').innerText = 'Create one';
                document.getElementById('footer').firstChild.textContent = "Don't have an account? ";
            }
            card.style.transform = 'rotateY(0deg)';
        }, 200);
    }

    async function handleAuth() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const otp = document.getElementById('otp').value;
        const loader = document.getElementById('loader');
        const btnText = document.getElementById('btnText');

        if(!email || (mode !== 'verify' && !password)) {
            Swal.fire({ icon: 'warning', title: 'Missing Data', text: 'Please fill in all fields.', background: '#0f172a', color: '#fff' });
            return;
        }

        loader.style.display = 'block';
        btnText.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('action', mode);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('otp', otp);

        try {
            const response = await fetch('auth.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.status === 'success') {
                if (mode === 'verify') {
                    Swal.fire({ icon: 'success', title: 'Authorized', text: 'Access granted. Redirecting...', showConfirmButton: false, timer: 2000, background: '#0f172a', color: '#fff' });
                    setTimeout(() => window.location.href = 'dashboard.php', 2000);
                } else {
                    mode = 'verify';
                    document.getElementById('credential-fields').classList.add('hidden');
                    document.getElementById('otp-group').style.display = 'block';
                    document.getElementById('title').innerText = 'Two-Factor';
                    document.getElementById('btnText').innerText = 'Confirm Access';
                    document.getElementById('footer').classList.add('hidden');
                    document.getElementById('otp').focus();
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Denied', text: data.message, background: '#0f172a', color: '#fff' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'System Error', text: 'Connection to server failed.', background: '#0f172a', color: '#fff' });
        } finally {
            loader.style.display = 'none';
            btnText.style.opacity = '1';
        }
    }
</script>
</body>
</html>