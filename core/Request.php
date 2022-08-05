<?php

/**
 * Request.
 * URL等のリクエストに対する処理を行う
 * Base_URLとPath_infoに分解する
 * HTTPメソッドの判定、GET、POSTなどの値の取得、リクエストされてURLの取得などを行う
 */
class Request
{
  /**
   * リクエストメソッドがPOSTかどうか判定
   *
   * @return boolean
   */
  public function isPost()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      return true;
    }

    return false;
  }

  /**
   * GETパラメータを取得
   *
   * @param string $name
   * @param mixed $default 指定したキーが存在しない場合のデフォルト値
   * @return mixed
   */
  public function getGet($name, $default = null)
  {
    if (isset($_GET[$name])) {
      return $_GET[$name];
    }

    return $default;
  }

  /**
   * POSTパラメータを取得
   *
   * @param string $name
   * @param mixed $default 指定したキーが存在しない場合のデフォルト値
   * @return mixed
   */
  public function getPost($name, $default = null)
  {
    if (isset($_POST[$name])) {
      return $_POST[$name];
    }

    return $default;
  }

  /**
   * ホスト名を取得
   *
   * @return string
   */
  public function getHost()
  {
    if (!empty($_SERVER['HTTP_HOST'])) {
      return $_SERVER['HTTP_HOST'];
    }

    return $_SERVER['SERVER_NAME'];
  }

  /**
   * SSLでアクセスされたかどうか判定
   *
   * @return boolean
   */
  public function isSsl()
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      return true;
    }
    return false;
  }

  /**
   * リクエストURIを取得
   *
   * @return string
   */
  public function getRequestUri()
  {
    //ホスト部より後の値が格納されている
    return $_SERVER['REQUEST_URI'];
  }

  /**
   * ベースURL(ドメイン以降からフロントコントローラーまでの値)を取得
   *
   *
   * @return string
   */
  public function getBaseUrl()
  {
    //現在のスクリプトのパス（フロントコントローラーまでのパスが含まれている）
    $script_name = $_SERVER['SCRIPT_NAME'];

    //リクエストのURIを取得
    $request_uri = $this->getRequestUri();

    //$request_uriの中で$script_nameの文字列が最初に現れる箇所を取得
    if (0 === strpos($request_uri, $script_name)) {
      //フロントコントローラーがURLに含まれている場合
      return $script_name;
    } else if (0 === strpos($request_uri, dirname($script_name))) {
      // フロントコントローラーが省略されている場合
      return rtrim(dirname($script_name), '/');
    }
    return '';
  }

  /**
   * PATH_INFO(ベースURLより後ろの値)を取得
   *
   * @return string
   */
  public function getPathInfo()
  {
    // ベースURL(ドメイン以降からフロントコントローラーまでの値)を取得
    $base_url = $this->getBaseUrl();

    //リクエストURIを取得
    $request_uri = $this->getRequestUri();

    //ゲットパラメーターを取り除いた値を取得する
    if (false !== ($pos = strpos($request_uri, '?'))) {
      $request_uri = substr($request_uri, 0, $pos);
    }

    #request_uriからベースURL部分を除いた値をpathinfoとして取得する
    $path_info = (string)substr($request_uri, mb_strlen($base_url));

    return $path_info;
  }
}
