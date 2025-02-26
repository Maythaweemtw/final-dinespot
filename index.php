<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_style.php');
    exit();
}

// Get user email and first name from session
$user_email = $_SESSION['email'] ?? 'Guest';
$user_firstname = $_SESSION['firstname'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Recommendation</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');
        :root {
            --accent-color: #f0c85a;
            --base-color: white;
            --text-color: #2E2B41;
            --input-color: #F3F0FF;
        }
        * {
            margin: 1;
            padding: 0;
            box-sizing: border-box;
        }
        html {
            font-family: Poppins, Segoe UI, sans-serif;
            font-size: 12pt;
            color: var(--text-color);
            text-align: center;
        }
        body {
            min-height: 50vh; /* This ensures the body takes at least the full viewport height */
            background-color: #f9f9f9; /* Light gray background */
            background-image: url(background6.jpg);
            background-position: center;
            background-size: cover; /* Ensures the image covers the entire body */
            background-repeat: no-repeat;
            overflow-y: auto; /* Allow vertical scrolling */
            position: relative;
        }
        .email-container {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--base-color);
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }
        nav {
            display: flex;
            justify-content: flex-start; /* Aligns items to the left */
            align-items: center;
            background-color: #f0c85a;
            padding: 10px;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
        #user-info {
            margin-left: auto;
        }
        .wrapper {
            box-sizing: border-box;
            background-color: var(--base-color);
            width: 80%;
            max-width: 1000px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        h1 {
            font-size: 3rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .input-container {
            text-align: center;
            margin: 20px;
        }
        .checkbox-group {
            text-align: center;
            margin-bottom: 20px;
        }
        .category-btn {
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 5px;
            cursor: pointer;
            border-radius: 12px; /* Slightly rounded corners */
            transition: background-color 150ms ease, transform 150ms ease;
        }
        .category-btn:hover {
            background-color: #f0c85a; /* Yellow color on hover */
            transform: scale(1.05); /* Slightly enlarge button on hover */
        }

        .selected {
            background-color: #4CAF50;
            color: white;
        }
        .button-container {
            width: min(400px, 100%);
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .button-container button {
            padding: .85em 4em;
            border: none;
            border-radius: 1000px;
            background-color: var(--accent-color);
            color: var(--base-color);
            font: inherit;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: 150ms ease;
        }
        .button-container button:hover {
            background-color: var(--text-color);
        }
        .button-container button:focus {
            outline: none;
            background-color: var(--text-color);
        }
    </style>
</head>
<body>

    <nav>
        <a href="home.php">Homepage</a>
        <a href="personalize.php">Restaurants for you</a>
        <a href="index.php">Find Restaurants</a>
        <a href="user.php">Profile</a>
        <a href="logout.php">Logout</a>
        <span id="user-info">
            <?php echo htmlspecialchars($user_firstname) . " (" . htmlspecialchars($user_email) . ")"; ?>
        </span>
    </nav>

    <div class="wrapper">
        <h1>Restaurant Recommendation</h1>

        <!-- User Input Section -->
        <div class="input-container">
            <label for="limit">Enter number of restaurants to show:</label>
            <input type="number" id="limit" min="1" max="100" value="10">
        </div>

        <!-- Categories Selection -->
        <div class="checkbox-group">
            <h3>Select Categories:</h3>
            <div id="categories">
                <!-- Categories will be dynamically generated here -->
            </div>
        </div>

        <!-- Button to submit and search -->
        <div class="input-container">
            <button onclick="fetchRestaurants()">Search</button>
        </div>

    </div>

    <script>
        const categories = {
            "Dietary Preferences": [
                "vegan_restaurant", 
                "vegetarian_restaurant"
            ],
            "Meal Type & Service Style": [
                "bar_and_grill", 
                "barbecue_restaurant", 
                "breakfast_restaurant", 
                "brunch_restaurant", 
                "buffet_restaurant", 
                "cafeteria", 
                "fast_food_restaurant", 
                "fine_dining_restaurant",
                "food_delivery", 
                "hamburger_restaurant", 
                "juice_shop", 
                "meal_delivery", 
                "meal_takeaway", 
                "pizza_restaurant", 
                "steak_house", 
                "wine_bar"
            ],
            "Cuisine Type": [
                "american_restaurant",
                "asian_restaurant", 
                "brazilian_restaurant", 
                "chinese_restaurant", 
                "dessert_restaurant", 
                "french_restaurant", 
                "greek_restaurant", 
                "indian_restaurant", 
                "italian_restaurant", 
                "japanese_restaurant", 
                "korean_restaurant",
                "lebanese_restaurant", 
                "mediterranean_restaurant",
                "mexican_restaurant", 
                "middle_eastern_restaurant",
                "ramen_restaurant", 
                "seafood_restaurant", 
                "spanish_restaurant",
                "sushi_restaurant", 
                "thai_restaurant", 
                "turkish_restaurant", 
                "vietnamese_restaurant"
            ],
            "Stores and Shops": [
                "deli", "bakery", "market", "home_goods_store", "convenience_store",
                "liquor_store", "food_store", "gift_shop", "sandwich_shop", "food_court", "cafe", "coffee_shop", "dessert_shop", "ice_cream_shop",
                "candy_store", "butcher_shop", "store", "tea_house", "health", "confectionery"
            ],
            "Entertainment and Leisure": [
                "bar", "dog_cafe", "event_venue", "farm", "karaoke", "museum", "night_club", "park", "performing_arts_theater", "pub", "sports_activity_location", "sports_club",
                "wedding_venue"
            ],
            "Services": [
                "catering_service", "child_care_agency", "massage"
            ]
        };

        const categoryContainer = document.getElementById('categories');
        Object.keys(categories).forEach(category => {
            const checkboxDiv = document.createElement('div');
            checkboxDiv.innerHTML = `<h4>${category}</h4>`;
            categories[category].forEach(type => {
                const button = document.createElement('button');
                button.className = 'category-btn';
                button.innerText = type;
                button.onclick = function() {
                    button.classList.toggle('selected');
                };
                checkboxDiv.appendChild(button);
            });
            categoryContainer.appendChild(checkboxDiv);
        });

        function fetchRestaurants() {
            let selectedCategories = [];
            document.querySelectorAll('.category-btn.selected').forEach(button => {
                selectedCategories.push(button.innerText);
            });
            let limit = document.getElementById('limit').value || 10;
            let queryString = `get_restaurants.php?categories=${JSON.stringify(selectedCategories)}&limit=${limit}`;
            window.location.href = `results.php?categories=${encodeURIComponent(JSON.stringify(selectedCategories))}&limit=${limit}`;
        }
    </script>

</body>
</html>
