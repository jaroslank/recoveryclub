<?php require_once "functions.php";?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de acesso ao site</title>
    <link rel="stylesheet" href="docs/login.css">
</head>
<body>
    <div class="main-login">
        <div class="left-login">
            <h1>PigHemp®<br>Painel Administrativo</h1>
            <img src="img/computer-troubleshooting-animate.svg" class="left-login-img" alt="Computador animado">
        </div>
        <div class="right-login">
            <form action="" method="post" class="card-login">
                <h1>LOGIN</h1>
                <div class="textfield">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Informe seu e-mail" required>
                </div>

                <div class="textfield">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" placeholder="Digite sua senha" required>
                </div>
                <input class="btn-login" type="submit" name="acessar" value="Acessar">
            </form>
        </div>
    </div>
    <?php 
        if (isset($_POST['acessar'])) {
            login($connect);
        }
    ?>
</body>
</html>