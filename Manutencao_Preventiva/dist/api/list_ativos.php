<?php
require '../src/db.php';

header('Content-Type: application/json');

$pdo = Database::getInstance();

$sql = "SELECT id, tag_identificacao, nome, status FROM assets";
$stmt = $pdo->query($sql);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'data' => $assets,
    'count' => count($assets),
    'generated_at' => date('Y-m-d H:i:s')
]);
?>