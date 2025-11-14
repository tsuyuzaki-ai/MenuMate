<?php
require_once '../functions/database.php';
require_once '../parts/header.php';

// データベース接続（database.php で接続しているので、ここでは再度接続しない）
try {
    // 提供待ち（status = 0）の注文を取得
    $stmt1 = $pdo->prepare("SELECT * FROM orders WHERE status = 0 ORDER BY created_at ASC, table_id ASC");
    $stmt1->execute();
    $ordered_orders = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // 会計待ち（status = 1）の注文を取得
    $stmt2 = $pdo->prepare("SELECT * FROM orders WHERE status = 1 ORDER BY created_at ASC, table_id ASC");
    $stmt2->execute();
    $served_orders = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// テーブルIDごとに注文をグループ化する関数
function group_orders_by_table($orders)
{
    $grouped_orders = [];

    foreach ($orders as $order) {
        // テーブルIDをキーにグループ化
        $grouped_orders[$order['table_id']][] = $order;
    }

    return $grouped_orders;
}

// 提供待ちと会計待ちをグループ化
$ordered_orders_grouped = group_orders_by_table($ordered_orders);
$served_orders_grouped = group_orders_by_table($served_orders);
?>

<title>注文確認 | MenuMate</title>
</head>

<body ontouchstart="">
    <main id="admin-order" class="adminPage orderListPage">
        <div class="content">
            <?php require_once '../parts/side.php'; ?>

            <div class="hasSide">
                <h1>注文一覧</h1>

                <!-- 提供待ちの注文を表示 -->
                <section id="status01">
                    <h2>提供待ち</h2>
                    <ul class="orders">
                        <?php if (empty($ordered_orders_grouped)): ?>
                            <p>注文はありません</p>
                        <?php else: ?>
                            <?php foreach ($ordered_orders_grouped as $table_id => $orders): ?>
                                <li>

                                    <form action="../functions/update-status.php" method="POST" class="update-status-form">
                                        <div class="orderInner">
                                            <div class="leftWrap">
                                                <p class="seat"><span><?= htmlspecialchars($table_id) ?></span>テーブルNo.</p>
                                                <!-- 一括変更ボタン -->
                                                <div class="btn">
                                                    <input type="hidden" name="table_id"
                                                        value="<?= htmlspecialchars($table_id) ?>">
                                                    <input type="hidden" name="status" value="1"> <!-- 提供済みに変更 -->
                                                    <input type="hidden" name="serving_time_seconds"
                                                        value="<?= $elapsed_seconds ?>" class="serving-time">
                                                    <button type="submit" class="btn-update-status">提供済み</button>
                                                </div>
                                            </div>
                                            <div class="rightWrap">
                                                <ul class="order">
                                                    <?php
                                                    $total_price = 0;
                                                    foreach ($orders as $order):

                                                        $order_details = !empty($order['options']) ? json_decode($order['options'], true) : [];
                                                        $order_details = is_array($order_details) ? $order_details : [];

                                                        $syoukei = $order['product_syokei'] * $order['quantity'];
                                                        $total_price += $syoukei;
                                                        ?>
                                                        <li>
                                                            <p class="name"><?= htmlspecialchars($order['product_name']) ?></p>
                                                            <p class="option"><?= !empty($order_details) ? htmlspecialchars(implode(' / ', $order_details)) : 'オプションなし' ?></p>
                                                            <p class="syoukei"><?= $order['product_syokei'] ?>円 <span
                                                                    class="<?= ($order['quantity'] >= 2) ? 'red' : '' ?>">×
                                                                    <?= $order['quantity'] ?></span>
                                                            </p>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <?php
                                                $created_at = new DateTime($orders[0]['created_at'], new DateTimeZone('Asia/Tokyo'));
                                                $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
                                                $elapsed_seconds = $now->getTimestamp() - $created_at->getTimestamp();
                                                ?>
                                                <p class="time" data-elapsed="<?= $elapsed_seconds ?>">
                                                    経過時間：<span class="minutes">00</span>分<span class="seconds">00</span>秒
                                                </p>
                                                <input type="hidden" name="serving_time_seconds" value="<?= $elapsed_seconds ?>"
                                                    class="serving-time">
                                            </div>
                                        </div>
                                    </form>

                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>

                <!-- 会計待ちの注文を表示 -->
                <section id="status02">
                    <h2>会計待ち</h2>
                    <ul class="orders">
                        <?php if (empty($served_orders_grouped)): ?>
                            <p>注文はありません</p>
                        <?php else: ?>
                            <?php foreach ($served_orders_grouped as $table_id => $orders): ?>
                                <li>
                                    <div class="orderInner">
                                        <div class="leftWrap">
                                            <p class="seat"><span><?= htmlspecialchars($table_id) ?></span>テーブルNo.</p>
                                            <!-- 一括変更ボタン -->
                                            <div class="btn">
                                                <form action="../functions/update-status.php" method="POST">
                                                    <input type="hidden" name="table_id"
                                                        value="<?= htmlspecialchars($table_id) ?>">
                                                    <input type="hidden" name="status" value="2"> <!-- 会計済みに変更 -->
                                                    <button type="submit" class="btn-update-status">会計済み</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="rightWrap">
                                            <ul class="order">
                                                <?php
                                                $total_price = 0;
                                                foreach ($orders as $order):

                                                    $order_details = !empty($order['options']) ? json_decode($order['options'], true) : [];
                                                    $order_details = is_array($order_details) ? $order_details : [];

                                                    $syoukei = $order['product_syokei'] * $order['quantity'];
                                                    $total_price += $syoukei;
                                                    ?>
                                                    <li>
                                                        <p class="name"><?= htmlspecialchars($order['product_name']) ?></p>
                                                        <p class="option"><?= !empty($order_details) ? htmlspecialchars(implode(' / ', $order_details)) : 'オプションなし' ?></p>
                                                        <p class="syoukei"><?= $order['product_syokei'] ?>円 ×
                                                            <?= $order['quantity'] ?>
                                                        </p>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <p class="goukei">合計：<?= number_format($total_price) ?>円</p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>


            </div>
        </div>
    </main>

    <?php require_once '../parts/footer.php'; ?>
</body>

</html>