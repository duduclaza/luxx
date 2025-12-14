const express = require('express');
const router = express.Router();
const db = require('../../config/database');

// Middleware de autenticação
const requireAuth = (req, res, next) => {
    if (!req.session.clienteId) {
        return res.status(401).json({ success: false, error: 'Não autenticado' });
    }
    next();
};

// GET /api/produtos - Listar produtos
router.get('/', requireAuth, async (req, res) => {
    try {
        const clienteId = req.session.clienteId;
        const { categoria, destino } = req.query;

        let sql = 'SELECT * FROM produtos WHERE cliente_id = ? AND ativo = 1 AND disponivel = 1';
        const params = [clienteId];

        if (categoria) {
            sql += ' AND categoria_id = ?';
            params.push(categoria);
        }

        if (destino) {
            sql += ' AND destino = ?';
            params.push(destino);
        }

        sql += ' ORDER BY destaque DESC, ordem, nome';

        const produtos = await db.fetchAll(sql, params);

        res.json({ success: true, produtos });

    } catch (error) {
        console.error('Erro ao listar produtos:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

// POST /api/produtos - Criar/atualizar produto
router.post('/', requireAuth, async (req, res) => {
    try {
        const clienteId = req.session.clienteId;
        const { action, id, ...data } = req.body;

        switch (action) {
            case 'criar': {
                const produtoId = await db.insert('produtos', {
                    cliente_id: clienteId,
                    categoria_id: data.categoria_id || 1,
                    nome: data.nome,
                    descricao: data.descricao || null,
                    preco: data.preco,
                    destino: data.destino || 'cozinha',
                    disponivel: true,
                    ativo: true
                });

                res.json({ success: true, produto_id: produtoId });
                break;
            }

            case 'atualizar': {
                if (!id) {
                    return res.status(400).json({ success: false, error: 'ID obrigatório' });
                }

                await db.update('produtos', {
                    nome: data.nome,
                    descricao: data.descricao,
                    preco: data.preco,
                    destino: data.destino,
                    disponivel: data.disponivel ?? true
                }, 'id = ? AND cliente_id = ?', [id, clienteId]);

                res.json({ success: true });
                break;
            }

            case 'deletar': {
                if (!id) {
                    return res.status(400).json({ success: false, error: 'ID obrigatório' });
                }

                await db.update('produtos', { ativo: false }, 'id = ? AND cliente_id = ?', [id, clienteId]);
                res.json({ success: true });
                break;
            }

            default:
                res.status(400).json({ success: false, error: 'Ação inválida' });
        }

    } catch (error) {
        console.error('Erro em produtos:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

// GET /api/produtos/categorias - Listar categorias
router.get('/categorias', requireAuth, async (req, res) => {
    try {
        const clienteId = req.session.clienteId;

        const categorias = await db.fetchAll(
            'SELECT * FROM categorias WHERE cliente_id = ? AND ativo = 1 ORDER BY ordem, nome',
            [clienteId]
        );

        res.json({ success: true, categorias });

    } catch (error) {
        console.error('Erro ao listar categorias:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

module.exports = router;
