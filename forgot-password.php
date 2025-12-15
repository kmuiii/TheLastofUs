<?php
session_start();
require 'koneksi.php';
require 'module.php';

preventAuthPage();

// Initialize reset step
if (!isset($_SESSION['reset_step'])) {
    $_SESSION['reset_step'] = 1;
}

$step = $_SESSION['reset_step'];

// Send reset code
if (isset($_POST['send_code'])) {
    $result = sendResetCode($conn, $_POST['email']);

    if ($result['status']) {
        $_SESSION['reset_step'] = 2;
        $_SESSION['reset_email'] = $_POST['email'];
        $_SESSION['reset_code']  = $result['code'];
        $step = 2;
    } else {
        $error = $result['message'];
    }
}

// Verify reset code
if (isset($_POST['verify_code'])) {
    $result = verifyResetCode($conn, $_POST['code']);

    if ($result['status']) {
        $_SESSION['reset_step'] = 3;
        $_SESSION['reset_verified'] = true;
        $step = 3;
    } else {
        $error = $result['message'];
    }
}

// Reset password
if (isset($_POST['reset_password'])) {
    $result = resetPassword(
        $conn,
        $_POST['password'],
        $_POST['confirm']
    );

    if ($result['status']) {
        // bersihkan session reset
        unset($_SESSION['reset_step']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_code']);

        header('Location: login.php?reset=success');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Last Of Us - Password Recovery</title>
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
            background: rgba(0, 0, 0, 0.75);
            z-index: 0;
        }

        .recovery-container {
            max-width: 500px;
            width: 90%;
            background: linear-gradient(135deg, #6a6a6a 0%, #4a4a4a 50%, #3a3a3a 100%);
            border-radius: 12px;
            padding: 15px;
            position: relative;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.95), 
                0 10px 30px rgba(0, 0, 0, 0.8),
                inset 0 2px 4px rgba(255, 255, 255, 0.1),
                inset 0 -2px 4px rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .recovery-container::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            background-color: rgba(6, 5, 4, 0.95);
            background-image: url('images/Group 8 (1).png');
            background-size: cover;
            background-position: center;
            background-blend-mode: multiply;
            pointer-events: none;
            border-radius: 8px;
            z-index: 0;
        }

        .recovery-inner {
            position: relative;
            z-index: 1;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .content-wrapper {
            flex: 1;
            overflow-y: auto;
            max-height: 500px;
            padding-right: 5px;
            margin-bottom: 15px;
        }

        .content-wrapper::-webkit-scrollbar {
            width: 4px;
        }

        .content-wrapper::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
        }

        .content-wrapper::-webkit-scrollbar-thumb {
            background: #b85c38;
            border-radius: 2px;
        }

        .icon-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .lock-image {
            width: 70px;
            height: 70px;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(184, 92, 56, 0.5));
        }

        .recovery-title {
            text-align: center;
            color: #f0f0f0;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .recovery-subtitle {
            text-align: center;
            color: #a8a8a8;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }

        .step-indicator {
            text-align: center;
            margin-bottom: 25px;
        }

        .step {
            display: inline-block;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(40, 40, 40, 0.8);
            border: 2px solid rgba(184, 92, 56, 0.4);
            color: #888;
            font-size: 16px;
            font-weight: 700;
            line-height: 31px;
            margin: 0 8px;
            transition: all 0.3s;
        }

        .step.active {
            background: #b85c38;
            border-color: #b85c38;
            color: white;
            box-shadow: 0 0 15px rgba(184, 92, 56, 0.5);
        }

        .step.completed {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .form-section {
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
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
            padding: 14px 18px;
            background: rgba(240, 240, 240, 0.95);
            border: 2px solid rgba(200, 200, 200, 0.5);
            border-radius: 4px;
            color: #333;
            font-size: 15px;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            font-family: 'Antonio', sans-serif;
        }

        input:focus {
            outline: none;
            border-color: #b85c38;
            background: rgba(255, 255, 255, 0.98);
        }

        input.error {
            border-color: #dc3545;
        }

        input.success {
            border-color: #28a745;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
            min-height: 18px;
        }

        .error-message.show {
            display: block;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.15);
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .success-message-text {
            color: #28a745;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .verification-code-container {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 20px 0;
        }

        .code-input {
            width: 45px;
            height: 50px;
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            padding: 0;
            border-radius: 6px;
        }

        .form-footer {
            margin-top: auto;
        }

        .action-btn {
            width: 100%;
            padding: 15px;
            background: #6E3420;
            border: none;
            border-radius: 50px;
            color: #f0f0f0;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(110, 52, 32, 0.4);
            font-family: 'Antonio', sans-serif;
            margin-bottom: 10px;
        }

        .action-btn:hover:not(:disabled) {
            background: #8B4626;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 52, 32, 0.6);
        }

        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .action-btn.secondary {
            background: rgba(80, 80, 80, 0.8);
        }

        .action-btn.secondary:hover {
            background: rgba(100, 100, 100, 0.9);
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #d4d4d4;
            text-decoration: none;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #b85c38;
        }

        .rivet {
            position: absolute;
            width: 15px;
            height: 15px;
            background: radial-gradient(circle, #a8a8a8 0%, #707070 40%, #4a4a4a 80%, #2a2a2a 100%);
            border-radius: 50%;
            box-shadow: 
                inset 0 2px 3px rgba(0, 0, 0, 0.6), 
                inset 0 -1px 2px rgba(255, 255, 255, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.5);
            z-index: 10;
            border: 1px solid rgba(80, 80, 80, 0.5);
        }

        .rivet-tl { top: 20px; left: 20px; }
        .rivet-tr { top: 20px; right: 20px; }
        .rivet-bl { bottom: 20px; left: 20px; }
        .rivet-br { bottom: 20px; right: 20px; }

        .hidden {
            display: none;
        }

        @media (max-width: 576px) {
            .recovery-inner {
                padding: 30px 25px;
            }

            .recovery-title {
                font-size: 24px;
            }

            .recovery-subtitle {
                font-size: 13px;
            }

            .code-input {
                width: 40px;
                height: 45px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="rivet rivet-tl"></div>
        <div class="rivet rivet-tr"></div>
        <div class="rivet rivet-bl"></div>
        <div class="rivet rivet-br"></div>

        <div class="recovery-inner">
            <div class="content-wrapper">
                <div class="icon-container">
                    <img src="images/Lock Icon.png" alt="Lock Icon" class="lock-image">
                </div>

                <h1 class="recovery-title">PASSWORD RECOVERY</h1>
                <p class="recovery-subtitle" id="subtitleText">
                    Enter your email to receive a recovery code
                </p>

                <div class="step-indicator">
                    <span class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">1</span>
                    <span class="step <?= $step == 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">2</span>
                    <span class="step <?= $step == 3 ? 'active' : '' ?>">3</span>
                </div>

                <?php if (!empty($error)): ?>
                <div style="
                    background: rgba(220,53,69,.15);
                    color:#dc3545;
                    padding:12px;
                    margin-bottom:15px;
                    border-radius:6px;
                    text-align:center;">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <!-- Step 1: Email Input -->
                <?php if ($step === 1): ?>
                <form method="POST">
                    <div class="form-section">
                        <div class="input-group">
                            <label class="input-label">EMAIL ADDRESS</label>
                            <input type="email" name="email" required>
                        </div>

                        <button class="action-btn" name="send_code">
                            SEND RECOVERY CODE
                        </button>
                    </div>
                </form>
                <?php endif; ?>

                <!-- Step 2: Verification Code -->
                <?php if ($step === 2): ?>
                    <form method="POST">
                        <div class="form-section">
                            <div class="input-group">
                                <label class="input-label">ENTER 6-DIGIT CODE</label>
                                <input type="text" name="code" maxlength="6" required>
                            </div>

                            <button class="action-btn" name="verify_code">
                                VERIFY CODE
                            </button>
                        </div>
                    </form>

                    <p style="color:#aaa;text-align:center">
                        Demo Code: <b><?= htmlspecialchars($_SESSION['reset_code'] ?? '') ?></b>
                    </p>
                    <?php endif; ?>


                <!-- Step 3: New Password -->
                <?php if ($step === 3): ?>
                <form method="POST">
                    <div class="form-section">
                        <div class="input-group">
                            <label class="input-label">NEW PASSWORD</label>
                            <input type="password" name="password" required>
                        </div>

                        <div class="input-group">
                            <label class="input-label">CONFIRM PASSWORD</label>
                            <input type="password" name="confirm" required>
                        </div>

                        <button class="action-btn" name="reset_password">
                            RESET PASSWORD
                        </button>
                    </div>
                </form>
                <?php endif; ?>

            <div class="form-footer">
                <div class="back-link">
                    <a href="login.php">← BACK TO LOGIN</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>