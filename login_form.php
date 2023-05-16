<?php

session_start();

require_once('function.php');

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
} else {
    $error_message = array();
}

if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
} else {
    $login_error = array();
}

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
} else {
    $email = null;
}

$_SESSION = array();
session_destroy();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログインフォーム</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <div class="section">
        <?php if (isset($login_error)) :?>
        <div class="error-message">
            <?php foreach ($login_error as $value): ?>
                <p><?php echo $value; ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2 class="title">ゆるゆるろぐ ログインフォーム</h2>
        <?php if (isset($error_message)) :?>
        <div class="error-message">
            <?php foreach ($error_message as $value): ?>
                <p><?php echo $value; ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <p>
                <label for="email">メールアドレス：</label>
                <input type="email" name="email" value="<?php if (isset($email)) { echo $email; } ?>">
            </p>
            <p>
                <label for="password">パスワード：</label>
                <input type="password" name="password">
            </p>
            <div class="btn">
                <input type="submit" name="btn_login" class="btn_login" value="ログイン">
                <a href="signup_form.php">新規登録</a>
            </div>
            <div class="top">
                <a href="index.php">TOP</a>
            </div>
        </form>
    </div>
</body>
</html>