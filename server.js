require('dotenv').config();
const express = require('express');
const session = require('express-session');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middlewares
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Sessão
app.use(session({
    secret: process.env.SESSION_SECRET || 'totem-luxx-secret-key-2024',
    resave: false,
    saveUninitialized: false,
    cookie: {
        secure: process.env.NODE_ENV === 'production',
        maxAge: 24 * 60 * 60 * 1000 // 24 horas
    }
}));

// Servir arquivos estáticos
app.use(express.static(path.join(__dirname, 'public')));

// Middleware para disponibilizar sessão nas views
app.use((req, res, next) => {
    res.locals.session = req.session;
    res.locals.isLoggedIn = !!req.session.clienteId;
    next();
});

// Rotas API
app.use('/api/auth', require('./routes/api/auth'));
app.use('/api/pedidos', require('./routes/api/pedidos'));
app.use('/api/chamadas', require('./routes/api/chamadas'));
app.use('/api/produtos', require('./routes/api/produtos'));

// Rotas de páginas
app.use('/', require('./routes/pages'));

// 404 handler
app.use((req, res) => {
    res.status(404).sendFile(path.join(__dirname, 'public', '404.html'));
});

// Error handler
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({ success: false, error: 'Erro interno do servidor' });
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`
╔═══════════════════════════════════════════╗
║         🎯 TOTEM LUXX - Servidor          ║
╠═══════════════════════════════════════════╣
║  URL: http://localhost:${PORT}               ║
║  Ambiente: ${process.env.NODE_ENV || 'development'}                  ║
╚═══════════════════════════════════════════╝
    `);
});

module.exports = app;
