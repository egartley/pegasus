<?php
# Derived from: https://webdevtrick.com/login-system-php-mysql/
function connection_logindb() {
    # TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS
    $logindbconnection = mysqli_connect("localhost","root","","pegasus_login", "3306");
    # TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS TEST CREDS
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    return $logindbconnection;
}
