<?php

session_start();

$success_message = null;
$logout = filter_input(INPUT_POST, 'btn_logout');

if (!($_SESSION['login_pass'] == true && isset($_SESSION['login_id']))) {
    $_SESSION['login_error'][]= 'ログインしてください';
    header("Location: login_form.php");
    exit;
}

if (empty($logout)) {
    exit('不正なリクエストです');
} else {
    $success_message = 'ログアウトしました';
}

// ログアウトする
$_SESSION = array();
session_destroy();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログアウト画面</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <div class="section logout">
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>

        <div>
            <a href="login_form.php">ログイン画面</a>
            <a href="./index.php">TOP</a>
        </div>
    </div>
</body>
</html>