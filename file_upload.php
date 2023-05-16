<?php

require_once "function.php";

$dbh = dbConnect();

$file = $_FILES['upload_img'];
$caption = filter_input(INPUT_POST, 'caption', FILTER_SANITIZE_SPECIAL_CHARS);
$file_name = basename($file['name']);
$file_tmp_name = $file['tmp_name'];
$file_error = $file['error'];
$file_size = $file['size'];
$upload_dir = 'img/';
$save_file_name = date('YmdHis') . $file_name;
$save_path = $upload_dir . $save_file_name;
$error_message = array();
$extension = ['image/jpg', 'image/jpeg', 'image/png'];

// キャプションのバリデーション
if (empty($caption)) {
    $error_message[] = 'キャプションを入力してください';
} else {
    if (strlen($caption) > 140) {
        $error_message[] = 'キャプションは140文字以下で入力してください';
    }
}

// ファイルのバリデーション
if ($file_size > 1048576 || $file_error ==2) {
    $error_message[] = 'ファイルサイズは1MB未満にしてください';
}

if (!in_array(mime_content_type($file_tmp_name), $extension, true)) {
    $error_message[] = '画像ファイルを添付してください';
}

if (empty($error_message)) {
    if (is_uploaded_file($file_tmp_name)) {
        if (move_uploaded_file($file_tmp_name, $save_path)) {
            echo $file_name . "を" . $upload_dir . 'にアップしました';

            $dbh->beginTransaction();

            try {
                $sql = 'INSERT INTO blogs_table(file_name, file_path, caption) VALUES (?, ?, ?)';
                $stmt = $dbh->prepare($sql);
                $stmt->execute([$file_name, $save_path, $caption]);
                $dbh->commit();
                echo 'データベースに保存しました';
            
            } catch (PDOException $e) {
                $dbh->rollBack();
                echo 'データベースへの保存に失敗しました'.$e->getMessage();
            }

        } else {
            echo 'ファイルのアップロードに失敗しました';
        }
    } else {
        echo 'ファイルが選択されていません';
    }
} else {
    foreach($error_message as $message) {
        echo $message;
    }
}

