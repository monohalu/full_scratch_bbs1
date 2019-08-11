<?php
session_start();

//セッション情報を削除
$_SESSION = array();
if (ini_get("session.use_cokkies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 420000,//空の内容を記録、有効期限を過去に
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
session_destroy();

//cookie情報も削除
setcookie('email', '', time() - 3600);//空の内容を記録、有効期限を過去に
setcookie('password', '', time() -3600);

header('Location: login.php');
exit();
?>