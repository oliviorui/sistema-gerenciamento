DROP DATABASE sistema_academico;

CREATE DATABASE sistema_academico;
USE sistema_academico;

-- =========================
-- TABELA DE USUÁRIOS
-- =========================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,

    -- NOVO: 3 actores no sistema
    tipo ENUM('estudante', 'funcionario', 'admin') NOT NULL DEFAULT 'estudante',

    -- Melhor: já cria automaticamente a data
    data_cadastro DATE NOT NULL DEFAULT (CURDATE()),

    PRIMARY KEY (id_usuario)
);

-- =========================
-- TABELA DE DISCIPLINAS
-- =========================
CREATE TABLE disciplinas (
    id_disciplina INT AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    codigo VARCHAR(5) NOT NULL,
    descricao TEXT,

    PRIMARY KEY (id_disciplina)
);

-- =========================
-- TABELA DE NOTAS
-- =========================
CREATE TABLE notas (
    id_nota INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_disciplina INT NOT NULL,
    nota DECIMAL(4,2),
    data_avaliacao DATE,
    tipo_avaliacao ENUM('Prova', 'Trabalho', 'Exame'),

    PRIMARY KEY (id_nota),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina) ON DELETE CASCADE
);

-- =========================
-- TABELA DE LOGS
-- =========================
CREATE TABLE logs_atividades (
    id_log INT AUTO_INCREMENT,
    id_usuario INT,
    data_hora DATETIME,
    descricao TEXT,
    tipo_actividade ENUM('Login', 'Logout', 'Cadastro', 'Registro', 'Admin'),

    PRIMARY KEY (id_log),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- =========================
-- TOKENS (remember-me)
-- =========================
CREATE TABLE IF NOT EXISTS user_tokens (
    id_token INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_token_hash (token_hash),
    INDEX idx_user_tokens_usuario (id_usuario),
    CONSTRAINT fk_user_tokens_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);



-- =========================
-- DISCIPLINAS PADRÃO
-- =========================
INSERT INTO disciplinas (nome, codigo, descricao) VALUES
('Algoritmos e Estruturas de Dados', 'AED01', 'Estudo de algoritmos, estruturas de dados e complexidade computacional.'),
('Base de Dados', 'BD02', 'Modelagem, normalização e implementação de bancos de dados relacionais.'),
('Programação Orientada a Objetos', 'POO03', 'Conceitos de programação orientada a objetos usando linguagens como Java e Python.'),
('Redes de Computadores', 'RC04', 'Fundamentos de redes, protocolos, segurança e administração de redes.'),
('Engenharia de Software', 'ES05', 'Processos de desenvolvimento de software, metodologias ágeis e boas práticas.'),
('Sistemas Operacionais', 'SO06', 'Princípios de sistemas operacionais, gerenciamento de memória, processos e arquivos.'),
('Desenvolvimento Web', 'DW07', 'Criação de aplicações web usando HTML, CSS, JavaScript e frameworks.'),
('Segurança da Informação', 'SI08', 'Conceitos de segurança, criptografia, ataques cibernéticos e proteção de dados.'),
('Ética e Legislação em TI', 'EL13', 'Aspectos éticos e legais relacionados ao uso da tecnologia da informação.'),
('Gestão de Projetos de TI', 'GP14', 'Metodologias para planejamento, execução e monitoramento de projetos de tecnologia.');

CREATE TABLE IF NOT EXISTS atividades (
    id_atividade INT AUTO_INCREMENT PRIMARY KEY,
    id_disciplina INT NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    descricao TEXT NULL,
    data_limite DATE NULL,
    criado_por INT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ativ_disc (id_disciplina),
    INDEX idx_ativ_criado_por (criado_por),
    CONSTRAINT fk_ativ_disc
        FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina)
        ON DELETE CASCADE,
    CONSTRAINT fk_ativ_criado_por
        FOREIGN KEY (criado_por) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS entregas (
    id_entrega INT AUTO_INCREMENT PRIMARY KEY,
    id_atividade INT NOT NULL,
    id_estudante INT NOT NULL,
    comentario TEXT NULL,
    arquivo_nome_original VARCHAR(255) NULL,
    arquivo_nome_servidor VARCHAR(255) NULL,
    arquivo_mime VARCHAR(120) NULL,
    arquivo_tamanho INT NULL,
    data_entrega DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    status ENUM('Pendente','Aprovado','Rejeitado') NOT NULL DEFAULT 'Pendente',
    feedback TEXT NULL,
    nota DECIMAL(5,2) NULL,
    avaliado_por INT NULL,
    avaliado_em DATETIME NULL,

    UNIQUE KEY uq_entrega (id_atividade, id_estudante),
    INDEX idx_entregas_atividade (id_atividade),
    INDEX idx_entregas_estudante (id_estudante),

    CONSTRAINT fk_ent_atividade
        FOREIGN KEY (id_atividade) REFERENCES atividades(id_atividade)
        ON DELETE CASCADE,
    CONSTRAINT fk_ent_estudante
        FOREIGN KEY (id_estudante) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_ent_avaliado_por
        FOREIGN KEY (avaliado_por) REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
);

-- =========================
-- USUÁRIO ADMIN PADRÃO
-- senha: Admin123  (já criptografada)
-- =========================
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@sistema.com', '$2y$10$TwQm5ZRkaoA8Z7Q3NMMb/u/8tGN4nHylBTdgrRUc/UO8.UFHe5StK', 'admin');

-- =========================
-- (Opcional) FUNCIONÁRIO PADRÃO
-- senha: Funcionario123 (você pode mudar depois)
-- hash pronto (pode gerar outro se quiser)
-- =========================
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Funcionário', 'funcionario@sistema.com', '$2y$10$Sxn1Iiavnf4atykiAJrks.8UXXFSBrehmPA1PRpEPuJTOdi.FaaZa', 'funcionario');
