<?php 

session_start();

require_once('function.php');

$dbh = dbConnect();

$error_message = array();
$token = filter_input(INPUT_POST, 'csrf_token');
$user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
$password_conf = filter_input(INPUT_POST, 'password_conf', FILTER_SANITIZE_SPECIAL_CHARS);

$file = $_FILES['upload_img'];
$file_name = basename($file['name']);
$file_tmp_name = $file['tmp_name'];
$file_error = $file['error'];
$file_size = $file['size'];
$upload_dir = 'user-img/';
$save_file_name = date('YmdHis') . $file_name;
$save_path = $upload_dir . $save_file_name;
$extension = ['image/jpg', 'image/jpeg', 'image/png'];

// トークンがない、もしくは一致しない場合、処理を中止
if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
    exit('不正なリクエストです');
}
unset($_SESSION['csrf_token']);

// 入力チェック
if (empty($user_name)) {
    $error_message[]= 'ユーザー名を入力してください';
} else {
    $_SESSION['user_name'] = $user_name;
}

if (empty($email)) {
    $error_message[]= 'メールアドレスを入力してください';
} else {
    if (!$email) {
        $error_message[]= '正しいメールアドレスを入力してください';
    }

    $_SESSION['email'] = $email;
}

if (empty($password)) {
    $error_message[]= 'パスワードを入力してください';
} else {
    $_SESSION['password'] = $password;

    // パスワードを正規表現でバリデーション
    if (!(preg_match('/\A[a-zA-Z0-9]{8,}+\z/', $password))) {
        $error_message[] = 'パスワードは8文字以上の半角英数字で入力してください';
    }

}

if (empty($password_conf)) {
    $error_message[]= '確認用パスワードを入力してください';
} else {
    $_SESSION['password_conf'] = $password_conf;

    if ($password !== $password_conf) {
        $error_message[] = '確認用パスワードが異なっています';
    }

}

// 添付画像のバリデーション
if (empty($file_tmp_name) || empty($file_size)) {
    $error_message[] = 'ユーザー画像を添付してください';

} else {
    if(!in_array(mime_content_type($file_tmp_name), $extension, true)) {
        $error_message[] = 'この画像は添付出来ません';
    }
    if ($file_size > 1048576 || $file_error ==2) {
        $error_message[] = 'ファイルサイズは1MB未満にしてください';
    }
}

// データベース内のメールアドレスを取得
$dbh->beginTransaction();
try {
    $sql = 'SELECT * FROM users WHERE email=?';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$email]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbh->rollBack();
}

// データベース内のメールアドレスと重複していなければ登録する
if (isset($data['email'])) {
    $error_message[] = 'すでに登録済みのメールアドレスです';
}

if (empty($error_message)) {
    if (is_uploaded_file($file_tmp_name)) {
        if (move_uploaded_file($file_tmp_name, $save_path)) {
            // echo $file_name . "を" . $upload_dir . 'にアップしました';

            try {
                $sql = 'INSERT INTO users (name, email, password, file_name, file_path) VALUES (?, ?, ?, ?, ?)';
                $stmt = $dbh->prepare($sql);
                $stmt->execute([$user_name, $email, password_hash($password, PASSWORD_DEFAULT), $file_name, $save_path]);
                $dbh->commit();
            
            } catch (PDOException $e) {
                $dbh->rollBack();
                $e->getMessage();
                $error_message[] = 'ユーザー登録に失敗しました'. $e;
            }

        }   else {
            $error_message[] = 'ファイルのアップロードに失敗しました';
        }

    } else {
        $error_message[] = 'ファイルが選択されていません';
    }
}

// エラーメッセージがあれば、登録フォームに戻す
$_SESSION['error'] = $error_message;
if (!empty($_SESSION['error'])) {
    header('Location: signup_form.php');
    return;
}


$stmt = null;
$dbh = null;

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録完了画面</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <div class="section">
            <div class="success-message">
                <p>ユーザー登録が完了しました</p>
            </div>

        <div>
            <a href="login_form.php">ログイン画面</a>
            <a href="index.php">TOP</a>
        </div>
    </div>
</body>
</html>