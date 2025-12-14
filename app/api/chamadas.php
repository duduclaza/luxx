<?php
/**
 * TOTEM LUXX - API de Chamadas de Clientes
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
    // Listar chamadas ativas
    $chamadas = db()->fetchAll(
        "SELECT cc.*, p.id as pedido
         FROM chamadas_clientes cc
         JOIN pedidos p ON p.id = cc.pedido_id
         WHERE cc.cliente_id = ? AND cc.ativo = 1 AND cc.exibir_ate > NOW()
         ORDER BY cc.created_at DESC",
        [$clienteId]
    );
    
    // Calcular tempo restante
    foreach ($chamadas as &$c) {
        $expirar = strtotime($c['exibir_ate']);
        $c['tempoRestante'] = max(0, $expirar - time());
        $c['codigo'] = $c['codigo_cliente'];
    }
    
    jsonResponse(['success' => true, 'chamadas' => $chamadas]);
    
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'chamar':
            $pedidoId = (int)($input['pedido_id'] ?? 0);
            $codigoCliente = sanitize($input['codigo_cliente'] ?? '');
            $duracao = (int)($input['duracao'] ?? 30);
            
            if (!$pedidoId || !$codigoCliente) {
                jsonResponse(['success' => false, 'error' => 'Dados inválidos'], 400);
            }
            
            // Verificar se já existe chamada ativa
            $existente = db()->fetchOne(
                "SELECT id FROM chamadas_clientes WHERE pedido_id = ? AND ativo = 1 AND exibir_ate > NOW()",
                [$pedidoId]
            );
            
            if ($existente) {
                // Atualizar tempo
                db()->update('chamadas_clientes', [
                    'exibir_ate' => date('Y-m-d H:i:s', time() + $duracao)
                ], 'id = ?', [$existente['id']]);
            } else {
                // Criar nova chamada
                db()->insert('chamadas_clientes', [
                    'cliente_id' => $clienteId,
                    'pedido_id' => $pedidoId,
                    'codigo_cliente' => $codigoCliente,
                    'numero_pedido' => $pedidoId,
                    'exibir_ate' => date('Y-m-d H:i:s', time() + $duracao)
                ]);
            }
            
            // Atualizar status do pedido
            db()->update('pedidos', [
                'status' => 'chamando',
                'vezes_chamado' => db()->fetchOne("SELECT vezes_chamado FROM pedidos WHERE id = ?", [$pedidoId])['vezes_chamado'] + 1,
                'ultima_chamada' => date('Y-m-d H:i:s')
            ], 'id = ?', [$pedidoId]);
            
            registrarLog('info', 'chamadas', 'cliente_chamado', "Cliente $codigoCliente chamado", ['pedido_id' => $pedidoId]);
            
            jsonResponse(['success' => true]);
            break;
            
        case 'encerrar':
            $pedidoId = (int)($input['pedido_id'] ?? 0);
            
            db()->update('chamadas_clientes', ['ativo' => 0], 'pedido_id = ?', [$pedidoId]);
            
            jsonResponse(['success' => true]);
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Ação inválida'], 400);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Método não permitido'], 405);
}
