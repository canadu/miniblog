<?php
class UserRepository extends DbRepository
{
  /*
  SQLを実行するうえで必要な情報はControllerクラスで取得し、Repositoryクラスはメソッドを呼び出す際に引数で情報を受け取るようになる
  insert:レコードを新規登録するメソッド名で使用する
  fetch:レコードを取得する場合に使用する 複数の場合:fetchAll
  **/

  /**
   * ユーザーを新規登録する
   */
  public function insert($user_name, $password)
  {
    //パスワードはハッシュ化した上でDBに登録する
    $password = $this->hashPassword($password);
    $now = new DateTime();
    $sql = "INSERT INTO user(user_name, password, created_at) VALUES(:user_name, :password, :created_at)";
    $stmt = $this->execute($sql, array(
      ':user_name' => $user_name,
      ':password' => $password,
      ':created_at' => $now->format('Y-m-d H:i:s'),
    ));
  }

  /**
   * パスワードのハッシュ化
   */
  public function hashPassword($password)
  {
    return sha1($password . 'SecretKey');
  }

  /**
   * ユーザーIDから名前を取得する
   */
  public function fetchByUserName($user_name)
  {
    $sql =  "SELECT * FROM user WHERE user_name = :user_name";
    return $this->fetch($sql, array(':user_name' => $user_name));
  }

  /**
   * ユーザーIDの重複を調べる
   */
  public function isUniqueUserName($user_name)
  {
    $sql = "SELECT COUNT(id) as count FROM user WHERE user_name = :user_name";
    $row = $this->fetch($sql, array(':user_name' => $user_name));
    if ($row['count'] === '0') {
      return true;
    }
    return false;
  }

  /**
   * フォローしているユーザーを取得する
   */
  public function fetchAllFollowingsByUserId($user_id)
  {
    $sql = "
      SELECT u.*
      FROM user u
        LEFT JOIN following f ON f.following_id = u.id
      WHERE f.user_id = :user_id
    ";
    return $this->fetchAll($sql, array(':user_id' => $user_id));
  }
}
