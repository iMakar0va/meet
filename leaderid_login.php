<?php
$client_id = "1e35caef-6ec7-4995-b5ef-6d2d054790c2";
$redirect_uri = urlencode("https://localhost/wow2/meet/leaderid_callback.php");

$auth_url = "https://leader-id.ru/apps/authorize?client_id=$client_id&redirect_uri=$redirect_uri";

header("Location: $auth_url");
exit();
