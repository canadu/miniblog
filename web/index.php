<?php
// フロントコントローラー
// 全てのリクエストを1つのPHPファイルで受け取る
require '../bootstrap.php';

//アプリケーションを実行
require '../MiniBlogApplication.php';
$app = new MiniBlogApplication(false);
$app->run();
