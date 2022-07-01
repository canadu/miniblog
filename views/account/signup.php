<?php $this->setLayoutVar('title', 'アカウント登録') ?>
<h2>アカウント登録</h2>
<form action="<?php echo $base_url; ?>/account/register" method="POST">
  <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>" />
  <!-- エラーの共通化処理 -->
  <?php if (isset($errors) && count($errors) > 0) : ?>
    <?php echo $this->render('errors', array('errors' => $errors)); ?>
  <?php endif; ?>
  <!-- エラーの共通化処理 end -->
  <?php echo $this->render('account/inputs', array(
    'user_name' => $user_name, 'password' => $password,
  )); ?>
  <p><input type="submit" value="登録"></p>
</form>