<?php
class StatusController extends Controller
{

  //認証が必要なアクションを変数に格納する
  protected $auth_actions = array('index', 'post');

  /**
   * ログインしているユーザーのホームを表示する
   */
  public function indexAction()
  {
    //セッションからユーザー情報を取得
    $user = $this->session->get('user');

    //ログインユーザーに関連する投稿を取得
    $statuses = $this->db_manager->get('Status')->fetchAllPersonalArchivesByUserId($user['id']);

    return $this->render(array(
      'statuses' => $statuses,
      'body' => '',
      //トークンを作成する
      '_token' => $this->generateCsrfToken('status/post'),
    ));
  }

  /**
   * 投稿処理
   */
  public function postAction()
  {

    //アクセスチェック
    if (!$this->request->isPost()) {
      //404エラー画面
      $this->forward404();
    }

    //トークンを取得
    $token = $this->request->getPost('_token');
    if (!$this->checkCsrfToken('status/post', $token)) {
      //ホームにリダイレクト
      return $this->redirect('/');
    }

    //投稿情報の取得
    $body = $this->request->getPost('body');

    $errors = array();

    //バリデーション
    if (!mb_strlen($body)) {
      $errors[] = 'ひとことを入力してください。';
    } else if (mb_strlen($body) > 200) {
      $errors[] = 'ひとことは200文字以内で入力してください';
    }

    if (count($errors) === 0) {
      //====正常処理==================
      //セッションからユーザー情報を取得
      $user = $this->session->get('user');

      //ユーザーIDと投稿データをDBに保存
      $this->db_manager->get('Status')->insert($user['id'], $body);

      //リダイレクト
      return $this->redirect('/');
    }

    //セッションからユーザー情報を取得
    $user = $this->session->get('user');

    //ログインユーザーに関連する投稿を取得
    $statuses = $this->db_manager->get('Status')->fetchAllPersonalArchivesByUserId($user['id']);

    //エラーの場合は再度post.phpをレンダリングしてエラーを表示する
    return $this->render(array(
      'errors' => $errors,
      'body' => $body,
      'statuses' => $statuses,
      //トークンを作成する
      '_token' => $this->generateCsrfToken('status/post'),
    ), 'index');
  }

  /**
   * ユーザーIDを指定して、特定ユーザーの一覧を取得する
   */
  public function userAction($params)
  {
    //ユーザーの存在チェック
    $user = $this->db_manager->get('User')->fetchByUserName($params['user_name']);
    if (!$user) {
      $this->forward404();
    }

    //ユーザーの投稿一覧の取得
    $statuses = $this->db_manager->get('Status')->fetchAllByUserId($user['id']);

    $following = null;
    if ($this->session->isAuthenticated()) {
      $my = $this->session->get('user');
      if ($my['id'] !== $user['id']) {
        $following = $this->db_manager->get('Following')->isFollowing($my['id'], $user['id']);
      }
    }

    return $this->render(array(
      'user' => $user,
      'statuses' => $statuses,
      'following' => $following,
      //トークンを作成する
      '_token' => $this->generateCsrfToken('account/follow'),
    ));
  }

  /**
   *投稿IDを指定して、特定の投稿を表示する
   */
  public function showAction($params)
  {
    // ユーザーの投稿を1件取得
    $status = $this->db_manager->get('Status')->fetchByIdAndUserName($params['id'], $params['user_name']);
    if (!$status) {
      $this->forward404();
    }
    return $this->render(array('status' => $status));
  }
}
