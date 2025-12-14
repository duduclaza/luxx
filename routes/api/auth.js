const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const db = require('../../config/database');

// POST /api/auth/login
router.post('/login', async (req, res) => {
    try {
        const { email, senha } = req.body;

        if (!email || !senha) {
            return res.status(400).json({ success: false, error: 'Email e senha são obrigatórios' });
        }

        const cliente = await db.fetchOne(
            'SELECT * FROM clientes WHERE email = $1 AND ativo = true',
            [email]
        );

        if (!cliente) {
            return res.status(401).json({ success: false, error: 'Email ou senha incorretos' });
        }

        const senhaValida = await bcrypt.compare(senha, cliente.senha);

        if (!senhaValida) {
            return res.status(401).json({ success: false, error: 'Email ou senha incorretos' });
        }

        // Verificar vencimento (exceto owner)
        if (!cliente.is_owner && cliente.data_vencimento) {
            if (new Date(cliente.data_vencimento) < new Date()) {
                return res.status(403).json({ success: false, error: 'Assinatura vencida' });
            }
        }

        // Salvar na sessão
        req.session.clienteId = cliente.id;
        req.session.clienteNome = cliente.nome_local;
        req.session.isOwner = cliente.is_owner;

        res.json({
            success: true,
            cliente: {
                id: cliente.id,
                nome: cliente.nome_local,
                email: cliente.email,
                cidade: cliente.cidade,
                estado: cliente.estado,
                pin_admin: cliente.pin_admin
            }
        });

    } catch (error) {
        console.error('Erro no login:', error);
        res.status(500).json({ success: false, error: 'Erro interno do servidor' });
    }
});

// POST /api/auth/logout
router.post('/logout', (req, res) => {
    req.session.destroy();
    res.json({ success: true });
});

// POST /api/auth/verify-pin
router.post('/verify-pin', async (req, res) => {
    try {
        if (!req.session.clienteId) {
            return res.status(401).json({ success: false, error: 'Não autenticado' });
        }

        const { pin } = req.body;

        const cliente = await db.fetchOne(
            'SELECT pin_admin FROM clientes WHERE id = $1',
            [req.session.clienteId]
        );

        if (cliente && cliente.pin_admin === pin) {
            req.session.moduloAtual = null;
            res.json({ success: true });
        } else {
            res.status(401).json({ success: false, error: 'PIN incorreto' });
        }

    } catch (error) {
        console.error('Erro ao verificar PIN:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

// GET /api/auth/me
router.get('/me', async (req, res) => {
    if (!req.session.clienteId) {
        return res.json({ success: true, loggedIn: false });
    }

    try {
        const cliente = await db.fetchOne(
            'SELECT id, nome_local, email, cidade, estado, plano, is_owner FROM clientes WHERE id = $1',
            [req.session.clienteId]
        );

        res.json({
            success: true,
            loggedIn: true,
            cliente
        });
    } catch (error) {
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

// POST /api/auth/register
router.post('/register', async (req, res) => {
    try {
        const { nome_local, email, senha, cidade, estado, telefone, pin_admin } = req.body;

        // Validações
        if (!nome_local || !email || !senha || !cidade || !estado || !pin_admin) {
            return res.status(400).json({ success: false, error: 'Preencha todos os campos obrigatórios' });
        }

        // Verificar se email já existe
        const existe = await db.fetchOne('SELECT id FROM clientes WHERE email = $1', [email]);
        if (existe) {
            return res.status(400).json({ success: false, error: 'Email já cadastrado' });
        }

        // Hash da senha
        const senhaHash = await bcrypt.hash(senha, 10);

        // Data de vencimento (30 dias)
        const dataVencimento = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

        // Criar cliente
        const clienteId = await db.insert('clientes', {
            nome_local,
            email,
            senha: senhaHash,
            pin_admin,
            cidade,
            estado,
            telefone: telefone || null,
            plano: 'basic',
            data_vencimento: dataVencimento,
            is_owner: false,
            ativo: true
        });

        res.json({ success: true, clienteId });

    } catch (error) {
        console.error('Erro no cadastro:', error);
        res.status(500).json({ success: false, error: 'Erro ao criar conta' });
    }
});

module.exports = router;
