<?php

require_once('function.php');

// ひとページに表示するブログの数
define('MAX_VIEW', 5);

$sql = null;
$stmt = null;
$blog_data = null;

$dbh = dbConnect();
$dbh->beginTransaction();

// 必要なページ数を求める
try {
    $sql = 'SELECT COUNT(*) AS count FROM blogs_table';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    $pages = ceil($count['count'] / MAX_VIEW);

} catch (PDOException $e) {
    $dbh->rollBack();
}

// 現在のページID(ページ番号)を取得
if (empty($_GET['page_id'])) {
    $now = 1;
} else {
    $now = $_GET['page_id'];
}

// ブログのデータを取得
try {
    $sql = 'SELECT * FROM blogs_table ORDER BY id DESC LIMIT :start, :max';
    $stmt = $dbh->prepare($sql);

    if ($now == 1) {
        // 1ページめの処理
        $stmt->bindValue(":start", $now-1, PDO::PARAM_INT);
        $stmt->bindValue(":max", MAX_VIEW, PDO::PARAM_INT);
    } else {
        // 2ページめ以降の処理
        $stmt->bindValue(":start", ($now-1) * MAX_VIEW, PDO::PARAM_INT);
        $stmt->bindValue(":max", MAX_VIEW, PDO::PARAM_INT);
    }

    $stmt->execute();
    $blog_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $dbh->rollBack();
}

// 登録ユーザーのデータを取得
try {
    $sql = 'SELECT * FROM users';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbh->rollBack();
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
    <meta name="description" content="ブログサイト">
    <title>ブログサイト</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./sanitize.css">
    <link rel="stylesheet" href="./common.css">
</head>

<body>
    <header id="header">
        <h1 class="site-ttl"><a href="#">ゆるゆるろぐ</a></h1>
        <div class="inner">
            <nav>
                <ul>
                    <li><a href="index.php">TOP</a></li>
                    <li><a href="index.php?page_id=<?php echo $pages; ?>#footer">最初から読む</a></li>
                    <li><a href="#">サイトについて</a></li>
                    <li><a href="#">お問い合わせ</a></li>
                </ul>
            </nav>
            <a class="btn" href="login_form.php">ログイン</a>
            <a class="btn" href="signup_form.php">新規登録</a>
        </div>
    </header>
    <main>
        <div class="primary">
            <section id="blog">
                <div class="wrap">
                    <?php foreach($blog_data as $val):?>
                        <div class="unit">
                            <div class="inner">
                                <div class="first-row">
                                    <div class="posted-user">
                                        <img src="<?php echo userImg($users, $val['user_id']) ; ?>" alt="投稿者の登録画像">
                                        <p class="name"><?php echo ($val['name']); ?></p>
                                    </div>
                                    <div class="date">
                                        <p><?php echo date_format((date_create(hsc($val['post_at']))), "Y/m/d"); ?></p>
                                        <?php if ($val['post_at'] !== $val['update_time']): ?>
                                        <p>（更新日：<?php echo date_format((date_create(hsc($val['update_time']))), "Y/m/d"); ?>）</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="posted-content">
                                    <div>
                                        <div class="second-row">
                                            <h2 class="title"><?php echo hsc($val['title']); ?></h2>
                                            <p class="category"><?php echo setCategoryName(hsc($val['category'])); ?></p>
                                        </div>
                                        <p class="text"><?php echo nl2br(hsc($val['content'])); ?></p>
                                    </div>
                                    <div class="posted-img">
                                        <img src="<?php echo $val['file_path']; ?>" alt="投稿画像">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="side">
                <div class="article">
                    <p class="info">
                        <i class="fa-solid fa-fish"></i>
                        最新記事
                        <i class="fa-solid fa-fish"></i>
                    </p>
                    <?php foreach(array_slice($blog_data, 0,3) as $val): ?>
                    <p>
                        <i class="fa-solid fa-paw"></i>
                        <?php echo " ".$val['title']; ?></p>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <!-- ページネーション -->
        <div class="secondary">
            <?php for ($i = 1; $i <= $pages; $i++) :?>
                <?php if ($i == $now) :?>
                    <p><?php echo $now; ?></p>
                <?php else :?>
                    <a href="index.php?page_id=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </main>

    <footer id="footer">
        <small class="footer-copy">Copyright 2023 ゆるゆるろぐ All Rights Reserved.</small>
    </footer>

</body>
</html>