<?php

/**
 * データベースへのアクセスを伴う処理を管理するクラス。
 * データベースのテーブル毎にDbRepositoryを継承させ、子クラスを作成する
 */
abstract class DbRepository
{
  protected $con;

  public function __construct($con)
  {
    $this->setConnection($con);
  }

  public function setConnection($con)
  {
    $this->con = $con;
  }

  public function execute($sql, $params = array())
  {
    //PDOStatementクラスのインスタンスが返ってくる
    $stmt = $this->con->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  //Select文を実行した際の実行結果を取得
  public function fetch($sql, $params = array())
  {
    //1行のみ取得
    return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
  }

  public function fetchAll($sql, $params = array())
  {
    //全ての行を取得
    return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
  }
}
