<!DOCTYPE html>
<head>
    <?php
    require_once '../includes/core/min-header.php';
    ?>
    <title>Registration</title>
</head>
<body>
<div class="outter">
    <?php

    $displayform = !isset($_REQUEST["username"]) || !isset($_REQUEST["password"]);

    session_start();
    if (isset($_SESSION["user"])) {
        echo "<p>Already logged in. Logout <a href=\"/logout\">here</a>.";
        exit;
    }

    if (!$displayform) {
        require_once '../includes/core/mysql-main.php';
        $connection = get_mysql_login_connection();
        $username = mysqli_real_escape_string($connection, stripslashes($_REQUEST["username"]));
        $password = mysqli_real_escape_string($connection, stripslashes($_REQUEST["password"]));
        check_login_db($connection);
        $uid = get_new_uid($connection);
        $result = create_new_user($connection, $uid, $username, $password);
        if ($result) {
            echo "<p>Successful registration. Please <a href=\"/login\">login</a> to proceed.</p>";
        } else {
            echo "<p>Something went wrong. Try again.</p>";
        }
        end_connection($connection);
    } else {
        echo "<form class=\"register\" action=\"\" method=\"post\"><h1>Register</h1>
    <input style=\"display:block\" type=\"text\" name=\"username\" placeholder=\"Username\" required autofocus/>
    <input style=\"display:block\" type=\"password\" name=\"password\" placeholder=\"Password\" required>
    <input type=\"submit\" value=\"Submit\"></form>";
    }

    ?>
</div>
</body>
