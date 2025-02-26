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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numRestaurants = (int)$_POST['num_restaurants'];
    
    // Query to fetch restaurants based on precomputed recommendations
    $user_id = $_SESSION['user_id'];  // Get user_id from session

    $query = "
        SELECT p.restaurant_id, p.displayname_text, p.types, p.short_formatted_address, 
           p.google_maps_uri, r.score 
        FROM precomputed_recommendations r
        JOIN places p ON r.restaurant_id = p.restaurant_id
        WHERE r.user_id = $1 
        AND r.restaurant_id NOT IN (
            SELECT restaurant_id FROM user_ratings WHERE user_id = $1
        )  -- Exclude restaurants the user has already rated
        ORDER BY r.score DESC
        LIMIT $2
    ";
    
    $result = pg_query_params($conn, $query, array($user_id, $numRestaurants));

    if (!$result) {
        die(json_encode(["error" => "Query failed: " . pg_last_error()]));
    }

    // Fetch the results
    $restaurants = [];
    while ($row = pg_fetch_assoc($result)) {
        $restaurants[] = $row;
    }
}

$user_email = $_SESSION['email'] ?? 'Guest';
$user_firstname = $_SESSION['firstname'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalize Restaurant Recommendations</title>
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
        .wrapper {
            background-color: var(--base-color);
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .container {
            text-align: center;
            margin: 20px;
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

    <div class="wrapper">
        <h1>Personalized Recommendations</h1>
        <form method="POST">
            <label for="num_restaurants">How many restaurants would you like to see?</label>
            <input type="number" name="num_restaurants" min="1" required>
            <button type="submit">Submit</button>
        </form>

    <?php if (isset($restaurants)): ?>
        <h1 style="text-align:center; clear:both;">Recommended Restaurants</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Restaurant Name</th>
                    <th>Types</th>
                    <th>Address</th>
                    <th>Google Maps Link</th>
                    <th>Match Score</th>
                    <th>Save</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $restaurant): ?>
                    <tr>
                        <td><?= htmlspecialchars($restaurant['displayname_text']) ?></td>
                        <td><?= htmlspecialchars($restaurant['types']) ?></td>
                        <td><?= htmlspecialchars($restaurant['short_formatted_address']) ?></td>
                        <td><a href="<?= htmlspecialchars($restaurant['google_maps_uri']) ?>" target="_blank">View on Google Maps</a></td>
                        <td><?= htmlspecialchars(number_format($restaurant['score'], 4)) ?></td>
                        <td><button class="save-btn" onclick="saveRestaurant('<?= htmlspecialchars($restaurant['displayname_text']) ?>', '<?= htmlspecialchars($restaurant['restaurant_id']) ?>')">Save</button></td>
                        </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script>
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
    </script>

</body>
</html>

<?php
// Close the connection
pg_close($conn);
?>
