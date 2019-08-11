<?php

session_start();
require('./dbconnect.php');

//htmlspecialcharsの省略
function hsc($value) {
  return htmlspecialchars($value, ENT_QUOTES);
}


if ($_COOKIE['email'] != '') {//cookieにログイン情報が保存された状態でアクセスしてきた場合
  $_POST['email'] = $_COOKIE['email'];//cookieを$_POSTに代入
  $_POST['password'] = $_COOKIE['password'];
  $_POST['save'] = 'on';//改めて新しい有効期間が設定される（2week）
}

if (!empty($_POST)) {//ログインボタンがクリックされているか確認？
  //ログインの処理
  if ($_POST['email'] != '' && $_POST['password'] != '') {
    $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');//データ検索
    $login->execute(array(
      $_POST['email'],
      sha1($_POST['password'])
    ));
    $member = $login->fetch();
  
      if ($member) {
        //ログイン成功
        $_SESSION['id'] = $member['id'];
        $_SESSION['time'] = time();
        header('Location: index.php');
        exit();
      } else {//ログイン失敗は、パスワードが間違えているか、会員登録されていないかなのでfailedエラー発生
        $error['login'] = 'failed';
      }
  } else {//emailとpasswordが空欄の場合、記入を促すためblankというエラー発生
    $error['login'] = 'blank';
  }

}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <link rel="stylesheet" href="base.css">
  <link rel="stylesheet" href="style.css">


  <title>Full Scratch BBS</title>
</head>
<body>
  <div class="inner">
    <h1>Full Scratch BBS</h1>
    <h2>ログイン</h2>

    <form action="" method="post">

      <dl>
        <dt>メールアドレス</dt>
        <dd>
          <input type="email" name="email" id="login_email" value="<?php echo hsc($_POST['email']); ?>">
        </dd>
      </dl>

      <dl>
        <dt>パスワード</dt>
        <dd>
          <input type="password" name="password" id="login_password" value="<?php echo hsc($_POST['password']); ?>">
        </dd>
      </dl>

      <?php if ($error['login'] == 'blank'): ?>
      <p class="error">* メールアドレスとパスワードを入力してください</p>
      <?php endif; ?>
      <?php if ($error['login'] == 'failed'): ?>
      <p class="error">* ログインに失敗しました。正しく入力されているかご確認ください</p>
      <?php endif; ?>
      
      <input type="submit" value="ログイン">
    </form>

    <a href="./join/index.php">会員登録する</a>
  </div>
</body>
</html>