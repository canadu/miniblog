<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php if (isset($title)) : echo $this->escape($title) . ' - ';
          endif; ?>Mini Blog</title>
</head>

<body>
  <div class="header">
    <h1><a href="<?php echo $base_url; ?>/"></a>フレームワークのテスト</h1>
  </div>
  <div class="main">
    <?php echo $_content; ?>
  </div>
</body>

</html>