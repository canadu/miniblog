<?php
abstract class Application
{
  protected $debug = false;
  protected $request;
  protected $response;
  protected $session;
  protected $db_manager;
  protected $login_action = array();

  public function __construct($debug = false)
  {
    $this->setDebugMode($debug);
    $this->initialize();
    $this->configure();
  }

  /**
   * デバッグモードに応じてエラー表示処理を変更する
   */
  protected function setDebugMode($debug)
  {
    if($debug) {
      $this->debug = true;
      ini_set('display_errors',1);
      error_reporting(-1);
    } else {
      $this->debug = false;
      ini_set('display_errors',0);
    }
  }

  /**
   * 各クラスの初期化処理を行う
   */
  protected function initialize()
  {
    $this->request = new Request();
    $this->response = new Response();
    $this->session = new Session();
    $this->db_manager = new DbManager();
    $this->router = new Router($this->registerRoutes());
  }

  /**
   * 空のメソッドとして定義 個別のアプリケーションで設定を出来るようにしている
   */
  protected function configure()
  {
  }

  abstract public function getRootDir();
  abstract protected function registerRoutes();

  public function isDebugMode()
  {
    return $this->debug;
  }

  public function getRequest()
  {
    return $this->request;
  }

  public function getResponse()
  {
    return $this->response;
  }

  public function getSession()
  {
    return $this->session;
  }

  public function getDbManager()
  {
    return $this->db_manager;
  }

  public function getControllerDir()
  {
    return $this->getRootDir() . '/controllers';
  }

  public function getViewDir()
  {
    return $this->getRootDir() . '/views';
  }

  public function getModelDir()
  {
    return $this->getRootDir() . '/models';
  }

  public function getWebDir()
  {
    return $this->getRootDir() . '/web';
  }

  /**
   * ルーティングパラメーターを取得し、コントローラー名と、アクション名を特定する
   */
  public function run()
  {
    try {
      $params = $this->router->resolve($this->request->getPathInfo());
      if ($params === false) {
        throw new HttpNotFoundException('No route found for' . $this->request->getPathInfo());
      }
      $controller = $params['controller'];
      $action = $params['action'];
      $this->runAction($controller, $action, $params);
    } catch (HttpNotFoundException $e) {
      $this->render404Page($e);
    } catch (UnauthorizedActionException $e) {
      list($controller,$action) = $this->login_action;
      $this->runAction($controller, $action);
    }
    $this->response->send();
  }

  /**
   * アクションを実行する
   */
  public function runAction($controller_name, $action, $params =  array())
  {
    //ucfirstを用いて先頭を大文字にする
    $controller_class =  ucfirst($controller_name) . 'Controller';
    $controller = $this->findController($controller_class);
    if ($controller === false) {
      throw new HttpFoundException($controller_class . 'controller is not found.');
    }
    $content = $controller->run($action, $params);
    $this->response->setContent($content);
  }

  /**
   * コントローラークラスが読み込まれていない場合に、クラスファイルの読み込みを行う
   */
  protected function findController($controller_class)
  {
    if (!class_exists($controller_class)) {
      $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
    }
    if (!is_readable($controller_file)) {
      return false;
    } else {
      require_once $controller_file;
      if(!class_exists($controller_class)) {
        return false;
      }
    }
    return new $controller_class($this);
  }

  protected function render404Page($e)
  {
    $this->response->setStatusCode(404, 'Not Found');
    $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found';
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $this->response->setContent(<<<EOF
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