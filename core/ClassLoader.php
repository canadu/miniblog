<?php

/**
 * オートロードに関する処理をまとめたクラス
 * オートロード対象となるクラスのルール
 *  1.クラス名.phpというファイル名(クラス名とファイル名が同じ)
 *  2.coreディレクトリ、modelsディレクトリに配置する
 */
class ClassLoader
{
  // オートロードで調べるディレクトを格納する変数
  protected $dirs;

  /**
   * オートロード実行メソッド
   * 指定した関数を __autoload() の実装として登録する
   */
  public function  register()
  {
    // loadClassメソッドを実行する
    spl_autoload_register(array($this, 'loadClass'));
  }

  /**
   *ディレクトリを登録する
    @param string $dir 探索するディレクトリを登録する
   */
  public function registerDir($dir)
  {
    $this->dirs[] = $dir;
  }

  /**
   * クラスファイルの読み込みを行う(phpから自動的に呼び出される)
   * 未定義のクラスをnewした場合呼び出される
   * $classはその時のクラス名
   */
  public function loadClass($class)
  {
    foreach ($this->dirs as $dir) {
      $file = $dir . '/' . $class . '.php';
      //ファイルが存在し、読み込み可能であるかどうかを知る
      if (is_readable($file)) {
        require $file;
        return;
      }
    }
  }
}
