<?php

class StatusRepository extends DbRepository
{
  /**
   * 投稿された情報を保存する
   */
  public function insert($user_id, $body)
  {
    $now = new DateTime();
    $sql = "
      INSERT INTO status(user_id, body, created_at)
        VALUES(:user_id, :body, :created_at)
    ";
    $stmt = $this->execute($sql, array(
      ':user_id' => $user_id,
      ':body' => $body,
      ':created_at' => $now->format('Y-m-d H:i:s'),
    ));
  }

  /**
   * ログインユーザーに関連する投稿を取得する。
   */
  public function fetchAllPersonalArchivesByUserId($user_id)
  {
    $sql = "
      SELECT a.*, u.user_name
        FROM status a
          LEFT JOIN user u ON a.user_id = u.id
        WHERE u.id = :user_id
        ORDER BY a.created_at DESC
      ";
    return $this->fetchAll($sql, array(':user_id' => $user_id));
  }

  /**
   * ユーザーIDに一致する投稿を、投稿日の降順に全件取得する
   */
  public function fetchAllByUserId($user_id)
  {
    $sql = "
      SELECT a.*, u.user_name
        FROM status a
          LEFT JOIN user u ON a.user_id = u.id
        WHERE u.id = :user_id
        ORDER BY a.created_at DESC
    ";
    return $this->fetchAll($sql, array(':user_id' => $user_id));
  }

  /**
   * 投稿IDとユーザーのIDに一致するレコードを1件取得する
   */
  public function fetchByIdAndUserName($id, $user_name)
  {
    $sql = "
      SELECT a.*, u.user_name
        FROM status a
          LEFT JOIN user u ON u.id = a.user_id
        WHERE a.id = :id
          AND u.user_name = :user_name
    ";
    return $this->fetch($sql, array(
      ':id' => $id,
      ':user_name' => $user_name,
    ));
  }
}
