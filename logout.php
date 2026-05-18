<?php
session_start();
session_unset();
session_destroy();

// Redirect straight to the login page
header("Location: login.php");
exit;