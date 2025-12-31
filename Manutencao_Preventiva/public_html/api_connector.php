<?php
// api_connector.php - Interface to communicate with external flows (Power Automate)

require_once 'src/db.php';

class PowerAutomateConnector
{
    private $pdo;
    private $webhookUrl;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->loadConfig();
    }

    private function loadConfig()
    {
        // In a real scenario, this URL might be stored in the DB or ENV
        // For now preventing errors if table is empty
        try {
            $stmt = $this->pdo->prepare("SELECT token_auth FROM integration_config WHERE plataforma = 'PowerAutomateSender' LIMIT 1");
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->webhookUrl = $config['token_auth'] ?? null; // Assuming token column might hold the URL for sending
        } catch (Exception $e) {
            $this->webhookUrl = null;
        }
    }

    public function sendNotification($message, $type = 'info')
    {
        if (!$this->webhookUrl) {
            return ['status' => 'error', 'message' => 'Webhook URL not configured'];
        }

        $payload = json_encode([
            'message' => $message,
            'type' => $type,
            'timestamp' => date('c')
        ]);

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status' => $httpCode == 200 ? 'success' : 'failed', 'response' => $result];
    }
}

// Example usage if called directly for testing
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json');
    $connector = new PowerAutomateConnector();
    echo json_encode(['status' => 'ready', 'message' => 'Connector initialized']);
}
?>