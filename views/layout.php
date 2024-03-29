<!-- 共通デザインののレイアウトファイル -->
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- $title変数が設定されている場合はtitle要素内に設定されたタイトルを出力する -->
  <title><?php if (isset($title)) : echo $this->escape($title) . ' - ';
          endif; ?>Mini Blog</title>
  <link rel="stylesheet" href="/css/style.css">
</head>

<body>
  <div class="header">
    <h1><a href="<?php echo $base_url; ?>/"></a></h1>
  </div>
  <div id="nav">
    <p>
      <?php if ($session->isAuthenticated()) : ?>
        <!-- ログイン状態 -->
        <a href="<?php echo $base_url; ?>/">ホーム</a>
        <a href="<?php echo $base_url; ?>/account">アカウント</a>
      <?php else : ?>
        <!-- 未ログイン状態 -->
        <a href="<?php echo $base_url; ?>/account/signin">ログイン</a>
        <a href="<?php echo $base_url; ?>/account/signup">アカウント登録</a>
      <?php endif; ?>
    </p>
  </div>
  <div class="main">
    <!-- $_content変数にアクションを毎のHTMLが格納される -->
    <?php echo $_content; ?>
  </div>
</body>

</html>