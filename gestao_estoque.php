<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include 'database.php';

$mensagem = '';
$tipo_mensagem = '';

// Buscar produtos para o select
$sql_produtos = "SELECT id_produto, nome, quantidade FROM produtos ORDER BY nome";
$result_produtos = $conexao->query($sql_produtos);
$produtos = [];
while ($row = $result_produtos->fetch_assoc()) {
    $produtos[] = $row;
}

// Processar movimentação
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['realizar_movimentacao'])) {
    $id_produto = intval($_POST['id_produto']);
    $tipo = $conexao->real_escape_string($_POST['tipo']);
    $quantidade = intval($_POST['quantidade']);
    $data_movimentacao = $conexao->real_escape_string($_POST['data_movimentacao']);
    $observacao = $conexao->real_escape_string($_POST['observacao']);
    $id_usuario = $_SESSION['usuario_id'];

    // Validações
    $erros = [];
    if ($id_produto <= 0) $erros[] = "Selecione um produto válido";
    if ($quantidade <= 0) $erros[] = "Quantidade deve ser maior que zero";
    if (empty($data_movimentacao)) $erros[] = "Data da movimentação é obrigatória";

    // Verificar estoque para saída
    if ($tipo == 'saida') {
        $sql_estoque = "SELECT quantidade FROM produtos WHERE id_produto = $id_produto";
        $result_estoque = $conexao->query($sql_estoque);
        $estoque_atual = $result_estoque->fetch_assoc()['quantidade'];
        
        if ($quantidade > $estoque_atual) {
            $erros[] = "Quantidade de saída maior que estoque disponível";
        }
    }

    if (empty($erros)) {
        // Iniciar transação
        $conexao->begin_transaction();

        try {
            // Inserir movimentação
            $sql_movimentacao = "INSERT INTO movimentacoes (id_produto, tipo, quantidade, data_movimentacao, observacao, id_usuario) 
                                VALUES ($id_produto, '$tipo', $quantidade, '$data_movimentacao', '$observacao', $id_usuario)";
            
            if (!$conexao->query($sql_movimentacao)) {
                throw new Exception("Erro ao registrar movimentação: " . $conexao->error);
            }

            // Atualizar estoque do produto
            $operador = ($tipo == 'entrada') ? '+' : '-';
            $sql_estoque = "UPDATE produtos SET quantidade = quantidade $operador $quantidade WHERE id_produto = $id_produto";
            
            if (!$conexao->query($sql_estoque)) {
                throw new Exception("Erro ao atualizar estoque: " . $conexao->error);
            }

            // Verificar estoque mínimo após movimentação
            $sql_verificar_estoque = "SELECT quantidade, quantidade_minima, nome FROM produtos WHERE id_produto = $id_produto";
            $result_verificar = $conexao->query($sql_verificar_estoque);
            $produto_info = $result_verificar->fetch_assoc();

            $conexao->commit();
            
            $mensagem = "Movimentação realizada com sucesso!";
            $tipo_mensagem = 'sucesso';

            // Alerta de estoque baixo
            if ($produto_info['quantidade'] <= $produto_info['quantidade_minima']) {
                $mensagem .= "<br>⚠️ <strong>Alerta:</strong> Estoque do produto '{$produto_info['nome']}' está abaixo do mínimo!";
            }

        } catch (Exception $e) {
            $conexao->rollback();
            $mensagem = $e->getMessage();
            $tipo_mensagem = 'erro';
        }
    } else {
        $mensagem = implode("<br>", $erros);
        $tipo_mensagem = 'erro';
    }
}

// Listar produtos ordenados alfabeticamente usando Bubble Sort
$sql_lista_produtos = "SELECT p.*, 
                       (SELECT COUNT(*) FROM movimentacoes m WHERE m.id_produto = p.id_produto) as total_movimentacoes
                       FROM produtos p";
$result_lista = $conexao->query($sql_lista_produtos);
$produtos_ordenados = [];
while ($row = $result_lista->fetch_assoc()) {
    $produtos_ordenados[] = $row;
}

// Aplicar Bubble Sort para ordenação alfabética
$n = count($produtos_ordenados);
for ($i = 0; $i < $n; $i++) {
    for ($j = 0; $j < $n - $i - 1; $j++) {
        if (strcmp($produtos_ordenados[$j]['nome'], $produtos_ordenados[$j + 1]['nome']) > 0) {
            // Trocar posições
            $temp = $produtos_ordenados[$j];
            $produtos_ordenados[$j] = $produtos_ordenados[$j + 1];
            $produtos_ordenados[$j + 1] = $temp;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestão de Estoque - Sistema de Estoque</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="gestao_estoque.css">
</head>
<body>
    <div class="container">
        <header>
            <h2>Gestão de Estoque</h2>
            <a href="index.php" class="btn-voltar">← Voltar ao Dashboard</a>
        </header>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <!-- Seção de Movimentação -->
        <div class="movimentacao-section">
            <h3>Realizar Movimentação</h3>
            
            <form method="post" action="gestao_estoque.php">
                <div class="grid-form">
                    <div class="form-group">
                        <label for="id_produto">Produto *</label>
                        <select id="id_produto" name="id_produto" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?php echo $produto['id_produto']; ?>">
                                    <?php echo htmlspecialchars($produto['nome']); ?> (Estoque: <?php echo $produto['quantidade']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="data_movimentacao">Data da Movimentação *</label>
                        <input type="date" id="data_movimentacao" name="data_movimentacao" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantidade">Quantidade *</label>
                        <input type="number" id="quantidade" name="quantidade" min="1" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tipo de Movimentação *</label>
                    <div class="tipo-movimentacao">
                        <label class="tipo-option">
                            <input type="radio" name="tipo" value="entrada" checked> 
                            Entrada (Adicionar ao estoque)
                        </label>
                        <label class="tipo-option">
                            <input type="radio" name="tipo" value="saida"> 
                            Saída (Remover do estoque)
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacao">Observação</label>
                    <textarea id="observacao" name="observacao" rows="3" maxlength="500" placeholder="Motivo da movimentação..."></textarea>
                </div>
                
                <button type="submit" name="realizar_movimentacao" class="btn btn-primary">Registrar Movimentação</button>
            </form>
        </div>

        <!-- Seção de Listagem -->
        <div class="lista-section">
            <div class="info-bubble">
                <strong>Ordenação alfabética dos produtos usando algoritmo Bubble Sort</strong>
            </div>
            
            <h3>Produtos em Estoque (<?php echo count($produtos_ordenados); ?>)</h3>
            
            <?php if (count($produtos_ordenados) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome do Produto</th>
                            <th>Categoria</th>
                            <th>Estoque Atual</th>
                            <th>Estoque Mínimo</th>
                            <th>Status</th>
                            <th>Movimentações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos_ordenados as $index => $produto): ?>
                            <?php 
                            $classe_estoque = '';
                            $status = 'Normal';
                            
                            if ($produto['quantidade'] == 0) {
                                $classe_estoque = 'estoque-critico';
                                $status = 'Esgotado';
                            } elseif ($produto['quantidade'] <= $produto['quantidade_minima']) {
                                $classe_estoque = 'estoque-baixo';
                                $status = 'Estoque Baixo';
                            }
                            ?>
                            <tr class="<?php echo $classe_estoque; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td><?php echo htmlspecialchars($produto['categoria']); ?></td>
                                <td><strong><?php echo $produto['quantidade']; ?></strong></td>
                                <td><?php echo $produto['quantidade_minima']; ?></td>
                                <td><?php echo $status; ?></td>
                                <td><?php echo $produto['total_movimentacoes']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum produto cadastrado.</p>
            <?php endif; ?>
            </div> 