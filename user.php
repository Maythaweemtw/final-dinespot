<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_style.php');
    exit();
}

// PostgreSQL database connection settings
$host = 'localhost';
$user = 'postgres';
$password = '11042001/05/2004';
$database = 'webappDB';

// Create connection
$conn = pg_connect("host=$host dbname=$database user=$user password=$password");

// Check connection
if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . pg_last_error()]));
}
// Get user email and first name from session
$user_email = $_SESSION['email'] ?? 'Guest';
$user_firstname = $_SESSION['firstname'] ?? 'User';

// SQL query to get the saved restaurants and their corresponding address
$query = "
    SELECT ur.displayname_text, ur.types, ur.restaurant_id, ur.rate, p.google_maps_uri
    FROM user_ratings ur
    JOIN places p ON ur.restaurant_id = p.restaurant_id
    WHERE ur.email = $1
";

$result = pg_query_params($conn, $query, array($user_email));

if (!$result) {
    die(json_encode(["error" => "Query failed: " . pg_last_error()]));
}

// Fetch the rows and store them in an array
$savedRestaurants = [];
while ($row = pg_fetch_assoc($result)) {
    $savedRestaurants[] = $row;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
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
        body {
            font-family: Poppins, Segoe UI, sans-serif;
            background-color: #f9f9f9;
            background-image: url('background4.jpg');
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            text-align: center;
        }
        nav {
            display: flex;
            justify-content: flex-start; /* Aligns items to the left */
            align-items: center;
            background-color: #f0c85a;
            padding: 10px;
        }
        nav a {
            margin-right: 15px; /* Space between links */
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
        #user-info {
            margin-left: auto; /* Pushes it to the right */
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
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
        button {
            padding: 8px 12px;
            margin-top: 10px;
            cursor: pointer;
        }
        .rate-button {
            background-color: #f0c85a;
            border: none;
            color: black;
            font-weight: bold;
            border-radius: 5px;
        }
        .remove-button {
            background-color: #ff4d4d;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 5px;
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

    <h1>Your Saved Restaurants</h1>

    <div id="popupMessage" style="
        display: none; 
        position: fixed; 
        top: 50%; left: 50%; 
        transform: translate(-50%, -50%); 
        background-color: #333; 
        color: white; 
        padding: 10px 20px; 
        border-radius: 5px;
        z-index: 1000;
    ">
    </div>


    <table id="savedRestaurantsTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Google Maps</th>
                <th>Rate</th>
                <th>Remove Restaurant</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($savedRestaurants as $restaurant): ?>
                <tr>
                    <td><?php echo htmlspecialchars($restaurant['displayname_text']); ?></td>
                    <td><?php echo htmlspecialchars($restaurant['types']); ?></td>
                    <td>
                    <a href="<?php echo htmlspecialchars($restaurant['google_maps_uri']); ?>" target="_blank">
                        View on Google Maps
                    </a>
                </td>
                <td>
                    <form method="post" action="rate_restaurant.php">
                        <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['restaurant_id']; ?>">
                        <input type="number" name="rate" min="1" max="5" value="<?php echo htmlspecialchars($restaurant['rate']); ?>" required>
                        <button class="rate-button" type="submit">Rate</button>
                    </form>
                <td>
                <button class="remove-button" onclick="removeRestaurant('<?php echo $restaurant['restaurant_id']; ?>')">Remove</button>
                </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    <script>
        // Fetch user details and saved restaurants
        async function loadUserProfile() {
            const response = await fetch("get_user_data.php");
            const data = await response.json();
        
            if (data.error) {
                alert(data.error);
                window.location.href = "login_style.html"; // Redirect to login if not logged in
                return;
            }
        
            document.getElementById("userName").textContent = data.firstname;  // Display first name
            document.getElementById("userEmail").textContent = data.email;
        
            const tableBody = document.getElementById("savedRestaurantsTable").getElementsByTagName("tbody")[0];
            tableBody.innerHTML = '';
        
            data.restaurants.forEach(restaurant => {
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td>${restaurant.displayname}</td>
                    <td>${restaurant.types}</td>
                    <td>${restaurant.google_maps_uri}</td>
                    <td>${restaurant.rating}</td>
                    <td>
                        <button onclick="removeRestaurant('<?php echo $restaurant['restaurant_id']; ?>')">Remove</button>
                    </td>
                    <td>
                        <button onclick="clearRating('<?php echo $restaurant['restaurant_id']; ?>')">Clear Rating</button>
                    </td>
                `;
            });
        }

        // Remove a saved restaurant
        async function removeRestaurant(restaurantId) {
            const response = await fetch("remove_restaurant.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `restaurant_id=${restaurantId}`
            });

            const result = await response.json();
            showPopup(result.success || result.error);
            if (result.success) {
                setTimeout(() => location.reload(), 1000); // Reload after success
            }
        }

        async function clearRating(restaurantId) {
            const response = await fetch("clear_rating.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `restaurant_id=${restaurantId}`
            });

            const result = await response.json();
            showPopup(result.success || result.error);
            if (result.success) {
                setTimeout(() => location.reload(), 1000); // Reload after success
            }
        }

        function showPopup(message) {
            let popup = document.getElementById("popupMessage");
            popup.innerText = message;
            popup.style.display = "block";
            setTimeout(() => { popup.style.display = "none"; }, 2000);
        }

        // Logout function
        function logout() {
            fetch("logout.php").then(() => {
                window.location.href = "login_style.html";
            });
        }

        // Load user profile on page load
        loadUserProfile();
    </script>
</body>
</html>
