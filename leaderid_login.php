<?php
$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$redirect_uri = urlencode("localhost/wow2/meet/leaderid_callback.php");

$auth_url = "https://leader-id.ru/api/oauth/authorize?client_id=KEY&redirect_uri=REDIRECT_URI&response_type=code";

header("Location: $auth_url");
exit();
