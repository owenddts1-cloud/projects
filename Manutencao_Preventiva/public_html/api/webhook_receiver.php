<?php
// API Receiver for Power Automate
// Expected JSON payload: { "tag": "M123", "alert_type": "HighTemp", "value": 90, "token": "secret..." }

require '../src/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$pdo = Database::getInstance();

// Validate Token
$stmt = $pdo->prepare("SELECT token_auth FROM integration_config WHERE plataforma = 'PowerAutomate'");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config || $input['token'] !== $config['token_auth']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Process Alert
// Log as Corrective Maintenance request or Create Alert
// For this MVP, we will try to find the asset and log a pending maintenance note via a placeholder table or logic.
// Simplification: We will just return success for now, but in a real app this would insert into a 'alerts' table.

if (isset($input['tag'])) {
    $stmtAsset = $pdo->prepare("SELECT id FROM assets WHERE tag_identificacao = :tag");
    $stmtAsset->execute(['tag' => $input['tag']]);
    $asset = $stmtAsset->fetch(PDO::FETCH_ASSOC);

    if ($asset) {
        // Log received successfully
        // Here you would insert into an 'alerts' table.
        echo json_encode([
            'status' => 'success',
            'message' => 'Alert received for asset ' . $input['tag'],
            'asset_id' => $asset['id']
        ]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Asset not found']);
?>