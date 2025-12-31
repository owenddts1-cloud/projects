<?php
session_start();

function login($email, $password, $pdo)
{
    // Sanitização básica
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $stmt = $pdo->prepare("SELECT id, nome, senha, nivel FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row['senha'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_nome'] = $row['nome'];
            $_SESSION['user_nivel'] = $row['nivel'];
            return true;
        }
    }
    return false;
}

function checkAuth($requiredLevel = null)
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    if ($requiredLevel) {
        if ($_SESSION['user_nivel'] !== 'admin' && $_SESSION['user_nivel'] !== $requiredLevel) {
            // Se não for admin e não tiver o nível requerido (ex: 'tecnico')
            echo "Acesso negado. Nível insuficiente.";
            exit;
        }
    }
}

function logout()
{
    session_destroy();
    header("Location: index.php");
    exit;
}
?>