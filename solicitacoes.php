<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT tipo_usuario FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$tipo_usuario = $stmt->fetchColumn();

$livro_id = isset($_GET['livro_id']) ? $_GET['livro_id'] : '';
$usuario_nome = isset($_GET['usuario_nome']) ? $_GET['usuario_nome'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT e.id, e.status, e.data_solicitacao, e.data_aprovacao, e.data_devolucao, l.titulo, u.nome_usuario 
          FROM emprestimos e 
          JOIN livros l ON e.livro_id = l.id 
          JOIN usuarios u ON e.usuario_id = u.id 
          WHERE 1=1";

$params = [];
if ($livro_id) {
    $query .= " AND l.id = ?";
    $params[] = $livro_id;
}
if ($usuario_nome) {
    $query .= " AND u.nome_usuario LIKE ?";
    $params[] = "%$usuario_nome%";
}
if ($status) {
    $query .= " AND e.status = ?";
    $params[] = $status;
}

if ($tipo_usuario === 'Leitor') {
    $query .= " AND e.usuario_id = ?";
    $params[] = $usuario_id;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT p.id, p.status, p.data_solicitacao, p.data_aprovacao, e.id AS emprestimo_id, l.titulo, u.nome_usuario 
          FROM prorrogacoes p 
          JOIN emprestimos e ON p.emprestimo_id = e.id 
          JOIN livros l ON e.livro_id = l.id 
          JOIN usuarios u ON e.usuario_id = u.id 
          WHERE 1=1";

if ($tipo_usuario === 'Leitor') {
    $query .= " AND e.usuario_id = ?";
    $params = [$usuario_id];
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$prorrogacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar'])) {
    if (isset($_POST['aceito_termos']) && $_POST['aceito_termos'] === 'on') {
        $livro_id = $_POST['livro_id'];
        $usuario_id = $_SESSION['usuario_id'];
        
        $stmt = $conn->prepare("INSERT INTO emprestimos (livro_id, usuario_id, status, data_solicitacao) VALUES (?, ?, 'Pendente', NOW())");
        $stmt->execute([$livro_id, $usuario_id]);

        header('Location: solicitacoes.php');
        exit();
    } else {
        echo "<script>alert('Você deve aceitar os termos e condições para solicitar um livro.');</script>";
    }
}

// Processar Prorrogação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prorrogar'])) {
    $emprestimo_id = $_POST['emprestimo_id'];

    // Verificar se o empréstimo está ativo e pode ser prorrogado
    $stmt = $conn->prepare("SELECT status FROM emprestimos WHERE id = ?");
    $stmt->execute([$emprestimo_id]);
    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($emprestimo && $emprestimo['status'] === 'Aprovado') {
        $stmt = $conn->prepare("INSERT INTO prorrogacoes (emprestimo_id, status, data_solicitacao) VALUES (?, 'Pendente', NOW())");
        $stmt->execute([$emprestimo_id]);

        header('Location: solicitacoes.php');
        exit();
    } else {
        echo "<script>alert('O livro não está emprestado ou não pode ser prorrogado.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Livros</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .btn-action {
            margin: 0 5px;
        }
        .footer {
            background-color: #007bff;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-family: 'Roboto', sans-serif;
        }
        footer p {
            margin: 0;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-footer {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Solicitações de Livros</h1>

        <!-- Filtro -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0">Filtrar Solicitações</h4>
          </div>
          <div class="card-body">
          <form method="GET" action="solicitacoes.php">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="livro_id">ID do Livro</label>
                            <input type="text" class="form-control" id="livro_id" name="livro_id" value="<?php echo htmlspecialchars($livro_id); ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="usuario_nome">Nome do Usuário</label>
                            <input type="text" class="form-control" id="usuario_nome" name="usuario_nome" value="<?php echo htmlspecialchars($usuario_nome); ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="Aprovado" <?php echo $status === 'Aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                <option value="Pendente" <?php echo $status === 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="Cancelado" <?php echo $status === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Solicitações -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Solicitações</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Livro</th>
                            <th>Usuário</th>
                            <th>Status</th>
                            <th>Data de Solicitação</th>
                            <th>Data de Aprovação</th>
                            <th>Data de Devolução</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emprestimos as $emprestimo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emprestimo['id']); ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['nome_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['data_solicitacao']); ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['data_aprovacao']); ?></td>
                                <td><?php echo htmlspecialchars($emprestimo['data_devolucao']); ?></td>
                                <td>
                                    <?php if ($tipo_usuario === 'Administrador' || $tipo_usuario === 'Secretaria'): ?>
                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                        <button class="btn btn-danger btn-action" onclick="deleteItem(<?php echo htmlspecialchars($emprestimo['id']); ?>)">Excluir</button>
                                    <?php endif; ?>
                                    <?php if ($tipo_usuario !== 'Leitor' || $emprestimo['status'] === 'Aprovado'): ?>
                                        <button class="btn btn-warning btn-action" data-toggle="modal" data-target="#prorrogarModal" data-id="<?php echo htmlspecialchars($emprestimo['id']); ?>">Prorrogar</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel">Confirmação de Solicitação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Olá,</p>
                        <p>Informamos que, caso o livro aprovado não seja devolvido ou prorrogado após 48 horas, será aplicada uma multa de R$ 3,00 por dia (válido apenas para dias de semana).</p>
                        <p>Ao clicar no botão abaixo para finalizar o processo, você concorda com os termos de multa descritos.</p>
                        <p>Agradecemos a sua compreensão.</p>
                        <p>Atenciosamente,<br>Escola CDA</p>
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="solicitacoes.php">
                            <input type="hidden" name="livro_id" id="livro_id_modal">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="aceito_termos" name="aceito_termos">
                                <label class="form-check-label" for="aceito_termos">
                                    Aceito os termos e condições
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary" name="solicitar">Confirmar Solicitação</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="prorrogarModal" tabindex="-1" role="dialog" aria-labelledby="prorrogarModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="prorrogarModalLabel">Prorrogar Empréstimo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Você deseja prorrogar o empréstimo deste livro?</p>
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="solicitacoes.php">
                            <input type="hidden" name="emprestimo_id" id="emprestimo_id_modal">
                            <button type="submit" class="btn btn-warning" name="prorrogar">Prorrogar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Itamar Junior. Todos os direitos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);
            modal.find('#livro_id_modal').val(id);
        });

        $('#prorrogarModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);
            modal.find('#emprestimo_id_modal').val(id);
        });

        function deleteItem(id) {
            if (confirm('Você tem certeza que deseja excluir este item?')) {
                window.location.href = 'excluir.php?id=' + id;
            }
        }
    </script>
</body>
</html>
