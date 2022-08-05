<?php

/**
 * テーブルからのデータ取得等のCRUDを担当しています。
 * テーブル毎にDbRepositoryを継承させ、子クラスを作成する
 * このフレームワークではモデルに相当する
 */

abstract class DbRepository
{
  protected $con;

  //PDOクラスのインスタンスを受け取る
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
    //
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
    //FETCH_ASSOC→取得結果を連想配列で受け取る
    return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
  }

  public function beginTransaction()
  {
    $this->con->beginTransaction();
  }

  public function commit()
  {
    $this->con->commit();
  }

  public function rollBack()
  {
    $this->con->rollBack();
  }
}
