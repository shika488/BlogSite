<?php 

session_start();

require_once('function.php');

$data = null;
$error_message = array();

if (!($_SESSION['login_pass'] == true && isset($_SESSION['login_id']))) {
    $_SESSION['login_error'][]= 'ログインしてください';
    header("Location: login_form.php");
    exit;
}

if (empty($_GET['id'])) {
    exit('idが不正です');
}

$dbh = dbConnect();

if (!empty($_GET['id']) && empty($_POST['id'])) {

    $stmt = $dbh->prepare ('SELECT * FROM blogs_table WHERE id = :id');
    $stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if($_SESSION['login_id'] !== $data['user_id']) {
        $error_message[] = 'このブログは削除できません';
    }

    if (empty($data)) {
        $error_message[] = 'ブログがありません';
    }

} elseif (!empty($_POST['id'])) {

    if (empty($error_message)) {

        $dbh->beginTransaction();

        try {
            $stmt = $dbh->prepare('DELETE FROM blogs_table WHERE id = :id');
            $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();
            $dbh->commit();
            $success_message = 'ブログを削除しました';

        } catch (Exception $e) {
            $dbh->rollBack();
            $error_message[] = 'ブログの削除に失敗しました';
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
    <title>ブログサイト 管理ページ(投稿の削除)</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <div class="section">
        <h2 class="title">ゆるゆるろぐ 管理ページ(投稿の削除)</h2>

        <?php if(!empty($error_message)): ?>
            <div class="error-message">
                <?php foreach ($error_message as $value): ?>
                <p><?php echo $value; ?></p>
                <?php endforeach; ?>
            </div>
            <input type="button" onclick="location.href='admin.php'" class="btn_return" value="戻る" >
        <?php endif; ?>

        <?php if(!empty($success_message)) :?>
            <div class="success-message">
                <p><?php echo $success_message; ?></p>
            </div>
            <input type="button" onclick="location.href='admin.php'" class="btn_return" value="戻る" >
        <?php endif ;?>


        <?php if(empty($error_message) && !empty($data['id'])) :?>
            <p class="confirm">
                以下の投稿を削除します<br>
                よろしければ「削除」ボタンを押してください
            </p>
            <form action="" method="post" class="delete">
                <div id="blog">
                    <div class="wrap">
                        <div class="delete-inner">
                                <div class="first-row">
                                    <div class="date">
                                        <p><?php echo date_format((date_create(hsc($data['post_at']))), "Y/m/d"); ?></p>
                                        <?php if ($data['post_at'] !== $data['update_time']): ?>
                                        <p>（更新日：<?php echo date_format((date_create(hsc($data['update_time']))), "Y/m/d"); ?>）</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="posted-content">
                                    <div>
                                        <div class="second-row">
                                            <h2 class="title delete-ttl"><?php echo hsc($data['title']); ?></h2>
                                            <p class="category"><?php echo setCategoryName(hsc($data['category'])); ?></p>
                                        </div>
                                        <p class="text"><?php echo nl2br(hsc($data['content'])); ?></p>
                                    </div>
                                    <div class="posted-img">
                                        <img src="<?php echo $data['file_path']; ?>" alt="投稿画像">
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            
                <div class="btn">
                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                    <input type="button" onclick="location.href='admin.php'" class="btn_cancel" value="キャンセル" >
                    <input type="submit" name="btn_delete" class="btn_delete" value="削除">
                </div>
            </form>
        <?php endif ;?>
    </div>
</body>
</html>