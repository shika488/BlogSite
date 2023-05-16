<?php

require_once "function.php";

$dbh = dbConnect();

$sql = 'SELECT * FROM blogs_table';
$files = $dbh->query($sql);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>画像ファイルアップロード</title>
</head>
<body>
    <form enctype="multipart/form-data" action="file_upload.php" method="POST">
        <div class="file-up">
            <input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
            <input name="upload_img" type="file" accept="image/*" />
        </div>
        <div>
            <textarea
            name="caption"
            placeholder="キャプション（140文字以下）"
            id="caption"
            ></textarea>
        </div>
        <div class="submit">
            <input type="submit" class="btn" value="送信" />
        </div>
    </form>
    <div>
        <?php foreach($files as $file): ?>
            <img src="<?php echo "{$file['file_path']}"; ?>" alt="">
            <p><?php echo hsc("{$file['caption']}"); ?></p>
        <?php endforeach; ?>
    </div>
</body>
</html>