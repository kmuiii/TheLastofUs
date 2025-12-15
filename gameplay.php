<?php
session_start();
require 'koneksi.php';
require 'module.php';

requireLogin();

$selectedCharacter = $_SESSION['character'] ?? null;
$inventoryItems = $_SESSION['inventory'] ?? [];

if (!$selectedCharacter || empty($inventoryItems)) {
    header('Location: inventory.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_result'])) {
    $_SESSION['game_result'] = [
        'health' => $_POST['health'],
        'stamina' => $_POST['stamina'],
        'score' => $_POST['score'],
        'status' => $_POST['status'],
        'remainingInventory' => json_decode($_POST['inventory'], true)
    ];
    echo json_encode(['status' => 'ok']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Last Of Us - Mission</title>
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
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            z-index: 0;
        }

        .hud {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.7) 70%, transparent 100%);
            padding: 20px 40px;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .hud-left {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .character-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .character-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #b85c38;
            object-fit: cover;
        }

        .character-name-hud {
            font-size: 24px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .stats-hud {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 250px;
        }

        .stat-hud {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-label-hud {
            font-size: 14px;
            font-weight: 700;
            color: #d4d4d4;
            text-transform: uppercase;
            width: 80px;
        }

        .stat-bar-hud {
            flex: 1;
            height: 20px;
            background: rgba(40, 40, 40, 0.9);
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid rgba(184, 92, 56, 0.4);
        }

        .stat-fill-hud {
            height: 100%;
            background: linear-gradient(90deg, #6E3420 0%, #b85c38 50%, #6E3420 100%);
            transition: width 0.5s ease;
            font-size: 12px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hud-right {
            display: flex;
            gap: 15px;
            background: rgba(20, 20, 20, 0.8);
            padding: 15px 20px;
            border-radius: 8px;
            border: 2px solid rgba(184, 92, 56, 0.4);
        }

        .inventory-item-hud {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            min-width: 60px;
        }

        .item-icon {
            width: 40px;
            height: 40px;
            object-fit: contain;
            opacity: 0.5;
        }

        .item-icon.active {
            opacity: 1;
        }

        .item-count {
            font-size: 12px;
            font-weight: 700;
            color: #d4d4d4;
        }

        .main-content {
            padding: 120px 80px 60px;
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .scenario-panel {
            background: linear-gradient(135deg, #6a6a6a 0%, #4a4a4a 50%, #3a3a3a 100%);
            border-radius: 8px;
            padding: 12px;
            position: relative;
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.9), 
                0 6px 20px rgba(0, 0, 0, 0.7),
                inset 0 2px 4px rgba(255, 255, 255, 0.1),
                inset 0 -2px 4px rgba(0, 0, 0, 0.5);
            max-width: 1000px;
            width: 100%;
        }

        .scenario-panel::before {
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
            pointer-events: none;
            border-radius: 4px;
            z-index: 1;
        }

        .panel-inner {
            position: relative;
            z-index: 2;
            padding: 50px 60px;
        }

        .scenario-title {
            font-size: 36px;
            font-weight: 700;
            color: #b85c38;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
            text-align: center;
        }

        .scenario-description {
            font-size: 20px;
            color: #d4d4d4;
            line-height: 1.8;
            margin-bottom: 40px;
            text-align: center;
        }

        .choices-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .choice-button {
            width: 100%;
            padding: 25px 35px;
            background: rgba(40, 40, 40, 0.8);
            border: 3px solid rgba(184, 92, 56, 0.4);
            border-radius: 8px;
            color: #d4d4d4;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Antonio', sans-serif;
            text-align: left;
            position: relative;
        }

        .choice-button:hover {
            background: rgba(60, 60, 60, 0.9);
            border-color: #b85c38;
            transform: translateX(10px);
            box-shadow: 0 6px 20px rgba(184, 92, 56, 0.4);
        }

        .choice-button::before {
            content: '→';
            position: absolute;
            right: 35px;
            font-size: 24px;
            color: #b85c38;
            opacity: 0;
            transition: all 0.3s;
        }

        .choice-button:hover::before {
            opacity: 1;
            right: 25px;
        }

        .choice-requirement {
            display: block;
            font-size: 14px;
            color: #888;
            font-weight: 400;
            margin-top: 8px;
            letter-spacing: 1px;
        }

        .choice-requirement.required {
            color: #b85c38;
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

        .notification {
            position: fixed;
            top: 100px;
            right: 40px;
            background: rgba(184, 92, 56, 0.95);
            padding: 20px 30px;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            z-index: 200;
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.5s;
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</head>
<body>
    <!-- HUD -->
    <div class="hud">
        <div class="hud-left">
            <div class="character-info">
                <img id="charAvatar" src="images/ellie.jpg" alt="Character" class="character-avatar">
                <div class="character-name-hud" id="charNameHUD">ELLIE</div>
            </div>
            <div class="stats-hud">
                <div class="stat-hud">
                    <span class="stat-label-hud">HEALTH</span>
                    <div class="stat-bar-hud">
                        <div class="stat-fill-hud" id="healthHUD" style="width: 85%">85%</div>
                    </div>
                </div>
                <div class="stat-hud">
                    <span class="stat-label-hud">STAMINA</span>
                    <div class="stat-bar-hud">
                        <div class="stat-fill-hud" id="staminaHUD" style="width: 90%">90%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hud-right" id="inventoryHUD">
            <!-- Inventory items will be loaded here -->
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="scenario-panel">
            <div class="rivet rivet-tl"></div>
            <div class="rivet rivet-tr"></div>
            <div class="rivet rivet-bl"></div>
            <div class="rivet rivet-br"></div>

            <div class="panel-inner">
                <h2 class="scenario-title" id="scenarioTitle">SCENARIO 1: ABANDONED BUILDING</h2>
                <p class="scenario-description" id="scenarioDesc">
                    You hear strange noises coming from the floor above. The staircase is partially collapsed, and the sun is setting fast. You need to decide quickly.
                </p>

                <div class="choices-container" id="choicesContainer">
                    <!-- Choices will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        const SESSION_CHARACTER = <?= json_encode($selectedCharacter) ?>;
        const SESSION_INVENTORY = <?= json_encode($inventoryItems) ?>;

        // Character data
        const characterData = {
            'Ellie': { image: 'images/ellie.jpg', health: 85, stamina: 90 },
            'Joel': { image: 'images/joel.jpg', health: 95, stamina: 80 },
            'Abby': { image: 'images/abby.jpg', health: 90, stamina: 95 }
        };

        // Game state
        let gameState = {
            character: SESSION_CHARACTER,
            health: characterData[SESSION_CHARACTER].health,
            stamina: characterData[SESSION_CHARACTER].stamina,
            inventory: [...SESSION_INVENTORY],
            currentScenario: 0,
            score: 0
        };

        // Item name to image mapping
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

        // Item name mapping (for scenario requirements)
        const itemNameMapping = {
            'Flashlight': 'Flashlight',
            'Pistol': 'Pistol',
            'Knife': 'Knife',
            'Axe': 'Axe',
            'Compass': 'Compass',
            'Rifle': 'Rifle',
            'Med Kit': 'Med Kit',
            'Bow': 'Bow',
            'Grenade': 'Grenade'
        };

        // Scenarios with requirements matching our items
        const scenarios = [
            {
                title: "SCENARIO 1: ABANDONED BUILDING",
                description: "You hear strange noises coming from the floor above. The staircase is partially collapsed, and the sun is setting fast. You need to decide quickly.",
                choices: [
                    {
                        text: "INVESTIGATE THE NOISE",
                        requirement: "Flashlight",
                        healthCost: -10,
                        staminaCost: -15,
                        score: 20,
                        outcome: "Used Flashlight to investigate safely"
                    },
                    {
                        text: "SNEAK PAST QUIETLY",
                        requirement: null,
                        healthCost: 0,
                        staminaCost: -20,
                        score: 15,
                        outcome: "Conserved health but used stamina"
                    },
                    {
                        text: "ENGAGE WITH WEAPON",
                        requirement: "Pistol",
                        healthCost: -15,
                        staminaCost: -10,
                        score: 25,
                        outcome: "Successfully neutralized threat with Pistol"
                    },
                    {
                        text: "RETREAT AND FIND ANOTHER WAY",
                        requirement: null,
                        healthCost: 0,
                        staminaCost: -5,
                        score: 5,
                        outcome: "Avoided danger but lost time"
                    }
                ]
            },
            {
                title: "SCENARIO 2: INFECTED ENCOUNTER",
                description: "Three infected block your path. Your weapon is running low on ammo. The alley is narrow with limited escape routes.",
                choices: [
                    {
                        text: "USE MEDICAL SUPPLIES AND FIGHT",
                        requirement: "Med Kit",
                        healthCost: 10,
                        staminaCost: -20,
                        score: 30,
                        outcome: "Healed up and fought successfully"
                    },
                    {
                        text: "THROW DISTRACTION AND RUN",
                        requirement: "Grenade",
                        healthCost: 0,
                        staminaCost: -25,
                        score: 25,
                        outcome: "Created distraction and escaped"
                    },
                    {
                        text: "STEALTH KILL WITH MELEE",
                        requirement: "Knife",
                        healthCost: -20,
                        staminaCost: -15,
                        score: 35,
                        outcome: "Silently eliminated threats"
                    },
                    {
                        text: "RISK IT AND SPRINT THROUGH",
                        requirement: null,
                        healthCost: -30,
                        staminaCost: -30,
                        score: 10,
                        outcome: "Took heavy damage but made it through"
                    }
                ]
            },
            {
                title: "SCENARIO 3: SAFE HOUSE REACHED",
                description: "You've reached the safe house, but it's not clear if it's truly safe. You can hear movement inside. This is your final decision.",
                choices: [
                    {
                        text: "SCOUT WITH FLASHLIGHT FIRST",
                        requirement: "Flashlight",
                        healthCost: 0,
                        staminaCost: -10,
                        score: 20,
                        outcome: "Identified safe entry point"
                    },
                    {
                        text: "BREACH WITH WEAPON READY",
                        requirement: "Rifle",
                        healthCost: -10,
                        staminaCost: -15,
                        score: 25,
                        outcome: "Cleared the building efficiently"
                    },
                    {
                        text: "ENTER CAUTIOUSLY",
                        requirement: null,
                        healthCost: -5,
                        staminaCost: -10,
                        score: 15,
                        outcome: "Made it inside safely"
                    }
                ]
            }
        ];

        window.addEventListener('DOMContentLoaded', function() {
            // Load character
            gameState.character = SESSION_CHARACTER;
            const charData = characterData[SESSION_CHARACTER];
            
            gameState.health = charData.health;
            gameState.stamina = charData.stamina;
            
            document.getElementById('charNameHUD').textContent = SESSION_CHARACTER.toUpperCase();
            document.getElementById('charAvatar').src = charData.image;
            
            updateHUD();
            loadInventory();
            loadScenario(0);
        });

        function loadInventory() {
            const inventoryHUD = document.getElementById('inventoryHUD');
            inventoryHUD.innerHTML = '';
            
            if (gameState.inventory.length === 0) {
                inventoryHUD.innerHTML = '<div style="color: #888; padding: 10px;">No items</div>';
                return;
            }
            
            gameState.inventory.forEach(item => {
                const itemName = itemNameMapping[item] || item;
                if (itemImages[itemName]) {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'inventory-item-hud';
                    itemDiv.innerHTML = `
                        <img src="${itemImages[itemName]}" alt="${itemName}" class="item-icon active">
                        <span class="item-count">x1</span>
                    `;
                    itemDiv.title = itemName;
                    inventoryHUD.appendChild(itemDiv);
                }
            });
        }

        function loadScenario(index) {
            if (index >= scenarios.length) {
                // Mission complete - redirect to result
                saveResults();
                return;
            }

            const scenario = scenarios[index];
            document.getElementById('scenarioTitle').textContent = scenario.title;
            document.getElementById('scenarioDesc').textContent = scenario.description;

            const choicesContainer = document.getElementById('choicesContainer');
            choicesContainer.innerHTML = '';

            scenario.choices.forEach((choice, i) => {
                const button = document.createElement('button');
                button.className = 'choice-button';
                
                let reqText = '';
                if (choice.requirement) {
                    const hasItem = gameState.inventory.includes(choice.requirement);
                    reqText = `<span class="choice-requirement ${hasItem ? 'required' : ''}">
                        ${hasItem ? '✓' : '✗'} Requires: ${choice.requirement}
                    </span>`;
                }
                
                button.innerHTML = `${choice.text}${reqText}`;
                button.onclick = () => makeChoice(choice, index);
                choicesContainer.appendChild(button);
            });
        }

        function makeChoice(choice, scenarioIndex) {
            // Check if has required item
            if (choice.requirement && !gameState.inventory.includes(choice.requirement)) {
                showNotification('⚠️ Missing required item: ' + choice.requirement);
                return;
            }

            // Apply effects
            gameState.health += choice.healthCost;
            gameState.stamina += choice.staminaCost;
            gameState.score += choice.score;

            // Clamp values
            gameState.health = Math.max(0, Math.min(100, gameState.health));
            gameState.stamina = Math.max(0, Math.min(100, gameState.stamina));

            // Remove used item if it was required
            if (choice.requirement) {
                const index = gameState.inventory.indexOf(choice.requirement);
                if (index > -1) {
                    gameState.inventory.splice(index, 1);
                }
            }

            // Show notification
            showNotification('✓ ' + choice.outcome);

            // Update UI
            updateHUD();
            loadInventory();

            // Check if died
            if (gameState.health <= 0 || gameState.stamina <= 0) {
                setTimeout(() => {
                    alert('Mission Failed! You didn\'t survive...');
                    saveResults();
                }, 1000);
                return;
            }

            // Load next scenario
            setTimeout(() => {
                loadScenario(scenarioIndex + 1);
            }, 1500);
        }

        function updateHUD() {
            const healthBar = document.getElementById('healthHUD');
            const staminaBar = document.getElementById('staminaHUD');
            
            healthBar.style.width = gameState.health + '%';
            healthBar.textContent = gameState.health + '%';
            staminaBar.style.width = gameState.stamina + '%';
            staminaBar.textContent = gameState.stamina + '%';
        }

        function showNotification(message) {
            const notif = document.getElementById('notification');
            notif.textContent = message;
            notif.classList.add('show');
            
            setTimeout(() => {
                notif.classList.remove('show');
            }, 3000);
        }

        function saveResults() {
            fetch('gameplay.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:
                    'save_result=1' +
                    '&health=' + gameState.health +
                    '&stamina=' + gameState.stamina +
                    '&score=' + gameState.score +
                    '&status=' + (gameState.health > 0 && gameState.stamina > 0 ? 'success' : 'failed') +
                    '&inventory=' + encodeURIComponent(JSON.stringify(gameState.inventory))
            })
            .then(res => res.json())
            .then(() => {
                window.location.href = 'result.php';
            });
        }
    </script>
</body>
</html>