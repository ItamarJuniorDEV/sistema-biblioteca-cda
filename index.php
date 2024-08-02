<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca CDA</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDF8fHNjaG9vbHxlbnwwfHx8fDE2NTkxNzA1MDI&ixlib=rb-1.2.1&q=80&w=1080');
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        h2 {
            font-family: 'Lora', serif;
            font-size: 24px; /* Tamanho menor */
            margin-bottom: 10px; /* Espaçamento menor */
        }
        h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #87cefa; /* Cor do texto */
        }
        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 20px;
            border: 4px solid #87cefa;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .login-container img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid #87cefa;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3), 0 0 15px rgba(255, 255, 255, 0.7);
            background: linear-gradient(to bottom, #fff, #f0f0f0);
        }
        .form-group {
            margin-bottom: 20px; /* Espaçamento entre os campos do formulário */
        }
        label {
            font-weight: 500;
            font-size: 16px;
            color: #333;
        }
        p.contact-info {
            font-family: 'Lora', serif;
            font-size: 16px;
            color: #333;
            margin-top: 20px;
        }
        p.contact-info a {
            color: #87cefa;
            text-decoration: none;
            font-weight: bold;
        }
        p.contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/logocda.jpg" alt="Logomarca da Escola CDA">
        <h2>Biblioteca CDA</h2>
        <h3>Sistema de Gerenciamento de Biblioteca</h3>
        <form action="authenticate.php" method="POST">
            <div class="form-group">
                <label for="username">Nome de Usuário</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        <p class="mt-3">
            <a href="recuperar_senha.php">Esqueceu a senha?</a>
        </p>
        <p class="contact-info">
            Para ajuda, entre em contato com: <a href="mailto:cdajuniorf@gmail.com">cdajuniorf@gmail.com</a>
        </p>
    </div>
</body>
</html>

