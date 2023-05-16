<?php

// データベースに接続
function dbConnect (){
    define( 'DB_HOST', 'localhost');
    define( 'DB_NAME', 'blog_site');
    define( 'DB_USER', '***');
    define( 'DB_PASS', '***');
    
    $dbh = null;
    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    
    try {
        $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS, $option);
    } catch (PDOException $e) {
        echo '接続失敗'. $e->getMessage();
        exit();
    }

    return $dbh;
}

// データを取得する
function getBlog() {
    $stmt = null;
    $result = null;

    $dbh = dbConnect();
    $sql = 'SELECT * FROM my_blog ORDER BY id DESC LIMIT 0, 10';
    $stmt = $dbh->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
    $dbh = null;
}

// カテゴリー名を表示
function setCategoryName ($cat) {
    if ($cat === 1) {
        return 'プログラミング';
    } elseif ($cat === 2) {
        return '日々のこと';
    } else {
        return 'その他';
    }
}

// ブログのバリデーション
function validate($post) {

    $error_message = array();
    
    if (empty($post['title'])) {
        $error_message[] = 'タイトルを入力してください';
    }

    if (empty($post['content'])) {
        $error_message[] = '本文を入力してください';
    }

    if (empty($post['category'])) {
        $error_message[] = 'カテゴリーを選択してください';
    }

    return $error_message;
}

// 特殊文字をHTMLエンティティに変換
function hsc ($val) {
    return htmlspecialchars($val, ENT_QUOTES, "UTF-8");
}

// ユーザーの画像パスを取得
function userImg($data, $id) {
    $users_data = array_column($data, 'file_path', 'id');
    $file_path = $users_data[$id];
    return $file_path;
}


?>