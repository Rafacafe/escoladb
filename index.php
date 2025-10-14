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
    <link rel="stylesheet" type="text/css" href="index.css">
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Estoque</title>
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
                <h3>Cadastro de Produtos</h3>
                <p>Cadastrar, editar e gerenciar produtos do estoque</p>
            </a>
            
            <a href="gestao_estoque.php" class="menu-card">
                <h3>Gestão de Estoque</h3>
                <p>Controlar entradas e saídas de produtos</p>
            </a>
        
        </div>
    </div>
</body>
</html>