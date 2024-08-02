<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Adicionar usuário
if (isset($_POST['adicionar'])) {
    $nome_usuario = $_POST['nome_usuario'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    $tipo_usuario = $_POST['tipo_usuario'];
    
    $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, senha, tipo_usuario) VALUES (:nome_usuario, :senha, :tipo_usuario)");
    $stmt->bindParam(':nome_usuario', $nome_usuario);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':tipo_usuario', $tipo_usuario);
    $stmt->execute();
}

// Excluir usuário
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

// Editar usuário
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Atualizar usuário
if (isset($_POST['atualizar'])) {
    $id = $_POST['id'];
    $nome_usuario = $_POST['nome_usuario'];
    $senha = $_POST['senha'] ? password_hash($_POST['senha'], PASSWORD_BCRYPT) : null;
    $tipo_usuario = $_POST['tipo_usuario'];
    
    $sql = "UPDATE usuarios SET nome_usuario = :nome_usuario, tipo_usuario = :tipo_usuario";
    if ($senha) {
        $sql .= ", senha = :senha";
    }
    $sql .= " WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nome_usuario', $nome_usuario);
    $stmt->bindParam(':tipo_usuario', $tipo_usuario);
    if ($senha) {
        $stmt->bindParam(':senha', $senha);
    }
    $stmt->execute();

    header('Location: gerenciar_usuarios.php');
    exit();
}

// Buscar usuários
$stmt = $conn->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .actions-column {
            width: 150px;
        }
        .actions-column .btn {
            margin-right: 5px;
        }
        .table td, .table th {
            vertical-align: middle;
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
        <h1 class="mt-4">Gerenciar Usuários</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h3>Adicionar Novo Usuário</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="nome_usuario">Nome de Usuário</label>
                        <input type="text" id="nome_usuario" name="nome_usuario" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo de Usuário</label>
                        <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
                            <option value="Administrador">Administrador</option>
                            <option value="Secretaria">Secretaria</option>
                            <option value="Leitor">Leitor</option>
                        </select>
                    </div>
                    <button type="submit" name="adicionar" class="btn btn-primary">Adicionar Usuário</button>
                </form>
            </div>
        </div>

        <!-- Formulário para editar usuários -->
        <?php if (isset($usuario)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h3>Editar Usuário</h3>
                <form action="" method="POST">
                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                    <div class="form-group">
                        <label for="nome_usuario">Nome de Usuário</label>
                        <input type="text" id="nome_usuario" name="nome_usuario" class="form-control" value="<?php echo htmlspecialchars($usuario['nome_usuario']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-control">
                        <small class="form-text text-muted">Deixe em branco para manter a senha atual.</small>
                    </div>
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo de Usuário</label>
                        <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
                            <option value="Administrador" <?php echo $usuario['tipo_usuario'] === 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                            <option value="Secretaria" <?php echo $usuario['tipo_usuario'] === 'Secretaria' ? 'selected' : ''; ?>>Secretaria</option>
                            <option value="Leitor" <?php echo $usuario['tipo_usuario'] === 'Leitor' ? 'selected' : ''; ?>>Leitor</option>
                        </select>
                    </div>
                    <button type="submit" name="atualizar" class="btn btn-primary">Atualizar Usuário</button>
                    <a href="gerenciar_usuarios.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela de usuários -->
        <div class="card">
            <div class="card-body">
                <h3>Lista de Usuários</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome de Usuário</th>
                            <th>Tipo de Usuário</th>
                            <th class="actions-column">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['id']; ?></td>
                            <td><?php echo htmlspecialchars($usuario['nome_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['tipo_usuario']); ?></td>
                            <td class="actions-column">
                                <div class="btn-group" role="group">
                                    <a href="?edit=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="?delete=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-sm">Excluir</a>
                                </div>
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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
