<?php
$hostname = "127.0.0.1";
$user = "root";
$password = "";
$database = "escola_db";

$conexao = new mysqli($hostname, $user, $password, $database);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>