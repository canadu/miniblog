<?php

/**
 *
 * オートロードを設定すると、クラスを呼び出した際にそのクラスが
 * PHP 上に読み込まれていない場合、自動的にファイルの読み込みを
 * 行うことができるようになります
 *
 * オートローダを行うクラスに実装する必要がある機能:
 * 1. PHP にオートローダクラスを登録する
 * 2. オートロードが実行された際にクラスファイルを読み込む
 *
 * オートロード対象となるクラスのルール:
 * クラスは「クラス名.php」というファイル名で保存する
 * クラスは core ディレクトリ及び model ディレクトリに配置する
 *
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
