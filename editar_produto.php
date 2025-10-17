<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include 'database.php';

$mensagem = '';
$tipo_mensagem = '';
$produto = null;

// Buscar produto por ID
if (isset($_GET['id'])) {
    $id_produto = intval($_GET['id']);
    $sql = "SELECT * FROM produtos WHERE id_produto = $id_produto";
    $resultado = $conexao->query($sql);
    
    if ($resultado->num_rows == 1) {
        $produto = $resultado->fetch_assoc();
    } else {
        $mensagem = "Produto não encontrado!";
        $tipo_mensagem = 'erro';
    }
} else {
    $mensagem = "ID do produto não informado!";
    $tipo_mensagem = 'erro';
}

// Processar atualização do produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_produto'])) {
    $id_produto = intval($_POST['id_produto']);
    $nome = $conexao->real_escape_string($_POST['nome']);
    $descricao = $conexao->real_escape_string($_POST['descricao']);
    $categoria = $conexao->real_escape_string($_POST['categoria']);
    $quantidade = intval($_POST['quantidade']);
    $quantidade_minima = intval($_POST['quantidade_minima']);
    $preco_custo = floatval($_POST['preco_custo']);
    $preco_venda = floatval($_POST['preco_venda']);

    // Validações
    $erros = [];
    if (empty($nome)) $erros[] = "Nome do produto é obrigatório";
    if ($quantidade < 0) $erros[] = "Quantidade não pode ser negativa";
    if ($quantidade_minima < 0) $erros[] = "Quantidade mínima não pode ser negativa";
    if ($preco_custo < 0) $erros[] = "Preço de custo não pode ser negativo";
    if ($preco_venda < 0) $erros[] = "Preço de venda não pode ser negativo";
    if ($preco_venda < $preco_custo) $erros[] = "Preço de venda não pode ser menor que o preço de custo";

    if (empty($erros)) {
        $sql = "UPDATE produtos SET 
                nome = '$nome',
                descricao = '$descricao',
                categoria = '$categoria',
                quantidade = $quantidade,
                quantidade_minima = $quantidade_minima,
                preco_custo = $preco_custo,
                preco_venda = $preco_venda
                WHERE id_produto = $id_produto";
        
        if ($conexao->query($sql)) {
            $mensagem = "Produto atualizado com sucesso!";
            $tipo_mensagem = 'sucesso';
            
            // Atualizar dados do produto após edição
            $sql = "SELECT * FROM produtos WHERE id_produto = $id_produto";
            $resultado = $conexao->query($sql);
            $produto = $resultado->fetch_assoc();
        } else {
            $mensagem = "Erro ao atualizar produto: " . $conexao->error;
            $tipo_mensagem = 'erro';
        }
    } else {
        $mensagem = implode("<br>", $erros);
        $tipo_mensagem = 'erro';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Produto - Sistema de Estoque</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="cadastro_produto.css">
</head>
<body>
    <div class="container">
        <header>
            <h2>Editar Produto</h2>
            <a href="cadastro_produto.php" class="btn-voltar">← Voltar para Cadastro</a>
        </header>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if ($produto): ?>
        <!-- Seção de Edição -->
        <div class="form-section">
            <h3>Editando: <?php echo htmlspecialchars($produto['nome']); ?></h3>
            <form method="post" action="editar_produto.php">
                <input type="hidden" name="id_produto" value="<?php echo $produto['id_produto']; ?>">
                
                <div class="grid-form">
                    <div class="form-group">
                        <label for="nome">Nome do Produto *</label>
                        <input type="text" id="nome" name="nome" required maxlength="100" 
                               value="<?php echo htmlspecialchars($produto['nome']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <input type="text" id="categoria" name="categoria" maxlength="50"
                               value="<?php echo htmlspecialchars($produto['categoria']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="quantidade">Quantidade atual no estoque</label>
                        <input type="number" id="quantidade" name="quantidade" 
                               value="<?php echo $produto['quantidade']; ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="quantidade_minima">Quantidade Mínima</label>
                        <input type="number" id="quantidade_minima" name="quantidade_minima" 
                               value="<?php echo $produto['quantidade_minima']; ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="preco_custo">Preço de Custo (R$)</label>
                        <input type="number" id="preco_custo" name="preco_custo" step="0.01" min="0"
                               value="<?php echo $produto['preco_custo']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="preco_venda">Preço de Venda (R$)</label>
                        <input type="number" id="preco_venda" name="preco_venda" step="0.01" min="0"
                               value="<?php echo $produto['preco_venda']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3" maxlength="500"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
                </div>
                
                <button type="submit" name="atualizar_produto" class="btn btn-primary">Atualizar Produto</button>
                <a href="cadastro_produto.php" class="btn" style="background: #6c757d; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; margin-left: 10px;">Cancelar</a>
            </form>
        </div>
        <?php else: ?>
            <div class="mensagem erro">
                Não foi possível carregar os dados do produto.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>