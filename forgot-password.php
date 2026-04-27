<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Forgot Password - Reset Your Account</title>
    <link rel="icon" type="image/png" href="assets/images/SDO-Logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #3498db;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Card container - LANDSCAPE STYLE */
        .forgot-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            padding: 30px 40px;
            transition: transform 0.3s ease;
        }

        .forgot-card:hover {
            transform: translateY(-3px);
        }

        /* Landscape layout using flexbox */
        .landscape-content {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        /* Left side - illustration */
        .left-side {
            flex: 1;
            text-align: center;
        }

        .left-side svg {
            max-width: 100%;
            height: auto;
        }

        .left-side h3 {
            font-size: 14px;
            color: #666;
            margin-top: 15px;
            font-weight: normal;
        }

        .left-side p {
            font-size: 11px;
            color: #888;
            margin-top: 8px;
        }

        /* Right side - form */
        .right-side {
            flex: 1;
        }

        /* Header - small text */
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 90px;
            height: auto;
            margin-bottom: 10px;
        }

        .logo h1 {
            font-size: 22px;
            color: #333;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 11px;
        }

        /* Input groups - small text */
        .input-group {
            margin-bottom: 15px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 11px;
        }

        .input-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 12px;
            transition: all 0.3s ease;
            outline: none;
            font-family: inherit;
        }

        .input-group input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.1);
        }

        /* Reset button */
        .reset-btn {
            width: 100%;
            background-color: #3498db;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .reset-btn:hover {
            background-color: #2980b9;
            transform: scale(1.01);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }

        /* Back to login link */
        .back-login {
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .back-login a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            font-size: 10px;
        }

        .back-login a:hover {
            text-decoration: underline;
        }

        /* Message area */
        .message {
            margin-top: 12px;
            text-align: center;
            font-size: 10px;
            min-height: 35px;
            padding: 8px;
            border-radius: 6px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Responsive */
        @media (max-width: 650px) {
            .landscape-content {
                flex-direction: column;
                gap: 20px;
            }
            
            .forgot-card {
                max-width: 400px;
                padding: 25px 20px;
            }
            
            .left-side {
                display: none;
            }
        }
        
        @media (min-width: 1024px) and (orientation: landscape) {
            .forgot-card {
                max-width: 900px;
                padding: 35px 50px;
            }
            
            .input-group input {
                padding: 9px 14px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-card">
        <div class="landscape-content">
            
            <!-- RIGHT SIDE: Forgot Password Form -->
            <div class="right-side">
                <div class="logo">
                    <img src="assets/images/SDO-Logo.png" alt="SDO Logo">
                    <h1>Reset Password</h1>
                    <p>Enter your email address to reset your password</p>
                </div>

                <form id="forgotPasswordForm">
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" placeholder="example@email.com" autocomplete="off" required>
                    </div>

                    <button type="submit" class="reset-btn">Send Reset Link</button>

                    <div class="back-login">
                        <a href="login.php">Back to Login</a>
                    </div>
                    
                    <div id="message" class="message"></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('forgotPasswordForm');
        const messageDiv = document.getElementById('message');
        
        
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            
            // Remove existing classes
            messageDiv.className = 'message';
            
            // Basic validation
            if (email === "") {
                messageDiv.classList.add('error');
                messageDiv.innerHTML = "⚠️ Pakilagay ang iyong email address.";
                return;
            }
            
            // Email format validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                messageDiv.classList.add('error');
                messageDiv.innerHTML = "❌ Invalid email format. Halimbawa: pangalan@domain.com";
                return;
            }
            
            // Check if email is registered (demo purpose)
            const isRegistered = registeredEmails.includes(email.toLowerCase());
            
            if (isRegistered) {
                // Success - email exists
                messageDiv.classList.add('success');
                messageDiv.innerHTML = "✅ Reset link naipadala na sa iyong email!<br><small>(Demo: suriin ang console para sa link)</small>";
                
                // Simulate sending reset link
                console.log(`📧 Reset link sent to: ${email}`);
                console.log(`🔗 Reset link: http://localhost/reset-password?token=demo_token_${Date.now()}`);
                
                // Optional: I-clear ang email field pagkatapos ng 3 seconds
                setTimeout(() => {
                    // Puwedeng mag-redirect sa login page
                    // window.location.href = "login.html";
                }, 3000);
            } else {
                // Email not found
                messageDiv.classList.add('error');
                messageDiv.innerHTML = "❌ Hindi mahanap ang email na ito.<br><small>Gamitin ang: user@example.com para sa demo</small>";
            }
        });
        
    </script>
</body>
</html>
