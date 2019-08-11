<?php
session_start();
require('./dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {//idがセッションにきろくされている、最後の行動から一時間以内である
  //ログインしている
  $_SESSION['time'] = time();//今の時間で上書き。さらにログインが60min有効になる。

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();
} else {
  header('Location: ./login.php'); exit();
}

//投稿を記録する
if (!empty($_POST)) {//postに値が入っているか（フォームから送信されたか）を確認

  $fileName = $_FILES['image']['name'];//$_FILESはinput要素のname属性がキー=['image']
  if (!empty($fileName)) {//空でないかチェック。必須項目ではないのでない場合はチェックしない
    $ext = substr($fileName, -3);//拡張子取り出し（後ろから三文字）
    if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
      $error['image'] = 'type';
    }
  }

  if (empty($error)) {//$error配列が空か確認。true:全ての項目が正常に代入されている
    //画像をアップロードする
    $image = date('YmdHis') . $_FILES['image']['name'];//ファイル名
    move_uploaded_file($_FILES['image']['tmp_name'], './picture/' .$image);//アップロード
  }

    if (isset($_REQUEST['res'])) {//返信の場合
      $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, image=?, reply_post_id=?, created=NOW()');//postsテーブルにデータを保存
      $message->execute(array(
        $member['id'],
        $_POST['message'],
        $image,
        $_POST['reply_post_id']
      ));
    } else {//通常の投稿の場合
      $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, image=?, created=NOW()');//postsテーブルにデータを保存
      $message->execute(array(
        $member['id'],
        $_POST['message'],
        $image,
      ));
    }
    header('Location: ./index.php');
    exit();
  
}

//投稿を取得する
$page = $_REQUEST['page'];//URLパラメータで指定されてあ値をページ数として$pageに代入
if ($page == '') {//からだったら1とする
  $page = 1;
}
$page = max($page, 1);//URLパラメータにマイナス値が指定された場合、$pageに1が代入される

//最終ページを取得する
  //件数を取得して最大のページ数を兼さんする
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);//ceilで切り上げ,最大ページ数を計算しmaxPageに代入
$page = min($page, $maxPage);//最大ページ数とURLパラメータの小さい方を代入

$start = ($page - 1) * 5;//スタート位置を計算

$posts = $db->prepare('SELECT m.name, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?, 5');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();


//返信の場合
if (isset($_REQUEST['res'])) {
  $response = $db->prepare('SELECT m.name, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
  $response->execute(array($_REQUEST['res']));

  $table = $response->fetch();
  $message = '@' .$table['name'] . ' ';
}

//htmlspecialcharsのショートカット
function hsc($value) {
  return htmlspecialchars($value, ENT_QUOTES);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <link rel="stylesheet" href="./base.css">
  <link rel="stylesheet" href="./style.css">

  <title>Full Scratch BBS</title>
</head>
<body>
  <div class="inner">
    <h1>Full Scratch BBS</h1>
    <div class="login-name">ログイン中のアカウント：<?php echo hsc($member['name']); ?></div>

    <form action="" method="post" enctype="multipart/form-data">
      <dl>
        <dt>メッセージを入力してね（140文字まで）</dt>
        <dd>
          <textarea name="message" id="message" cols="50" rows="5" maxlength="140"><?php echo hsc($message); //返信する場合メッセージ表示?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php echo hsc($_REQUEST['res']); //返信先idを記録するためinputを追加しておく?>"> </dd>
      </dl>

      <dl>
        <dt>画像をつける</dt>
        <dd>
          <input type="file" name="image" id="image">
          <?php if ($error['image'] == 'type'): ?>
          <p class="error">* 添付画像はGIFファイルまたはJPGファイルまたはPNGファイルの画像を指定してください</p>
          <?php endif; ?>
        </dd>
      </dl>

      <input type="submit" value="投稿する">
    </form>

    
    <div class="post-container">
      
      <h3>最新のつぶやき</h3>

    <?php
    foreach ($posts as $post): ?>

      <section class="post">
        <div class="post-contents" class="name">
          <p><?php echo hsc($post['name']); ?>さん</p>
        </div>
        
        <div class="post-contents" class="message">
          <p><?php echo hsc($post['message']); ?></p>
        </div>
        
        <div class="post-contents" class="image">
          <img src="./picture/<?php echo hsc($post['image']); ?>" alt="">
        </div>
        
        
        <div class="post-contents" class="res">
          <a href="index.php?res=<?php echo hsc($post['id']);?>">返信する</a>
        </div>
        <?php if ($_SESSION['id'] == $post['member_id']): //投稿した本人だった場合、削除を表示?>
          <a href="delete.php?id=<?php echo hsc($post['id']); ?>" style="color:red">削除</a>
          <?php endif; ?>
          
          <div class="post-contents" class="date">
            <p>投稿日時：<?php echo hsc($post['created']); ?></p>
          </div>
        <hr>
      </section>
    <?php endforeach; ?>
    
    <ul class="paging">
<?php if ($page > 1): ?>
  <li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
<?php else: ?>
  <li>前のページへ</li>
<?php endif; ?>

<?php if ($page < $maxPage): ?>
  <li><a href="index.php?page=<?php print($page + 1); ?>"> 次のページへ</a></li>
<?php else: ?>
  <li>次のページへ</li>
<?php endif; ?>
</ul>

    </div>

    <a href="./logout.php">ログアウト</a>
      



  </div>
</body>
</html>