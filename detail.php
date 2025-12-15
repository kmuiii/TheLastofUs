<?php
session_start();
require 'koneksi.php';
require 'module.php';

requireLogin();
$user = getLoggedInUser($conn);

$selectedCharacter = $_SESSION['character'] ?? null;
$inventoryItems = is_array($_SESSION['inventory'] ?? null) ? $_SESSION['inventory'] : [];

$allowedCharacters = ['Ellie', 'Joel', 'Abby'];

if ($selectedCharacter && !in_array($selectedCharacter, $allowedCharacters, true)) {
    unset($_SESSION['character']);
    header('Location: character.php');
    exit;
}

if (!$selectedCharacter) {
    header('Location: character.php');
    exit;
}

if (empty($inventoryItems)) {
    header('Location: inventory.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Last Of Us - Detail</title>
    <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }

        body {
            font-family: 'Antonio', sans-serif;
            background-image: url('images/bg detail.png');
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
            background: rgba(0, 0, 0, 0.80);
            z-index: 0;
        }

        /* Popup Overlay */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease-out;
        }

        .popup-container {
            background: linear-gradient(135deg, #6a6a6a 0%, #4a4a4a 50%, #3a3a3a 100%);
            border-radius: 12px;
            padding: 20px;
            position: relative;
            box-shadow: 
                0 25px 80px rgba(0, 0, 0, 0.95), 
                0 15px 40px rgba(0, 0, 0, 0.8),
                inset 0 2px 4px rgba(255, 255, 255, 0.1),
                inset 0 -2px 4px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            width: 90%;
            z-index: 1001;
            animation: scaleIn 0.4s ease-out;
        }

        .popup-container::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            background-color: rgba(20, 19, 19, 0.95);
            background-image: url('images/Group 8 (1).png');
            background-size: cover;
            background-position: center;
            background-blend-mode: multiply;
            pointer-events: none;
            border-radius: 4px;
            z-index: 1;
        }

        .popup-content {
            position: relative;
            z-index: 2;
            padding: 40px 50px;
            text-align: center;
        }

        .popup-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: #b85c38;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .popup-title {
            font-size: 42px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .popup-message {
            font-size: 24px;
            color: #a8a8a8;
            margin-bottom: 40px;
            letter-spacing: 1.5px;
            line-height: 1.5;
        }

        .popup-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .popup-button {
            padding: 18px 40px;
            background: #6E3420;
            border: none;
            border-radius: 50px;
            color: #f0f0f0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Antonio', sans-serif;
            box-shadow: 0 4px 15px rgba(110, 52, 32, 0.4);
            min-width: 180px;
        }

        .popup-button:hover {
            background: #8B4626;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 52, 32, 0.6);
        }

        .popup-button.secondary {
            background: rgba(80, 80, 80, 0.8);
        }

        .popup-button.secondary:hover {
            background: rgba(100, 100, 100, 0.9);
        }

        .popup-rivet {
            position: absolute;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle, #a8a8a8 0%, #707070 40%, #4a4a4a 80%, #2a2a2a 100%);
            border-radius: 50%;
            box-shadow: 
                inset 0 2px 3px rgba(0, 0, 0, 0.6), 
                inset 0 -1px 2px rgba(255, 255, 255, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.5);
            z-index: 10;
            border: 1px solid rgba(80, 80, 80, 0.5);
        }

        .popup-rivet-tl { top: 25px; left: 25px; }
        .popup-rivet-tr { top: 25px; right: 25px; }
        .popup-rivet-bl { bottom: 25px; left: 25px; }
        .popup-rivet-br { bottom: 25px; right: 25px; }

        /* Flash Notification */
        .flash-notification {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #6a6a6a 0%, #4a4a4a 50%, #3a3a3a 100%);
            border-radius: 8px;
            padding: 15px 25px;
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.8),
                inset 0 1px 2px rgba(255, 255, 255, 0.1);
            z-index: 999;
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: fadeInOut 3s ease-in-out forwards;
            border-left: 5px solid #b85c38;
        }

        .flash-notification::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(20, 19, 19, 0.95);
            background-image: url('images/Group 8 (1).png');
            background-size: cover;
            background-blend-mode: multiply;
            border-radius: 4px;
            z-index: -1;
        }

        .flash-notification p {
            position: relative;
            z-index: 1;
            font-size: 20px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Original navbar styles */
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
            font-weight: 700;
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

        /* Original main content styles */
        .main-content {
            padding: 60px 80px;
            position: relative;
            z-index: 1;
        }

        .page-title {
            font-size: 72px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 50px;
            text-align: center;
            animation: fadeIn 1s ease-out backwards;
            animation-delay: 0.2s;
        }

        .detail-container {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Character Panel */
        .character-panel {
            background: linear-gradient(135deg, #6a6a6a 0%, #4a4a4a 50%, #3a3a3a 100%);
            border-radius: 8px;
            padding: 12px;
            position: relative;
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.9), 
                0 6px 20px rgba(0, 0, 0, 0.7),
                inset 0 2px 4px rgba(255, 255, 255, 0.1),
                inset 0 -2px 4px rgba(0, 0, 0, 0.5);
            animation: scaleIn 0.8s ease-out backwards;
            animation-delay: 0.3s;
        }

        .character-panel::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            bottom: 12px;
            background-color: rgba(20, 19, 19, 0.95);
            background-image: url('images/Group 8 (1).png');
            background-size: cover;
            background-position: center;
            background-blend-mode: multiply;
            opacity: 1;
            pointer-events: none;
            border-radius: 4px;
            z-index: 1;
        }

        .panel-inner {
            position: relative;
            z-index: 2;
            padding: 30px;
        }

        .character-image-box {
            width: 100%;
            height: 450px;
            overflow: hidden;
            border-radius: 8px;
            position: relative;
            background: #1a1a1a;
            margin-bottom: 30px;
            animation: fadeIn 1s ease-out backwards;
            animation-delay: 0.5s;
        }

        .character-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .character-image-box::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(20, 19, 19, 0.8) 100%);
        }

        .character-name {
            font-size: 64px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            text-align: center;
            animation: fadeIn 1s ease-out backwards;
            animation-delay: 0.6s;
        }

        .character-quote {
            font-size: 20px;
            color: #a8a8a8;
            line-height: 1.5;
            text-align: center;
            font-style: italic;
            margin-bottom: 30px;
            padding: 0 20px;
            animation: fadeIn 1s ease-out backwards;
            animation-delay: 0.7s;
        }

        .stats-section {
            background: rgba(20, 20, 20, 0.6);
            border: 2px solid rgba(184, 92, 56, 0.4);
            border-radius: 8px;
            padding: 25px;
            animation: slideUp 1s ease-out backwards;
            animation-delay: 0.8s;
        }

        .stat-item {
            margin-bottom: 20px;
        }

        .stat-label {
            font-size: 20px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }

        .stat-bar {
            width: 100%;
            height: 30px;
            background: rgba(40, 40, 40, 0.8);
            border-radius: 15px;
            overflow: hidden;
            border: 2px solid rgba(184, 92, 56, 0.3);
        }

        .stat-fill {
            height: 100%;
            background: linear-gradient(90deg, #6E3420 0%, #b85c38 50%, #6E3420 100%);
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 15px;
            font-size: 14px;
            font-weight: 700;
            color: white;
        }

        /* Inventory Panel */
        .inventory-panel {
            background: linear-gradient(135deg, #6a6a6a 0%, #4a4a4a 50%, #3a3a3a 100%);
            border-radius: 8px;
            padding: 12px;
            position: relative;
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.9), 
                0 6px 20px rgba(0, 0, 0, 0.7),
                inset 0 2px 4px rgba(255, 255, 255, 0.1),
                inset 0 -2px 4px rgba(0, 0, 0, 0.5);
            animation: scaleIn 0.8s ease-out backwards;
            animation-delay: 0.4s;
        }

        .inventory-panel::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            bottom: 12px;
            background-color: rgba(20, 19, 19, 0.95);
            background-image: url('images/Group 8 (1).png');
            background-size: cover;
            background-position: center;
            background-blend-mode: multiply;
            opacity: 1;
            pointer-events: none;
            border-radius: 4px;
            z-index: 1;
        }

        .panel-title {
            font-size: 36px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
            animation: fadeIn 1s ease-out backwards;
            animation-delay: 0.6s;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .inventory-slot {
            aspect-ratio: 1;
            background: rgba(20, 20, 20, 0.9);
            border: 3px solid rgba(184, 92, 56, 0.4);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            animation: scaleIn 0.5s ease-out backwards;
        }

        .inventory-slot:nth-child(1) { animation-delay: 0.7s; }
        .inventory-slot:nth-child(2) { animation-delay: 0.8s; }
        .inventory-slot:nth-child(3) { animation-delay: 0.9s; }
        .inventory-slot:nth-child(4) { animation-delay: 1.0s; }
        .inventory-slot:nth-child(5) { animation-delay: 1.1s; }
        .inventory-slot:nth-child(6) { animation-delay: 1.2s; }

        .inventory-slot img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .inventory-slot.empty {
            opacity: 0.3;
        }

        .mission-status {
            background: rgba(20, 20, 20, 0.6);
            border: 2px solid rgba(184, 92, 56, 0.4);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            animation: slideUp 1s ease-out backwards;
            animation-delay: 0.9s;
        }

        .mission-title {
            font-size: 24px;
            font-weight: 700;
            color: #b85c38;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
        }

        .mission-objective {
            font-size: 18px;
            color: #d4d4d4;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .mission-status-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(184, 92, 56, 0.2);
            border: 2px solid #b85c38;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            color: #b85c38;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .action-button {
            padding: 18px;
            background: #6E3420;
            border: none;
            border-radius: 50px;
            color: #f0f0f0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Antonio', sans-serif;
            box-shadow: 0 4px 15px rgba(110, 52, 32, 0.4);
            animation: scaleIn 0.5s ease-out backwards;
        }

        .action-button:nth-child(1) { animation-delay: 1.0s; }
        .action-button:nth-child(2) { animation-delay: 1.1s; }

        .action-button:hover {
            background: #8B4626;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 52, 32, 0.6);
        }

        .action-button.secondary {
            background: rgba(80, 80, 80, 0.8);
        }

        .action-button.secondary:hover {
            background: rgba(100, 100, 100, 0.9);
        }

        .rivet {
            position: absolute;
            width: 18px;
            height: 18px;
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

        /* Disabled state */
        .disabled-message {
            text-align: center;
            padding: 60px;
            color: #a8a8a8;
        }

        .disabled-message h3 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #b85c38;
        }

        .disabled-message p {
            font-size: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 1200px) {
            .detail-container {
                grid-template-columns: 1fr;
            }
            
            .popup-container {
                width: 95%;
                max-width: 500px;
            }
            
            .popup-title {
                font-size: 36px;
            }
            
            .popup-message {
                font-size: 20px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 40px 20px;
            }
            
            .page-title {
                font-size: 48px;
            }
            
            .popup-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .popup-button {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Flash Notification (auto-hide) -->
    <div id="flashNotification" class="flash-notification" style="display: none;">
        <p id="flashMessage">Please select a character first!</p>
    </div>

    <!-- Popup untuk karakter/inventory belum dipilih -->
    <div id="selectionPopup" class="popup-overlay" style="display: none;">
        <div class="popup-container">
            <div class="popup-rivet popup-rivet-tl"></div>
            <div class="popup-rivet popup-rivet-tr"></div>
            <div class="popup-rivet popup-rivet-bl"></div>
            <div class="popup-rivet popup-rivet-br"></div>
            
            <div class="popup-content">
                <div class="popup-icon">⚠️</div>
                <h2 class="popup-title" id="popupTitle">MISSING SELECTION</h2>
                <p class="popup-message" id="popupMessage">
                    You need to select a character and equip your inventory before proceeding.
                </p>
                <div class="popup-buttons">
                    <button class="popup-button secondary" onclick="goToCharacter()">SELECT CHARACTER</button>
                    <button class="popup-button" onclick="goToInventory()">SELECT INVENTORY</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Original Content -->
    <nav class="navbar">
        <div class="logo">THE LAST OF US</div>
        <div style="display: flex; align-items: center; gap: 60px; position: relative; z-index: 1;">
            <div class="user-section">
                <div class="user-info">
                    <span id="userGreeting">HI, <?= htmlspecialchars($user['username']) ?></span>
                </div>
                <button class="logout-btn" onclick="showLogoutPopup()">LOGOUT</button>
            </div>
            <ul class="nav-menu">
                <li onclick="goToCharacter()">CHARACTER</li>
                <li onclick="goToInventory()">INVENTORY</li>
                <li class="active">DETAIL</li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <h1 class="page-title">MISSION BRIEFING</h1>

        <div class="detail-container">
            <!-- Character Panel -->
            <div class="character-panel">
                <div class="rivet rivet-tl"></div>
                <div class="rivet rivet-tr"></div>
                <div class="rivet rivet-bl"></div>
                <div class="rivet rivet-br"></div>

                <div class="panel-inner">
                    <div class="character-image-box">
                        <img id="characterImage" src="" alt="Character">
                    </div>

                    <h2 class="character-name" id="characterName">YOUR CHARACTER</h2>
                    <p class="character-quote" id="characterQuote">
                        "Select a character in the Character section to begin your journey."
                    </p>

                    <div class="stats-section">
                        <div class="stat-item">
                            <div class="stat-label">HEALTH</div>
                            <div class="stat-bar">
                                <div class="stat-fill" id="healthBar" style="width: 0%">0%</div>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-label">STAMINA</div>
                            <div class="stat-bar">
                                <div class="stat-fill" id="staminaBar" style="width: 0%">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory & Mission Panel -->
            <div class="inventory-panel">
                <div class="rivet rivet-tl"></div>
                <div class="rivet rivet-tr"></div>
                <div class="rivet rivet-bl"></div>
                <div class="rivet rivet-br"></div>

                <div class="panel-inner">
                    <h2 class="panel-title">EQUIPMENT LOADOUT</h2>
                    
                    <div class="inventory-grid" id="inventoryDisplay">
                        <div class="inventory-slot empty">
                            <div style="color: #888; font-size: 14px;">EMPTY</div>
                        </div>
                        <div class="inventory-slot empty">
                            <div style="color: #888; font-size: 14px;">EMPTY</div>
                        </div>
                        <div class="inventory-slot empty">
                            <div style="color: #888; font-size: 14px;">EMPTY</div>
                        </div>
                        <div class="inventory-slot empty">
                            <div style="color: #888; font-size: 14px;">EMPTY</div>
                        </div>
                        <div class="inventory-slot empty">
                            <div style="color: #888; font-size: 14px;">EMPTY</div>
                        </div>
                        <div class="inventory-slot empty">
                            <div style="color: #888; font-size: 14px;">EMPTY</div>
                        </div>
                    </div>

                    <div class="mission-status">
                        <h3 class="mission-title">CURRENT OBJECTIVE</h3>
                        <p class="mission-objective" id="objectiveText">
                            Please select a character and equip your inventory first to receive your mission briefing.
                        </p>
                        <div style="margin-top: 15px;">
                            <span class="mission-status-badge" id="statusBadge">⏸ NOT READY</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="action-button secondary" onclick="goToInventory()">SELECT INVENTORY</button>
                        <button class="action-button" onclick="startMission()" id="startButton">START MISSION →</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const selectedCharacter = <?= json_encode($selectedCharacter); ?>;
        const inventoryItems = <?= json_encode($inventoryItems); ?>;
        
        // Character data
        const characterData = {
            'Ellie': {
                image: 'images/ellie.jpg',
                quote: '"Everyone I have cared for has either died or left me. Everyone - fucking except for you."',
                health: 85,
                stamina: 90,
                objective: 'Survive the infected zone and reach the safe house before nightfall. Conserve your resources and stay alert.'
            },
            'Joel': {
                image: 'images/joel.jpg',
                quote: '"No matter what, you keep finding something to fight for."',
                health: 95,
                stamina: 80,
                objective: 'Navigate through the abandoned city, scavenge for supplies, and avoid infected patrols.'
            },
            'Abby': {
                image: 'images/abby.jpg',
                quote: '"I just had to lighten the load a little bit."',
                health: 90,
                stamina: 95,
                objective: 'Track down the target in the wilderness. Use stealth and avoid direct confrontation when possible.'
            }
        };

        // Item images mapping
        const itemImages = {
            'Flashlight': 'images/senter.png',
            'Pistol': 'images/pistol.png',
            'Knife': 'images/pisau.png',
            'Axe': 'images/kapak.png',
            'Compass': 'images/kompas.png',
            'Rifle': 'images/tembakan.png',
            'Med Kit': 'images/medkit.png',
            'Bow': 'images/panah.png',
            'Grenade': 'images/granat.png'
        };

        window.addEventListener('DOMContentLoaded', function() {
            loadCharacter();
            loadInventory();
            updateMissionStatus();
        });


        function loadCharacter() {
            if (!selectedCharacter) return;

            const charData = characterData[selectedCharacter];

            if (!charData) return;

            document.getElementById('characterName').textContent = selectedCharacter.toUpperCase();
            document.getElementById('characterImage').src = charData.image;
            document.getElementById('characterQuote').textContent = charData.quote;

            const healthBar = document.getElementById('healthBar');
            const staminaBar = document.getElementById('staminaBar');

            healthBar.style.width = charData.health + '%';
            healthBar.textContent = charData.health + '%';

            staminaBar.style.width = charData.stamina + '%';
            staminaBar.textContent = charData.stamina + '%';

            document.getElementById('objectiveText').textContent = charData.objective;
        }

        function loadInventory() {
            const inventorySlots = document.querySelectorAll('#inventoryDisplay .inventory-slot');

            inventorySlots.forEach(slot => {
                slot.classList.add('empty');
                slot.innerHTML = '<div style="color: #888; font-size: 14px;">EMPTY</div>';
            });

            if (!inventoryItems || inventoryItems.length === 0) return;

            inventoryItems.forEach((itemName, index) => {
                if (index < inventorySlots.length && itemImages[itemName]) {
                    inventorySlots[index].classList.remove('empty');
                    inventorySlots[index].innerHTML =
                        `<img src="${itemImages[itemName]}" alt="${itemName}">`;
                }
            });
        }

        function updateMissionStatus() {
            const ready = selectedCharacter && inventoryItems && inventoryItems.length > 0;

            if (ready) {
                const badge = document.getElementById('statusBadge');
                badge.textContent = '● READY TO DEPLOY';
                badge.style.color = '#28a745';
                badge.style.borderColor = '#28a745';
                badge.style.background = 'rgba(40, 167, 69, 0.2)';
                document.getElementById('startButton').disabled = false;
            } else {
                const badge = document.getElementById('statusBadge');
                badge.textContent = '⏸ NOT READY';
                badge.style.color = '#b85c38';
                badge.style.borderColor = '#b85c38';
                badge.style.background = 'rgba(184, 92, 56, 0.2)';
                document.getElementById('startButton').disabled = true;
            }
        }

        function showSelectionPopup(type) {
            const popup = document.getElementById('selectionPopup');
            const title = document.getElementById('popupTitle');
            const message = document.getElementById('popupMessage');
            
            if (type === 'character') {
                title.textContent = 'CHARACTER NOT SELECTED';
                message.textContent = 'Please select a character first to continue. Each character has unique abilities and stats.';
            } else if (type === 'inventory') {
                title.textContent = 'INVENTORY NOT EQUIPPED';
                message.textContent = 'Your backpack is empty. Please equip at least one item from your inventory before proceeding.';
            } else if (type === 'both') {
                title.textContent = 'MISSING SELECTIONS';
                message.textContent = 'You need to select a character and equip your inventory before starting the mission.';
            }
            
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hidePopup() {
            document.getElementById('selectionPopup').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function goToCharacter() {
            hidePopup();
            window.location.href = 'character.php';
        }

        function goToInventory() {
            if (!selectedCharacter) {
                showFlashNotification('Please select a character first!');
                return;
            }

            hidePopup();
            window.location.href = 'inventory.php';
        }


        function showFlashNotification(message) {
            const flash = document.getElementById('flashNotification');
            const flashMsg = document.getElementById('flashMessage');
            
            flashMsg.textContent = message;
            flash.style.display = 'block';
            
            setTimeout(() => {
                flash.style.display = 'none';
            }, 3000);
        }

        function startMission() {
            if (!selectedCharacter) {
                showSelectionPopup('character');
                return;
            }

            if (!inventoryItems || inventoryItems.length === 0) {
                showSelectionPopup('inventory');
                return;
            }

            window.location.href = 'gameplay.php';
        }

        function showLogoutPopup() {
            const popup = document.createElement('div');
            popup.className = 'popup-overlay';
            popup.style.display = 'flex';
            
            popup.innerHTML = `
                <div class="popup-container">
                    <div class="popup-rivet popup-rivet-tl"></div>
                    <div class="popup-rivet popup-rivet-tr"></div>
                    <div class="popup-rivet popup-rivet-bl"></div>
                    <div class="popup-rivet popup-rivet-br"></div>
                    
                    <div class="popup-content">
                        <div class="popup-icon">⚠️</div>
                        <h2 class="popup-title">LOGOUT CONFIRMATION</h2>
                        <p class="popup-message">
                            Are you sure you want to logout?<br>
                            Your progress will be saved.
                        </p>
                        <div class="popup-buttons">
                            <button class="popup-button secondary" onclick="this.closest('.popup-overlay').remove(); document.body.style.overflow = 'auto';">CANCEL</button>
                            <button class="popup-button" onclick="window.location.href='logout.php'">LOGOUT</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popup);
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>
</html>