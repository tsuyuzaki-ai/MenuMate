<?php
ini_set('session.gc_maxlifetime', 21600);
session_start();

require_once '../parts/header.php';
require_once '../functions/database.php';

// テーブルIDの取得
$table_id = $_SESSION['table_id'] ?? null;
if (!$table_id) {
    die("テーブルIDが取得できません。");
}

// カート情報取得
$cart = $_SESSION['cart'][$table_id] ?? [];

if (empty($cart)) {
    echo "カートが空です。2秒後にショップページに戻ります。";
    echo '<meta http-equiv="refresh" content="2;url=shop.php">';
    exit;
}



// 注文データを保存
$sql = "INSERT INTO orders (table_id, cart_key, product_name, options, product_syokei, quantity, status) 
        VALUES (:table_id, :cart_key, :product_name, :options, :product_syokei, :quantity, 0)";

$stmt = $pdo->prepare($sql);

foreach ($cart as $cart_key => $item) {
    $options_json = !empty($item['options']) ? json_encode($item['options'], JSON_UNESCAPED_UNICODE) : null;

    $stmt->execute([
        ':table_id' => $table_id,
        ':cart_key' => $cart_key,
        ':product_name' => $item['product_name'],
        ':options' => $options_json,
        ':product_syokei' => $item['product_syokei'],
        ':quantity' => $item['quantity']
    ]);
}

// カートをクリア
unset($_SESSION['cart'][$table_id]);
// 注文が完了したらカートをリセット
// unset($_SESSION['cart'][$_SESSION['table_id']]);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>注文完了 | MenuMate</title>
    <link rel="stylesheet" href="/menumate/assets/style.css">
</head>

<body>
    <main id="user-front" class="itemListPage orderCompletePage">
        <div class="content">

            <h1>
                <span class="ja">注文完了</span>
                <span class="en">THANK YOU!</span>
            </h1>
            <p>
            ご注文ありがとうございます。
            <br>料理の提供まで少々お待ちください。
            </p>
            <a class="btn" href="shop.php">メニューへ戻る</a>

        </div>
    </main>












</body>

</html>