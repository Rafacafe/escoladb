<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include 'database.php';

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
        
        .container {
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
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-voltar {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .mensagem {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-section, .busca-section, .lista-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-busca {
            background: #28a745;
            color: white;
        }
        
        .btn-busca:hover {
            background: #218838;
        }
        
        .busca-form {
            display: flex;
            gap: 10px;
        }
        
        .busca-form input {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .estoque-baixo {
            background: #fff3cd !important;
            color: #856404;
        }
        
        .acoes {
            display: flex;
            gap: 5px;
        }
        
        .btn-editar {
            background: #ffc107;
            color: #212529;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .btn-excluir {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .grid-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
    </style>
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