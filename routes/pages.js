const express = require('express');
const router = express.Router();
const path = require('path');

// ============================================
// PÁGINAS (sem autenticação server-side no Vercel)
// A autenticação é feita via API e localStorage no frontend
// ============================================

// Login
router.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/login.html'));
});

router.get('/login', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/login.html'));
});

// Cadastro
router.get('/cadastro', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/cadastro.html'));
});

// Logout
router.get('/logout', (req, res) => {
    res.redirect('/login');
});

// Seleção de módulos
router.get('/modulos', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/modulos.html'));
});

// Módulo Cozinha
router.get('/cozinha', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/cozinha.html'));
});

// Módulo Bar
router.get('/bar', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/bar.html'));
});

// Módulo Cardápio
router.get('/cardapio', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/cardapio.html'));
});

// Módulo Bilheteria
router.get('/bilheteria', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/bilheteria.html'));
});

// Painel de Chamada
router.get('/painel', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/painel.html'));
});

// Admin
router.get('/admin', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/admin/dashboard.html'));
});

router.get('/admin/configuracoes', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/admin/configuracoes.html'));
});

router.get('/admin/produtos', (req, res) => {
    res.sendFile(path.join(__dirname, '../public/admin/produtos.html'));
});

module.exports = router;
