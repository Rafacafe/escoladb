-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS escola_db;
USE escola_db;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'usuario') DEFAULT 'usuario',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50),
    quantidade INT DEFAULT 0,
    quantidade_minima INT DEFAULT 5,
    preco_custo DECIMAL(10,2),
    preco_venda DECIMAL(10,2),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario_cadastro INT,
    FOREIGN KEY (id_usuario_cadastro) REFERENCES usuarios(id_usuario)
);

-- Tabela de Movimentações de Estoque
CREATE TABLE IF NOT EXISTS movimentacoes (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATE NOT NULL,
    observacao TEXT,
    id_usuario INT,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Inserção de dados de exemplo
INSERT INTO usuarios (nome, email, senha, tipo) VALUES 
('Administrador', 'admin@escola.com', '123456', 'admin'),
('João Silva', 'joao@escola.com', '123456', 'usuario'),
('Maria Santos', 'maria@escola.com', '123456', 'usuario');
