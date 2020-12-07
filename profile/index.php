<!DOCTYPE html>
<head>
    <?php
    require_once '../includes/core/min-header.php';
    ?>
    <title>Profile</title>
</head>
<body>
<div class="outter">
    <?php

    require_once "../includes/core/check-auth.php";

    $user = $_SESSION["user"];

    echo "<p>Username: " . $user["username"] . " (" . $user["uid"] . ")<br>";
    echo "Creation: " . $user["creation"] . "<br>";
    echo "Last login: " . $user["lastlogin"] . "<br><br>";

    echo "<a href=\"/dashboard/\">Dashboard</a><br>";
    echo "<a href=\"/logout/\">Logout</a></p>";

    ?>
</div>
</body>
