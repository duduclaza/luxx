<?php
/**
 * TOTEM LUXX - API de Pedidos
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Não autenticado'], 401);
}

$clienteId = $_SESSION['cliente_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Listar pedidos
    $destino = sanitize($_GET['destino'] ?? '');
    $status = sanitize($_GET['status'] ?? '');
    
    $where = "p.cliente_id = ?";
    $params = [$clienteId];
    
    if ($status) {
        $where .= " AND p.status = ?";
        $params[] = $status;
    }
    
    // Pegar pedidos com itens
    $sql = "SELECT p.*, 
            (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', pi.id, 'nome', pi.nome, 'qtd', pi.quantidade, 'destino', pi.destino, 'status', pi.status))
             FROM pedido_itens pi WHERE pi.pedido_id = p.id" . ($destino ? " AND pi.destino = '$destino'" : "") . ") as itens
            FROM pedidos p
            WHERE $where AND p.status != 'entregue' AND p.status != 'cancelado'
            ORDER BY p.created_at DESC";
    
    $pedidos = db()->fetchAll($sql, $params);
    
    // Processar para separar itens por destino
    foreach ($pedidos as &$pedido) {
        $pedido['itens'] = json_decode($pedido['itens'] ?? '[]', true) ?: [];
    }
    
    jsonResponse(['success' => true, 'pedidos' => $pedidos]);
    
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'criar':
            // Criar novo pedido
            $itens = $input['itens'] ?? [];
            
            if (empty($itens)) {
                jsonResponse(['success' => false, 'error' => 'Nenhum item no pedido'], 400);
            }
            
            $codigoCliente = gerarCodigoCliente($clienteId);
            $total = 0;
            
            // Calcular se vai para cozinha e/ou bar
            $temCozinha = false;
            $temBar = false;
            
            foreach ($itens as $item) {
                $total += $item['preco'] * $item['quantidade'];
                if ($item['destino'] === 'cozinha') $temCozinha = true;
                if ($item['destino'] === 'bar') $temBar = true;
            }
            
            // Criar pedido
            $pedidoId = db()->insert('pedidos', [
                'cliente_id' => $clienteId,
                'codigo_cliente' => $codigoCliente,
                'status' => 'pendente',
                'status_cozinha' => $temCozinha ? 'pendente' : 'na',
                'status_bar' => $temBar ? 'pendente' : 'na',
                'subtotal' => $total,
                'total' => $total,
                'origem' => $input['origem'] ?? 'cardapio',
                'forma_pagamento' => $input['forma_pagamento'] ?? 'pendente'
            ]);
            
            // Inserir itens
            foreach ($itens as $item) {
                db()->insert('pedido_itens', [
                    'pedido_id' => $pedidoId,
                    'produto_id' => $item['produto_id'] ?? null,
                    'ingresso_id' => $item['ingresso_id'] ?? null,
                    'nome' => $item['nome'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'preco_total' => $item['preco'] * $item['quantidade'],
                    'destino' => $item['destino'],
                    'observacoes' => $item['observacoes'] ?? null
                ]);
            }
            
            registrarLog('info', 'pedidos', 'pedido_criado', "Pedido #$pedidoId criado", ['pedido_id' => $pedidoId]);
            
            jsonResponse([
                'success' => true, 
                'pedido_id' => $pedidoId,
                'codigo_cliente' => $codigoCliente,
                'total' => $total
            ]);
            break;
            
        case 'update_status':
            $pedidoId = (int)($input['pedido_id'] ?? 0);
            $novoStatus = sanitize($input['status'] ?? '');
            $destino = sanitize($input['destino'] ?? '');
            
            if (!$pedidoId || !$novoStatus) {
                jsonResponse(['success' => false, 'error' => 'Dados inválidos'], 400);
            }
            
            // Atualizar status do item ou do pedido
            if ($destino) {
                // Atualizar itens do destino
                db()->query(
                    "UPDATE pedido_itens SET status = ? WHERE pedido_id = ? AND destino = ?",
                    [$novoStatus, $pedidoId, $destino]
                );
                
                // Atualizar status específico do pedido
                $campo = "status_$destino";
                db()->update('pedidos', [$campo => $novoStatus], 'id = ?', [$pedidoId]);
                
                // Verificar se tudo está pronto
                $pedido = db()->fetchOne("SELECT * FROM pedidos WHERE id = ?", [$pedidoId]);
                if ($pedido) {
                    $cozinhaOk = $pedido['status_cozinha'] === 'pronto' || $pedido['status_cozinha'] === 'na';
                    $barOk = $pedido['status_bar'] === 'pronto' || $pedido['status_bar'] === 'na';
                    
                    if ($cozinhaOk && $barOk) {
                        db()->update('pedidos', ['status' => 'pronto'], 'id = ?', [$pedidoId]);
                    } else {
                        db()->update('pedidos', ['status' => 'preparando'], 'id = ?', [$pedidoId]);
                    }
                }
            } else {
                db()->update('pedidos', ['status' => $novoStatus], 'id = ?', [$pedidoId]);
            }
            
            jsonResponse(['success' => true]);
            break;
            
        case 'entregar':
            $pedidoId = (int)($input['pedido_id'] ?? 0);
            
            db()->update('pedidos', [
                'status' => 'entregue',
                'finalizado_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$pedidoId]);
            
            registrarLog('info', 'pedidos', 'pedido_entregue', "Pedido #$pedidoId entregue");
            
            jsonResponse(['success' => true]);
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Ação inválida'], 400);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Método não permitido'], 405);
}
