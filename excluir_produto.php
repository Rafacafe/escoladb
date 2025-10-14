<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include 'database.php';

if (isset($_GET['id'])) {
    $id_produto = intval($_GET['id']);
    
    // Verificar se existem movimentações para este produto
    $sql_verificar = "SELECT COUNT(*) as total FROM movimentacoes WHERE id_produto = $id_produto";
    $result_verificar = $conexao->query($sql_verificar);
    $total_movimentacoes = $result_verificar->fetch_assoc()['total'];
    
    if ($total_movimentacoes > 0) {
        // Não permitir exclusão se houver movimentações
        header('Location: cadastro_produto.php?erro=produto_com_movimentacoes');
    } else {
        // Excluir produto
        $sql_excluir = "DELETE FROM produtos WHERE id_produto = $id_produto";
        if ($conexao->query($sql_excluir)) {
            header('Location: cadastro_produto.php?sucesso=produto_excluido');
        } else {
            header('Location: cadastro_produto.php?erro=erro_exclusao');
        }
    }
} else {
    header('Location: cadastro_produto.php');
}
exit();
?>