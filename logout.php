<?php
session_start();
session_destroy();
header("Location: AdminLogin.php");
exit();
?>