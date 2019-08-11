<?php
session_start();
require('../dbconnect.php');

//htmlspecialcharsの省略
function hsc($value) {
  return htmlspecialchars($value, ENT_QUOTES);
}

//入力画面を経ずにcheck.phpが呼び出された場合、join_form.phpに戻す
if (!isset($_SESSION['join'])) {
  header('Location: ./index.php');
  exit();
}

if (!empty($_POST)) {
  //登録処理をする
  $statement = $db->prepare('INSERT INTO members SET name=?, email=?, password=?, created=NOW()');//sql組み立て、セッションに保存された値をセット
  echo $ret = $statement->execute(array(//データベースに実行
    $_SESSION['join']['name'],
    $_SESSION['join']['email'],
    sha1($_SESSION['join']['password']),//暗号化
  ));
  unset($_SESSION['join']);
  header('Location: ./done.php');
  exit();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <link rel="stylesheet" href="../base.css">
  <link rel="stylesheet" href="../style.css">

  <title>Full Scratch BBS</title>
</head>
<body>
  <div class="inner">
    <h1>Full Scratch BBS</h1>
    <h2>登録内容確認</h2>

    <form action="" method="post">
      <input type="hidden" name="action" value="submit">
      <dl>
        <dt>ニックネーム</dt>
        <dd>
          <p><?php echo hsc($_SESSION['join']['name']); ?></p>
        </dd>
      </dl>

      <dl>
        <dt>メールアドレス</dt>
        <dd>
          <p><?php echo hsc($_SESSION['join']['email']); ?></p>
        </dd>
      </dl>

      <dl>
        <dt>パスワード</dt>
        <dd>
          <p><?php echo hsc($_SESSION['join']['password']); ?></p>
        </dd>
      </dl>
      
      <a href="./index.php">戻る</a>
      <input type="submit" value="登録する">
    </form>

  </div>
</body>
</html>