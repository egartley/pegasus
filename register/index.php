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

    # Derived from: https://webdevtrick.com/login-system-php-mysql/
    if (!$displayform) {
        require_once '../includes/core/login-db.php';
        $connection = connection_logindb();
        $username = mysqli_real_escape_string($connection, stripslashes($_REQUEST["username"]));
        $password = mysqli_real_escape_string($connection, stripslashes($_REQUEST["password"]));
        # check if users table exists
        $tablecheck = mysqli_query($connection, "DESCRIBE `users_v0`");
        if ($tablecheck == FALSE) {
            # create the table
            $table = mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `users_v0` (`uid` int(6) NOT NULL, `username` varchar(32) NOT NULL, `password` varchar(256) NOT NULL, `creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `lastlogin` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`uid`))");
        }
        try {
            $uid = random_int(100000, 999999);
            $check = mysqli_query($connection, "SELECT * FROM `users_v0` WHERE uid=" . $uid);
            while (mysqli_num_rows($check) > 0) {
                # ensure no duplicate uid's
                $uid = random_int(100000, 999999);
                $check = mysqli_query($connection, "SELECT * FROM `users_v0` WHERE uid=" . $uid);
            }
        } catch (Exception $e) {
            $uid = 1;
            echo "<p>Error while generating the UID: " . $e->getMessage() . "</p>";
        }
        # NOTE: allow duplicate usernames?
        $query = "INSERT into `users_v0` (`uid`, `username`, `password`, `creation`, `lastlogin`) VALUES (" . $uid
            . ", '$username', '" . password_hash($password, PASSWORD_DEFAULT)
            . "', CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP())";
        $result = mysqli_query($connection, $query);
        if ($result) {
            echo "<p>Successful registration. Please <a href=\"/login\">login</a> to proceed.</p>";
        } else {
            echo "<p>Something went wrong. Try again.</p>";
        }
        mysqli_close($connection);
    } else {
        echo "<form class=\"register\" action=\"\" method=\"post\"><h1>Register</h1>
    <input style=\"display:block\" type=\"text\" name=\"username\" placeholder=\"Username\" required autofocus/>
    <input style=\"display:block\" type=\"password\" name=\"password\" placeholder=\"Password\" required>
    <input type=\"submit\" value=\"Submit\"></form>";
    }

    ?>
</div>
</body>
