<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$erro = '';
if (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'senha_incorreta':
            $erro = "Senha incorreta!";
            break;
        case 'email_nao_encontrado':
            $erro = "E-mail não encontrado!";
            break;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema de Estoque Escolar</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Acesso ao Sistema</h2>
        <?php if (!empty($erro)): ?>
            <div class="erro"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="post" action="processar_login.php">
            <div class="form-group">
                <label>E-mail:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Senha:</label>
                <input type="password" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>