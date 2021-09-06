<?php

$LOGIN_USER = "root";
$LOGIN_PASS = "";
$LOGIN_HOST = "localhost";
$LOGIN_DB = "pegasus_login";
$LOGIN_PORT = 3306;
$LOGIN_TABLE_NAME = "users_v0";
$LOGIN_TABLE_TEMPLATE = "(`uid` int(6) NOT NULL, `username` varchar(32) NOT NULL, `password` varchar(1024) NOT NULL, `creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `lastlogin` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`uid`))";
$LOGIN_TABLE_VALUES = "(`uid`, `username`, `password`, `creation`, `lastlogin`)";

function get_mysql_connection($database, $username = "", $password = "", $hostname = "", $port = null)
{
    $connection = mysqli_connect($hostname, $username, $password, $database, $port);
    if ($connection == FALSE) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    return $connection;
}

function run_query($connection, $query)
{
    return mysqli_query($connection, $query);
}

function end_connection($connection)
{
    mysqli_close($connection);
}

function get_mysql_login_connection()
{
    global $LOGIN_USER;
    global $LOGIN_PASS;
    global $LOGIN_HOST;
    global $LOGIN_DB;
    global $LOGIN_PORT;
    return get_mysql_connection($LOGIN_DB, $LOGIN_USER, $LOGIN_PASS, $LOGIN_HOST, $LOGIN_PORT);
}

function get_new_uid($connection): int
{
    global $LOGIN_TABLE_NAME;
    try {
        $uid = random_int(100000, 999999);
        $check = run_query($connection, "SELECT * FROM `$LOGIN_TABLE_NAME` WHERE uid=" . $uid);
        while (mysqli_num_rows($check) > 0) {
            $uid = random_int(100000, 999999);
            $check = run_query($connection, "SELECT * FROM `$LOGIN_TABLE_NAME` WHERE uid=" . $uid);
        }
    } catch (Exception $exception) {
        $uid = 1;
    }
    return $uid;
}

function check_login_db($connection)
{
    global $LOGIN_TABLE_NAME;
    global $LOGIN_TABLE_TEMPLATE;
    $tablecheck = run_query($connection, "DESCRIBE `" . $LOGIN_TABLE_NAME . "`");
    if ($tablecheck == FALSE) {
        run_query($connection, "CREATE TABLE IF NOT EXISTS `$LOGIN_TABLE_NAME` $LOGIN_TABLE_TEMPLATE");
    }
}

function create_new_user($connection, $uid, $username, $password)
{
    global $LOGIN_TABLE_NAME;
    global $LOGIN_TABLE_VALUES;
    return run_query($connection, "INSERT into `$LOGIN_TABLE_NAME` $LOGIN_TABLE_VALUES VALUES (" . $uid . ", '$username', '" . password_hash($password, PASSWORD_DEFAULT) . "', CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP())");
}

function get_user_by_username($connection, $username)
{
    global $LOGIN_TABLE_NAME;
    return run_query($connection, "SELECT * FROM `$LOGIN_TABLE_NAME` WHERE username='$username' LIMIT 1");
}

function set_user_lastlogin($connection, $uid)
{
    global $LOGIN_TABLE_NAME;
    return run_query($connection, "UPDATE `$LOGIN_TABLE_NAME` SET lastlogin=CURRENT_TIMESTAMP() WHERE uid=" . $uid);
}
