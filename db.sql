CREATE DATABASE IF NOT EXISTS plataforma;
USE plataforma;

CREATE TABLE jogo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_jogo VARCHAR(100) NOT NULL,
    descricao VARCHAR(300),
    desenvolvedor VARCHAR(100),
    data_lancamento DATE,
    idioma_primario VARCHAR(100),
    classificacao_etaria VARCHAR(100), 
    generos VARCHAR(100),
    modo_jogo VARCHAR(100),
    capa_jogo VARCHAR(500),
    plataforma VARCHAR(100),
    preco DECIMAL(10,2)
);
