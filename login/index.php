<!DOCTYPE html>
<head>
    <?php
    require_once '../includes/core/min-header.php';
    ?>
    <title>Login</title>
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
        $result = get_user_by_username($connection, $username);
        $user = array();
        while ($r = mysqli_fetch_array($result)) {
            $user = $r;
        }
        if (sizeof($user) == 0) {
            echo "<p>Incorrect login information. Please try <a href=\"/login\">logging in</a> again.</p>";
        } else if (password_verify($password, $user["password"])) {
            $result = set_user_lastlogin($connection, $user["uid"]);
            if ($result) {
                $_SESSION["user"] = $user;
                end_connection($connection);
                if (isset($_GET["r"])) {
                    $validurl = filter_var(urldecode($_GET["r"]), FILTER_SANITIZE_URL);
                    if ($validurl !== false) {
                        header("Location: " . $validurl);
                    } else {
                        header("Location: /profile/");
                    }
                } else {
                    header("Location: /profile/");
                }
            } else {
                echo "<p>Could not update last login. Please try <a href=\"/login\">logging in</a> again.</p>";
            }
        } else {
            echo "<p>Incorrect login information. Please try <a href=\"/login\">logging in</a> again.</p>";
        }
        end_connection($connection);
    } else {
        echo "<form class=\"login\" action=\"\" method=\"post\"><h1>Login</h1>
    <input style=\"display:block\" type=\"text\" name=\"username\" placeholder=\"Username\" required autofocus/>
    <input style=\"display:block\" type=\"password\" name=\"password\" placeholder=\"Password\" required>
    <input type=\"submit\" value=\"Submit\"></form>";
    }

    ?>
</div>
</body>
