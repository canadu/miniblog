<?php

/**
 * PDO クラスのインスタンスがデータベースとの接続情報になるので、
 * DbManager ではその管理を行う
 */
class DbManager
{
  protected $connections = array();
  protected $repository_connection_map = array();
  protected $repositories = array();

  public function connect($name, $params)
  {
    $params = array_merge(array(
      'dsn' => null,
      'user' => '',
      'password' => '',
      'options' => array(),
    ), $params);

    $con = new PDO(
      $params['dsn'],
      $params['user'],
      $params['password'],
      $params['options']
    );

    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->connections[$name] = $con;
  }

  public function getConnection($name = null)
  {
    if (is_null($name)) {
      return current($this->connections);
    }
    return $this->connections[$name];
  }

  public function setRepositoryConnectionMap($repository_name, $name)
  {
    $this->repository_connection_map[$repository_name] = $name;
  }

  public function getConnectionForRepository($repository_name)
  {
    if (isset($this->repository_connection_map[$repository_name])) {
      $name = $this->repository_connection_map[$repository_name];
      $con = $this->getConnection($name);
    } else {
      $con = $this->getConnection();
    }
    return $con;
  }

  /**
   * インスタンスの生成を行う
   * Repository名が$repositoriesに入っていない場合のみ生成を行う
   */
  public function get($repository_name)
  {
    if (!isset($this->repositories[$repository_name])) {
      //クラス名 = 名前+Repository とするのがルール
      $repository_class = $repository_name . 'Repository';
      //コネクション取得
      $con = $this->getConnectionForRepository($repository_name);
      $repository = new $repository_class($con);

      //インスタンスを保持するためプロパティに設定する
      $this->repositories[$repository_name] = $repository;
    }
    return $this->repositories[$repository_name];
  }

  /**
   * 接続の解放処理 インスタンスが破棄された際に自動的に呼び出される
   */
  public function __destruct()
  {
    //Repository内でも接続情報を参照しているため先にRepositoryのインスタンスを破棄する
    foreach ($this->repositories as $repository) {
      unset($repository);
    }
    foreach ($this->connections as $con) {
      unset($con);
    }
  }
}
