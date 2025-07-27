# Sistema de Gestão de Serviços

Sistema completo para gestão e contratação de serviços com **arquitetura dual**: administrativa e pública.

## 🌐 Acessos do Sistema

### 🏠 **Área Pública (Clientes)**
- **URL Principal**: `http://localhost/trabalho/`
- **Funcionalidades**: Buscar serviços, carrinho, cadastro, login, contratação

### 🔧 **Área Administrativa** 
- **URL Admin**: `http://localhost/trabalho/admin/`
- **Login**: `admin` / `admin123`
- **Operador**: `operador1` / `user123`
- **Funcionalidades**: Dashboard, CRUD completo, relatórios

## 🚀 Instalação Rápida

### 1. **Banco de Dados** (Uma única etapa!)
Execute o arquivo `BD_Tema.sql` no MySQL:
```bash
mysql -u root < BD_Tema.sql
```
ou via phpMyAdmin: importe o arquivo `BD_Tema.sql`

**✅ Pronto!** O arquivo `BD_Tema.sql` já contém:
- Criação do banco `trabalho_web`
- Todas as tabelas com relacionamentos compatíveis
- Dados de exemplo (usuários, clientes, serviços)
- Senhas já configuradas corretamente
- Suporte para arquitetura dual (admin + público)

### 2. **Estrutura de URLs**
```
http://localhost/trabalho/           ← Área Pública
http://localhost/trabalho/admin/     ← Área Administrativa
```

### 3. **Configuração** (Padrão XAMPP)
- Banco: `trabalho_web`
- Host: `localhost`
- Usuário: `root` (sem senha)

## 📋 Requisitos do Sistema

- **PHP**: 8.0 ou superior
- **MySQL**: 5.7 ou superior  
- **Servidor Web**: Apache (XAMPP recomendado)
- **Extensões PHP**: PDO, PDO_MySQL, mbstring, json

## 🧹 Manutenção do Projeto

### Limpeza Automática de Arquivos de Teste

O projeto inclui scripts automatizados para remover arquivos de teste:

#### **PowerShell** (Recomendado)
```powershell
.\cleanup-tests.ps1
```

#### **Padrões Monitorados:**
- `teste_*.html` / `teste_*.php`
- `test_*.html` / `test_*.php`  
- `*_test.html` / `*_test.php`
- `debug_*.html` / `debug_*.php`
- `temp_*.html` / `temp_*.php`
- `*.tmp` (arquivos temporários)
- `logs/*debug*` (logs de debug)

#### **Configuração Automática:**
O arquivo `.gitignore` já exclui automaticamente arquivos de teste do controle de versão.

### Como Usar:
1. Execute o script quando necessário: `.\cleanup-tests.ps1`
2. O script remove automaticamente todos os arquivos que seguem os padrões de teste
3. Logs são exibidos mostrando quais arquivos foram removidos

## �️ Arquitetura do Sistema

### **Estrutura Dual Completa**
```
trabalho/
├── index.php               ← Página inicial pública
├── buscar.php             ← Busca de serviços
├── carrinho.php           ← Carrinho de compras
├── checkout.php           ← Finalização de compra
├── login.php              ← Login de clientes
├── cadastro.php           ← Cadastro de clientes
├── meus_contratos.php     ← Histórico do cliente
├── admin/                 ← Área administrativa
│   ├── index.php         ← Login admin
│   ├── dashboard.php     ← Painel de controle
│   ├── logout.php        ← Logout admin
│   ├── servicos/         ← CRUD de serviços
│   ├── clientes/         ← CRUD de clientes
│   ├── usuarios/         ← CRUD de usuários
│   └── contratacao/      ← Gestão de contratações
├── dao/                   ← Data Access Objects
├── config/                ← Configurações
├── assets/                ← CSS, JS, imagens
└── logs/                  ← Arquivos de log
```

### **Funcionalidades por Área**

#### 🌍 **Área Pública** (`/`)
- ✅ **Página Inicial**: Hero section, busca rápida, destaques
- ✅ **Busca Avançada**: Filtros por nome, tipo, preço
- ✅ **Carrinho Inteligente**: JavaScript + LocalStorage + PHP Sessions
- ✅ **Sistema de Clientes**: Cadastro, login, histórico
- ✅ **Processo Completo**: Da busca até a contratação finalizada

#### 🔧 **Área Administrativa** (`/admin/`)
- ✅ **Dashboard Analítico**: Estatísticas, gráficos, resumos
- ✅ **CRUD Completo**: Serviços, clientes, usuários
- ✅ **Gestão de Contratações**: Status, valores, datas
- ✅ **Controle de Usuários**: Admin e operadores

## 🛒 Sistema de Carrinho Avançado

### **Tecnologias do Carrinho**
- **Frontend**: JavaScript ES6 + LocalStorage
- **Backend**: PHP Sessions + AJAX
- **Sincronização**: Bi-direcional client-server
- **Persistência**: Entre sessões e dispositivos

### **Funcionalidades do Carrinho**
- ✅ Adicionar/remover serviços dinamicamente
- ✅ Seleção de datas disponíveis por serviço
- ✅ Validação de quantidade máxima (5 itens)
- ✅ Cálculo automático de totais
- ✅ Sincronização em tempo real
- ✅ Interface responsiva com Bootstrap 5

## �🎯 Especificações Técnicas

### Tecnologias Utilizadas
- **Backend**: PHP 8+ (sem frameworks)
- **Banco de Dados**: MySQL com PDO
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript/jQuery
- **Segurança**: Hash de senhas, CSRF protection, prepared statements

### Arquitetura
- **Padrão DAO** para acesso aos dados
- **Padrão Singleton** para conexão de banco
- **Separação de responsabilidades** em camadas
- **Validação client-side e server-side**

## 📋 Funcionalidades Implementadas

### 🔐 **Autenticação Dual**
- ✅ **Login Admin**: Para usuários administrativos
- ✅ **Login Cliente**: Para clientes do site público
- ✅ **Sistema "Lembrar-me"**: Cookies seguros
- ✅ **Proteção CSRF**: Tokens de segurança
- ✅ **Hash Seguro**: `password_hash()` com salt

### 👥 **Gestão de Usuários**
- ✅ **CRUD Completo**: Apenas para administradores
- ✅ **Tipos**: Administrador e Operador
- ✅ **Status**: Ativo/inativo
- ✅ **Auditoria**: Último acesso registrado
- ✅ **Segurança**: Login único, senhas criptografadas

### 👤 **Gestão de Clientes**
- ✅ **CRUD Completo**: Interface administrativa
- ✅ **Auto-cadastro**: Clientes podem se registrar
- ✅ **Validações**: CPF, email único
- ✅ **Dados Completos**: Nome, CPF, cidade, contato
- ✅ **Relacionamentos**: Histórico de contratações

### 🛍️ **Gestão de Serviços**
- ✅ **CRUD Completo**: Interface administrativa
- ✅ **Categorização**: Tipos (Tecnologia, Marketing, Design, etc.)
- ✅ **Preços**: Formato monetário brasileiro
- ✅ **Disponibilidade**: Datas disponíveis por serviço
- ✅ **Status**: Ativo/inativo
- ✅ **Busca Pública**: Filtros avançados

### 🛒 **Sistema de Contratação Público**
- ✅ **Busca Avançada**: Por nome, tipo, faixa de preço
- ✅ **Carrinho Inteligente**: Máximo 5 itens, persistente
- ✅ **Seleção de Datas**: Para cada serviço individual
- ✅ **Cálculo Automático**: Valores e totais em tempo real
- ✅ **Checkout Completo**: Validação e finalização
- ✅ **Histórico**: Clientes veem suas contratações
- [x] **Resumo e confirmação** de pedidos
- [x] **Transações seguras** com rollback em caso de erro
- [x] **Controle de estoque** de datas (evita double booking)

### ✅ Dashboard e Interface
- [x] **Painel principal** com resumo estatístico
- [x] **Design responsivo** com Bootstrap 5
- [x] **Navegação intuitiva** com sidebar
- [x] **Mensagens de feedback** para todas as ações
- [x] **Validação JavaScript** em tempo real
- [x] **Carregamento assíncrono** com AJAX

## 🛠️ Estrutura do Projeto

```
/trabalho/
├── index.php              # Página de login
├── BD_Tema.sql           # ⭐ Instalação completa (schema + dados)
├── config/
│   ├── db.php             # Configuração do banco (Singleton)
│   └── auth.php           # Middleware de autenticação
├── dao/
│   ├── UsuarioDAO.php     # CRUD de usuários
│   ├── ClienteDAO.php     # CRUD de clientes
│   ├── ServicoDAO.php     # CRUD de serviços
│   ├── DataDisponivelDAO.php # Gestão de datas
│   └── ContratacaoDAO.php # Sistema de contratação
├── public/
│   ├── dashboard.php      # ⭐ Painel principal
│   └── logout.php         # Logout seguro
├── usuarios/
│   ├── list.php          # Listagem de usuários (admin)
│   └── form.php          # Formulário de usuários
├── clientes/
│   ├── list.php          # Listagem de clientes
│   └── form.php          # Formulário de clientes
├── servicos/
│   ├── list.php          # Listagem de serviços
│   └── form.php          # Formulário de serviços
├── contratacao/
│   ├── buscar.php        # ⭐ Busca de serviços
│   ├── resumo.php        # ⭐ Carrinho de compras
│   └── confirmar.php     # ⭐ Finalização do pedido
└── assets/
    ├── css/style.css     # Estilos customizados
    └── js/util.js        # Funções JavaScript
```

## 🛡️ Recursos de Segurança

- **Senhas**: Hash com `password_hash()` e verificação com `password_verify()`
- **SQL Injection**: Prepared statements em todas as consultas
- **XSS**: Sanitização com `htmlspecialchars()`
- **CSRF**: Tokens de segurança em formulários críticos
- **Sessões**: Regeneração de ID e configuração segura
- **Cookies**: HttpOnly e Secure flags quando aplicável
- **Transações**: Rollback automático em caso de erro

## 🔍 Validações Implementadas

### Server-side (PHP)
- ✅ Campos obrigatórios
- ✅ Formatos de email e CPF
- ✅ Tipos de dados numéricos
- ✅ Limites de caracteres
- ✅ Unicidade de dados (login, email, CPF)
- ✅ Validação de relacionamentos

### Client-side (JavaScript)
- ✅ Validação em tempo real
- ✅ Formatação automática de CPF
- ✅ Contadores de caracteres
- ✅ Masks para campos especiais
- ✅ Confirmação de ações destrutivas

## 📊 Casos de Borda Tratados

- ✅ **Limite de datas**: Máximo 7 datas por serviço → Bloqueado
- ✅ **Serviço sem datas**: Alerta exibido durante contratação
- ✅ **Corrida por mesma data**: Transação com `SELECT FOR UPDATE`
- ✅ **Carrinho lotado**: Máximo 5 itens → Limite aplicado
- ✅ **Exclusão com vínculos**: Confirmação obrigatória
- ✅ **CPF/Email duplicado**: Validação e bloqueio
- ✅ **Sessão expirada**: Redirecionamento automático para login

## 🎨 Interface e UX

### Design Moderno
- **Bootstrap 5** para responsividade
- **Sidebar** responsiva com navegação intuitiva
- **Cards** informativos no dashboard
- **Tabelas** com busca e paginação
- **Formulários** com validação visual

### Experiência do Usuário
- **Feedbacks visuais** para todas as ações (sucesso/erro)
- **Loading states** durante operações assíncronas
- **Confirmações** para ações irreversíveis
- **Navegação breadcrumb** clara
- **Responsive design** para mobile

## 🚨 Solução de Problemas

### Erro de Conexão com Banco
```bash
# Verificar se MySQL está rodando no XAMPP
# Verificar credenciais em config/db.php
# Verificar se o banco 'trabalho_web' existe
```

### Problema de Login
```bash
# Executar novamente o BD_Tema.sql
mysql -u root < BD_Tema.sql

# Credenciais corretas:
# admin / admin123
# operador1 / user123
```

### Erro de Permissões
```bash
# No Windows (XAMPP):
# Certifique-se que está na pasta htdocs/trabalho/
# No Linux:
chmod -R 755 /caminho/para/trabalho/
chmod 777 logs/
```

## � Dados de Exemplo

O sistema já vem configurado com:

### Usuários
- **Admin**: admin/admin123
- **Operadores**: operador1 e operador2 (senha: user123)

### Clientes
- 8 clientes de exemplo nas cidades da Grande Vitória
- CPFs e emails únicos já configurados

### Serviços
- **Desenvolvimento de Site** (R$ 2.500,00)
- **Consultoria em Marketing Digital** (R$ 800,00)
- **Design de Logo e Identidade Visual** (R$ 650,00)
- **Manutenção de Computadores** (R$ 150,00)
- **Fotografia de Eventos** (R$ 1.200,00)

### Datas Disponíveis
- 7 datas para cada serviço (de agosto a novembro de 2025)
- Prontas para contratação

## 📞 Notas Importantes

1. **Instalação**: Use apenas o arquivo `BD_Tema.sql` - ele contém tudo!
2. **Credenciais**: Admin (admin/admin123) - Operador (operador1/user123)
3. **Primeiro acesso**: Use a conta admin para gerenciar o sistema
4. **XAMPP**: Sistema otimizado para ambiente XAMPP padrão
5. **Segurança**: Senhas já estão com hash correto - não há problemas de login

---

## � Últimas Atualizações

### **v2.0 - Arquitetura Dual (27/07/2025)**
- ✅ **Estrutura Reorganizada**: Área pública na raiz (`/`) e admin em (`/admin/`)
- ✅ **Página Inicial Pública**: Design moderno com hero section e busca rápida
- ✅ **Sistema de Carrinho Completo**: JavaScript + LocalStorage + PHP Sessions
- ✅ **Checkout Público**: Processo completo de contratação para clientes
- ✅ **Banco Atualizado**: `BD_Tema.sql` compatível com todas as funcionalidades
- ✅ **URLs Amigáveis**: Estrutura limpa e intuitiva
- ✅ **Correções de Paths**: Todos os caminhos e links atualizados
- ✅ **APIs Públicas**: `get_dates.php` e `sync_cart.php` acessíveis sem autenticação admin

### **Melhorias de Segurança**
- ✅ **Headers de Segurança**: `.htaccess` configurado
- ✅ **Validação Robusta**: Client-side + server-side
- ✅ **Autenticação Separada**: Admin e clientes independentes

## �🏆 Destaques Técnicos

- ✨ **Instalação em 1 comando**: `mysql -u root < BD_Tema.sql`
- 🔒 **100% seguro**: Prepared statements, CSRF, hash de senhas
- 🎯 **Arquitetura limpa**: DAO pattern, separation of concerns
- 📱 **Totalmente responsivo**: Bootstrap 5 mobile-first
- ⚡ **Performance**: Singleton connection, índices otimizados
- 🛡️ **Validação dupla**: Client-side + server-side
- 🏗️ **Arquitetura Dual**: Interface pública + administrativa

## 🚀 Próximas Funcionalidades
- [ ] Sistema de avaliações e comentários
- [ ] Notificações por email
- [ ] Chat online entre cliente e prestador
- [ ] API REST para integração mobile
- [ ] Dashboard analytics avançado

*Sistema desenvolvido seguindo as melhores práticas de desenvolvimento web e segurança, sem frameworks, conforme especificado.*

