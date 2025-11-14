<?php
require_once '../functions/database.php';
session_start();

// POSTデータの取得
$table_id = $_POST['table_id'] ?? null;
$status = $_POST['status'] ?? null;
$serving_time_seconds = $_POST['serving_time_seconds'] ?? null; // 追加

if ($table_id && $status !== null) {
    try {
        // SQLのプレースホルダーとバインド変数を一致させる
        $stmt = $pdo->prepare("UPDATE orders SET status = :status, serving_time_seconds = :serving_time WHERE table_id = :table_id");
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':serving_time', $serving_time_seconds, PDO::PARAM_INT); // 追加
        $stmt->bindParam(':table_id', $table_id, PDO::PARAM_STR);
        
        $stmt->execute();

        // リダイレクト（注文一覧ページに戻る）
        header("Location: ../public/order.php");
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


