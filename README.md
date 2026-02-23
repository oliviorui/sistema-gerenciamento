# 📊 Sistema de Gerenciamento

Sistema de gerenciamento desenvolvido em **PHP**, com foco em autenticação de usuários, organização de dados e visualização de informações através de um painel administrativo.  
O projeto simula um sistema real de gestão, integrando backend, frontend e banco de dados.

---

## 🧾 Descrição do Projeto

Sistema de gerenciamento criado em PHP para controlar acessos, armazenar informações e apresentar dados em um **dashboard administrativo**.  
Inclui autenticação (login e cadastro), registo de dados, logs e visualização gráfica, sendo ideal como projeto de estudo e portfólio backend/fullstack básico.

---

## 🚀 Funcionalidades

- Sistema de autenticação:
  - Cadastro de usuários
  - Login e logout
- Painel administrativo (dashboard)
- Registo e listagem de dados
- Logs do sistema
- Visualização gráfica de dados
- Separação entre páginas públicas e área autenticada
- Autenticação segura (session + remember token)
- CSRF Protection
- Roles: Admin, Funcionário, Estudante
- Gestão de disciplinas
- Lançamento de notas
- Sistema de atividades e entregas
- Upload seguro de arquivos
- Controle de permissões

---

## 🛠️ Tecnologias Utilizadas

- **PHP**
- **MySQL**
- **HTML5**
- **CSS3**
- **JavaScript**
- **Chart.js**
- **jQuery**

---

## 📂 Estrutura do Projeto

```bash
sistema_gerenciamento/
│
├── config/          # Configuração de conexão com o banco
├── controllers/     # Lógica de autenticação e processamento
├── css/             # Estilos da aplicação
├── js/              # Scripts e validações
├── img/             # Imagens e assets
├── dados/           # Endpoints de obtenção de dados
├── database/        # Script SQL do banco de dados
├── pages/
│   ├── auth/        # Login e cadastro
│   └── logged/      # Área autenticada (dashboard, logs)
├── index.php        # Página inicial
└── README.md
```

---

## ▶️ Como Executar o Projeto

### Pré-requisitos
- PHP >= 7.x
- Servidor local (XAMPP, WAMP, Laragon, etc.)
- MySQL

### Passos

1. Clone o repositório:
```bash
git clone https://github.com/oliviorui/sistema-gerenciamento.git
```

2. Coloque o projeto no servidor local:
```bash
htdocs/sistema-gerenciamento
```

3. Configure o banco de dados:
- Crie um banco MySQL
- Importe o ficheiro:
```bash
database/database.sql
```

4. Ajuste a conexão em:
```bash
config/conexao.php
```

5. Acesse no navegador:
```text
http://localhost/sistema-gerenciamento
```

---

## 🎯 Objetivo do Projeto

- Praticar desenvolvimento **PHP com banco de dados**
- Implementar autenticação de usuários
- Criar um painel administrativo funcional
- Simular um sistema de gerenciamento real
- Compor portfólio de backend/fullstack inicial

---

## 🧭 Possíveis Melhorias Futuras

- Hash de senhas mais robusto
- Controle de permissões por perfil
- Proteção contra SQL Injection
- API REST
- Interface mais moderna
- Testes automatizados

---

## 🔐 Visibilidade do Repositório

**Status recomendado:** 🔓 **Público**

**Motivos:**
- Não contém variáveis sensíveis expostas
- Credenciais podem ser facilmente genéricas
- Demonstra autenticação, dashboard e integração com BD
- Excelente projeto para portfólio

---

## 📄 Licença

Este projeto está licenciado sob a licença **MIT**.
