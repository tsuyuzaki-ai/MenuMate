<?php

ini_set('session.gc_maxlifetime', 21600);
session_start();
require_once '../parts/header.php';

// テーブルIDの取得
$table_id = $_SESSION['table_id'] ?? null;

// カート情報取得
$cart = $_SESSION['cart'][$table_id] ?? [];

// 合計金額を計算（数量考慮）
$total_price = 0;
$total_items = 0; // 総数を計算
foreach ($cart as $item) {
    $total_price += $item['product_syokei'] * $item['quantity'];
    $total_items += $item['quantity'];
}
?>

<title>注文かご | MenuMate</title>
</head>

<body>
    <main id="user-front" class="itemListPage cartPage">
        <div class="content">
            <h1>
                <span class="ja">注文かご</span>
                <span class="en">BASKET</span>
            </h1>

            <ul class="items">
                <?php if (empty($cart)): ?>
                    <p>カートに商品がありません。</p>
                <?php else: ?>
                    <?php foreach ($cart as $unique_key => $item): ?>
                        <li>
                            <form action="../functions/delete-cart.php" method="POST" class="delete-form">
                                <input type="hidden" name="table_id" value="<?= htmlspecialchars($table_id) ?>">
                                <input type="hidden" name="cart_key" value="<?= htmlspecialchars($unique_key) ?>">
                                <button type="submit" class="delete">
                                    <img width="30" src="/menumate/assets/img/icon_close.png" alt="削除">
                                </button>
                            </form>

                            <p class="name"><?= htmlspecialchars($item['product_name']) ?></p>
                            <p class="option">
                                <?php if (!empty($item['options']) && is_array($item['options'])): ?>
                                    <?php
                                    $option_names = [];
                                    foreach ($item['options'] as $option) {
                                        if (is_array($option) && isset($option['name'])) {
                                            $option_names[] = htmlspecialchars($option['name']);
                                        } elseif (!is_array($option)) {
                                            $option_names[] = htmlspecialchars($option);
                                        }
                                    }
                                    echo "<span>" . implode(' / ', $option_names) . "</span>";
                                    ?>
                                <?php else: ?>
                                    <span>オプションなし</span>
                                <?php endif; ?>
                            </p>

                            <p class="price">
                                <?= number_format($item['product_syokei']) ?>円 × <?= $item['quantity'] ?>
                            </p>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <div class="cartPart">
                <div class="inner">
                    <div class="iconWrap">
                        <div class="icon">
                            <?php if ($total_items > 0): ?>
                                <span class="num"><?= $total_items ?></span>
                            <?php endif; ?>
                            <img width="48" src="/menumate/assets/img/icon_cart.svg" alt="カートアイコン">
                        </div>
                    </div>
                    <?php if ($total_items > 0): ?>
                        <p class="price">合計：<?= number_format($total_price) ?>円</p>
                    <?php endif; ?>
                    <div class="btnWrap">
                        <a class="btn history" href="shop.php">戻る</a>
                        <a id="order-confirm-btn" class="btn cart <?= $total_items == 0 ? 'disabled' : '' ?>" href="confirm.php" <?= $total_items == 0 ? 'disabled' : '' ?>>注文する</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once '../parts/footer.php'; ?>
</body>

</html>