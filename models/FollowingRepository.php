<?php

/**
 * ログイン中に他のユーザの投稿一覧画面を開いた際、
 * 未フォローであればフォローボタンを表示し、
 * フォロー済みであればメッセージを出力する
 */
class FollowingRepository extends DbRepository
{
  /**
   * フォローした際にレコードを作成
   */
  public function insert($user_id, $following_id)
  {
    $sql = "INSERT INTO following VALUES(:user_id, :following_id)";
    $stmt = $this->execute($sql, array(
      ':user_id' => $user_id,
      ':following_id' => $following_id,
    ));
  }

  /**
   *Followingテーブルからレコードを取得する
   */
  public function isFollowing($user_id, $following_id)
  {
    $sql = "
      SELECT COUNT(user_id) as count
        FROM following
        WHERE user_id = :user_id
        AND following_id = :following_id
    ";
    $row = $this->fetch($sql, array(
      ':user_id' => $user_id,
      ':following_id' => $following_id,
    ));
    if ($row['count'] !== '0') {
      return true;
    }
    return false;
  }
}
