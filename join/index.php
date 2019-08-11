<?php
session_start();
require('../dbconnect.php');

//htmlspecialcharsの省略
function hsc($value) {
  return htmlspecialchars($value, ENT_QUOTES);
}

//空でないか確認
if (!empty($_POST)) {
  if ($_POST['name'] == '') {
    $error['name'] = 'blank';
  }
  if ($_POST['email'] == '') {
    $error['email'] = 'blank';
  }
  if (strlen($_POST['password']) < 4) {//文字数をチェック
    $error['password'] = 'length';
  }
  if ($_POST['password'] == '') {
    $error['password'] = 'blank';
  }
  
  //重複アドレスチェック
  if (empty($error)) {
    $member = $db->prepare('SELECT COUNT(*) AS count FROM members WHERE email=?');//入力されたemailと同じemailの件数
      $member->execute(array($_POST['email']));
      $record = $member->fetch();//件数取り出し
      if ($record['count'] > 0) {//1以上=重複なのでエラー
        $error['email'] = 'duplicate';
      }
  }
  
  if (empty($error)) {//$error配列が空か（エラーがないか）確認。true:全ての項目が正常に代入されている
    $_SESSION['join'] = $_POST;//セッションに値を保存
    header('Location: ./check.php');//headerファンクションで次の画面に移動
    exit();
  }
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
    <h2>会員登録</h2>

    <form action="" method="post" enctype="multipart/form-data">
      <dl>
        <dt>BBSネーム(必須)</dt>
        <dd>
          <input type="text" name="name" id="name" value="<?php echo hsc($_POST['name']); ?>">

          <?php if ($error['name'] == 'blank'): //空だった場合(=blank)エラー表示?>
          <p class="error">* 入力してください</p>
          <?php endif; ?> 

        </dd>
      </dl>

      <dl>
        <dt>メールアドレス（必須）</dt>
        <dd>
          <input type="email" name="email" id="email" value="<?php echo hsc($_POST['email']); ?>">

          <?php if ($error['email'] == 'blank'): //空だった場合(=blank)エラー表示?>
          <p class="error">* 入力してください</p>
          <?php endif; ?> 

          <?php if ($error['email'] == 'duplicate'): ?>
          <p class="error">* すでに登録されたアドレスです</p>
          <?php endif; ?> 
          
        </dd>
      </dl>

      <dl>
        <dt>パスワード（必須）</dt>
        <dd>
          <input type="password" name="password" id="password">

          <?php if ($error['password'] == 'blank'): //空だった場合(=blank)エラー表示?>
          <p class="error">* 入力してください</p>
          <?php endif; ?> 

          <?php if ($error['password'] == 'length'): ?>
          <p class="error">* パスワードは4文字以上で入力してください</p>
          <?php endif; ?> 

        </dd>
      </dl>
      
      <input type="submit" value="確認する">
    </form>

  </div>
</body>
</html>