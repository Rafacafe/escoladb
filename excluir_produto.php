<?php
include 'database.php';

if (isset($_GET['id'])) {
    $id_produto = intval($_GET['id']);

    // Verificar se o produto existe
    $sql_verifica = "SELECT * FROM produtos WHERE id_produto = $id_produto";
    $resultado = $conexao->query($sql_verifica);
    if ($resultado->num_rows == 0) {
        header('Location: cadastro_produto.php?erro=produto_nao_encontrado');
        exit();
    }

    // Inicia uma transação para segurança
    $conexao->begin_transaction();

    try {
        // 1️⃣ Exclui movimentações relacionadas a este produto
        $sql_mov = "DELETE FROM movimentacoes WHERE id_produto = $id_produto";
        $conexao->query($sql_mov);

        // 2️⃣ Exclui o produto da tabela principal
        $sql_produto = "DELETE FROM produtos WHERE id_produto = $id_produto";
        $conexao->query($sql_produto);

        // 3️⃣ Confirma as exclusões
        $conexao->commit();

        // Redireciona de volta com mensagem de sucesso
        header('Location: cadastro_produto.php?sucesso=produto_excluido');
        exit();

    } catch (Exception $e) {
        // Caso algo dê errado, desfaz as mudanças
        $conexao->rollback();
        echo "❌ Erro ao excluir produto: " . $e->getMessage();
    }
} else {
    echo "❌ ID do produto não informado.";
}
?>