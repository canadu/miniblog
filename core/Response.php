<?php

/**
 * リクエストに対するレスポンス。最終的にユーザーへ返すレスポンシ情報を管理する
 * （Viewから受け取ったブラウザに描画したい内容をechoで表示。HTTPレスポンスを返す）
 */
class Response
{
  protected $content;
  protected $status_code = 200;
  protected $status_text = 'ok';
  protected $http_headers = array();

  /**
   *各プロパティに設定された値を元にレスポンスの送信を行う
   */
  public function send()
  {
    //ステータスコードの指定を行っている
    header('HTTP/1.1' . $this->status_code . ' ' . $this->status_code);

    //HTTPレスポンスヘッダの指定があれば、header関数を用いて送信する
    foreach ($this->http_headers as $name => $value) {
      header($name . ': ' . $value);
    }
    echo $this->content;
  }

  /**
   * クライアントに返す内容をcontentプロパティに設定する
   */
  public function setContent($content)
  {
    $this->content  = $content;
  }

  /**
   * HTTPのステータスコードを格納する
   */
  public function setStatusCode($status_code, $status_text = '')
  {
    $this->status_code = $status_code;
    $this->status_text = $status_text;
  }

  /**
   * HTTPヘッダを格納するプロパティ
   */
  public function setHttpHeader($name, $value)
  {
    $this->http_headers[$name] = $value;
  }
}
