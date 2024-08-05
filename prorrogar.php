<?php
session_start();
include 'config.php';

// Verificar se o usuário está logado e é do tipo Administrador ou Secretaria
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT tipo_usuario FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$tipo_usuario = $stmt->fetchColumn();

if ($tipo_usuario !== 'Administrador' && $tipo_usuario !== 'Secretaria') {
    header('Location: emprestimos.php');
    exit();
}

// Prorrogar Empréstimo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['aceito_termos'])) {
        header('Location: solicitacoes.php?msg=Você deve aceitar os termos e condições para prorrogar o empréstimo.');
        exit();
    }

    $emprestimo_id = $_POST['emprestimo_id'];
    $nova_data_devolucao = $_POST['data_nova_devolucao'];

    // Verificar se a solicitação está aprovada e se o livro está com o leitor
    $stmt = $conn->prepare("SELECT e.status, e.data_devolucao FROM emprestimos e WHERE e.id = ?");
    $stmt->execute([$emprestimo_id]);
    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($emprestimo['status'] === 'Aprovado' && $emprestimo['data_devolucao'] === null) {
        // Atualizar data de devolução
        $stmt = $conn->prepare("UPDATE emprestimos SET data_devolucao = ? WHERE id = ?");
        $stmt->execute([$nova_data_devolucao, $emprestimo_id]);
        header('Location: emprestimos.php?msg=Prorrogação realizada com sucesso');
    } else {
        header('Location: emprestimos.php?msg=Não é possível prorrogar um livro que não está com o leitor ou cuja solicitação não foi aprovada');
    }
}
?>
