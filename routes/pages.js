const express = require('express');
const router = express.Router();
const path = require('path');

// Middleware para verificar autenticação
const requireAuth = (req, res, next) => {
    if (!req.session.clienteId) {
        return res.redirect('/login');
    }
    next();
};

// Middleware para páginas públicas (redireciona se já logado)
const redirectIfLoggedIn = (req, res, next) => {
    if (req.session.clienteId) {
        return res.redirect('/modulos');
    }
    next();
};

// ============================================
// PÁGINAS PÚBLICAS
// ============================================

// Login
router.get('/', redirectIfLoggedIn, (req, res) => {
    res.sendFile(path.join(__dirname, '../public/login.html'));
});

router.get('/login', redirectIfLoggedIn, (req, res) => {
    res.sendFile(path.join(__dirname, '../public/login.html'));
});

// Cadastro
router.get('/cadastro', redirectIfLoggedIn, (req, res) => {
    res.sendFile(path.join(__dirname, '../public/cadastro.html'));
});

// Logout
router.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/login');
});

// ============================================
// PÁGINAS PROTEGIDAS
// ============================================

// Seleção de módulos (sem auth por enquanto - Vercel serverless)
router.get('/modulos', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/modulos.html'));
});

// Módulo Cozinha
router.get('/cozinha', requireAuth, (req, res) => {
    req.session.moduloAtual = 'cozinha';
    res.sendFile(path.join(__dirname, '../public/cozinha.html'));
});

// Módulo Bar
router.get('/bar', requireAuth, (req, res) => {
    req.session.moduloAtual = 'bar';
    res.sendFile(path.join(__dirname, '../public/bar.html'));
});

// Módulo Cardápio
router.get('/cardapio', requireAuth, (req, res) => {
    req.session.moduloAtual = 'cardapio';
    res.sendFile(path.join(__dirname, '../public/cardapio.html'));
});

// Módulo Bilheteria
router.get('/bilheteria', requireAuth, (req, res) => {
    req.session.moduloAtual = 'bilheteria';
    res.sendFile(path.join(__dirname, '../public/bilheteria.html'));
});

// Painel de Chamada
router.get('/painel', requireAuth, (req, res) => {
    req.session.moduloAtual = 'painel';
    res.sendFile(path.join(__dirname, '../public/painel.html'));
});

// Admin Dashboard
router.get('/admin', requireAuth, (req, res) => {
    res.sendFile(path.join(__dirname, '../public/admin/dashboard.html'));
});

// Admin Configurações
router.get('/admin/configuracoes', requireAuth, (req, res) => {
    res.sendFile(path.join(__dirname, '../public/admin/configuracoes.html'));
});

// Admin Produtos
router.get('/admin/produtos', requireAuth, (req, res) => {
    res.sendFile(path.join(__dirname, '../public/admin/produtos.html'));
});

module.exports = router;
