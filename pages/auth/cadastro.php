<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Acadêmico - Cadastro</title>
    <link rel="stylesheet" href="../../css/cadastro.css">
    <script src="../../js/jquery.js"></script>
    <script src="../../js/validate.js"></script>
    <script src="../../js/valida_form.js"></script>
    
    <style>
        input.error {
            border: 1px solid red;
            color: red;
        }

        label.error {
            color: red;
            margin-top: -5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <img src="../../img/logo.png" alt="Logo do Sistema" class="logo">
        <h1>Cadastro de Usuário</h1>

        <form action="../../controllers/processa_cadastro.php" method="POST" id="cadastro">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>

            <button type="submit">Cadastrar</button>
        </form>

        <p>Já tem conta? <a href="login.php">Faça login aqui</a>.</p>
    </div>
</body>
</html>
