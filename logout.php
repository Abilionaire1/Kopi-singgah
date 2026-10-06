<?php
session_start();
session_unset();
session_destroy();

// Redirect to the login page after ending the session.
header("Location: login.php");
exit();
?>