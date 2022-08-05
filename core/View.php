<?php

/**
 * View.
 *
 * ビューファイルの読み込みは require を用いれば実行可能だが、
 * その際、読み込んだファイル内で出力が行われていると、読み込んだ時点で
 * 出力が行われてしまう。出力情報を文字列として読み込み、
 * レスポンスに設定する必要がある
 *
 * そこでアウトプットバッファリングという仕組みを用いて、出力を文字列として取得する
 *
 * require を用いてファイルを読み込むと、require を実行した側で
 * アクセス可能な変数に対し、読み込まれた側のファイルでもアクセスすることができる
 *
 */

class View
{
  protected $base_dir;
  protected $defaults;
  protected $layout_variables = array();

  /**
   * コンストラクタ
   * @param viewsディレクトリへの絶対パス
   * @param ビューファイルに渡す変数を指定
   */
  public function __construct($base_dir, $defaults = array())
  {
    $this->base_dir = $base_dir;
    $this->defaults = $defaults;
  }

  /**
   *
   */
  public function setLayoutVar($name, $value)
  {
    $this->layout_variables[$name] = $value;
  }

  /**
   * 実際にビューファイルの読み込みを行うメソッド
   * @param ビューファイルへのパス
   * @param ビューファイルに渡す変数を指定
   * @param レイアウトファイル名を指定
   */
  public function render($_path, $_variables = array(), $_layout = false)
  {
    //読み込むビューファイル
    $_file = $this->base_dir . '/' . $_path . '.php';

    //連想配列を変数に変換。キーが変数名となる
    extract(array_merge($this->defaults, $_variables));

    //アウトプットバッファリングを開始
    ob_start();

    //バッファの自動フラッシュを制御
    ob_implicit_flush(0);

    require $_file;

    // バッファの内容を変数に格納
    $content = ob_get_clean();

    if ($_layout) {
      $content = $this->render(
        $_layout,
        array_merge(
          $this->layout_variables,
          array(
            '_content' => $content,
          )
        )
      );
    }
    return $content;
  }


  public function escape($string)
  {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }
}
