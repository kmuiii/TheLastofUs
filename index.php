<?php
session_start();
require 'koneksi.php';
require 'module.php';

requireLogin();
$user = getLoggedInUser($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Last Of Us - Story</title>
    <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Antonio', sans-serif;
            background-image: url('images/image 8.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, 
                rgba(10, 10, 10, 1) 0%, 
                rgba(10, 10, 10, 0.95) 25%, 
                rgba(10, 10, 10, 0.85) 40%,
                rgba(10, 10, 10, 0.5) 60%,
                transparent 80%);
            z-index: 0;
        }

        .navbar {
            background-color: rgba(101, 101, 101, 0.95);
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 10;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.8), 0 2px 10px rgba(0, 0, 0, 0.6);
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('images/Frame 39.png');
            background-size: cover;
            background-position: center;
            opacity: 50;
            mix-blend-mode: multiply;
            pointer-events: none;
        }

        .logo {
            font-size: 42px;
            font-weight: 700;
            color: #d4d4d4;
            letter-spacing: 2px;
            text-transform: uppercase;
            position: relative;
            z-index: 1;
        }

        .nav-menu {
            display: flex;
            gap: 40px;
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .nav-menu li {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .nav-menu li.active {
            color: #b85c38;
        }

        .nav-menu li:not(.active) {
            color: #d4d4d4;
        }

        .nav-menu li:hover {
            color: #b85c38;
        }

        .hero-section {
            display: flex;
            min-height: calc(100vh - 82px);
            position: relative;
            z-index: 1;
        }

        .hero-content {
            flex: 1;
            padding: 80px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .season-tag {
            color: #b85c38;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 120px;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 0.9;
            margin-bottom: 30px;
            letter-spacing: -2px;
        }

        .hero-meta {
            font-size: 18px;
            color: #d4d4d4;
            margin-bottom: 35px;
            font-weight: 400;
        }

        .hero-description {
            font-size: 20px;
            color: #d4d4d4;
            line-height: 1.6;
            margin-bottom: 50px;
            max-width: 600px;
            font-weight: 400;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #b85c38;
            color: #ffffff;
            padding: 18px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s;
            width: fit-content;
            box-shadow: 0 4px 15px rgba(184, 92, 56, 0.4);
            border: none;
            cursor: pointer;
        }

        .cta-button:hover {
            background: #a04d2f;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(184, 92, 56, 0.6);
        }

        .play-icon {
            width: 0;
            height: 0;
            border-left: 12px solid #ffffff;
            border-top: 7px solid transparent;
            border-bottom: 7px solid transparent;
        }

        .hero-image {
            flex: 1;
            position: relative;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info span {
            color: #d4d4d4;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .logout-btn {
            background: rgba(220, 53, 69, 0.8);
            border: 2px solid rgba(220, 53, 69, 0.6);
            color: #f0f0f0;
            padding: 10px 25px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Antonio', sans-serif;
        }

        .logout-btn:hover {
            background: rgba(220, 53, 69, 1);
            border-color: #dc3545;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }

        @media (max-width: 1024px) {
            .hero-section {
                flex-direction: column;
            }

            .hero-title {
                font-size: 80px;
            }

            .hero-content {
                padding: 60px 40px;
            }

            .hero-image {
                min-height: 400px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">THE LAST OF US</div>
        <div style="display: flex; align-items: center; gap: 60px; position: relative; z-index: 1;">
            <div class="user-section">
                <div class="user-info">
                    <span>HI, <?= htmlspecialchars($user['username']); ?></span>
                </div>
                <a href="logout.php" name="logout" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">LOGOUT</a>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-content">
            <div class="season-tag">NEW SEASON • PROLOGUE</div>
            <h1 class="hero-title">THE LAST<br>OF US</h1>
            <div class="hero-meta">2023 | TV-MA | 98% Match</div>
            <p class="hero-description">
                Batas antara manusia dan monster kian memudar.<br>
                Di dunia pasca-pandemi yang brutal ini, kamu tidak<br>
                bisa menyelamatkan semua orang. Tentukan<br>
                takdirmu sendiri sebelum alam liar mengambilnya<br>
                darimu.
            </p>
            <a href="character.php" class="cta-button">
                <div class="play-icon"></div>
                MULAI CERITAMU
            </a>
        </div>
        <div class="hero-image"></div>
    </div>
</body>
</html>