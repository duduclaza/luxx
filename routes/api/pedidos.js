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

// GET /api/pedidos - Listar pedidos
router.get('/', requireAuth, async (req, res) => {
    try {
        const clienteId = req.session.clienteId;
        const { destino, status } = req.query;

        let sql = `
            SELECT p.*, 
                   DATE_FORMAT(p.created_at, '%H:%i') as hora,
                   TIMESTAMPDIFF(SECOND, p.created_at, NOW()) as tempo_segundos
            FROM pedidos p
            WHERE p.cliente_id = ? AND p.status NOT IN ('entregue', 'cancelado')
        `;
        const params = [clienteId];

        if (status) {
            sql += ' AND p.status = ?';
            params.push(status);
        }

        sql += ' ORDER BY p.created_at DESC';

        const pedidos = await db.fetchAll(sql, params);

        // Buscar itens de cada pedido
        for (let pedido of pedidos) {
            let itensSql = 'SELECT * FROM pedido_itens WHERE pedido_id = ?';
            const itensParams = [pedido.id];

            if (destino) {
                itensSql += ' AND destino = ?';
                itensParams.push(destino);
            }

            pedido.itens = await db.fetchAll(itensSql, itensParams);
        }

        // Filtrar pedidos que têm itens (quando filtrado por destino)
        const pedidosFiltrados = destino
            ? pedidos.filter(p => p.itens.length > 0)
            : pedidos;

        res.json({ success: true, pedidos: pedidosFiltrados });

    } catch (error) {
        console.error('Erro ao listar pedidos:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

// POST /api/pedidos - Criar pedido ou atualizar status
router.post('/', requireAuth, async (req, res) => {
    try {
        const clienteId = req.session.clienteId;
        const { action } = req.body;

        switch (action) {
            case 'criar': {
                const { itens, forma_pagamento, origem } = req.body;

                if (!itens || itens.length === 0) {
                    return res.status(400).json({ success: false, error: 'Nenhum item no pedido' });
                }

                // Gerar código do cliente
                const codigoCliente = await gerarCodigoCliente(clienteId);

                // Calcular total e verificar destinos
                let total = 0;
                let temCozinha = false;
                let temBar = false;

                for (const item of itens) {
                    total += item.preco * item.quantidade;
                    if (item.destino === 'cozinha') temCozinha = true;
                    if (item.destino === 'bar') temBar = true;
                }

                // Criar pedido
                const pedidoId = await db.insert('pedidos', {
                    cliente_id: clienteId,
                    codigo_cliente: codigoCliente,
                    status: 'pendente',
                    status_cozinha: temCozinha ? 'pendente' : 'na',
                    status_bar: temBar ? 'pendente' : 'na',
                    subtotal: total,
                    total: total,
                    origem: origem || 'cardapio',
                    forma_pagamento: forma_pagamento || 'pendente'
                });

                // Inserir itens
                for (const item of itens) {
                    await db.insert('pedido_itens', {
                        pedido_id: pedidoId,
                        produto_id: item.produto_id || null,
                        ingresso_id: item.ingresso_id || null,
                        nome: item.nome,
                        quantidade: item.quantidade,
                        preco_unitario: item.preco,
                        preco_total: item.preco * item.quantidade,
                        destino: item.destino || 'cozinha',
                        observacoes: item.observacoes || null
                    });
                }

                res.json({ success: true, pedido_id: pedidoId, codigo_cliente: codigoCliente, total });
                break;
            }

            case 'update_status': {
                const { pedido_id, status: novoStatus, destino } = req.body;

                if (!pedido_id || !novoStatus) {
                    return res.status(400).json({ success: false, error: 'Dados inválidos' });
                }

                if (destino) {
                    // Atualizar itens do destino
                    await db.query(
                        'UPDATE pedido_itens SET status = ? WHERE pedido_id = ? AND destino = ?',
                        [novoStatus, pedido_id, destino]
                    );

                    // Atualizar status específico
                    const campo = `status_${destino}`;
                    await db.query(
                        `UPDATE pedidos SET ${campo} = ? WHERE id = ?`,
                        [novoStatus, pedido_id]
                    );

                    // Verificar se tudo está pronto
                    const pedido = await db.fetchOne('SELECT * FROM pedidos WHERE id = ?', [pedido_id]);
                    if (pedido) {
                        const cozinhaOk = pedido.status_cozinha === 'pronto' || pedido.status_cozinha === 'na';
                        const barOk = pedido.status_bar === 'pronto' || pedido.status_bar === 'na';

                        const statusGeral = (cozinhaOk && barOk) ? 'pronto' : 'preparando';
                        await db.update('pedidos', { status: statusGeral }, 'id = ?', [pedido_id]);
                    }
                } else {
                    await db.update('pedidos', { status: novoStatus }, 'id = ?', [pedido_id]);
                }

                res.json({ success: true });
                break;
            }

            case 'entregar': {
                const { pedido_id } = req.body;

                await db.update('pedidos', {
                    status: 'entregue',
                    finalizado_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
                }, 'id = ?', [pedido_id]);

                res.json({ success: true });
                break;
            }

            default:
                res.status(400).json({ success: false, error: 'Ação inválida' });
        }

    } catch (error) {
        console.error('Erro em pedidos:', error);
        res.status(500).json({ success: false, error: 'Erro interno' });
    }
});

// Função para gerar código do cliente
async function gerarCodigoCliente(clienteId) {
    const hoje = new Date().toISOString().split('T')[0];

    let codigo = await db.fetchOne(
        'SELECT * FROM codigos_clientes WHERE cliente_id = ?',
        [clienteId]
    );

    if (!codigo || codigo.data_reset !== hoje) {
        // Reset diário
        if (codigo) {
            await db.update('codigos_clientes', {
                prefixo: 'A',
                numero_atual: 1,
                data_reset: hoje
            }, 'cliente_id = ?', [clienteId]);
        } else {
            await db.insert('codigos_clientes', {
                cliente_id: clienteId,
                prefixo: 'A',
                numero_atual: 1,
                data_reset: hoje
            });
        }
        return 'A-01';
    }

    let prefixo = codigo.prefixo;
    let numero = codigo.numero_atual + 1;

    if (numero > 99) {
        prefixo = String.fromCharCode(prefixo.charCodeAt(0) + 1);
        numero = 1;
        if (prefixo > 'Z') prefixo = 'A';
    }

    await db.update('codigos_clientes', {
        prefixo,
        numero_atual: numero
    }, 'cliente_id = ?', [clienteId]);

    return `${prefixo}-${String(numero).padStart(2, '0')}`;
}

module.exports = router;
