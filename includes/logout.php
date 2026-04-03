<?php
session_start();
session_unset();
session_destroy();
header("Location: ../Login Page 2.0/index.html");
exit();
?>