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

    if (session_destroy()) {
        echo "<p>Destoryed session. Login again <a href=\"/login\">here</a>.</p>";
    }

    ?>
</div>
</body>
