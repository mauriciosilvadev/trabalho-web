# Sistema de Gestão de Serviços

Sistema web completo para gestão de serviços desenvolvido em PHP 8+ puro, utilizando PDO, MySQL e Bootstrap 5.

## 🚀 Instalação Rápida

### 1. **Banco de Dados** (Uma única etapa!)
Via phpMyAdmin: importe o arquivo `BD_Tema.sql`

**✅ Pronto!** O arquivo `BD_Tema.sql` já contém:
- Criação do banco `trabalho_web`
- Todas as tabelas com relacionamentos
- Dados de exemplo (usuários, clientes, serviços)
- Senhas já configuradas corretamente

### 2. **Acesso ao Sistema**
- **URL**: http://localhost/trabalho/
- **Admin**: `admin` / `admin123`
- **Operador**: `operador1` / `user123`

### 3. **Configuração** (Padrão XAMPP)
- Banco: `trabalho_web`
- Host: `localhost`
- Usuário: `root` (sem senha)

## 📋 Requisitos do Sistema

- **PHP**: 8.0 ou superior
- **MySQL**: 5.7 ou superior  
- **Servidor Web**: Apache (XAMPP recomendado)
- **Extensões PHP**: PDO, PDO_MySQL, mbstring, json

## 🎯 Especificações Técnicas

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

### ✅ Sistema de Autenticação
- [x] Login com validação de credenciais
- [x] Sistema "Lembrar-me" com cookies seguros
- [x] Gerenciamento de sessões
- [x] Logout completo
- [x] Proteção CSRF
- [x] Hash seguro de senhas com `password_hash()`

### ✅ Gestão de Usuários
- [x] CRUD completo (apenas para admin)
- [x] Tipos: Administrador e Operador
- [x] Status ativo/inativo
- [x] Validação de login único
- [x] Último acesso registrado
- [x] Hash automático de senhas

### ✅ Gestão de Clientes
- [x] CRUD completo
- [x] Validação de CPF
- [x] Validação de email único
- [x] Dados completos (nome, CPF, cidade, email, telefone, endereço)
- [x] Prevenção de dados duplicados
- [x] Relacionamento com contratos

### ✅ Gestão de Serviços
- [x] CRUD completo
- [x] Categorização por tipo (Tecnologia, Marketing, Design, etc.)
- [x] Preços em formato monetário brasileiro
- [x] Descrições detalhadas
- [x] **Gestão de datas disponíveis** (máximo 7 por serviço)
- [x] Busca e filtros

### ✅ Sistema de Contratação
- [x] **Busca avançada** de serviços por nome e tipo
- [x] **Carrinho de compras** (máximo 5 itens)
- [x] **Seleção de datas disponíveis** para cada serviço
- [x] **Cálculo automático** de valores totais
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

## 🏆 Destaques Técnicos

- 🔒 **100% seguro**: Prepared statements, CSRF, hash de senhas
- 🎯 **Arquitetura limpa**: DAO pattern, separation of concerns
- 📱 **Totalmente responsivo**: Bootstrap 5 mobile-first
- ⚡ **Performance**: Singleton connection, índices otimizados
- 🛡️ **Validação dupla**: Client-side + server-side

*Sistema desenvolvido seguindo as melhores práticas de desenvolvimento web e segurança, sem frameworks, conforme especificado.*

