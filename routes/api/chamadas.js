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

// GET /api/chamadas - Listar chamadas ativas
router.get('/', requireAuth, async (req, res) => {
    try {
        const clienteId = req.session.clienteId;

        const chamadas = await db.fetchAll(`
            SELECT cc.*, p.id as pedido,
                   TIMESTAMPDIFF(SECOND, NOW(), cc.exibir_ate) as tempo_restante
            FROM chamadas_clientes cc
            JOIN pedidos p ON p.id = cc.pedido_id
            WHERE cc.cliente_id = ? AND cc.ativo = 1 AND cc.exibir_ate > NOW()
            ORDER BY cc.created_at DESC
        `, [clienteId]);

        res.json({
            success: true,
            chamadas: chamadas.map(c => ({
                ...c,
                codigo: c.codigo_cliente,
                tempoRestante: Math.max(0, c.tempo_restante)
            }))
        });

    } catch (error) {
        console.error('Erro ao listar chamadas:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

// POST /api/chamadas - Chamar cliente ou encerrar chamada
router.post('/', requireAuth, async (req, res) => {
    try {
        const clienteId = req.session.clienteId;
        const { action, pedido_id, codigo_cliente, duracao = 30 } = req.body;

        switch (action) {
            case 'chamar': {
                if (!pedido_id || !codigo_cliente) {
                    return res.status(400).json({ success: false, error: 'Dados inválidos' });
                }

                const expirarEm = new Date(Date.now() + duracao * 1000);
                const expirarStr = expirarEm.toISOString().slice(0, 19).replace('T', ' ');

                // Verificar se já existe chamada ativa
                const existente = await db.fetchOne(
                    'SELECT id FROM chamadas_clientes WHERE pedido_id = ? AND ativo = 1 AND exibir_ate > NOW()',
                    [pedido_id]
                );

                if (existente) {
                    await db.update('chamadas_clientes', {
                        exibir_ate: expirarStr
                    }, 'id = ?', [existente.id]);
                } else {
                    await db.insert('chamadas_clientes', {
                        cliente_id: clienteId,
                        pedido_id,
                        codigo_cliente,
                        numero_pedido: pedido_id,
                        exibir_ate: expirarStr
                    });
                }

                // Atualizar pedido
                await db.query(`
                    UPDATE pedidos 
                    SET status = 'chamando', 
                        vezes_chamado = vezes_chamado + 1,
                        ultima_chamada = NOW()
                    WHERE id = ?
                `, [pedido_id]);

                res.json({ success: true });
                break;
            }

            case 'encerrar': {
                await db.update('chamadas_clientes', { ativo: 0 }, 'pedido_id = ?', [pedido_id]);
                res.json({ success: true });
                break;
            }

            default:
                res.status(400).json({ success: false, error: 'Ação inválida' });
        }

    } catch (error) {
        console.error('Erro em chamadas:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

module.exports = router;
