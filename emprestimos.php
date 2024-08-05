<?php
session_start();
include 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Verificar o tipo de usuário e redirecionar se não for autorizado
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT tipo_usuario FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$tipo_usuario = $stmt->fetchColumn();

// Filtros
$livro_id = isset($_GET['livro_id']) ? $_GET['livro_id'] : '';
$usuario_nome = isset($_GET['usuario_nome']) ? $_GET['usuario_nome'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Buscar Empréstimos com Filtro
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

// Filtrar por usuário se for do tipo 'Leitor'
if ($tipo_usuario === 'Leitor') {
    $query .= " AND e.usuario_id = ?";
    $params[] = $usuario_id;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar Prorrogações
$query = "SELECT p.id, p.status, p.data_solicitacao, p.data_aprovacao, e.id AS emprestimo_id, l.titulo, u.nome_usuario 
          FROM prorrogacoes p 
          JOIN emprestimos e ON p.emprestimo_id = e.id 
          JOIN livros l ON e.livro_id = l.id 
          JOIN usuarios u ON e.usuario_id = u.id 
          WHERE 1=1";

// Filtrar por usuário se for do tipo 'Leitor'
if ($tipo_usuario === 'Leitor') {
    $query .= " AND e.usuario_id = ?";
    $params = [$usuario_id];
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$prorrogacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empréstimos e Devoluções</title>
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
            background-color: #87cefa;
            color: white;
        }
        .table thead th {
            background-color: #87cefa;
            color: white;
        }
        .btn-action {
            margin: 0 5px;
        }
        .footer {
            background-color: #87cefa;
            color: #333;
            text-align: center;
            align-items: center;
            justify-content: center;
            padding: 8px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-family: 'Roboto', sans-serif;
            z-index: 1000;
        }
        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Empréstimos e Devoluções</h1>

        <!-- Filtro -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Filtrar Empréstimos</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="emprestimos.php">
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

        <!-- Empréstimos -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Empréstimos</h4>
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
                                    <!-- Ações do administrador e secretaria -->
                                    <?php if ($tipo_usuario === 'Administrador' || $tipo_usuario === 'Secretaria'): ?>
                                        <button class="btn btn-primary btn-action" data-toggle="modal" data-target="#editModal" data-id="<?php echo htmlspecialchars($emprestimo['id']); ?>">Editar</button>
                                        <button class="btn btn-danger btn-action" onclick="deleteItem(<?php echo htmlspecialchars($emprestimo['id']); ?>)">Excluir</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Prorrogações -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Prorrogações</h4>
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
                            <th>Empréstimo ID</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prorrogacoes as $prorrogacao): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prorrogacao['id']); ?></td>
                                <td><?php echo htmlspecialchars($prorrogacao['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($prorrogacao['nome_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($prorrogacao['status']); ?></td>
                                <td><?php echo htmlspecialchars($prorrogacao['data_solicitacao']); ?></td>
                                <td><?php echo htmlspecialchars($prorrogacao['data_aprovacao']); ?></td>
                                <td><?php echo htmlspecialchars($prorrogacao['emprestimo_id']); ?></td>
                                <td>
                                    <!-- Ações do administrador e secretaria -->
                                    <?php if ($tipo_usuario === 'Administrador' || $tipo_usuario === 'Secretaria'): ?>
                                        <button class="btn btn-primary btn-action" data-toggle="modal" data-target="#editModal" data-id="<?php echo htmlspecialchars($prorrogacao['id']); ?>">Editar</button>
                                        <button class="btn btn-danger btn-action" onclick="deleteItem(<?php echo htmlspecialchars($prorrogacao['id']); ?>)">Excluir</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Itamar Junior. Todos os direitos reservados.</p>
    </footer>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Empréstimo/Prorrogação</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Conteúdo do formulário de edição -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary">Salvar alterações</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Script para manipular o modal de edição
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            // Use o ID para carregar dados e preencher o formulário de edição
        });

        function deleteItem(id) {
            if (confirm('Você tem certeza que deseja excluir este item?')) {
                // Adicione a lógica para excluir o item com o ID fornecido
            }
        }
    </script>
</body>
</html>
