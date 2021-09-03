<?php 
    include_once('includes/header.php');
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: login.html");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/scripts.js"></script>
</head>
<body>

<header class="header">
    <div class="header-limiter">
        <h1><a href="#">App<span>Logo</span></a></h1>

        <nav>
            <a href="#">Profile</a>
            <a href="?logout">Logout</a>
        </nav>
    </div>
</header>


<?php
    $result = getUserInfo($_SESSION['user_id'], $conn);
?>
    
    <div>
        <h1>Welcome <?=ucwords($result[0]['username'])?>, Nice having you here</h1>
    </div>



</body>
</html>