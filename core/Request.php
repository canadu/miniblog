<?php
class Request
{
  /**
   * HTTPメソッドの判定メソッド
   * @return boolean(true:POST)
   *
   */
  public function isPost()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      return true;
    }
    return false;
  }

  /**
   * $_Get変数から値を取得する
   */
  public function getGet($name, $default = null)
  {
    if (isset($_GET[$name])) {
      return $_GET[$name];
    }
    return $default;
  }

  /**
   * $_POST変数から値を取得する
   */
  public function getPost($name, $default = null)
  {
    if (isset($_POST[$name])) {
      return $_POST[$name];
    }
    return $default;
  }

  /**
   * サーバのホスト名を取得するメソッド
   */
  public function getHost()
  {
    if (!empty($_SERVER['HTTP_HOST']))
    {
      return $_SERVER['HTTP_HOST'];
    }
    return $_SERVER['SEVER_NAME'];
  }

  /**
   * SSLでアクセスされたかどうかを判定する
   */
  public function isSsl()
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    {
      return true;
    }
    return false;
  }

  /**
  * URLのホスト部分以降の値を返す
  */
  public function getRequestUri()
  {
    return $_SERVER['REQUEST_URI'];
  }

  /**
   * ベースURL(ホスト部以降からフロントコントローラーまで)の特定を行う
   */
  public function getBaseUrl()
  {
    $script_name = $_SERVER['SCRIPT_NAME'];
    $request_uri = $this->getRequestUri();
    if(0 === strpos($request_uri,$script_name)) {
      return $script_name;
    } else if (0 === strpos($request_uri, dirname($script_name))) {
      return rtrim(dirname($script_name), '/');
    }
    return '';
  }

  /**
   * REQUEST_URIからベースURLを取り除いた値を返す
   */
  public function getPathInfo()
  {
    $base_url = $this->getBaseUrl();
    $request_uri = $this->getRequestUri();
    if(false !== ($pos = strpos($request_uri,'?'))) {
      $request_uri = sbstr($request_uri,0,$pos);
    }
    $path_info = (string)substr($request_uri,strlen($base_url));
    return $path_info;
  }



}