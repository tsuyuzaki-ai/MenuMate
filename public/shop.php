<?php

ini_set('session.gc_maxlifetime', 21600);
session_start(); // セッションを開始

// URLの`table_id`を取得してセッションに格納
if (isset($_GET['table_id'])) {
    $_SESSION['table_id'] = $_GET['table_id'];  // table_idをセッションに保存
}

require_once '../functions/database.php';

// データベース接続（$pdoをそのまま使用）
$db = $pdo;

// 商品情報を取得
$sql = "SELECT * FROM products WHERE is_visible = 1 ORDER BY created_at DESC";
$products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 商品ごとのオプション情報を取得
$options_sql = "
    SELECT ot.id AS option_title_id, ot.product_id, ot.option_title, ot.option_order, 
           od.id AS option_id, od.option_name, od.option_price
    FROM option_titles ot
    LEFT JOIN option_details od ON ot.id = od.option_title_id
    ORDER BY ot.product_id, ot.option_order, od.id";
$options = $db->query($options_sql)->fetchAll(PDO::FETCH_ASSOC);

// オプションデータを整理
$product_options = [];
foreach ($options as $option) {
    $product_id = $option['product_id'];
    $option_title_id = $option['option_title_id'];

    if (!isset($product_options[$product_id])) {
        $product_options[$product_id] = [];
    }
    if (!isset($product_options[$product_id][$option_title_id])) {
        $product_options[$product_id][$option_title_id] = [
            'title' => $option['option_title'],
            'details' => []
        ];
    }
    $product_options[$product_id][$option_title_id]['details'][] = [
        'name' => $option['option_name'],
        'price' => $option['option_price']
    ];
}



// カート部分の情報取得
$table_id = $_SESSION['table_id'] ?? null;
$cart = $_SESSION['cart'][$table_id] ?? [];

// 合計金額を計算（数量考慮）
$total_price = 0;
$total_items = 0; // 合計アイテム数

foreach ($cart as $item) {
    $total_price += $item['product_syokei'] * $item['quantity'];  // 金額計算
    $total_items += $item['quantity'];
}


?>


<?php require_once '../parts/header.php'; ?>

<title>商品一覧 | MenuMate</title>
</head>

<body>

    <main id="user-front" class="orderPage">
        <div class="content">

                    <!-- 注釈 -->
                    <ul class="attention">
                <li>今月のスープは正面ホワイトボードをご確認ください</li>
                <li>＋500円でダブルスープにできます</li>
             </ul>
             
            <!-- カテゴリー一覧 -->
            <ul class="category">
                <?php
                $categories = [
                    "morning" => "モーニング",
                    "set" => "セット",
                    "soup" => "スープ",
                    "ippin" => "一品料理",
                    "bread" => "パン・ご飯",
                    "salad" => "サラダ",
                    "drink" => "ドリンク",
                    "sweets" => "スイーツ"
                ];
                foreach ($categories as $class => $name) {
                    echo "<li class='$class'>$name</li>";
                }
                ?>
            </ul>



            <!-- 商品一覧 -->
            <ul class="itemList">
                <?php foreach ($products as $product): ?>

                    <?php
                    $categories = [
                        "morning" => "モーニング",
                        "set" => "セット",
                        "soup" => "スープ",
                        "ippin" => "一品料理",
                        "bread" => "パン・ご飯",
                        "salad" => "サラダ",
                        "drink" => "ドリンク",
                    "sweets" => "スイーツ"
                    ];

                    // 日本語から英語へのマッピング
                    $category_map = array_flip($categories);

                    ?>

                    <li
                        class="product_<?= htmlspecialchars($product['id']) ?> <?= htmlspecialchars($category_map[$product['product_category']] ?? 'unknown') ?>">


                        <?php if (!empty($product['product_badge'])): ?>
                            <p class="badge"><?= htmlspecialchars($product['product_badge']) ?></p>
                        <?php endif; ?>
                        <div class="image">
                            <?php
                            // 商品画像が存在するか確認
                            $product_image = isset($product['product_image']) && !empty($product['product_image'])
                                ? htmlspecialchars($product['product_image'])
                                : '/menumate/assets/img/dummy.png';  // 画像がない場合はデフォルト画像
                            ?>
                            <img src="<?= $product_image ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                        </div>

                        <p class="title"><?= htmlspecialchars($product['product_name']) ?></p>
                        <p class="info"><?= nl2br(htmlspecialchars($product['product_description'])) ?></p>
                        <p class="price"><?= number_format($product['product_price']) ?>円</p>
                    </li>
                <?php endforeach; ?>
            </ul>


            <!-- ポップアップ（詳細表示） -->
            <div class="mask"></div>
            <ul class="popupList">


                <?php foreach ($products as $product): ?>
                    <form action="../functions/add-cart.php" method="POST">
                        <li class="product_<?= htmlspecialchars($product['id']) ?>">
                            <img class="close" src="/menumate/assets/img/icon_close.png" alt="閉じるボタン">

                            <div class="image">
                                <?php
                                $product_image = isset($product['product_image']) && !empty($product['product_image'])
                                    ? htmlspecialchars($product['product_image'])
                                    : '/menumate/assets/img/dummy.png';
                                ?>
                                <img src="<?= $product_image ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            </div>

                            <p class="title"><?= htmlspecialchars($product['product_name']) ?></p>
                            <p class="info"><?= nl2br(htmlspecialchars($product['product_description'])) ?></p>

                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <input type="hidden" name="product_name"
                                value="<?= htmlspecialchars($product['product_name']) ?>">
                            <input type="hidden" name="base_price"
                                value="<?= htmlspecialchars($product['product_price']) ?>">
                            <!-- 🔥 【追加】オプション込みの小計を送る hidden input -->
                            <input type="hidden" name="product_syokei" value="">


                            <ul class="options">
                                <?php if (isset($product_options[$product['id']])): ?>
                                    <?php foreach ($product_options[$product['id']] as $option_id => $option): ?>
                                        <li id="option_<?= htmlspecialchars($option_id) ?>">
                                            <div class="flexBox">
                                                <p class="title"><?= htmlspecialchars($option['title']) ?></p>
                                                <p class="openBtn">選ぶ</p>
                                            </div>
                                            <ul class="option">
                                                <?php foreach ($option['details'] as $detail): ?>
                                                    <li>
                                                        <label>
                                                            <input type="radio"
                                                                name="options[<?= htmlspecialchars($option['title']) ?>]"
                                                                value="<?= htmlspecialchars($detail['name']) ?>"
                                                                data-price="<?= htmlspecialchars($detail['price']) ?>"
                                                                onchange="calculateTotal()">
                                                            <span class="name"><?= htmlspecialchars($detail['name']) ?></span>
                                                            <span class="price">
                                                                <?= ($detail['price'] >= 0 ? '+' : '') . number_format($detail['price']) ?>円
                                                            </span>
                                                        </label>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <p class="current">- 選択なし</p>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>

                            <!-- 小計金額 -->
                            <div class="syoukei" data-base-price="<?= htmlspecialchars($product['product_price']) ?>">
                                <?= number_format($product['product_price']) ?>円
                            </div>

                            <div class="to_cart">
                                <button type="submit" class="text">注文かごに入れる</button>
                            </div>
                        </li>
                    </form>
                <?php endforeach; ?>








            </ul>


            

            <!-- カートアイコン -->
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
                    <?php if (count($cart) > 0): ?>
                        <p class="price">合計：<?= number_format($total_price) ?>円</p>
                    <?php endif; ?>
                    <div class="btnWrap">
                        <a class="btn history" href="history.php">注文履歴</a>
                        <a class="btn cart" href="cart.php">注文かご</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once '../parts/footer.php'; ?>