<?php

session_start();

require_once('function.php');

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
} else {
    $error_message = array();
}

if (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name'];
} else {
    $user_name = null;
}

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
} else {
    $email = null;
}

if (isset($_SESSION['password'])) {
    $password = $_SESSION['password'];
} else {
    $password = null;
}

if (isset($_SESSION['password_conf'])) {
    $password_conf = $_SESSION['password_conf'];
} else {
    $password_conf = null;
}

$_SESSION = array();

$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録フォーム</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <div class="section">
        <h2 class="title">ゆるゆるろぐ ユーザー登録フォーム</h2>
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php foreach ($error_message as $value): ?>
                <p><?php echo $value; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="signup">
            <form action="signup.php" method="post" enctype="multipart/form-data">
                <p>
                    <label for="user_name">ユーザー名：</label>
                    <input type="text" name="user_name" value="<?php if (isset($user_name)) { echo $user_name; } ?>">
                    <p class="note">
                        ※20文字以内
                    </p>
                </p>
                <p>
                    <label for="email">メールアドレス：</label>
                    <input type="email" name="email" value="<?php if (isset($email)) { echo $email; } ?>">
                </p>
                <p>
                    <label for="password">パスワード：</label>
                    <input type="password" name="password" value="<?php if (isset($password)) { echo $password; } ?>">
                    <p class="note">
                        ※8文字以上の半角英数字
                    </p>
                </p>
                <p>
                    <label for="password_conf">パスワード確認：</label>
                    <input type="password" name="password_conf" value="<?php if (isset($password_conf)) { echo $password_conf; } ?>">
                </p>

                <div class="file">
                    <label>ユーザー画像：</label>
                    <div class="file-up">
                        <input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
                        <input name="upload_img" type="file" accept="image/*" />
                    </div>
                </div>

                <div class="btn">
                    <input type="submit" name="btn_signup" class="btn_signup" value="新規登録">
                    <input type="hidden" name="csrf_token" value="<?php echo hsc($csrf_token);?>">
                    <a href="login_form.php">ログイン</a>
                </div>
                <div class="top">
                    <a href="index.php">TOP</a>
                </div>
            </form>
        </div>

    </div>
</body>
</html>