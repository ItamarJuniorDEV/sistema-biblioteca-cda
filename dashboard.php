<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT tipo_usuario, nome_usuario FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuario_id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$tipo_usuario = $usuario['tipo_usuario'];
$nome_usuario = $usuario['nome_usuario'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Principal - Sistema de Gerenciamento de Biblioteca</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2991/2991148.png" type="image/png">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-image: url('assets/img-dashboard.jpg');
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            background-attachment: fixed;
            color: #333;
        }
        .navbar {
            margin-bottom: 20px;
            background-color: #87cefa;
        }
        .navbar-brand, .navbar-nav .nav-link, .navbar-toggler-icon {
            color: #fff;
        }
        .navbar-brand {
            font-family: 'Lora', serif;
            font-size: 24px;
            font-weight: 700;
        }
        .container {
            padding-top: 20px;
        }
        .card {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-body {
            padding: 20px;
        }
        .card-title {
            font-family: 'Lora', serif;
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .card-text {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            color: #555;
        }
        .btn-primary {
            background-color: #87cefa;
            border-color: #87cefa;
        }
        .btn-primary:hover {
            background-color: #6ec1e4;
            border-color: #6ec1e4;
        }
        .logout-btn {
            margin-left: auto;
        }
        .card-img-top {
            height: 150px;
            object-fit: contain;
            margin-top: 20px;
        }
        .tooltip-inner {
            background-color: #87cefa;
            color: #fff;
        }
        .accordion-button {
            font-family: 'Roboto', sans-serif;
        }
        .search-container {
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .search-bar {
            margin-bottom: 10px;
        }
        .info-section {
            margin-top: 40px;
        }
        .footer {
            background-color: #87cefa; 
            color: #333; 
            text-align: center; 
            align-items:center; 
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
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">Biblioteca CDA</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

            </ul>
            <form class="form-inline my-2 my-lg-0 logout-btn" action="logout.php" method="POST">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Logout</button> 
            </form>
        </div>
    </nav>
    
    <div class="container">
        <h1 class="text-center">Bem-vindo ao Sistema de Gerenciamento de Biblioteca</h1>
        <p class="text-center">Olá, <?php echo htmlspecialchars($nome_usuario); ?>!</p>

        <div class="row mt-4">
            <?php if ($tipo_usuario == 'Administrador' || $tipo_usuario == 'Secretaria') : ?>
            <div class="col-md-4">
                <div class="card" data-toggle="tooltip" data-placement="top" title="Gerencie todos os livros no sistema">
                    <img src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png" class="card-img-top" alt="Gerenciar Livros">
                    <div class="card-body">
                        <h5 class="card-title">Gerenciar Livros</h5>
                        <p class="card-text">Adicione, edite e remova livros do acervo.</p>
                        <a href="gerenciar_livros.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($tipo_usuario == 'Administrador') : ?>
            <div class="col-md-4">
                <div class="card" data-toggle="tooltip" data-placement="top" title="Gerencie os usuários do sistema">
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" class="card-img-top" alt="Gerenciar Usuários">
                    <div class="card-body">
                        <h5 class="card-title">Gerenciar Usuários</h5>
                        <p class="card-text">Adicione, edite e remova usuários do sistema.</p>
                        <a href="gerenciar_usuarios.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($tipo_usuario == 'Administrador' || $tipo_usuario == 'Secretaria') : ?>
            <div class="col-md-4">
                <div class="card" data-toggle="tooltip" data-placement="top" title="Controle os empréstimos e devoluções">
                    <img src="https://cdn-icons-png.flaticon.com/512/2919/2919592.png" class="card-img-top" alt="Empréstimos e Devoluções">
                    <div class="card-body">
                        <h5 class="card-title">Empréstimos e Devoluções</h5>
                        <p class="card-text">Gerencie os empréstimos e devoluções de livros.</p>
                        <a href="emprestimos_devolucoes.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="search-container text-center">
            <input id="search" class="form-control search-bar" type="text" placeholder="Pesquisar...">
        </div>

        <div id="accordion" class="info-section">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link accordion-button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Lembrete Importante
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                    Para garantir que todos tenham a oportunidade de ler os livros, por favor, devolva-os dentro do prazo estipulado. A colaboração de todos é essencial para manter nosso acervo disponível para todos os usuários.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Informação Importante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Lembre-se de devolver os livros emprestados dentro do prazo para evitar multas. Em caso de dúvidas, entre em contato com a administração da biblioteca ou consulte o regulamento disponível no site.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer">
    <p>&copy; 2024 Itamar Junior. Todos os direitos reservados.</p>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(document).ready(function() {
            $("#search").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $(".card").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>
