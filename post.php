<?php 

session_start();

require_once('fanction.php');

$dbh = dbConnect();

$post = $_POST;
$error_message = array();
$file_error_message = array();
$success_message = array();
$input_post = filter_input(INPUT_POST, 'btn_post');

$file = $_FILES['upload_img'];
$file_name = basename($file['name']);
$file_tmp_name = $file['tmp_name'];
$file_error = $file['error'];
$file_size = $file['size'];
$upload_dir = 'img/';
$save_file_name = date('YmdHis') . $file_name;
$save_path = $upload_dir . $save_file_name;
$extension = ['image/jpg', 'image/jpeg', 'image/png'];


if (!($_SESSION['login_pass'] == true && isset($_SESSION['login_id']))) {
    $_SESSION['login_error'][] = 'ログインしてください';
    header('Location: login_form.php');
    exit;
}

if (empty($input_post)) {
    exit('不正なリクエストです');
}

if (isset($_SESSION['login_user'])) {
    $login_user = $_SESSION['login_user'];
    unset($_SESSION['login_user']);
} else {
    $login_user = null;
}

// 投稿内容のバリデーション
$error_message = validate($post);

// 添付画像のバリデーション
if (empty($file_tmp_name) || empty($file_size)) {
    $error_message[] = '画像ファイルを添付してください';

} else {
    if(!in_array(mime_content_type($file_tmp_name), $extension, true)) {
        $error_message[] = 'この画像は添付出来ません';
    }
    if ($file_size > 1048576 || $file_error ==2) {
        $error_message[] = 'ファイルサイズは1MB未満にしてください';
    }
}


// ブログを投稿する
if (empty($error_message)) {

    if (is_uploaded_file($file_tmp_name)) {
        if (move_uploaded_file($file_tmp_name, $save_path)) {
    
            $dbh->beginTransaction();

            try {
                $sql = 'INSERT INTO blogs_table(user_id, name, title, content, category, file_name, file_path) VALUES (:user_id, :name, :title, :content, :category, :file_name, :file_path)';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':user_id', $_SESSION['login_id'], PDO::PARAM_INT);
                $stmt->bindValue(':name', $login_user, PDO::PARAM_STR);
                $stmt->bindValue(':title', $post['title'], PDO::PARAM_STR);
                $stmt->bindValue(':content', $post['content'], PDO::PARAM_STR);
                $stmt->bindValue(':category', $post['category'], PDO::PARAM_INT);
                $stmt->bindValue(':file_name', $file_name, PDO::PARAM_STR);
                $stmt->bindValue(':file_path', $save_path, PDO::PARAM_STR);
                $stmt->execute();
                $dbh->commit();
                $success_message[] = 'ブログを投稿しました';
                $_SESSION['success'] = $success_message;
                header("Location: admin.php");

            } catch (PDOException $e) {
                $dbh->rollBack();
                // $e->getMessage();
                $error_message[] =  'ブログの投稿に失敗しました';
            }
        } else {
            $error_message[] = 'ファイルのアップロードに失敗しました';
        }
    } else {
        $error_message[] = 'ファイルが選択されていません';
    }
}

// エラーメッセージがあれば、戻す
$_SESSION['error'] = $error_message;
if (!empty($_SESSION['error'])) {
    header('Location: admin.php');
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
    <title>ブログ投稿画面</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <div class="section post">
        <?php if (isset($message)): ?>
        <div class="error-message">
            <?php foreach ($message as $value): ?>
            <p><?php echo $value; ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div>
            <a href="login_form.php">ログイン画面</a>
            <a href="index.php">TOP</a>
        </div>
    </div>
</body>
</html>


