<?php

/**
 * P271 アクションの作成手順
 * 1. データベースアクセス処理を Repository クラスに定義
 * 2. ルーティングを MiniBlogApplication クラスに定義
 * 3. コントローラクラスを定義
 * 4. コントローラクラスにアクションを定義
 * 5. アクションのビューファイルを記述
 */

class MiniBlogApplication extends Application
{
  protected $login_action = array('account', 'signin');

  /**
   * ルートディレクトリへのパスを返す
   */
  public function getRootDir()
  {
    return dirname(__FILE__);
  }

  /**
   * ルーティングの設定を行う
   * ルーティング定義配列を返す。アクションを実装する段階で適宜追加する。
   */
  protected function registerRoutes()
  {
    return array(
      //StatusControllerのルーティング
      '/' => array('controller' => 'status', 'action' => 'index'),
      '/status/post' => array('controller' => 'status', 'action' => 'post'),
      '/user/:user_name' => array('controller' => 'status', 'action' => 'user'),
      '/user/:user_name/status/:id' => array('controller' => 'status', 'action' => 'show'),
      //AccountControllerのルーティング
      '/account' => array('controller' => 'account', 'action' => 'index'),
      '/account/:action' => array('controller' => 'account'),
      //followのルーティング
      '/follow' => array('controller' => 'account', 'action' => 'follow'),
    );
  }

  /**
   * アプリケーションの設定を行うメソッド
   * ここではDBへの接続設定を記述する
   */
  protected function configure()
  {
    $this->db_manager->connect('master', array(
      'dsn' => 'mysql:dbname=mini_blog;host=localhost',
      'user' => 'root',
      'password' => '',
    ));
  }
}
