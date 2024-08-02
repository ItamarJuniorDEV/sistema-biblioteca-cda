<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Adicionar livro
if (isset($_POST['adicionar'])) {
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $ano = $_POST['ano'];
    
    $stmt = $conn->prepare("INSERT INTO livros (titulo, autor, ano) VALUES (:titulo, :autor, :ano)");
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':autor', $autor);
    $stmt->bindParam(':ano', $ano);
    $stmt->execute();
}

// Excluir livro
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM livros WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

// Editar livro
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM livros WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $livro = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Atualizar livro
if (isset($_POST['atualizar'])) {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $ano = $_POST['ano'];
    
    $stmt = $conn->prepare("UPDATE livros SET titulo = :titulo, autor = :autor, ano = :ano WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':autor', $autor);
    $stmt->bindParam(':ano', $ano);
    $stmt->execute();

    header('Location: gerenciar_livros.php');
    exit();
}

// Buscar livros
$stmt = $conn->query("SELECT * FROM livros");
$livros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Livros</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .actions-column {
            width: 200px; /* Ajuste para a largura necessária */
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-family: 'Roboto', sans-serif;
            border-radius: 15px 15px 0 0;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Gerenciar Livros</h1>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Adicionar Novo Livro</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="autor">Autor</label>
                        <input type="text" id="autor" name="autor" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="ano">Ano</label>
                        <input type="number" id="ano" name="ano" class="form-control" required>
                    </div>
                    <button type="submit" name="adicionar" class="btn btn-primary">Adicionar Livro</button>
                </form>
            </div>
        </div>

        <!-- Formulário para editar livros -->
        <?php if (isset($livro)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Editar Livro</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="id" value="<?php echo $livro['id']; ?>">
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" value="<?php echo htmlspecialchars($livro['titulo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="autor">Autor</label>
                        <input type="text" id="autor" name="autor" class="form-control" value="<?php echo htmlspecialchars($livro['autor']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ano">Ano</label>
                        <input type="number" id="ano" name="ano" class="form-control" value="<?php echo htmlspecialchars($livro['ano']); ?>" required>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="submit" name="atualizar" class="btn btn-primary">Atualizar Livro</button>
                        <a href="gerenciar_livros.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela de livros -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Livros</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Ano</th>
                            <th class="actions-column">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livros as $livro): ?>
                        <tr>
                            <td><?php echo $livro['id']; ?></td>
                            <td><?php echo $livro['titulo']; ?></td>
                            <td><?php echo $livro['autor']; ?></td>
                            <td><?php echo $livro['ano']; ?></td>
                            <td class="actions-column">
                                <div class="btn-group" role="group">
                                    <a href="?edit=<?php echo $livro['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="?delete=<?php echo $livro['id']; ?>" class="btn btn-danger btn-sm">Excluir</a>
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
