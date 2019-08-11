<?php
session_start();
require('./dbconnect.php');

if (isset($_SESSION['id'])) {//ログインしているか検査
  $id = $_REQUEST['id'];

  //投稿を検査する
  $messages = $db->prepare('SELECT * FROM posts WHERE id=?');
  $messages->execute(array($id));
  $message = $messages->fetch();

  if ($message['member_id'] == $_SESSION['id']) {//削除したい投稿の投稿者とログインユーザーが同じか
    //削除する
    $del = $db->prepare('DELETE FROM posts WHERE id=?');
    $del->execute(array($id));
  }
}

header('Location: ./index.php');
exit();
?>