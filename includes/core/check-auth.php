<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: /login/?r=" . urlencode($_SERVER['REQUEST_URI']));
}
