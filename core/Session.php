<?php

/**
 * セッションの管理。認証も担当
 * セッションへの情報の格納や、認証済みかどうかの判定を行っている。
 */
class Session
{
  protected static $sessionStarted = false;
  protected static $sessionIdRegenerated = false;

  public function __construct()
  {
    //複数回呼び出される事が無いように静的プロパティでチェックする
    if (!self::$sessionStarted) {
      session_start();
      self::$sessionStarted = true;
    }
  }

  public function set($name, $value)
  {
    $_SESSION[$name] = $value;
  }

  public function get($name, $default = null)
  {
    if (isset($_SESSION[$name])) {
      return $_SESSION[$name];
    }
    return $default;
  }

  public function remove($name)
  {
    unset($_SESSION[$name]);
  }

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
   * _authenticatedというキーでログインしているかどうかの判定を行う
   */
  public function isAuthenticated()
  {
    return $this->get('_authenticated', false);
  }
}
