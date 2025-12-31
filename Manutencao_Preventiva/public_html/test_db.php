<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'src/db.php';

echo "<h2>Testando Conexão com Supabase (PostgreSQL)</h2>";

try {
    $pdo = Database::getInstance();
    echo "<p style='color:green'>✅ Conexão bem sucedida!</p>";

    // Teste de consulta
    echo "<h3>Teste de Query (Listar Tabelas):</h3>";
    $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
    $stmt = $pdo->query($query);
    $tables = $stmt->fetchAll(PDO::FETCH_Column);

    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>Found table: $table</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nenhuma tabela encontrada (ou erro de permissão).</p>";
    }

    // Teste de Inserção (Users) - Verificar se tabela users existe
    if (in_array('users', $tables)) {
        echo "<h3>Verificando Usuários:</h3>";
        $stmt = $pdo->query("SELECT count(*) FROM users");
        $count = $stmt->fetchColumn();
        echo "Total de usuários: $count<br>";

        if ($count == 0) {
            echo "Nenhum usuário encontrado. Criando usuário admin padrão...<br>";
            // Criar admin se não existir
            // Senha: admin123 (hash)
            $pass = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (nome, email, senha, nivel) VALUES ('Admin', 'admin@admin.com', '$pass', 'admin')";
            $pdo->exec($sql);
            echo "<p style='color:green'>✅ Usuário Admin criado com sucesso.</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se a senha foi preenchida corretamente em <code>src/db.php</code> e se a extensão <code>pdo_pgsql</code> está ativa.</p>";
}
?>