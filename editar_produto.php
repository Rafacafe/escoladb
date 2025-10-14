<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include 'database.php';

// Implementar edição de produto similar ao cadastro
// Buscar produto por ID, preencher formulário e atualizar
?>

<!-- Interface similar ao cadastro_produto.php mas para edição -->