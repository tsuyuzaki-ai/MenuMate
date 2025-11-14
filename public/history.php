<?php

ini_set('session.gc_maxlifetime', 21600);
session_start(); // セッションを開始

require_once '../functions/database.php'; // データベース接続ファイル

// URLの`table_id`を取得してセッションに格納
if (isset($_GET['table_id'])) {
    $_SESSION['table_id'] = $_GET['table_id'];
}

$table_id = $_SESSION['table_id'] ?? '';

// 注文履歴を取得
$orders = [];
$total_price = 0;

if (!empty($table_id)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE table_id = ? ORDER BY created_at DESC");
    $stmt->execute([$table_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<?php require_once '../parts/header.php'; ?>

<title>注文履歴 | MenuMate</title>
</head>

<body ontouchstart="">
    <main id="user-front" class="itemListPage historyPage">
        <div class="content">
            <h1>
                <span class="ja">注文履歴</span>
                <span class="en">HISTORY</span>
            </h1>

            <ul class="items">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <li>
                            <p class="name">
                                <?= htmlspecialchars($order['product_name'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="option">
    <?php 
    $options = !empty($order['options']) ? json_decode($order['options'], true) : [];
    $options = is_array($options) ? $options : []; // NULL の可能性を排除

    if (!empty($options)) {
        echo '<span class="option01">' . implode(' / ', array_map('htmlspecialchars', $options)) . '</span>';
    } else {
        echo '<span class="option01">オプションなし</span>'; // オプションがない場合「なし」と表示
    }
    ?>
</p>

                            <p class="price">
                                <?= number_format($order['product_syokei'] * $order['quantity']) ?>円
                            </p>
                        </li>
                        <?php $total_price += $order['product_syokei'] * $order['quantity']; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>注文履歴がありません。</p>
                <?php endif; ?>
            </ul>

            <div class="cartPart">
                <div class="inner">
                    <p class="price">合計：<?= number_format($total_price) ?>円</p>
                    <div class="btnWrap">
                        <a class="btn history" href="shop.php">戻る</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once '../parts/footer.php'; ?>
