<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_usuario = $_POST['username']; // Corrigir se necessário
    $senha = $_POST['password']; // Corrigir se necessário

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nome_usuario = :nome_usuario");
    $stmt->bindParam(':nome_usuario', $nome_usuario);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        header('Location: dashboard.php');
    } else {
        echo "<script>alert('Nome de usuário ou senha inválidos.'); window.location.href='index.php';</script>";
    }
}
?>

