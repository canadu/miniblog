<?php
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
   * ルーティング定義配列を返す
   */
  protected function registerRoutes()
  {
    return array(
      //StatusControllerのルーティング
      '/'
      => array('controller' => 'status', 'action' => 'index'),
      '/status/post'
      => array('controller' => 'status', 'action' => 'post'),
      '/user/:user_name'
      => array('controller' => 'status', 'action' => 'user'),
      '/user/:user_name/status/:id'
      => array('controller' => 'status', 'action' => 'show'),
      //AccountControllerのルーティング
      '/account'
      => array('controller' => 'account', 'action' => 'index'),
      '/account/:action'
      => array('controller' => 'account'),
      '/follow'
      => array('controller' => 'account', 'action' => 'follow'),
    );
  }

  /**
   * アプリケーションの設定を行うメソッド
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
