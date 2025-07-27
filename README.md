# 🏢 Sistema de Serviços - Arquitetura Reorganizada

Sistema web para contratação de serviços com áreas públicas e administrativas separadas.

## 📁 Estrutura do Projeto

```
trabalho/
├── index.php                 # Página inicial com seleção de áreas
├── BD_Tema.sql              # Script do banco de dados
├── README.md                # Este arquivo
│
├── public/                  # 🌐 ÁREA PÚBLICA (Clientes)
│   ├── index.php           # Portal inicial
│   ├── buscar.php          # Busca de serviços
│   ├── carrinho.php        # Carrinho de compras
│   ├── checkout.php        # Finalização da compra
│   ├── cadastro.php        # Cadastro de clientes
│   ├── login.php           # Login de clientes
│   ├── meus_contratos.php  # Contratos do cliente
│   ├── detalhes_contrato.php
│   ├── logout_cliente.php
│   └── api/                # APIs públicas
│       ├── get_dates.php   # Datas disponíveis
│       └── sync_cart.php   # Sincronização do carrinho
│
├── admin/                   # ⚙️ ÁREA ADMINISTRATIVA
│   ├── index.php           # Login administrativo
│   ├── dashboard.php       # Dashboard principal
│   ├── logout.php          # Logout admin
│   ├── usuarios/           # Gestão de usuários
│   │   ├── form.php
│   │   └── list.php
│   ├── clientes/           # Gestão de clientes
│   │   ├── form.php
│   │   └── list.php
│   ├── servicos/           # Gestão de serviços
│   │   ├── form.php
│   │   └── list.php
│   └── contratacao/        # Gestão de contratos
│       ├── buscar.php
│       ├── carrinho.js
│       ├── carrinho.php
│       ├── confirmar.php
│       ├── get_dates.php
│       ├── listar.php
│       ├── resumo.php
│       ├── sync_cart.php
│       └── update_status.php
│
├── shared/                  # 📦 RECURSOS COMPARTILHADOS
│   ├── config/             # Configurações
│   │   ├── auth.php        # Autenticação
│   │   └── db.php          # Banco de dados
│   ├── dao/                # Data Access Objects
│   │   ├── ClienteDAO.php
│   │   ├── ContratacaoDAO.php
│   │   ├── DataDisponivelDAO.php
│   │   ├── ServicoDAO.php
│   │   └── UsuarioDAO.php
│   └── assets/             # CSS, JS, imagens
│       ├── css/
│       │   ├── style.css
│       │   └── datas-disponiveis.css
│       └── js/
│           └── util.js
│
├── logs/                    # 📋 Logs do sistema
└── copilot-rules/          # 📝 Regras de desenvolvimento
```

## 🚀 Acesso ao Sistema

### 🌐 Página Inicial
- **URL:** `http://localhost/trabalho/`
- **Funcionalidade:** Interface de seleção entre áreas pública e administrativa

### 👥 Área Pública (Clientes)
- **URL:** `http://localhost/trabalho/public/`
- **Funcionalidades:** Buscar serviços, cadastro, login, carrinho, contratos
- **Usuários de teste:**
  - maria@teste.com / teste123
  - joao@teste.com / teste123

### ⚙️ Área Administrativa
- **URL:** `http://localhost/trabalho/admin/`
- **Login:** admin / admin123
- **Funcionalidades:** Gerenciar usuários, clientes, serviços e contratos

## 🗄️ Banco de Dados

Execute o arquivo `BD_Tema.sql` no MySQL para criar:
- **Database:** trabalho_web
- **Usuários padrão:** 
  - 2 clientes (maria@teste.com, joao@teste.com) - senha: teste123
  - 1 admin (admin) - senha: admin123  
  - 1 operador (operador) - senha: op123

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 8+, MySQL 8+
- **Frontend:** Bootstrap 5, jQuery 3.7, JavaScript ES6
- **Arquitetura:** DAO Pattern, MVC, Sessões PHP + localStorage
- **Servidor:** Apache (XAMPP)

## ⭐ Principais Funcionalidades

- ✅ **Arquitetura Modular:** Separação clara entre áreas pública, administrativa e recursos compartilhados
- ✅ **Sistema de Carrinho:** Integração localStorage + sessão PHP com sincronização em tempo real
- ✅ **Autenticação Dupla:** Sistema diferenciado para clientes e usuários administrativos
- ✅ **Gestão Completa:** CRUD de contratos, serviços, clientes e usuários
- ✅ **Interface Responsiva:** Bootstrap 5 com design mobile-first
- ✅ **APIs REST:** Endpoints para sincronização e dados dinâmicos

## 🔄 Reorganização Realizada

Esta versão representa uma **reorganização completa** da estrutura anterior:
- 📁 **Antes:** Arquivos misturados na raiz
- 📁 **Depois:** Estrutura organizada em `public/`, `admin/`, `shared/`
- 🔧 **Benefícios:** Maior escalabilidade, manutenibilidade e separação de responsabilidades

---
*Sistema reorganizado com arquitetura modular e escalável*
