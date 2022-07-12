<?php
class AccountController extends Controller
{

  protected $auth_actions = array('index', 'signout', 'follow');

  /**
   * アカウント登録する
   */
  public function signupAction()
  {
    //アカウント登録画面を表示する
    return $this->render(array(
      'user_name' => '',
      'password' => '',
      '_token' => $this->generateCsrfToken('account/signup'),
    ));
  }

  /**
   *ユーザーの登録処理
   */
  public function registerAction()
  {

    //HTTPメソッドのチェック
    if (!$this->request->isPost()) {
      $this->forward404();
    }

    //CSRFトークンのチェック
    $token = $this->request->getPost('_token'); {
      if (!$this->checkCsrfToken('account/signup', $token)) {
        return $this->redirect('/account/signup');
      }
    }

    //フォームから送信された値を取得
    $user_name = $this->request->getPost('user_name');
    $password = $this->request->getPost('password');

    //バリデーション
    $errors = array();
    if (!strlen($user_name)) {
      $errors[] = 'ユーザーIDを入力して下さい';
    } else if (!preg_match('/^\w{3,20}$/', $user_name)) {
      $errors[] = 'ユーザーIDは半角英数字およびアンダースコアを3～20文字で入力して下さい';
    } else if (!$this->db_manager->get('User')->isUniqueUserName($user_name)) {
      $errors[] = 'ユーザーIDは既に使用されています。';
    }

    if (!strlen($password)) {
      $errors[] = 'パスワードを入力して下さい';
    } else if (4 > strlen($password) || strlen($password) > 30) {
      $errors[] = 'パスワードは4～30文字以内で入力して下さい';
    }

    if (count($errors) === 0) {

      //=====正常な処理============

      // レコードの登録
      $this->db_manager->get('User')->insert($user_name, $password);

      //ログイン状態の制御
      $this->session->setAuthenticated(true);

      // レコードの取得
      $user = $this->db_manager->get('User')->fetchByUserName($user_name);

      //セッションにユーザー情報を格納
      $this->session->set('user', $user);

      //リダイレクト
      return $this->redirect('/');
    }

    //エラーの場合は再度signup.phpをレンダリングしてエラーを表示する
    return $this->render(array(
      'user_name' => $user_name,
      'password' => $password,
      'errors' => $errors,
      '_token' => $this->generateCsrfToken('account/signup'),
    ), 'signup');
  }

  /**
   * セッションからユーザー情報を取得してビューファイルに渡す
   */
  public function indexAction()
  {
    $user = $this->session->get('user');
    $followings = $this->db_manager->get('User')->fetchAllFollowingsByUserId($user['id']);
    return $this->render(array(
      'user' => $user,
      'followings' => $followings,
    ));
  }

  /**
   * ログインする
   */
  public function signinAction()
  {
    //ログイン状態のチェック
    if ($this->session->isAuthenticated()) {
      return $this->redirect('/account');
    }
    return $this->render(array(
      'user_name' => '',
      'password' => '',
      '_token' => $this->generateCsrfToken('account/signin'),
    ));
  }

  /**
   * ログイン状態か確認する
   */
  public function authenticateAction()
  {

    //アクセスチェック
    //ログイン状態か？POST送信か?CSRFトークンは正しいか?
    if ($this->session->isAuthenticated()) {
      return $this->redirect('/account');
    }
    if (!$this->request->isPost()) {
      $this->forward404();
    }
    $token = $this->request->getPost('_token');
    if (!$this->checkCsrfToken('account/signin', $token)) {
      return $this->redirect('/account/signin');
    }

    $user_name = $this->request->getPost('user_name');
    $password = $this->request->getPost('password');

    /**バリデーション */
    $errors = array();
    if (!strlen($user_name)) {
      $errors[] = 'ユーザーIDを入力してください';
    }
    if (!strlen($password)) {
      $errors[] = 'パスワードを入力してください';
    }

    if (count($errors) === 0) {

      $user_repository = $this->db_manager->get('User');
      $user = $user_repository->fetchByUserName($user_name);

      if (!$user || ($user['password'] !== $user_repository->hashPassword($password))) {
        $errors[] = 'ユーザーIDかパスワードが不正です。';
      } else {
        $this->session->setAuthenticated(true);
        $this->session->set('user', $user);
        return $this->redirect('/');
      }
    }
    return $this->render(array(
      'user_name' => $user_name,
      'password' => $password,
      'errors' => $errors,
      '_token' => $this->generateCsrfToken('account/signin'),
    ), 'signin');
  }

  /**
   * ログアウトする
   */
  public function signoutAction()
  {
    //セッションをクリア
    $this->session->clear();
    //
    $this->session->setAuthenticated(false);
    return $this->redirect('/account/signin');
  }

  /**
   * ユーザーをフォローする
   */
  public function followAction()
  {

    // アクセスチェック
    if (!$this->request->isPost()) {
      $this->forward404();
    }
    //フォローするユーザーが投稿されたか確認
    $following_name = $this->request->getPost('following_name');
    if (!$following_name) {
      $this->forward404();
    }

    //投稿されたトークンを取得
    $token = $this->request->getPost('_token');

    //CSRFトークンのチェック
    if (!$this->checkCsrfToken('account/follow', $token)) {
      return $this->redirect('/user/' . $following_name);
    }
    //ユーザーの存在チェック
    $follow_user = $this->db_manager->get('User')->fetchByUserName($following_name);
    if (!$follow_user) {
      $this->forward404();
    }

    //セッションからユーザー情報を取得
    $user = $this->session->get('user');

    //フォローデータを取得
    $following_repository = $this->db_manager->get('Following');

    // 既にフォロー済みでないかチェック
    // 通常フォロー済みの場合はフォローボタンは表示されないが、
    // Web アプリケーションでは HTML を書きかえてデータを
    // 送信することも可能であるため、必ず事前にバリデーションを行う
    if (
      $user['id'] !== $follow_user['id']
      && !$following_repository->isFollowing($user['id'], $follow_user['id'])
    ) {
      //DBに登録してユーザーをフォロー
      $following_repository->insert($user['id'], $follow_user['id']);
    }
    return $this->redirect('/account');
  }
}
