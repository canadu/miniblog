<?php

/**
 * ユーザがアクセスしてきたURLをRequestクラスから受け取り、どのコントローラを呼び出すかを決定する
 * これにより物理的なディレクトリ構造に縛られないURLの制御を可能にします。
 * 例えば "/user/info"というPathInfoをUserコントローラーのeditアクションに紐づけるというルールを定義する
 *
 * Router.
 */
class Router
{
    protected $routes;

    /**
     * コンストラクタ
     *
     * @param array $definitions　ルーティング定義配列
     */
    public function __construct($definitions)
    {
        $this->routes = $this->compileRoutes($definitions);
    }

    /**
     * ルーティング定義配列を内部用に変換する
     *
     * @param array $definitions
     * @return array
     */
    public function compileRoutes($definitions)
    {
        $routes = array();

        foreach ($definitions as $url => $params) {
            // スラッシュで分割
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token) {
                if (0 === strpos($token, ':')) {
                    // : で始まる文字列があったとき、正規表現の形式に変換する
                    $name = substr($token, 1);
                    // 正規表現のキャプチャという機能を利用する
                    // (:p<名前>パターン)とすると指定した名前でパターンを取得できる
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }
            // implode — 配列要素を文字列により連結する
            $pattern = '/' . implode('/', $tokens);
            $routes[$pattern] = $params;
        }

        return $routes;
    }

    /**
     * 指定されたPATH_INFOを元にルーティングパラメータを特定する
     *
     * @param string $path_info
     * @return array|false
     */
    public function resolve($path_info)
    {
        if ('/' !== substr($path_info, 0, 1)) {
            //引数の先頭がスラッシュでない場合、先頭にスラッシュを付与する
            $path_info = '/' . $path_info;
        }

        foreach ($this->routes as $pattern => $params) {
            //正規表現を用いてマッチング
            if (preg_match('#^' . $pattern . '$#', $path_info, $matches)) {

                // array_merge - 配列を連結する
                // array('controller' => 'user', 'action' => 'show') に
                // array('id' => '1') が加わる
                $params = array_merge($params, $matches);
                //$paramsにマージ
                return $params;
            }
        }

        return false;
    }
}
