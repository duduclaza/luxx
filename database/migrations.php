<?php
/**
 * TOTEM LUXX - Sistema de Migrations
 * 
 * Execute: php database/migrations.php
 * Isso criará todas as tabelas necessárias automaticamente
 */

require_once __DIR__ . '/../config/database.php';

echo "==========================================\n";
echo "  TOTEM LUXX - Sistema de Migrations\n";
echo "==========================================\n\n";

// Primeiro, tentar criar o banco de dados se não existir
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_DATABASE . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco de dados '" . DB_DATABASE . "' verificado/criado\n\n";
} catch (PDOException $e) {
    die("❌ Erro ao criar banco de dados: " . $e->getMessage() . "\n");
}

// Conectar ao banco
$db = db()->getConnection();

// Array de migrations
$migrations = [
    // ===== TABELA DE CLIENTES (TENANTS) =====
    'clientes' => "
        CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_local VARCHAR(255) NOT NULL COMMENT 'Nome do estabelecimento',
            email VARCHAR(255) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            pin_admin VARCHAR(10) NOT NULL COMMENT 'PIN para sair dos módulos',
            cidade VARCHAR(100) NOT NULL,
            estado CHAR(2) NOT NULL,
            telefone VARCHAR(20),
            logo_url VARCHAR(500),
            cor_primaria VARCHAR(7) DEFAULT '#6366f1' COMMENT 'Cor tema do estabelecimento',
            cor_secundaria VARCHAR(7) DEFAULT '#8b5cf6',
            
            -- Configurações de pagamento
            mp_access_token TEXT COMMENT 'Token Mercado Pago',
            mp_public_key VARCHAR(255),
            pin_maquininha VARCHAR(50) COMMENT 'PIN da maquininha',
            
            -- Controle de assinatura
            plano ENUM('free', 'basic', 'premium', 'enterprise') DEFAULT 'basic',
            is_owner BOOLEAN DEFAULT FALSE COMMENT 'Se é o dono do sistema (gratuito)',
            data_vencimento DATE COMMENT 'Vencimento da mensalidade',
            ativo BOOLEAN DEFAULT TRUE,
            
            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_email (email),
            INDEX idx_ativo (ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE CATEGORIAS =====
    'categorias' => "
        CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            nome VARCHAR(100) NOT NULL,
            tipo ENUM('cozinha', 'bar', 'ingresso') NOT NULL COMMENT 'Define onde o item aparece',
            icone VARCHAR(50) DEFAULT 'utensils',
            cor VARCHAR(7) DEFAULT '#6366f1',
            ordem INT DEFAULT 0,
            ativo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            INDEX idx_cliente_tipo (cliente_id, tipo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE PRODUTOS =====
    'produtos' => "
        CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            categoria_id INT NOT NULL,
            nome VARCHAR(255) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10,2) NOT NULL,
            imagem_url VARCHAR(500),
            
            -- Controle de estoque (opcional)
            controla_estoque BOOLEAN DEFAULT FALSE,
            estoque_atual INT DEFAULT 0,
            estoque_minimo INT DEFAULT 0,
            
            -- Configurações
            destino ENUM('cozinha', 'bar') NOT NULL COMMENT 'Para onde vai o pedido',
            tempo_preparo_min INT DEFAULT 10 COMMENT 'Tempo estimado em minutos',
            disponivel BOOLEAN DEFAULT TRUE,
            destaque BOOLEAN DEFAULT FALSE,
            
            ordem INT DEFAULT 0,
            ativo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
            INDEX idx_cliente_categoria (cliente_id, categoria_id),
            INDEX idx_disponivel (cliente_id, disponivel, ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE INGRESSOS =====
    'ingressos' => "
        CREATE TABLE IF NOT EXISTS ingressos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            nome VARCHAR(255) NOT NULL COMMENT 'Nome do ingresso (Ex: Inteira, Meia, VIP)',
            descricao TEXT,
            preco DECIMAL(10,2) NOT NULL,
            
            -- Controle de disponibilidade
            quantidade_total INT DEFAULT 0 COMMENT '0 = ilimitado',
            quantidade_vendida INT DEFAULT 0,
            
            -- Configurações visuais
            cor VARCHAR(7) DEFAULT '#6366f1',
            icone VARCHAR(50) DEFAULT 'ticket',
            imagem_url VARCHAR(500),
            
            -- Validade
            data_evento DATE,
            hora_evento TIME,
            
            disponivel BOOLEAN DEFAULT TRUE,
            ativo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            INDEX idx_cliente_disponivel (cliente_id, disponivel, ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE PEDIDOS =====
    'pedidos' => "
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            codigo_cliente VARCHAR(10) NOT NULL COMMENT 'Código exibido (A-01, B-02...)',
            
            -- Status geral do pedido
            status ENUM('pendente', 'preparando', 'pronto', 'chamando', 'entregue', 'cancelado') DEFAULT 'pendente',
            
            -- Status específicos
            status_cozinha ENUM('pendente', 'preparando', 'pronto', 'na') DEFAULT 'na' COMMENT 'na = não aplicável',
            status_bar ENUM('pendente', 'preparando', 'pronto', 'na') DEFAULT 'na',
            
            -- Valores
            subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
            desconto DECIMAL(10,2) DEFAULT 0,
            total DECIMAL(10,2) NOT NULL DEFAULT 0,
            
            -- Pagamento
            forma_pagamento ENUM('pix', 'credito', 'debito', 'dinheiro', 'pendente') DEFAULT 'pendente',
            status_pagamento ENUM('pendente', 'aprovado', 'recusado', 'cancelado') DEFAULT 'pendente',
            mp_payment_id VARCHAR(100) COMMENT 'ID do pagamento no Mercado Pago',
            
            -- Origem
            origem ENUM('cardapio', 'bilheteria', 'balcao') DEFAULT 'cardapio',
            
            -- Controle de chamada
            vezes_chamado INT DEFAULT 0,
            ultima_chamada TIMESTAMP NULL,
            
            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            finalizado_at TIMESTAMP NULL,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            INDEX idx_cliente_status (cliente_id, status),
            INDEX idx_codigo_cliente (cliente_id, codigo_cliente),
            INDEX idx_data (cliente_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE ITENS DO PEDIDO =====
    'pedido_itens' => "
        CREATE TABLE IF NOT EXISTS pedido_itens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id INT NOT NULL,
            produto_id INT NULL COMMENT 'NULL se for ingresso',
            ingresso_id INT NULL COMMENT 'NULL se for produto',
            
            nome VARCHAR(255) NOT NULL COMMENT 'Nome do produto/ingresso no momento da compra',
            quantidade INT NOT NULL DEFAULT 1,
            preco_unitario DECIMAL(10,2) NOT NULL,
            preco_total DECIMAL(10,2) NOT NULL,
            
            -- Qual módulo processa este item
            destino ENUM('cozinha', 'bar', 'bilheteria') NOT NULL,
            status ENUM('pendente', 'preparando', 'pronto') DEFAULT 'pendente',
            
            observacoes TEXT,
            
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
            FOREIGN KEY (ingresso_id) REFERENCES ingressos(id) ON DELETE SET NULL,
            INDEX idx_pedido_destino (pedido_id, destino)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE VENDAS DE INGRESSOS =====
    'vendas_ingressos' => "
        CREATE TABLE IF NOT EXISTS vendas_ingressos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            pedido_id INT NOT NULL,
            ingresso_id INT NOT NULL,
            
            codigo_ingresso VARCHAR(20) NOT NULL UNIQUE COMMENT 'Código único do ingresso vendido',
            qrcode_data TEXT COMMENT 'Dados do QR Code',
            
            -- Status
            status ENUM('valido', 'utilizado', 'cancelado') DEFAULT 'valido',
            utilizado_em TIMESTAMP NULL,
            
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
            FOREIGN KEY (ingresso_id) REFERENCES ingressos(id) ON DELETE RESTRICT,
            INDEX idx_codigo (codigo_ingresso),
            INDEX idx_cliente_status (cliente_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE CHAMADAS DE CLIENTES =====
    'chamadas_clientes' => "
        CREATE TABLE IF NOT EXISTS chamadas_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            pedido_id INT NOT NULL,
            codigo_cliente VARCHAR(10) NOT NULL,
            numero_pedido INT NOT NULL,
            
            -- Controle de exibição
            ativo BOOLEAN DEFAULT TRUE,
            exibir_ate TIMESTAMP NOT NULL COMMENT 'Quando a chamada expira no painel',
            
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
            INDEX idx_cliente_ativo (cliente_id, ativo),
            INDEX idx_exibir (cliente_id, exibir_ate)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE CONTROLE DE CÓDIGOS DE CLIENTES =====
    'codigos_clientes' => "
        CREATE TABLE IF NOT EXISTS codigos_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            prefixo CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'Letra atual (A, B, C...)',
            numero_atual INT NOT NULL DEFAULT 0 COMMENT 'Número atual',
            
            -- Reset diário
            data_reset DATE NOT NULL,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            UNIQUE KEY uk_cliente (cliente_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE SESSÕES =====
    'sessoes' => "
        CREATE TABLE IF NOT EXISTS sessoes (
            id VARCHAR(128) PRIMARY KEY,
            cliente_id INT NOT NULL,
            dados TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            ultimo_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            INDEX idx_cliente (cliente_id),
            INDEX idx_ultimo_acesso (ultimo_acesso)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE CONFIGURAÇÕES DO SISTEMA =====
    'configuracoes' => "
        CREATE TABLE IF NOT EXISTS configuracoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            chave VARCHAR(100) NOT NULL,
            valor TEXT,
            tipo ENUM('string', 'int', 'bool', 'json') DEFAULT 'string',
            
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            UNIQUE KEY uk_cliente_chave (cliente_id, chave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    // ===== TABELA DE LOGS =====
    'logs' => "
        CREATE TABLE IF NOT EXISTS logs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT,
            tipo ENUM('info', 'warning', 'error', 'debug') DEFAULT 'info',
            modulo VARCHAR(50),
            acao VARCHAR(100),
            descricao TEXT,
            dados JSON,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            INDEX idx_cliente_tipo (cliente_id, tipo),
            INDEX idx_data (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

// Executar migrations
$success = 0;
$errors = 0;

foreach ($migrations as $table => $sql) {
    try {
        $db->exec($sql);
        echo "✅ Tabela '$table' criada/verificada com sucesso\n";
        $success++;
    } catch (PDOException $e) {
        echo "❌ Erro ao criar tabela '$table': " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n==========================================\n";
echo "  Resultado: $success sucesso(s), $errors erro(s)\n";
echo "==========================================\n\n";

// Criar usuário owner (você)
echo "Verificando usuário owner...\n";

$ownerEmail = 'du.claza@gmail.com';
$ownerExists = db()->fetchOne("SELECT id FROM clientes WHERE email = ?", [$ownerEmail]);

if (!$ownerExists) {
    $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
    $pinAdmin = '1234'; // PIN padrão, mudar depois
    
    db()->insert('clientes', [
        'nome_local' => 'TOTEM LUXX - Admin',
        'email' => $ownerEmail,
        'senha' => $senhaHash,
        'pin_admin' => $pinAdmin,
        'cidade' => 'Sua Cidade',
        'estado' => 'SP',
        'plano' => 'enterprise',
        'is_owner' => true,
        'ativo' => true
    ]);
    
    echo "✅ Usuário owner criado: $ownerEmail\n";
    echo "   Senha: admin123 (MUDE ISSO!)\n";
    echo "   PIN Admin: 1234 (MUDE ISSO!)\n";
} else {
    echo "ℹ️ Usuário owner já existe: $ownerEmail\n";
}

echo "\n==========================================\n";
echo "  ✅ MIGRATIONS CONCLUÍDAS!\n";
echo "==========================================\n";
echo "\nPróximos passos:\n";
echo "1. Copie .env.example para .env\n";
echo "2. Configure as credenciais do banco no .env\n";
echo "3. Acesse o sistema e faça login\n";
echo "4. MUDE sua senha e PIN imediatamente!\n";
echo "\n";
