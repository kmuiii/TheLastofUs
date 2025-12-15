<?php
session_start();
include 'koneksi.php';
include 'module.php';

preventAuthPage();

$error = null;

if (isset($_POST['login'])) {
    if (loginUser($conn, $_POST['email'], $_POST['password'])) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Email/Username atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Last Of Us - Survivor Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Antonio', sans-serif;
            background-image: url('images/bg character.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 0;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.70);
            z-index: 0;
        }

        .login-container {
            display: flex;
            max-width: 1200px;
            width: 90%;
            height: 700px;
            background: rgba(13, 13, 13, 0.95);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.9);
            position: relative;
            z-index: 1;
        }

        .poster-section {
            flex: 0 0 45%;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            position: relative;
            overflow: hidden;
        }

        .poster-section::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 30%;
            height: 100%;
            background: linear-gradient(to right, transparent 0%, rgba(13, 13, 13, 0.5) 50%, rgba(13, 13, 13, 0.9) 100%);
            pointer-events: none;
            z-index: 2;
        }

        .poster-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            position: relative;
            z-index: 1;
        }

        .login-section {
            flex: 0 0 55%;
            padding: 40px 60px 40px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: transparent;
            z-index: 3;
        }

        .login-content {
            position: relative;
            z-index: 1;
            background-color: rgba(6, 5, 4, 0.9);
            border: 3px solid rgba(184, 92, 56, 0.6);
            border-radius: 8px;
            padding: 45px 50px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.8);
            overflow: hidden;
            width: 100%;
            backdrop-filter: blur(10px);
        }

        .login-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('images/Group 8 (1).png');
            background-size: cover;
            background-position: center;
            opacity: 10;
            mix-blend-mode: multiply;
            pointer-events: none;
            z-index: 0;
        }

        .login-title {
            text-align: center;
            color: #f0f0f0;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
            position: relative;
            z-index: 1;
        }

        .login-subtitle {
            text-align: center;
            color: #b85c38;
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 35px;
            letter-spacing: 1.5px;
            position: relative;
            z-index: 1;
        }

        .input-group {
            margin-bottom: 22px;
            position: relative;
            z-index: 1;
        }

        .input-label {
            display: block;
            color: #d4d4d4;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px 20px;
            background: rgba(240, 240, 240, 0.95);
            border: 2px solid rgba(200, 200, 200, 0.5);
            border-radius: 4px;
            color: #333;
            font-size: 15px;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            font-family: 'Antonio', sans-serif;
            position: relative;
            z-index: 1;
        }

        input::placeholder {
            color: #666;
            letter-spacing: 0.5px;
        }

        input:focus {
            outline: none;
            border-color: #b85c38;
            background: rgba(255, 255, 255, 0.98);
        }

        input.error {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
            letter-spacing: 0.5px;
        }

        .error-message.show {
            display: block;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: #6E3420;
            border: none;
            border-radius: 50px;
            color: #f0f0f0;
            font-size: 17px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            margin-top: 28px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(110, 52, 32, 0.4);
            font-family: 'Antonio', sans-serif;
            position: relative;
            z-index: 1;
        }

        .login-btn:hover {
            background: #8B4626;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 52, 32, 0.6);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .links {
            text-align: center;
            margin-top: 24px;
            position: relative;
            z-index: 1;
        }

        .links a {
            color: #d4d4d4;
            text-decoration: none;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin: 10px 0;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #b85c38;
        }

        @media (max-width: 968px) {
            .login-container {
                flex-direction: column;
                height: auto;
                min-height: 700px;
                background: #0d0d0d;
            }

            .poster-section {
                flex: 0 0 250px;
                padding: 30px 20px;
            }

            .poster-section::after {
                display: none;
            }

            .poster-image {
                max-height: 300px;
            }

            .login-section {
                padding: 40px 30px;
            }

            .login-content {
                padding: 35px 35px;
            }

            .login-title {
                font-size: 30px;
            }
        }

        @media (max-width: 576px) {
            .login-section {
                padding: 30px 20px;
            }

            .login-content {
                padding: 30px 25px;
            }

            .login-title {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Poster Section -->
        <div class="poster-section">
            <img src="images/tlou login.png" alt="The Last Of Us" class="poster-image">
        </div>

        <!-- Login Section -->
        <div class="login-section">
            <div class="login-content">
                <h1 class="login-title">SURVIVOR LOGIN</h1>
                <p class="login-subtitle">ACCESS YOUR JOURNEY</p>

                <?php if (!empty($error)) : ?>
                    <div style="
                        background: rgba(220,53,69,.15);
                        color:#dc3545;
                        padding:12px;
                        margin-bottom:15px;
                        border-radius:6px;
                        font-size:14px;
                        text-align:center;
                        letter-spacing:1px;
                    ">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="">
                    <div class="input-group">
                        <label class="input-label">EMAIL OR USERNAME</label>
                        <input type="text" name="email" id="username" placeholder="salsabilafirrah10@gmail.com" required>
                        <span class="error-message" id="usernameError">Please enter your email or username</span>
                    </div>
                    <div class="input-group">
                        <label class="input-label">PASSWORD</label>
                        <input type="password" name="password" id="password" placeholder="Enter your password" required>
                        <span class="error-message" id="passwordError">Please enter your password</span>
                    </div>
                    <button type="submit" name="login" class="login-btn">LOGIN</button>
                    <div class="links">
                        <a href="forgot-password.php">FORGOT PASSWORD?</a>
                        <a href="register.php">CREATE ACCOUNT?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>