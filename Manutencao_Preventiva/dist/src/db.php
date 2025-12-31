<?php
class Database
{
    private static $instance = null;
    private $conn;

    // Supabase Credentials
    private $host = 'db.swcdujmakyrctzoostaf.supabase.co';
    private $db_name = 'postgres';
    private $username = 'postgres';
    private $password = 'Guidata@4834'; // USER: Please update this with your actual database password
    private $port = '5432';

    private function __construct()
    {
        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // set names is not needed/different in pgsql DSN usually handles charset, but we can set it if needed.
            // PostgreSQL defaults to UTF8 usually.
        } catch (PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
            exit;
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
?>