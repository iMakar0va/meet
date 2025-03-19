<?php
setcookie("token", "", time() - 3600, "/"); // Удаляем токен
header("Location: auth.php");
exit();
?>
