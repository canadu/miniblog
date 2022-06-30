<?php

/**
 * モデルやビューの制御を行うコントローラー。
 * アクションと呼ばれるメソッドを定義している
 */
abstract class Controller
{
  protected $controller_name;
  protected $action_name;
  protected $application;
  protected $request;
  protected $response;
  protected $session;
  protected $db_manager;
  protected $auth_actions = array();

  public function __construct($application)
  {
    $this->controller_name = strtolower(substr(get_class($this), 0, -10));
    $this->application = $application;
    $this->request = $application->getRequest();
    $this->response  = $application->getResponse();
    $this->session = $application->getSession();
    $this->db_manager = $application->getDbManager();
  }

  public function run($action, $params = array())
  {
    $this->action_name = $action;
    $action_method = $action . 'Action';
    if (!method_exists($this, $action_method)) {
      $this->forward404();
    }

    //ログインされていない場合エラーを発生させる
    if($this->needsAuthentication($action) && !$this->session->isAuthenticated()) {
      throw new UnauthorizedActionException();
    }

    $content = $this->$action_method($params);
    return $content;
  }
  protected function needsAuthentication($action)
  {
    if($this->auth_actions === true || (is_array($this->auth_actions) && in_array($action, $this->auth_actions))
    ) {
      return true;
    }
    return false;
  }
  protected function render($variables = array(), $template = null, $layout = 'layout')
  {
    $defaults = array(
      'request' => $this->request,
      'base_url' => $this->request->getBaseUrl(),
      'session' => $this->session,
    );

    $view = new View($this->application->getViewDir(), $defaults);

    if(is_null($template)) {
      $template = $this->action_name;
    }
    $path = $this->controller_name . '/' .$template;
    return $view->render($path, $variables, $layout);
  }

  /**
   * 404エラー画面に遷移するメソッド
   */
  protected function forward404()
  {
    throw new HttpNotFoundException('Forwarded 404 page from '
    . $this->controller_name . '/' . $this->action_name);
  }

  /**
   * リダイレクトを行う
   */
  protected function redirect($url)
  {
    if(!preg_match('#https?://#', $url)) {
      $protocol = $this->request->isSsl() ? 'https://' : 'http://';
      $host = $this->request->getHost();
      $base_url = $this->request-getBaseUrl();
      $url = $protocol . $host . $base_url . $url;
    }
    $this->response->setStatusCode(302, 'Found');
    $this->response->setHttpHeader('Location', $url);
  }

  /**
   * トークンを作成し、セッションに格納する
   * 同一アクションを複数画面開いた場合、最大10個のトークンを保持し、すでに10個保持している場合、古いものから削除する
   */
  protected function generateCsrfToken($form_name)
  {
    $key = 'csrf_tokens/' . $form_name;
    $tokens = $this->session->get($key, array());
    if (is_array($tokens)) {
      if (count($tokens) >= 10) {
        array_shift($tokens);
      }
    }
    $token = sha1($form_name . session_id() . microtime());
    $tokens = array();
    $tokens[] = $token;
    $this->session->set($key, $tokens);
    return $token;
  }

  /**
   * セッション上に格納されたトークンからPostされたトークンを探す
   */
  protected function checkCsrfToken($form_name, $token)
  {
    $key = 'csrf_tokens/' . $form_name;
    $tokens = $this->session->get($key, array());
    if(false !==($pos = array_search($token, $tokens, true))) {
      unset($tokens[$pos]);
      $this->session->set($key, $tokens);
      return true;
    }
  }


}