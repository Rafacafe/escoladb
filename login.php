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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .erro {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffcdd2;
        }
    </style>
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