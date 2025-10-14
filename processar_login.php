<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conexao->real_escape_string($_POST['email']);
    $senha = $conexao->real_escape_string($_POST['senha']);
    
    $sql = "SELECT id_usuario, nome, senha, tipo FROM usuarios WHERE email = '$email'";
    $resultado = $conexao->query($sql);
    
    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        if ($senha === $usuario['senha']) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            header('Location: index.php');
            exit();
        } else {
            header('Location: login.php?erro=senha_incorreta');
            exit();
        }
    } else {
        header('Location: login.php?erro=email_nao_encontrado');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>