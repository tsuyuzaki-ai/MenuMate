<?php
require_once '../functions/database.php';
require_once '../parts/header.php';

// 初期設定
$limit = 500; // 最大500件まで表示
$search_query = "";
$search_params = [];

// 🔹 開始日と終了日の検索条件を設定
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $search_query .= " AND created_at BETWEEN ? AND ?";
    $search_params[] = $_GET['start_date'] . " 00:00:00";
    $search_params[] = $_GET['end_date'] . " 23:59:59";
} elseif (!empty($_GET['start_date'])) {
    $search_query .= " AND created_at >= ?";
    $search_params[] = $_GET['start_date'] . " 00:00:00";
} elseif (!empty($_GET['end_date'])) {
    $search_query .= " AND created_at <= ?";
    $search_params[] = $_GET['end_date'] . " 23:59:59";
}

if (!empty($_GET['product_name'])) {
    $search_query .= " AND product_name LIKE ?";
    $search_params[] = "%" . $_GET['product_name'] . "%";
}





// 注文データ取得
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE status = 2 $search_query ORDER BY created_at DESC, table_id ASC LIMIT ?");
    foreach ($search_params as $k => $v) {
        $stmt->bindValue($k + 1, $v, PDO::PARAM_STR);
    }
    $stmt->bindValue(count($search_params) + 1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}


// 変数の初期化（0 に設定）
$totalOrders = 0;
$totalPrice = 0;

// **🔹 注文データの集計**
foreach ($orders as $order) {
    $totalOrders += $order['quantity']; // 表示件数
    $totalPrice += $order['product_syokei'] * $order['quantity']; // 合計金額
}






?>

<title>注文確認 | MenuMate</title>
</head>

<body ontouchstart="">
    <main id="admin-sales" class="adminPage salesListPage">
        <div class="content">
            <?php require_once '../parts/side.php'; ?>

            <div class="hasSide">
                <h1>売上一覧</h1>
                <form id="searchForm" method="get" action="sales.php">
                    <div class="flexBox">
                        <div class="left">
                            開始日: <input type="date" name="start_date"
                                value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>"> 〜
                            終了日: <input type="date" name="end_date"
                                value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                            <br>メニュー名: <input type="text" name="product_name"
                                value="<?= htmlspecialchars($_GET['product_name'] ?? '') ?>">
                        </div>
                        <div class="right">
                            <button type="submit">検索</button>
                        </div>
                    </div>
                </form>
                <p class="right">※最大500件まで表示されます</p>

                <ul class="goukei">
                    <li>表示件数：<span><?= $totalOrders ?></span>件</li>
                    <li>合計金額：<span><?= number_format($totalPrice) ?></span>円</li>
                </ul>


                <table>
                    <thead>
                        <tr>
                            <th>注文日時</th>
                            <th>メニュー名</th>
                            <th>オプション</th>
                            <th>小計</th>
                            <th>タイム</th>
                        </tr>
                    </thead>
                    <tbody id="orderTable">
                        <?php foreach ($orders as $order): ?>
                            <?php for ($i = 0; $i < $order['quantity']; $i++): ?>
                                <?php
                                $order_details = !empty($order['options']) ? json_decode($order['options'], true) : [];
                                $order_details = is_array($order_details) ? $order_details : []; // NULL を防ぐ
                                ?>

                                <tr>
                                    <td><?= htmlspecialchars(date("Y-m-d H:i", strtotime($order['created_at']))) ?></td>
                                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                                    <td><?= !empty($order_details) ? htmlspecialchars(implode(' / ', $order_details)) : 'なし' ?>
                                    </td>

                                    <td><?= htmlspecialchars($order['product_syokei']) ?>円</td>
                                    <td><?= gmdate("i:s", $order['serving_time_seconds']) ?></td>
                                </tr>
                            <?php endfor; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>