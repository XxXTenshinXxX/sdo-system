<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Simpleng Login Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Card container */
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        /* Header */
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 8px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        /* Input groups */
        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
            font-family: inherit;
        }

        .input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Options row (remember + forgot) */
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
            cursor: pointer;
        }

        .remember input {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot a:hover {
            text-decoration: underline;
        }

        /* Login button */
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Sign up link */
        .signup {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .signup a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 600;
        }

        .signup a:hover {
            text-decoration: underline;
        }

        /* Simple message area */
        .message {
            margin-top: 15px;
            text-align: center;
            font-size: 13px;
            color: #e74c3c;
            min-height: 40px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <h1>📱 Login</h1>
            <p>Welcome back! Mag-login para makapagpatuloy</p>
        </div>

        <form id="loginForm">
            <div class="input-group">
                <label>📧 Email o Username</label>
                <input type="text" id="username" placeholder="halimbawa@email.com" autocomplete="off" required>
            </div>

            <div class="input-group">
                <label>🔒 Password</label>
                <input type="password" id="password" placeholder="Ilagay ang iyong password" required>
            </div>

            <div class="options">
                <label class="remember">
                    <input type="checkbox" id="rememberCheck"> Tandaan ako
                </label>
                <div class="forgot">
                    <a href="#">Nakalimutan ang password?</a>
                </div>
            </div>

            <button type="submit" class="login-btn">🔓 Mag-login</button>

            <div class="signup">
                Wala pang account? <a href="#">Mag-sign up</a>
            </div>
            
            <div id="message" class="message"></div>
        </form>
    </div>

    <script>
        // Simple client-side validation at demo message
        const form = document.getElementById('loginForm');
        const messageDiv = document.getElementById('message');
        
        // Optional: demo credentials (pwede mong palitan)
        const DEMO_USER = "user@example.com";
        const DEMO_PASS = "password123";
        
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Huwag mag-reload ng page
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const remember = document.getElementById('rememberCheck').checked;
            
            // Basic validation
            if (username === "" || password === "") {
                messageDiv.style.color = "#e74c3c";
                messageDiv.innerHTML = "⚠️ Paki-fill ang lahat ng fields.";
                return;
            }
            
            // Demo authentication (palitan ng real backend kung kinakailangan)
            if (username === DEMO_USER && password === DEMO_PASS) {
                messageDiv.style.color = "#27ae60";
                messageDiv.innerHTML = "✅ Login successful! (Demo mode)";
                
                // Kung naka-check ang "Remember me" - i-save sa localStorage (demo)
                if (remember) {
                    localStorage.setItem('rememberedUser', username);
                    console.log("Na-save ang user sa localStorage");
                } else {
                    localStorage.removeItem('rememberedUser');
                }
                
                // Dito pwedeng mag-redirect sa dashboard
                // setTimeout(() => { window.location.href = "dashboard.html"; }, 1000);
            } else {
                messageDiv.style.color = "#e74c3c";
                messageDiv.innerHTML = "❌ Maling email o password. Subukan muli.<br><small>(Gamitin: user@example.com / password123)</small>";
            }
        });
        
        // Optional: Auto-fill kung may na-save na "Remember me"
        window.addEventListener('DOMContentLoaded', function() {
            const savedUser = localStorage.getItem('rememberedUser');
            if (savedUser) {
                document.getElementById('username').value = savedUser;
                document.getElementById('rememberCheck').checked = true;
                // Pwedeng i-focus ang password field
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>