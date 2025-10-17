<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include 'database.php';

if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'produto_excluido') {
    $mensagem = "Produto excluído com sucesso!";
    $tipo_mensagem = 'sucesso';
}

if (isset($_GET['erro']) && $_GET['erro'] == 'produto_nao_encontrado') {
    $mensagem = "Erro: Produto não encontrado!";
    $tipo_mensagem = 'erro';
}

$mensagem = '';
$tipo_mensagem = '';

// Processar cadastro de novo produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_produto'])) {
    $nome = $conexao->real_escape_string($_POST['nome']);
    $descricao = $conexao->real_escape_string($_POST['descricao']);
    $categoria = $conexao->real_escape_string($_POST['categoria']);
    $quantidade = intval($_POST['quantidade']);
    $quantidade_minima = intval($_POST['quantidade_minima']);
    $preco_custo = floatval($_POST['preco_custo']);
    $preco_venda = floatval($_POST['preco_venda']);
    $id_usuario = $_SESSION['usuario_id'];

    // Validações
    $erros = [];
    if (empty($nome)) $erros[] = "Nome do produto é obrigatório";
    if ($quantidade < 0) $erros[] = "Quantidade não pode ser negativa";
    if ($quantidade_minima < 0) $erros[] = "Quantidade mínima não pode ser negativa";
    if ($preco_custo < 0) $erros[] = "Preço de custo não pode ser negativo";
    if ($preco_venda < 0) $erros[] = "Preço de venda não pode ser negativo";
    if ($preco_venda < $preco_custo) $erros[] = "Preço de venda não pode ser menor que o preço de custo";

    if (empty($erros)) {
        $sql = "INSERT INTO produtos (nome, descricao, categoria, quantidade, quantidade_minima, preco_custo, preco_venda, id_usuario_cadastro) 
                VALUES ('$nome', '$descricao', '$categoria', $quantidade, $quantidade_minima, $preco_custo, $preco_venda, $id_usuario)";
        
        if ($conexao->query($sql)) {
            $mensagem = "Produto cadastrado com sucesso!";
            $tipo_mensagem = 'sucesso';
        } else {
            $mensagem = "Erro ao cadastrar produto: " . $conexao->error;
            $tipo_mensagem = 'erro';
        }
    } else {
        $mensagem = implode("<br>", $erros);
        $tipo_mensagem = 'erro';
    }
}

// Processar busca
$termo_busca = '';
$produtos = [];
$sql_busca = "SELECT * FROM produtos";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar'])) {
    $termo_busca = $conexao->real_escape_string($_POST['termo_busca']);
    if (!empty($termo_busca)) {
        $sql_busca .= " WHERE nome LIKE '%$termo_busca%' OR descricao LIKE '%$termo_busca%' OR categoria LIKE '%$termo_busca%'";
    }
}

$resultado = $conexao->query($sql_busca);
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $produtos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Produtos - Sistema de Estoque</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="cadastro_produto.css">
</head>
<body>
    <div class="container">
        <header>
            <h2>Cadastro de Produtos</h2>
            <a href="index.php" class="btn-voltar">← Voltar ao Dashboard</a>
        </header>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <!-- Seção de Cadastro -->
        <div class="form-section">
            <h3>Cadastrar Novo Produto</h3>
            <form method="post" action="cadastro_produto.php">
                <div class="grid-form">
                    <div class="form-group">
                        <label for="nome">Nome do Produto *</label>
                        <input type="text" id="nome" name="nome" required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <input type="text" id="categoria" name="categoria" maxlength="50">
                    </div>
                    
                    <div class="form-group">
                        <label for="quantidade">Quantidade atual no estoque</label>
                        <input type="number" id="quantidade" name="quantidade" value="0" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="quantidade_minima">Quantidade Mínima</label>
                        <input type="number" id="quantidade_minima" name="quantidade_minima" value="5" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="preco_custo">Preço de Custo (R$)</label>
                        <input type="number" id="preco_custo" name="preco_custo" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="preco_venda">Preço de Venda (R$)</label>
                        <input type="number" id="preco_venda" name="preco_venda" step="0.01" min="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3" maxlength="500"></textarea>
                </div>
                
                <button type="submit" name="cadastrar_produto" class="btn btn-primary">Cadastrar Produto</button>
            </form>
        </div>

        <!-- Seção de Busca -->
        <div class="busca-section">
            <h3>Buscar Produtos</h3>
            <form method="post" action="cadastro_produto.php" class="busca-form">
                <input type="text" name="termo_busca" placeholder="Digite o nome, descrição ou categoria..." value="<?php echo htmlspecialchars($termo_busca); ?>">
                <button type="submit" name="buscar" class="btn btn-busca">Buscar</button>
            </form>
        </div>

        <!-- Seção de Listagem -->
        <div class="lista-section">
            <h3>Produtos Cadastrados (<?php echo count($produtos); ?>)</h3>
            
            <?php if (count($produtos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Estoque</th>
                            <th>Mínimo</th>
                            <th>Preço Custo</th>
                            <th>Preço Venda</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <?php 
                            $classe_estoque = '';
                            if ($produto['quantidade'] <= $produto['quantidade_minima']) {
                                $classe_estoque = 'estoque-baixo';
                            }
                            ?>
                            <tr class="<?php echo $classe_estoque; ?>">
                                <td><?php echo $produto['id_produto']; ?></td>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td><?php echo htmlspecialchars($produto['categoria']); ?></td>
                                <td><?php echo $produto['quantidade']; ?></td>
                                <td><?php echo $produto['quantidade_minima']; ?></td>
                                <td>R$ <?php echo number_format($produto['preco_custo'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($produto['preco_venda'], 2, ',', '.'); ?></td>
                                <td class="acoes">
                                    <a href="editar_produto.php?id=<?php echo $produto['id_produto']; ?>" class="btn-editar">Editar</a>
                                    <a href="excluir_produto.php?id=<?php echo $produto['id_produto']; ?>" 
                                       class="btn-excluir"
                                       onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum produto cadastrado.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>