<?php

/**
 * オートロードに関する処理をまとめたクラス
 * オートロード対象となるクラスのルール
 *  1.クラス名.phpというファイル名
 *  2.coreディレクトリ、modelsディレクトリに配置する
 */
class ClassLoader
{
  protected $dirs;

  /**
   * phpにオートローダークラスを登録する
   */
  public function  register()
  {
    spl_autoload_register(array($this, 'loadClass'));
  }

  /**
   *ディレクトリを登録する
    @param string $dir ディレクトリを登録する
   */
  public function registerDir($dir)
  {
    $this->dirs[] = $dir;
  }

  /**
   * クラスファイルの読み込みを行う(phpから自動的に呼び出される)
   */
  public function loadClass($class)
  {
    foreach ($this->dirs as $dir) {
      $file = $dir . '/' . $class . '.php';
      if (is_readable($file)) {
        require $file;
        return;
      }
    }
  }
}
