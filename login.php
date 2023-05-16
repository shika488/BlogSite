<?php

session_start();

require_once('function.php');

$dbh = dbConnect();

$login = filter_input(INPUT_POST, 'btn_login');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
$user_data = array();
$error_message = array();
$login_error = array();

if (empty($login)) {
    exit('不正なリクエストです');
}

// データベース内のメールアドレスを取得
$dbh->beginTransaction();
try {
    $sql = 'SELECT * FROM users WHERE email = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$email]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $dbh->rollBack();
}

if (empty($email)) {
    $error_message[] = 'メールアドレスを入力してください';
} else {
    if (empty($user_data['email'])) {
        $login_error[] = 'メールアドレスが登録されていません';
    } 
}

if (empty($password)) {
    $error_message[] = 'パスワードを入力してください';
}

if(isset($email) && isset($user_data['email']) && !empty($password)) {
       // パスワードの照会
    if (password_verify($password, $user_data['password'])) {
        session_regenerate_id(true);
        $_SESSION['login_pass'] = true;
        $_SESSION['login_id'] = $user_data['id'];
        header("Location: admin.php");
        exit;
    } else {
        $login_error[] = 'パスワードが一致しません';
    }
}

//エラーがあった場合はログイン画面へ戻す
$_SESSION['error'] = $error_message;
$_SESSION['login_error'] = $login_error;
$_SESSION['email'] = $email;
if (isset($_SESSION['error']) || isset($_SESSION['login_error'])) {
    header("Location: login_form.php");
    return;
}

$stmt = null;
$dbh = null;

