<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include 'database.php';

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'];
$usuario_tipo = $_SESSION['usuario_tipo'];

// Buscar estatísticas
$sql_total_produtos = "SELECT COUNT(*) as total FROM produtos";
$result_total_produtos = $conexao->query($sql_total_produtos);
$total_produtos = $result_total_produtos->fetch_assoc()['total'];

$sql_estoque_baixo = "SELECT COUNT(*) as total FROM produtos WHERE quantidade <= quantidade_minima";
$result_estoque_baixo = $conexao->query($sql_estoque_baixo);
$estoque_baixo = $result_estoque_baixo->fetch_assoc()['total'];

$sql_total_movimentacoes = "SELECT COUNT(*) as total FROM movimentacoes";
$result_total_movimentacoes = $conexao->query($sql_total_movimentacoes);
$total_movimentacoes = $result_total_movimentacoes->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Sistema de Estoque</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .btn-sair {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-left: auto;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #333;
        }
        
        .menu-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .menu-card h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .menu-card p {
            color: #666;
        }
        
        .alerta-estoque {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <div class="user-info">
                <h2>Bem-vindo, <?php echo $usuario_nome; ?>!</h2>
                <span>(<?php echo $usuario_tipo; ?>)</span>
            </div>
            <a href="sair.php" class="btn-sair">Sair</a>
        </header>

        <?php if ($estoque_baixo > 0): ?>
            <div class="alerta-estoque">
                ⚠️ <strong>Alerta:</strong> <?php echo $estoque_baixo; ?> produto(s) com estoque baixo!
            </div>
        <?php endif; ?>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Total de Produtos</h3>
                <div class="stat-number"><?php echo $total_produtos; ?></div>
            </div>
            <div class="stat-card">
                <h3>Estoque Baixo</h3>
                <div class="stat-number" style="color: #dc3545;"><?php echo $estoque_baixo; ?></div>
            </div>
            <div class="stat-card">
                <h3>Movimentações</h3>
                <div class="stat-number"><?php echo $total_movimentacoes; ?></div>
            </div>
        </div>

        <div class="menu-container">
            <a href="cadastro_produto.php" class="menu-card">
                <h3>📦 Cadastro de Produtos</h3>
                <p>Cadastrar, editar e gerenciar produtos do estoque</p>
            </a>
            
            <a href="gestao_estoque.php" class="menu-card">
                <h3>📊 Gestão de Estoque</h3>
                <p>Controlar entradas e saídas de produtos</p>
            </a>
            
            <a href="relatorios.php" class="menu-card">
                <h3>📈 Relatórios</h3>
                <p>Visualizar relatórios e estatísticas</p>
            </a>
        </div>
    </div>
</body>
</html>