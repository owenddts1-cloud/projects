<?php
require 'src/db.php';

echo "<h1>Criar Usuário Admin</h1>";

try {
    $pdo = Database::getInstance();

    $nome = "Administrador";
    $email = "admin@admin.com";
    $senha = "admin123"; // Senha padrão
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $nivel = "admin";

    // Verifica se já existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo "<p style='color:orange'>O usuário <strong>$email</strong> já existe!</p>";

        // Opcional: Atualizar a senha
        $update = $pdo->prepare("UPDATE users SET senha = :senha WHERE email = :email");
        $update->execute(['senha' => $hash, 'email' => $email]);
        echo "<p style='color:green'>A senha foi redefinida para: <strong>$senha</strong></p>";

    } else {
        $sql = "INSERT INTO users (nome, email, senha, nivel) VALUES (:nome, :email, :senha, :nivel)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $hash,
            'nivel' => $nivel
        ]);

        echo "<p style='color:green'>Usuário Admin criado com sucesso!</p>";
        echo "<ul>
                <li>Email: <strong>$email</strong></li>
                <li>Senha: <strong>$senha</strong></li>
              </ul>";
    }

    echo "<p><a href='index.php'>Ir para Login</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro ao conectar ou criar usuário: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se o banco de dados 'manutencao_db' foi criado e se as tabelas existem (importe o arquivo database.sql).</p>";
}
?>