-- Database setup for Service Management System
-- Run this single file to create database, tables and sample data
-- UPDATED: Compatible with dual architecture (admin + public)
-- UPDATED: Support for flexible contratacao fields

DROP DATABASE IF EXISTS `trabalho_web`;

CREATE DATABASE `trabalho_web` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `trabalho_web`;

-- Table: usuarios
CREATE TABLE `usuarios` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `login` varchar(50) NOT NULL,
    `senha` varchar(255) NOT NULL,
    `nome` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `tipo` enum('admin', 'operador') DEFAULT 'operador',
    `ativo` tinyint(1) DEFAULT 1,
    `remember_token` varchar(255) DEFAULT NULL,
    `ultimo_acesso` timestamp NULL DEFAULT NULL,
    `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `login` (`login`),
    UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Table: clientes
CREATE TABLE `clientes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `cpf` varchar(14) NOT NULL,
    `cidade` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `telefone` varchar(20) DEFAULT NULL,
    `endereco` varchar(200) DEFAULT NULL,
    `senha` varchar(255) DEFAULT NULL COMMENT 'Senha opcional para checkout express',
    `remember_token` varchar(255) DEFAULT NULL,
    `ativo` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `cpf` (`cpf`),
    UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Table: servicos
CREATE TABLE `servicos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `tipo` varchar(50) NOT NULL,
    `preco` decimal(10, 2) NOT NULL,
    `descricao` text DEFAULT NULL,
    `ativo` tinyint(1) DEFAULT 1 COMMENT 'Status do serviço',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Table: datas_disponiveis
CREATE TABLE `datas_disponiveis` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `servico_id` int(11) NOT NULL,
    `data` date NOT NULL,
    `disponivel` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `servico_id` (`servico_id`),
    KEY `data` (`data`),
    CONSTRAINT `fk_data_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Table: contratacoes
CREATE TABLE `contratacoes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cliente_id` int(11) NOT NULL,
    `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuário que processou (opcional)',
    `total` decimal(10, 2) DEFAULT NULL COMMENT 'Total simplificado',
    `valor_total` decimal(10, 2) NOT NULL COMMENT 'Valor total da contratação',
    `status` enum(
        'pendente',
        'confirmada',
        'ativo',
        'cancelado',
        'concluido'
    ) DEFAULT 'pendente',
    `observacoes` text DEFAULT NULL,
    `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `cliente_id` (`cliente_id`),
    KEY `usuario_id` (`usuario_id`),
    CONSTRAINT `fk_contrato_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
    CONSTRAINT `fk_contrato_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Table: contratacao_servicos
CREATE TABLE `contratacao_servicos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `contrato_id` int(11) NOT NULL COMMENT 'Referência para admin',
    `contratacao_id` int(11) DEFAULT NULL COMMENT 'Referência para público',
    `servico_id` int(11) NOT NULL,
    `data_id` int(11) DEFAULT NULL COMMENT 'Referência para datas_disponiveis',
    `data_disponivel_id` int(11) DEFAULT NULL COMMENT 'Alias para data_id',
    `quantidade` int(11) NOT NULL DEFAULT 1,
    `preco_unitario` decimal(10, 2) DEFAULT NULL,
    `preco` decimal(10, 2) DEFAULT NULL COMMENT 'Preço simplificado',
    `valor` decimal(10, 2) DEFAULT NULL COMMENT 'Valor do serviço',
    `subtotal` decimal(10, 2) DEFAULT NULL COMMENT 'Subtotal calculado',
    `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contrato_id` (`contrato_id`),
    KEY `contratacao_id` (`contratacao_id`),
    KEY `servico_id` (`servico_id`),
    KEY `data_id` (`data_id`),
    CONSTRAINT `fk_contrato_servico_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratacoes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_contrato_servico_contratacao` FOREIGN KEY (`contratacao_id`) REFERENCES `contratacoes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_contrato_servico_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`),
    CONSTRAINT `fk_contrato_servico_data` FOREIGN KEY (`data_id`) REFERENCES `datas_disponiveis` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Table: agendamentos
CREATE TABLE `agendamentos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `contrato_servico_id` int(11) NOT NULL,
    `data_agendada` date NOT NULL,
    `status` enum(
        'agendado',
        'realizado',
        'cancelado'
    ) DEFAULT 'agendado',
    `observacoes` text DEFAULT NULL,
    `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contrato_servico_id` (`contrato_servico_id`),
    KEY `data_agendada` (`data_agendada`),
    CONSTRAINT `fk_agendamento_contrato_servico` FOREIGN KEY (`contrato_servico_id`) REFERENCES `contratacao_servicos` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ========================================
-- SAMPLE DATA
-- ========================================

-- Users (admin/admin123, operador1/user123, operador2/user123)
INSERT INTO
    `usuarios` (
        `login`,
        `senha`,
        `nome`,
        `email`,
        `tipo`,
        `ativo`
    )
VALUES (
        'admin',
        '$2y$10$80XiANuz/lUmdz2BvAL78u3sPV08uPXk4qJcCsNBlIEsqGkzJJ/Lu',
        'Administrador',
        'admin@lojaweb.com',
        'admin',
        1
    ),
    (
        'operador1',
        '$2y$10$jX4tAoRRYbr4/b2U6Y33Ee0X5CGcvL0GZF8a/tU2rCAgb3FEumiHK',
        'Operador Um',
        'operador1@lojaweb.com',
        'operador',
        1
    ),
    (
        'operador2',
        '$2y$10$jX4tAoRRYbr4/b2U6Y33Ee0X5CGcvL0GZF8a/tU2rCAgb3FEumiHK',
        'Operador Dois',
        'operador2@lojaweb.com',
        'operador',
        1
    );

-- Sample clients
INSERT INTO
    `clientes` (
        `nome`,
        `cpf`,
        `cidade`,
        `email`,
        `telefone`,
        `endereco`
    )
VALUES (
        'João Silva Santos',
        '123.456.789-01',
        'Vitória',
        'joao.silva@email.com',
        '(27) 99999-1111',
        'Rua das Flores, 123'
    ),
    (
        'Maria Oliveira Costa',
        '234.567.890-12',
        'Vila Velha',
        'maria.oliveira@email.com',
        '(27) 99999-2222',
        'Av. Central, 456'
    ),
    (
        'Pedro Souza Lima',
        '345.678.901-23',
        'Serra',
        'pedro.souza@email.com',
        '(27) 99999-3333',
        'Rua do Campo, 789'
    ),
    (
        'Ana Carolina Pereira',
        '456.789.012-34',
        'Cariacica',
        'ana.pereira@email.com',
        '(27) 99999-4444',
        'Rua das Palmeiras, 101'
    ),
    (
        'Carlos Eduardo Martins',
        '567.890.123-45',
        'Vitória',
        'carlos.martins@email.com',
        '(27) 99999-5555',
        'Av. Beira Mar, 202'
    ),
    (
        'Fernanda Alves Rodrigues',
        '678.901.234-56',
        'Vila Velha',
        'fernanda.alves@email.com',
        '(27) 99999-6666',
        'Rua do Sol, 303'
    ),
    (
        'Roberto Carlos Silva',
        '789.012.345-67',
        'Serra',
        'roberto.silva@email.com',
        '(27) 99999-7777',
        'Rua Nova, 404'
    ),
    (
        'Juliana Santos Costa',
        '890.123.456-78',
        'Vitória',
        'juliana.santos@email.com',
        '(27) 99999-8888',
        'Av. da Praia, 505'
    );

-- Sample services
INSERT INTO
    `servicos` (
        `nome`,
        `tipo`,
        `preco`,
        `descricao`
    )
VALUES (
        'Desenvolvimento de Site',
        'Tecnologia',
        2500.00,
        'Criação de site responsivo com HTML, CSS e JavaScript. Inclui até 5 páginas e formulário de contato.'
    ),
    (
        'Consultoria em Marketing Digital',
        'Marketing',
        800.00,
        'Análise completa de presença digital e estratégias de marketing online para sua empresa.'
    ),
    (
        'Design de Logo e Identidade Visual',
        'Design',
        650.00,
        'Criação de logotipo profissional e manual de identidade visual completo para sua marca.'
    ),
    (
        'Manutenção de Computadores',
        'Tecnologia',
        150.00,
        'Limpeza, formatação e otimização de computadores e notebooks. Inclui backup de dados.'
    ),
    (
        'Fotografia de Eventos',
        'Fotografia',
        1200.00,
        'Cobertura fotográfica completa de eventos sociais e corporativos. Inclui edição e entrega digital.'
    );

-- Available dates (7 per service maximum)
INSERT INTO
    `datas_disponiveis` (
        `servico_id`,
        `data`,
        `disponivel`
    )
VALUES
    -- Desenvolvimento de Site
    (1, '2025-08-15', 1),
    (1, '2025-09-01', 1),
    (1, '2025-09-15', 1),
    (1, '2025-10-01', 1),
    (1, '2025-10-15', 1),
    (1, '2025-11-01', 1),
    (1, '2025-11-15', 1),
    -- Consultoria em Marketing Digital  
    (2, '2025-08-10', 1),
    (2, '2025-08-20', 1),
    (2, '2025-09-05', 1),
    (2, '2025-09-20', 1),
    (2, '2025-10-05', 1),
    (2, '2025-10-20', 1),
    (2, '2025-11-05', 1),
    -- Design de Logo e Identidade Visual
    (3, '2025-08-05', 1),
    (3, '2025-08-25', 1),
    (3, '2025-09-10', 1),
    (3, '2025-09-25', 1),
    (3, '2025-10-10', 1),
    (3, '2025-10-25', 1),
    (3, '2025-11-10', 1),
    -- Manutenção de Computadores
    (4, '2025-07-30', 1),
    (4, '2025-08-08', 1),
    (4, '2025-08-18', 1),
    (4, '2025-08-28', 1),
    (4, '2025-09-08', 1),
    (4, '2025-09-18', 1),
    (4, '2025-09-28', 1),
    -- Fotografia de Eventos
    (5, '2025-08-12', 1),
    (5, '2025-08-22', 1),
    (5, '2025-09-02', 1),
    (5, '2025-09-12', 1),
    (5, '2025-09-22', 1),
    (5, '2025-10-02', 1),
    (5, '2025-10-12', 1);

-- Installation success message
SELECT 'SUCCESS: Database created with sample data!' as status;

SELECT 'ARCHITECTURE: Dual system - Public (/) + Admin (/admin/)' as architecture;

SELECT 'UPDATED: Compatible with cart system and flexible fields' as compatibility;

SELECT 'LOGIN: admin / admin123' as admin_credentials;

SELECT 'LOGIN: operador1 / user123' as operator_credentials;