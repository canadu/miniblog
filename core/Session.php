<?php

/**
 * セッションの管理。認証も担当
 * セッションへの情報の格納や、認証済みかどうかの判定を行っている。
 */
class Session
{
  protected static $sessionStarted = false;
  protected static $sessionIdRegenerated = false;

  /**
   * コンストラクタ
   * セッションを自動的に開始する
   */
  public function __construct()
  {
    //複数回呼び出される事が無いように静的プロパティでチェックする
    if (!self::$sessionStarted) {
      session_start();
      self::$sessionStarted = true;
    }
  }

  /**
   * セッションに値を設定
   *
   * @param string $name
   * @param mixed $value
   */
  public function set($name, $value)
  {
    $_SESSION[$name] = $value;
  }

  /**
   * セッションから値を取得
   *
   * @param string $name
   * @param mixed $default 指定したキーが存在しない場合のデフォルト値
   */
  public function get($name, $default = null)
  {
    if (isset($_SESSION[$name])) {
      return $_SESSION[$name];
    }
    return $default;
  }

  /**
   * セッションから値を削除
   *
   * @param string $name
   */
  public function remove($name)
  {
    unset($_SESSION[$name]);
  }

  /**
   * セッションを空にする
   */
  public function clear()
  {
    $_SESSION = array();
  }

  /**
   * セッションIDを新しく発行する
   */
  public function regenerate($destroy = true)
  {
    if (!self::$sessionIdRegenerated) {
      session_regenerate_id($destroy);
      self::$sessionIdRegenerated = true;
    }
  }

  /**
   * ログイン状態の制御
   */
  public function setAuthenticated($bool)
  {
    $this->set('_authenticated', (bool)$bool);
    $this->regenerate();
  }

  /**
   * 認証済みか判定
   * _authenticatedというキーでログインしているかどうかの判定を行う
   */
  public function isAuthenticated()
  {
    return $this->get('_authenticated', false);
  }
}
