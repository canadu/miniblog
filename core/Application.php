<?php

/**
 * Application.
 *
 * Request クラスや Router クラス、Response クラス、
 * Session クラスなどのオブジェクトの管理を行うほか、
 * ルーティングの定義、コントローラの実行、レスポンスの送信など、
 * アプリケーション全体の流れを司る
 *
 * また、管理するのはオブジェクトだけではなく、
 * アプリケーションの様々なディレクトリヘのパスの管理なども行う
 *
 * この他にも、デバッグモードで実行できるような機能も持たせます
 *
 *
 */

abstract class Application
{
  protected $debug = false;
  protected $request;
  protected $response;
  protected $session;
  protected $db_manager;

  /**
   * コンストラクタ
   *
   * @param boolean $debug
   */
  public function __construct($debug = false)
  {
    $this->setDebugMode($debug);
    //各クラスをインスタンス化してプロパティに設定
    $this->initialize();
    $this->configure();
  }

  /**
   * デバッグモードを設定
   *
   * @param boolean $debug
   */
  protected function setDebugMode($debug)
  {
    if ($debug) {
      $this->debug = true;
      ini_set('display_errors', 1);
      error_reporting(-1);
    } else {
      $this->debug = false;
      ini_set('display_errors', 0);
    }
  }

  /**
   * アプリケーションの初期化
   */
  protected function initialize()
  {
    $this->request    = new Request();
    $this->response   = new Response();
    $this->session    = new Session();
    $this->db_manager = new DbManager();
    $this->router     = new Router($this->registerRoutes());
  }

  /**
   * アプリケーションの設定
   * アプリケーション特有の設定を行うために定義
   */
  protected function configure()
  {
  }

  /**
   * プロジェクトのルートディレクトリを取得
   *
   * @return string ルートディレクトリへのファイルシステム上の絶対パス
   */
  abstract public function getRootDir();

  /**
   * ルーティングを取得
   *
   * @return array
   */
  abstract protected function registerRoutes();

  /**
   * デバッグモードか判定
   *
   * @return boolean
   */
  public function isDebugMode()
  {
    return $this->debug;
  }

  /**
   * Requestオブジェクトを取得
   *
   * @return Request
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Responseオブジェクトを取得
   *
   * @return Response
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Sessionオブジェクトを取得
   *
   * @return Session
   */
  public function getSession()
  {
    return $this->session;
  }

  /**
   * DbManagerオブジェクトを取得
   *
   * @return DbManager
   */
  public function getDbManager()
  {
    return $this->db_manager;
  }

  /**
   * コントローラファイルが格納されているディレクトリへのパスを取得
   *
   * @return string
   */
  public function getControllerDir()
  {
    return $this->getRootDir() . '/controllers';
  }

  /**
   * ビューファイルが格納されているディレクトリへのパスを取得
   *
   * @return string
   */
  public function getViewDir()
  {
    return $this->getRootDir() . '/views';
  }

  /**
   * モデルファイルが格納されているディレクトリへのパスを取得
   *
   * @return string
   */
  public function getModelDir()
  {
    return $this->getRootDir() . '/models';
  }

  /**
   * ドキュメントルートへのパスを取得
   *
   * @return string
   */
  public function getWebDir()
  {
    return $this->getRootDir() . '/web';
  }

  /**
   * アプリケーションを実行する
   *
   * @throws HttpNotFoundException ルートが見つからない場合
   */
  public function run()
  {
    try {

      //ルーティングパラメーターを取得し、コントローラーとアクション名の配列を受け取る
      $params = $this->router->resolve($this->request->getPathInfo());

      if ($params === false) {
        throw new HttpNotFoundException('No route found for ' . $this->request->getPathInfo());
      }

      //コントローラーを変数に設定
      $controller = $params['controller'];

      //アクションを変数に設定
      $action = $params['action'];

      // アクションを実行する
      $this->runAction($controller, $action, $params);
    } catch (HttpNotFoundException $e) {
      $this->render404Page($e);
    } catch (UnauthorizedActionException $e) {
      list($controller, $action) = $this->login_action;
      $this->runAction($controller, $action);
    }

    // 結果を送信し、画面表示する
    $this->response->send();
  }

  /**
   * 指定されたアクションを実行する
   *
   * @param string $controller_name
   * @param string $action
   * @param array $params
   *
   * @throws HttpNotFoundException コントローラが特定できない場合
   */
  public function runAction($controller_name, $action, $params = array())
  {
    //コントローラーのクラス名はコントローラー名にControllerを付ける
    //先頭を大文字にする
    $controller_class = ucfirst($controller_name) . 'Controller';

    //コントローラーオブジェクトを取得
    $controller = $this->findController($controller_class);

    if ($controller === false) {
      //コントローラークラスのphpファイルが見つからない場合エラーをスローする
      throw new HttpNotFoundException($controller_class . ' controller is not found.');
    }

    //アクションを実行する
    $content = $controller->run($action, $params);

    //クライアントに返す内容をプロパティに設定する
    $this->response->setContent($content);
  }

  /**
   * 指定されたコントローラ名から対応するControllerオブジェクトを取得
   * @param string $controller_class
   * @return Controller
   */
  protected function findController($controller_class)
  {
    if (!class_exists($controller_class)) {
      $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
      if (!is_readable($controller_file)) {
        return false;
      } else {
        //コントローラーファイルの読み込み
        require_once $controller_file;
        if (!class_exists($controller_class)) {
          return false;
        }
      }
    }
    return new $controller_class($this);
  }

  /**
   * 404エラー画面を返す設定
   *
   * @param Exception $e
   */
  protected function render404Page($e)
  {
    $this->response->setStatusCode(404, 'Not Found');
    $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    $this->response->setContent(
      <<<EOF
        <!DOCTYPE html>
        <html lang="ja">
        <head>
          <meta charset="UTF-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>404</title>
        </head>
        <body>
          {$message}
        </body>
        </html>
EOF
    );
  }
}
