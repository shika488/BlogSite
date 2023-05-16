<?php

session_start();

require_once('function.php');

$get = $_GET;
$post = $_POST;
$data = null;
$error_message = array();
$success_message = null;

if (!($_SESSION['login_pass'] == true && isset($_SESSION['login_id']))) {
    $_SESSION['login_error'][]= 'ログインしてください';
    header("Location: login_form.php");
    exit;
}

if (empty($get['id'])) {
    exit('idが不正です');
}

$dbh = dbConnect();

if (!empty($get['id']) && empty($post['id'])) {

    $stmt = $dbh->prepare ('SELECT * FROM blogs_table WHERE id = :id');
    $stmt->bindValue(':id', $get['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($data)) {
        $error_message[] = 'ブログがありません';
    } else {
        if($_SESSION['login_id'] !== $data['user_id']) {
            $error_message[] = 'このブログは編集できません';
        }
    }

}  elseif (!empty($post['id'])) {

    if (empty($error_message)) {

        $dbh->beginTransaction();

        $file = $_FILES['upload_img'];
        $file_name = basename($file['name']);
        $file_tmp_name = $file['tmp_name'];
        $file_error = $file['error'];
        $file_size = $file['size'];
        $upload_dir = 'img/';
        $save_file_name = date('YmdHis') . $file_name;
        $save_path = $upload_dir . $save_file_name;
        $extension = ['image/jpg', 'image/jpeg', 'image/png'];

        if (is_uploaded_file($file_tmp_name)) {
            if (move_uploaded_file($file_tmp_name, $save_path)) {

                try {
                    $sql = 'UPDATE blogs_table SET title = :title, content = :content, category = :category, file_name = :file_name, file_path = :file_path WHERE id = :id';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(':title', $post['title'], PDO::PARAM_STR);
                    $stmt->bindValue(':content', $post['content'], PDO::PARAM_STR);
                    $stmt->bindValue(':category', $post['category'], PDO::PARAM_INT);
                    $stmt->bindValue(':file_name', $file_name, PDO::PARAM_STR);
                    $stmt->bindValue(':file_path', $save_path, PDO::PARAM_STR);
                    $stmt->bindValue(':id', $post['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $dbh->commit();
                    $success_message = 'ブログを更新しました';

                } catch (PDOException $e) {
                    $dbh->rollBack();
                    $e->getMessage();
                    $error_message[] = 'ブログの更新に失敗しました'.$e;
                }
            } else {
                $error_message[] = 'ファイルのアップロードに失敗しました';
            }
        } else {
            try {
                $sql = 'UPDATE blogs_table SET title = :title, content = :content, category = :category WHERE id = :id';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':title', $post['title'], PDO::PARAM_STR);
                $stmt->bindValue(':content', $post['content'], PDO::PARAM_STR);
                $stmt->bindValue(':category', $post['category'], PDO::PARAM_INT);
                $stmt->bindValue(':id', $post['id'], PDO::PARAM_INT);
                $stmt->execute();
                $dbh->commit();
                $success_message = 'ブログを更新しました';
    
            } catch (PDOException $e) {
                $dbh->rollBack();
                $e->getMessage();
                $error_message[] = 'ブログの更新に失敗しました'.$e;
            }
        }
    }
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
    <title>ブログサイト 投稿の編集</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <div class="section">
        <h2 class="title">ゆるゆるろぐ 投稿の編集ページ</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php foreach ($error_message as $value): ?>
                <p><?php echo $value; ?></p>
                <?php endforeach; ?>
            </div>
            <input type="button" onclick="location.href='admin.php'" class="btn_return" value="戻る" >
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <p><?php echo $success_message; ?></p>
            </div>
            <input type="button" onclick="location.href='admin.php'" class="btn_return" value="戻る" >
        <?php endif; ?>

        <?php if(empty($error_message) && !empty($data['id'])) :?>
            <div class="blog-edit">
                <form action="" method="post" enctype="multipart/form-data">
                    <p>タイトル：</p>
                    <input type="text" name="title" value="<?php if(!empty($data['title'])) { echo $data['title'];} elseif(!empty($post['title'])) { echo hsc($post['title']);} ?>">
                    <p>本文：</p>
                    <textarea name="content" cols="40" rows="8"><?php if(!empty($data['content'])) { echo $data['content'];} elseif(!empty($post['content'])) { echo nl2br(hsc($post['content']));} ?></textarea>
                    <div class="inner">
                        <?php if(!empty($data['category'])) :?>
                            <p>カテゴリー：</p>
                            <select name="category">
                                <option value="1" <?php if($data['category']===1) { echo "selected"; } ?>>プログラミング</option>
                                <option value="2" <?php if($data['category']===2) { echo "selected"; } ?>>日々のこと</option>
                                <option value="3" <?php if($data['category']===3) { echo "selected" ;} ?>>その他</option>
                            </select>
                        <?php endif ;?>

                        <div class="edit-img">
                            <div class="now">
                                <p>投稿画像：</p>
                                <img src="<?php echo $data['file_path']; ?>" alt="投稿画像">
                            </div>
                            <div class="change">
                                <label>変更画像：</label>
                                <div class="file-up">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
                                    <input name="upload_img" type="file" accept="image/*" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn">
                            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                            <input type="button" onclick="location.href='admin.php'" class="btn_cancel" value="キャンセル" >
                            <input type="submit" name="btn_update" class="btn_update" value="更新">
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>