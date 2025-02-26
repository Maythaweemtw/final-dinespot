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

$categories_json = $_GET['categories'] ?? '[]';  // JSON string from frontend
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Default limit 10

// Decode JSON string into a PHP array
$categories = json_decode($categories_json, true);

// Check if categories array is empty
if (empty($categories)) {
    echo json_encode(["error" => "No categories selected"]);
    exit;
}

// Build SQL condition dynamically for ranking
$rankExpressions = [];
foreach ($categories as $category) {
    $safeCategory = pg_escape_string($category);
    $rankExpressions[] = "CASE WHEN types ILIKE '%$safeCategory%' THEN 1 ELSE 0 END";
}

// Create the ranking score expression
$rankScore = implode(" + ", $rankExpressions);

$matchedTypesExpr = implode(" || ', ' || ", $rankExpressions);

// Final SQL query
$query = "
    SELECT 
        restaurant_id,
        displayname_text, 
        types, 
        rating, 
        short_formatted_address, 
        google_maps_uri, 
        ($rankScore) AS relevance,
        -- Add a count of matches for each restaurant
        (SELECT COUNT(*) FROM unnest(string_to_array(types, ',')) AS type WHERE type ILIKE ANY (ARRAY[" . implode(',', array_map(fn($c) => "'%$c%'", $categories)) . "])) AS match_count,
        -- Show which restaurant types matched
        array_to_string(array(SELECT type FROM unnest(string_to_array(types, ',')) AS type WHERE type ILIKE ANY (ARRAY[" . implode(',', array_map(fn($c) => "'%$c%'", $categories)) . "])), ', ') AS matched_types
    FROM places
    WHERE " . implode(" OR ", array_map(fn($c) => "types ILIKE '%" . pg_escape_string($c) . "%'", $categories)) . "
    ORDER BY relevance DESC, displayname_text ASC
    LIMIT $limit
";


$result = pg_query($conn, $query);

if (!$result) {
    die(json_encode(["error" => "Query failed: " . pg_last_error()]));
}

// Fetch data and return it as JSON
$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;

// Close the connection
pg_close($conn);
?>