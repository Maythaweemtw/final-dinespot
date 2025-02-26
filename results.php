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
    <title>Restaurant Recommendations - Results</title>
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
            min-height: 100vh;
            background-color: #f9f9f9;
            background-image: url(background4.jpg);
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            overflow-y: auto;
            position: relative;
        }
        nav {
            display: flex;
            justify-content: flex-start;
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
        h1 {
            font-size: 3rem;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 30px;
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
        .container {
            text-align: center;
            margin: 20px;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white; /* Solid background color */
            border-radius: 10px; /* Optional: rounded corners */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Optional: shadow for better visibility */
        }
        th, td {
            background-color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .save-btn {
            padding: 5px 10px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
        }
        .save-btn:hover {
            background-color: #333;
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
        <span id="user-info" style="float:right; margin-right:20px;">
            <?php echo htmlspecialchars($user_firstname) . " (" . htmlspecialchars($user_email) . ")"; ?>
        </span>
    </nav>

    <h1 style="text-align:center; clear:both;">Restaurant Recommendations</h1>
    <div class="container">
        <p><strong>Selected Categories:</strong> <span id="selectedCategories"></span></p>
        <p><strong>Showing:</strong> <span id="resultLimit"></span> results</p>
    </div>

    <table id="resultsTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Rating</th>
                <th>Address</th>
                <th>Google Maps</th>
                <th>Match Count</th>
                <th>Matched Types</th>
                <th>Save</th>
            </tr>
        </thead>
        <tbody>
            <!-- Results will be populated here -->
        </tbody>
    </table>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const categories = JSON.parse(decodeURIComponent(urlParams.get('categories')));
        const limit = urlParams.get('limit') || 10;

        document.getElementById('selectedCategories').textContent = categories.join(', ');
        document.getElementById('resultLimit').textContent = limit;

        async function fetchResults() {
            const response = await fetch(`get_restaurants.php?categories=${encodeURIComponent(JSON.stringify(categories))}&limit=${limit}`);
            const data = await response.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            const tableBody = document.getElementById('resultsTable').getElementsByTagName('tbody')[0];
            tableBody.innerHTML = '';

            data.forEach(restaurant => {
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td>${restaurant.displayname_text}</td>
                    <td>${restaurant.types}</td>
                    <td>${restaurant.rating}</td>
                    <td>${restaurant.short_formatted_address ? restaurant.short_formatted_address : 'N/A'}</td>
                    <td><a href="${restaurant.google_maps_uri}" target="_blank" rel="noopener noreferrer">View on Google Maps</a></td>
                    <td>${restaurant.match_count}</td>
                    <td>${restaurant.matched_types || 'None'}</td>
                    <td><button class="save-btn" onclick="saveRestaurant('${restaurant.displayname_text}', '${restaurant.restaurant_id}')">Save</button></td>
                `;
            });
        }

        function saveRestaurant(displayname, restaurantId) {
            console.log("Saving restaurant:", displayname, restaurantId);

            fetch('save_restaurant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    restaurant_id: restaurantId,
                    restaurant_name: displayname
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Restaurant saved successfully!');
                } else {
                    alert('Error saving restaurant: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save restaurant.');
            });
        }

        fetchResults();
    </script>

</body>
</html>
