<?php
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

// Fetch recommendations for a specific user
$user_id = 1; // Replace with the actual user_id based on session or input

$query = "
    SELECT p.restaurant_id, p.displayname_text, p.types, p.rating, p.short_formatted_address, 
           p.google_maps_uri, r.score 
    FROM precomputed_recommendations r
    JOIN places p ON r.restaurant_id = p.restaurant_id
    WHERE r.user_id = $user_id
    ORDER BY r.score DESC
";

$result = pg_query($conn, $query);

if (!$result) {
    die(json_encode(["error" => "Query failed: " . pg_last_error()]));
}

// Fetch recommendations
$recommendations = [];
while ($row = pg_fetch_assoc($result)) {
    $recommendations[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Recommendations</title>
</head>
<body>

    <h1>Your Recommended Restaurants</h1>

    <?php if (empty($recommendations)): ?>
        <p>No recommendations available.</p>
    <?php else: ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Restaurant Name</th>
                    <th>Types</th>
                    <th>Rating</th>
                    <th>Address</th>
                    <th>Google Maps Link</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recommendations as $recommendation): ?>
                    <tr>
                        <td><?= htmlspecialchars($recommendation['displayname_text']) ?></td>
                        <td><?= htmlspecialchars($recommendation['types']) ?></td>
                        <td><?= htmlspecialchars($recommendation['rating']) ?></td>
                        <td><?= htmlspecialchars($recommendation['short_formatted_address']) ?></td>
                        <td><a href="<?= htmlspecialchars($recommendation['google_maps_uri']) ?>" target="_blank">View on Google Maps</a></td>
                        <td><?= htmlspecialchars($recommendation['score']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>

<?php
// Close the connection
pg_close($conn);
?>
