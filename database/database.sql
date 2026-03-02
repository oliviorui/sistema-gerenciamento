DROP DATABASE IF EXISTS sistema_academico;
CREATE DATABASE sistema_academico;
USE sistema_academico;

-- =========================
-- TURMAS
-- =========================
CREATE TABLE turmas (
  id_turma INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(60) NOT NULL UNIQUE
);

-- =========================
-- USUÁRIOS (3 perfis)
-- Estudante pertence a UMA turma (id_turma)
-- =========================
CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  email VARCHAR(80) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('estudante','docente','admin') NOT NULL DEFAULT 'estudante',
  id_turma INT NULL,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE SET NULL
);

-- =========================
-- DISCIPLINAS
-- =========================
CREATE TABLE disciplinas (
  id_disciplina INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE
);

-- =========================
-- ATRIBUIÇÕES (docente–turma–disciplina)
-- =========================
CREATE TABLE atribuicoes (
  id_atribuicao INT AUTO_INCREMENT PRIMARY KEY,
  id_docente INT NOT NULL,
  id_turma INT NOT NULL,
  id_disciplina INT NOT NULL,
  data_atribuicao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_docente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE CASCADE,
  FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina) ON DELETE CASCADE,

  UNIQUE KEY uq_atribuicao (id_docente, id_turma, id_disciplina)
);

-- =========================
-- ACTIVIDADES (sempre ligadas a uma atribuição)
-- =========================
CREATE TABLE atividades (
  id_atividade INT AUTO_INCREMENT PRIMARY KEY,
  id_atribuicao INT NOT NULL,
  titulo VARCHAR(120) NOT NULL,
  descricao TEXT,
  data_limite DATE DEFAULT NULL,
  data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_atribuicao) REFERENCES atribuicoes(id_atribuicao) ON DELETE CASCADE
);

-- =========================
-- ENTREGAS (submissões)
-- =========================
CREATE TABLE entregas (
  id_entrega INT AUTO_INCREMENT PRIMARY KEY,
  id_atividade INT NOT NULL,
  id_estudante INT NOT NULL,

  arquivo_nome_original VARCHAR(255) NOT NULL,
  arquivo_nome_servidor VARCHAR(255) NOT NULL,
  arquivo_mime VARCHAR(100) DEFAULT NULL,

  comentario TEXT,
  status ENUM('Pendente','Avaliado') NOT NULL DEFAULT 'Pendente',

  data_entrega DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  nota DECIMAL(5,2) DEFAULT NULL,
  feedback TEXT DEFAULT NULL,

  FOREIGN KEY (id_atividade) REFERENCES atividades(id_atividade) ON DELETE CASCADE,
  FOREIGN KEY (id_estudante) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,

  UNIQUE KEY uq_entrega (id_atividade, id_estudante)
);

-- =========================
-- NOTAS (docente lança para estudante numa disciplina)
-- Validamos permissão via backend comparando com atribuicoes
-- =========================
CREATE TABLE notas (
  id_nota INT AUTO_INCREMENT PRIMARY KEY,
  id_docente INT NOT NULL,
  id_estudante INT NOT NULL,
  id_disciplina INT NOT NULL,

  tipo_avaliacao VARCHAR(60) NOT NULL,
  nota DECIMAL(5,2) NOT NULL,
  data_avaliacao DATE DEFAULT NULL,
  data_registo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_docente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_estudante) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina) ON DELETE CASCADE
);

-- =========================
-- LOGS
-- =========================
CREATE TABLE logs_atividades (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT DEFAULT NULL,
  data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  descricao VARCHAR(255) NOT NULL,
  tipo_actividade VARCHAR(50) NOT NULL,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- =========================
-- REMEMBER ME (TOKENS)
-- =========================
CREATE TABLE user_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  INDEX (token_hash)
);

-- =========================
-- DADOS DEMO
-- senha hash demo (mesma para todos)
-- =========================
INSERT INTO turmas (nome) VALUES
('Turma A'),
('Turma B');

INSERT INTO disciplinas (nome) VALUES
('Matemática'),
('Programação Web'),
('Redes');

-- =========================
-- DADOS DEMO (credenciais conhecidas)
-- =========================
-- admin@sistema.com / Admin123
-- docente@sistema.com / Docente123
-- estudante1@sistema.com / Estudante123
-- estudante2@sistema.com / Estudante123
-- estudante3@sistema.com / Estudante123
-- =========================
INSERT INTO usuarios (nome, email, senha, tipo, id_turma) VALUES
('Admin',        'admin@sistema.com',      '$2y$10$51nUV.FH1CEQD6mFLUyPMeB1Pl4ts8YEHzfFCd2rqc2hO1srTIus2', 'admin',     NULL),
('Docente 1',    'docente@sistema.com',    '$2y$10$O5kTt0ktDtebhK4e5qCjMOHZPnVtfkQFm0Cfd1mkvR3XsfyqjBh1K', 'docente',   NULL),
('Estudante A1', 'estudante1@sistema.com', '$2y$10$cgtst0M5bL3ZtGJ1eyJikuK50.2kNt0Np9WCX8u4SVUG.nlu5DiRS', 'estudante', 1),
('Estudante A2', 'estudante2@sistema.com', '$2y$10$cgtst0M5bL3ZtGJ1eyJikuK50.2kNt0Np9WCX8u4SVUG.nlu5DiRS', 'estudante', 1),
('Estudante B1', 'estudante3@sistema.com', '$2y$10$cgtst0M5bL3ZtGJ1eyJikuK50.2kNt0Np9WCX8u4SVUG.nlu5DiRS', 'estudante', 2);

-- Atribuições: Docente 1 -> Turma A (Matemática e Programação Web)
INSERT INTO atribuicoes (id_docente, id_turma, id_disciplina) VALUES
(2, 1, 1),
(2, 1, 2);
