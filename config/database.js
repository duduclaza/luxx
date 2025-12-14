const mysql = require('mysql2/promise');

// Pool de conexões
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    port: process.env.DB_PORT || 3306,
    database: process.env.DB_DATABASE,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    enableKeepAlive: true,
    keepAliveInitialDelay: 0
});

// Helper functions
const db = {
    // Executar query
    async query(sql, params = []) {
        const [rows] = await pool.execute(sql, params);
        return rows;
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
        const placeholders = Object.keys(data).map(() => '?').join(', ');
        const values = Object.values(data);

        const sql = `INSERT INTO ${table} (${columns}) VALUES (${placeholders})`;
        const [result] = await pool.execute(sql, values);
        return result.insertId;
    },

    // Atualizar registros
    async update(table, data, where, whereParams = []) {
        const set = Object.keys(data).map(key => `${key} = ?`).join(', ');
        const values = [...Object.values(data), ...whereParams];

        const sql = `UPDATE ${table} SET ${set} WHERE ${where}`;
        const [result] = await pool.execute(sql, values);
        return result.affectedRows;
    },

    // Deletar registros
    async delete(table, where, params = []) {
        const sql = `DELETE FROM ${table} WHERE ${where}`;
        const [result] = await pool.execute(sql, params);
        return result.affectedRows;
    },

    // Testar conexão
    async testConnection() {
        try {
            const connection = await pool.getConnection();
            console.log('✅ Conexão com o banco de dados estabelecida!');
            connection.release();
            return true;
        } catch (error) {
            console.error('❌ Erro ao conectar ao banco:', error.message);
            return false;
        }
    }
};

module.exports = db;
