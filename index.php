<?php

date_default_timezone_set("Asia/Tokyo");

$comment_array = array();

$pdo = null;
$stmt = null;
$error_messages = array();

// 別の場所に保存された設定ファイルへのパス
$configFilePath = 'config.php';

// 設定ファイルを読み込む
if (file_exists($configFilePath)) {
    require_once($configFilePath);
} else {
    // 設定ファイルが見つからない場合のエラーハンドリング
    die('設定ファイルが見つかりません。');
}

// データベース接続
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
$pdo = new PDO($dsn, $username, $password);

//フォームの入力時
if(!empty($_POST["submitButton"])) {

    //バリデーションチェック
    if (empty($_POST["username"])){
        
        $error_messages["username"] = "名前を入力してください。";
        
    }
    if (empty($_POST["comment"])){
        
        $error_messages["comment"] = "コメントを入力してください。";
    }

}

    //データの送信
    if(empty($error_messages)){

    $postDate = date("Y-m-d H:i:s");

    try {
        $stmt = $pdo->prepare("INSERT INTO `bbs-table` (`username`, `comment`, `postDate`) VALUES (:username, :comment, :postDate);");
        $stmt->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
        $stmt->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
        $stmt->bindParam(':postDate', $postDate, PDO::PARAM_STR);

        $stmt->execute();

    } catch (PDOException $e) {
        echo $e->getMessage();
        }
}

//DBからコメントデータを取得する。
$sql = "SELECT `id`, `username`, `comment`, `postDate` FROM `bbs-table`;";
$comment_array = $pdo->query($sql);

//DBの接続を閉じる。
$pdo = null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 class="title"> PHPで掲示板アプリ</h1>
    <hr>
    <div class="boardWrapper">
        <section>
            <?php foreach($comment_array as $comment): ?>
            <article>
                <div class="wrapper">
                    <div class="nameArea">
                        <span>名前:</span>
                        <p class="username"><?php echo $comment["username"]; ?></p>
                        <time><?php echo $comment["postDate"]; ?></time>
                    </div>
                    <p class="comment"><?php echo $comment["comment"]; ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
        <form class="formWrapper" method="POST">
            <div>
                <input type="submit" value="書き込む" name="submitButton">
                <label for="">名前:</label>
                <input type="text" name="username" id="comment" style="<?php echo !empty($error_messages["username"]) ? 'border-color: red;' : ''; ?>" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <?php if (isset($error_messages['username'])) { ?>
                <div class="error_message"><?php echo $error_messages['username']; ?></div>
                <?php } ?>
            </div>
            <div>
                <textarea class="commentTextArea" name="comment" style="<?php echo isset($error_messages['comment']) ? 'border-color: red;' : ''; ?>"><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                <?php if (isset($error_messages['comment'])) { ?>
        <div class="error_message"><?php echo $error_messages['comment']; ?></div>
    <?php } ?>

            </div>
        </form>
        <script>
    // エラーがある場合にテキストエリアにスクロールしてフォーカスを当てる
    window.onload = function() {
        <?php if (isset($error_messages['comment'])) { ?>
            var commentTextarea = document.getElementById("comment");
            commentTextarea.scrollIntoView();
            commentTextarea.focus();
        <?php } ?>
              };
        </script>
    </div>


</body>
</html>