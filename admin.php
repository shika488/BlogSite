<?php 

session_start();

require_once('function.php');

$login_id = null;
$blogData = array();

if (!($_SESSION['login_pass'] == true && isset($_SESSION['login_id']))) {
    $_SESSION['login_error'][] = 'ログインしてください';
    header("Location: login_form.php");
    return;
}

$login_id = $_SESSION['login_id'];

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
} else {
    $error_message = array();
}

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
} else {
    $success_message = array();
}

$dbh = dbConnect();

// ブログのデータを取得
$sql = 'SELECT * FROM blogs_table ORDER BY id DESC';
$data = $dbh->query($sql);
$blog_data = $data->fetchAll(PDO::FETCH_ASSOC);

// 登録ユーザーのデータを取得
$dbh->beginTransaction();
try {
    $sql = 'SELECT * FROM users';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbh->rollBack();
}

$user_name_data = array_column($users, 'name', 'id');
$login_user = $user_name_data[$login_id];
$_SESSION['login_user'] = $login_user;

$stmt = null;
$dbh = null;

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ブログサイト マイページ</title>
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>
<body>
    <section id="my-page">
        <h2 class="title">ゆるゆるろぐ マイページ</h2>
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php foreach ($error_message as $value): ?>
                <p><?php echo $value; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
        <div class="success-message">
            <?php foreach ($success_message as $value): ?>
                <p><?php echo $value; ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="inner">
            <div class="blog-post">
                <form action="post.php" method="post" enctype="multipart/form-data">

                    <p>タイトル：</p>
                    <input type="text" name="title">

                    <p>本文：</p>
                    <textarea name="content" cols="40" rows="8"></textarea>
                    
                    <p>カテゴリー：</p>
                    <select name="category">
                        <option value=""> カテゴリー </option>
                        <option value="1">プログラミング</option>
                        <option value="2">日々のこと</option>
                        <option value="3">その他</option>
                    </select>

                    <label>画像：</label>
                    <div class="file-up">
                        <input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
                        <input name="upload_img" type="file" accept="image/*" />
                    </div>

                    <input type="submit" name="btn_post" class="btn_post" value="投稿する">
                </form>
            </div>

            <div class="user">
                <div class="intro">
                    <div class="img">
                        <img src="<?php echo userImg($users, $login_id); ?>" alt="ユーザーの登録画像">
                    </div>
                    <div class="name">
                        <p>ユーザー名：</p>
                        <p><?php echo $login_user; ?></p>
                    </div>
                </div>
                <div class="logout">
                    <form action="logout.php" method="post">
                        <input type="submit" name="btn_logout" class="btn_logout" value="ログアウト">
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section id="blog">
    <?php foreach($blog_data as $val):?>
            <div class="admin-wrap">
                
                    <div class="unit admin-unit">

                        <div class="admin-inner">
                            <div class="first-row">
                                <div class="posted-user">
                        
                                    <img src="<?php echo userImg($users, $val['user_id']);?>" alt="投稿者の登録画像">
                                    <p class="name"><?php echo ($val['name']); ?></p>
                                </div>
                                <div class="date">
                                    <p><?php echo date_format((date_create(hsc($val['post_at']))), "Y/m/d"); ?></p>
                                    <?php if ($val['post_at'] !== $val['update_time']): ?>
                                    <p class="">（更新日：<?php echo date_format((date_create(hsc($val['update_time']))), "Y/m/d"); ?>）</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="posted-content">
                                <div>
                                    <div class="second-row">
                                        <h2 class="title"><?php echo hsc($val['title']); ?></h2>
                                        <p class="category"><?php echo setCategoryName($val['category']); ?></p>
                                    </div>
                                    <p class="text"><?php echo nl2br(hsc($val['content'])); ?></p>
                                </div>
                                <div class="posted-img">
                                    <img src="<?php echo $val['file_path']; ?>" alt="投稿画像">
                                </div>
                            </div>
                        </div>
                    </div>
                        
                    <div class="admin-btn">
                        <input type="button" onclick="location.href='edit.php?id=<?php echo $val['id']; ?>'" class="btn-e <?php if($login_id == $val['user_id']) { echo "display"; } ?>" value="編集" >
                        <input type="button" onclick="location.href='./delete.php?id=<?php echo $val['id']; ?>'" class="btn-d <?php if($login_id == $val['user_id']) { echo "display"; } ?>" value="削除" >
                    </div>

            </div>
            <?php endforeach; ?>
        </div>
    </section>
</body>
</html>

