<?php
session_start();
// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: login_style.php');
  exit();
}
$user_email = $_SESSION['email'] ?? 'Guest';
$user_firstname = $_SESSION['firstname'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Page</title>
  <script type="text/javascript" src="validation.js" defer></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');
    :root {
      --accent-color: #f0c85a;
      --base-color: white;
      --text-color: #2E2B41;
      --input-color: #F3F0FF;
    }
    * {
      margin: 0;
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
      background-image: url(background1.jpg);
      background-size: cover;
      background-position: right;
      overflow: hidden;
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
    .wrapper {
      box-sizing: border-box;
      background-color: var(--base-color);
      height: 100vh;
      width: max(40%, 600px);
      padding: 10px;
      border-radius: 0 20px 20px 0;
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
    a {
      text-decoration: none;
      color: var(--accent-color);
    }
    a:hover {
      text-decoration: underline;
    }
    @media(max-width: 1100px) {
      .wrapper {
        width: min(600px, 100%);
        border-radius: 0;
      }
    }
  </style>
</head>
<body>
<span id="user-info" style="float:right; margin-right:20px;">
            <?php echo htmlspecialchars($user_firstname) . " (" . htmlspecialchars($user_email) . ")"; ?>
</span>
  <div class="wrapper">
    <h1>Dine Spot</h1>
    <h2>Welcome to the Home Page</h2>
    <div class="button-container">
      <button onclick="window.location.href='personalize.php'">Recommended Restaurants for you!</button>
      <button onclick="window.location.href='index.php'">Select Restaurant by Type</button>
      <button onclick="window.location.href='user.php'">Go to User Profile</button>
      <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </div>
</body>
</html>