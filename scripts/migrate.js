/**
 * TOTEM LUXX - Migrations para PostgreSQL (Neon)
 * Execute: node scripts/migrate.js
 */
require('dotenv').config();
const { Pool } = require('pg');
const bcrypt = require('bcryptjs');

const pool = new Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: { rejectUnauthorized: false }
});

async function migrate() {
    console.log('🚀 Iniciando migrations...\n');

    try {
        // Tabela clientes
        await pool.query(`
            CREATE TABLE IF NOT EXISTS clientes (
                id SERIAL PRIMARY KEY,
                nome_local VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                senha VARCHAR(255) NOT NULL,
                pin_admin VARCHAR(10) NOT NULL DEFAULT '1234',
                telefone VARCHAR(20),
                cidade VARCHAR(100),
                estado VARCHAR(2),
                plano VARCHAR(50) DEFAULT 'basic',
                data_vencimento DATE,
                is_owner BOOLEAN DEFAULT FALSE,
                ativo BOOLEAN DEFAULT TRUE,
                mp_access_token TEXT,
                mp_public_key TEXT,
                pin_maquininha VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Tabela clientes criada');

        // Tabela categorias
        await pool.query(`
            CREATE TABLE IF NOT EXISTS categorias (
                id SERIAL PRIMARY KEY,
                cliente_id INTEGER REFERENCES clientes(id),
                nome VARCHAR(100) NOT NULL,
                tipo VARCHAR(50) DEFAULT 'cozinha',
                ordem INTEGER DEFAULT 0,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Tabela categorias criada');

        // Tabela produtos
        await pool.query(`
            CREATE TABLE IF NOT EXISTS produtos (
                id SERIAL PRIMARY KEY,
                cliente_id INTEGER REFERENCES clientes(id),
                categoria_id INTEGER REFERENCES categorias(id),
                nome VARCHAR(255) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL,
                imagem_url TEXT,
                destino VARCHAR(50) DEFAULT 'cozinha',
                disponivel BOOLEAN DEFAULT TRUE,
                destaque BOOLEAN DEFAULT FALSE,
                ordem INTEGER DEFAULT 0,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Tabela produtos criada');

        // Tabela ingressos
        await pool.query(`
            CREATE TABLE IF NOT EXISTS ingressos (
                id SERIAL PRIMARY KEY,
                cliente_id INTEGER REFERENCES clientes(id),
                nome VARCHAR(255) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL,
                cor VARCHAR(20) DEFAULT '#8b5cf6',
                quantidade_total INTEGER DEFAULT 0,
                quantidade_vendida INTEGER DEFAULT 0,
                disponivel BOOLEAN DEFAULT TRUE,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Tabela ingressos criada');

        // Tabela pedidos
        await pool.query(`
            CREATE TABLE IF NOT EXISTS pedidos (
                id SERIAL PRIMARY KEY,
                cliente_id INTEGER REFERENCES clientes(id),
                codigo_cliente VARCHAR(10),
                status VARCHAR(50) DEFAULT 'pendente',
                status_cozinha VARCHAR(50) DEFAULT 'na',
                status_bar VARCHAR(50) DEFAULT 'na',
                subtotal DECIMAL(10,2) DEFAULT 0,
                desconto DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2) DEFAULT 0,
                forma_pagamento VARCHAR(50),
                status_pagamento VARCHAR(50) DEFAULT 'pendente',
                origem VARCHAR(50) DEFAULT 'cardapio',
                vezes_chamado INTEGER DEFAULT 0,
                ultima_chamada TIMESTAMP,
                finalizado_at TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Tabela pedidos criada');

        // Tabela pedido_itens
        await pool.query(`
            CREATE TABLE IF NOT EXISTS pedido_itens (
                id SERIAL PRIMARY KEY,
                pedido_id INTEGER REFERENCES pedidos(id),
                produto_id INTEGER REFERENCES produtos(id),
                ingresso_id INTEGER REFERENCES ingressos(id),
                nome VARCHAR(255) NOT NULL,
                quantidade INTEGER DEFAULT 1,
                preco_unitario DECIMAL(10,2),
                preco_total DECIMAL(10,2),
                destino VARCHAR(50) DEFAULT 'cozinha',
                status VARCHAR(50) DEFAULT 'pendente',
                observacoes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Tabela pedido_itens criada');

        // Tabela chamadas_clientes
        await pool.query(`
            CREATE TABLE IF NOT EXISTS chamadas_clientes (
                id SERIAL PRIMARY KEY,
                cliente_id INTEGER REFERENCES clientes(id),
                pedido_id INTEGER REFERENCES pedidos(id),
                codigo_cliente VARCHAR(10),
                numero_pedido INTEGER,
                exibir_ate TIMESTAMP,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Tabela chamadas_clientes criada');

        // Tabela codigos_clientes
        await pool.query(`
            CREATE TABLE IF NOT EXISTS codigos_clientes (
                id SERIAL PRIMARY KEY,
                cliente_id INTEGER UNIQUE REFERENCES clientes(id),
                prefixo CHAR(1) DEFAULT 'A',
                numero_atual INTEGER DEFAULT 0,
                data_reset DATE
            )
        `);
        console.log('✅ Tabela codigos_clientes criada');

        // Criar usuário owner
        const senhaHash = await bcrypt.hash('admin123', 10);

        const existe = await pool.query('SELECT id FROM clientes WHERE email = $1', ['du.claza@gmail.com']);

        if (existe.rows.length === 0) {
            await pool.query(`
                INSERT INTO clientes (nome_local, email, senha, pin_admin, cidade, estado, plano, is_owner, ativo)
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
            `, ['TOTEM LUXX', 'du.claza@gmail.com', senhaHash, '1234', 'São Paulo', 'SP', 'enterprise', true, true]);

            console.log('\n✅ Usuário owner criado:');
            console.log('   Email: du.claza@gmail.com');
            console.log('   Senha: admin123');
            console.log('   PIN: 1234');
        } else {
            // Atualizar senha do owner existente
            await pool.query('UPDATE clientes SET senha = $1 WHERE email = $2', [senhaHash, 'du.claza@gmail.com']);
            console.log('\n✅ Senha do owner atualizada para: admin123');
        }

        console.log('\n🎉 Migrations concluídas com sucesso!');

    } catch (error) {
        console.error('❌ Erro nas migrations:', error);
    } finally {
        await pool.end();
    }
}

migrate();
