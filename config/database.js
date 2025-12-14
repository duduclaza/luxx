const { Pool } = require('pg');

// Pool de conexões PostgreSQL (Neon)
const pool = new Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: {
        rejectUnauthorized: false
    }
});

// Helper functions
const db = {
    // Executar query
    async query(sql, params = []) {
        const result = await pool.query(sql, params);
        return result.rows;
    },

    // Buscar um registro
    async fetchOne(sql, params = []) {
        const rows = await this.query(sql, params);
        return rows[0] || null;
    },

    // Buscar múltiplos registros
    async fetchAll(sql, params = []) {
        return await this.query(sql, params);
    },

    // Inserir e retornar ID
    async insert(table, data) {
        const columns = Object.keys(data).join(', ');
        const placeholders = Object.keys(data).map((_, i) => `$${i + 1}`).join(', ');
        const values = Object.values(data);

        const sql = `INSERT INTO ${table} (${columns}) VALUES (${placeholders}) RETURNING id`;
        const result = await pool.query(sql, values);
        return result.rows[0]?.id;
    },

    // Atualizar registros
    async update(table, data, where, whereParams = []) {
        const setClause = Object.keys(data).map((key, i) => `${key} = $${i + 1}`).join(', ');
        const values = [...Object.values(data), ...whereParams];

        // Ajustar placeholders do WHERE
        const whereAdjusted = where.replace(/\?/g, () => `$${values.length - whereParams.length + whereParams.indexOf(whereParams[whereParams.length - 1]) + 1}`);

        const sql = `UPDATE ${table} SET ${setClause} WHERE ${where.replace(/\?/g, `$${Object.keys(data).length + 1}`)}`;
        const result = await pool.query(sql, values);
        return result.rowCount;
    },

    // Deletar registros
    async delete(table, where, params = []) {
        const sql = `DELETE FROM ${table} WHERE ${where.replace(/\?/g, (_, i) => `$${i + 1}`)}`;
        const result = await pool.query(sql, params);
        return result.rowCount;
    },

    // Testar conexão
    async testConnection() {
        try {
            const result = await pool.query('SELECT NOW()');
            console.log('✅ Conexão com Neon PostgreSQL estabelecida!');
            return true;
        } catch (error) {
            console.error('❌ Erro ao conectar ao Neon:', error.message);
            return false;
        }
    },

    // Obter o pool diretamente
    getPool() {
        return pool;
    }
};

module.exports = db;
