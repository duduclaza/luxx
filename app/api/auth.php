<?php
/**
 * TOTEM LUXX - API de Autenticação
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'verify_pin':
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'error' => 'Não autenticado'], 401);
        }
        
        $pin = $input['pin'] ?? '';
        $cliente = getCliente();
        
        if ($cliente && $cliente['pin_admin'] === $pin) {
            // Limpar módulo atual
            unset($_SESSION['modulo_atual']);
            registrarLog('info', 'auth', 'pin_verificado', 'PIN verificado com sucesso');
            jsonResponse(['success' => true]);
        } else {
            registrarLog('warning', 'auth', 'pin_invalido', 'Tentativa de PIN inválido');
            jsonResponse(['success' => false, 'error' => 'PIN incorreto']);
        }
        break;
        
    case 'check_session':
        jsonResponse([
            'success' => true,
            'logged_in' => isLoggedIn(),
            'cliente' => isLoggedIn() ? [
                'id' => $_SESSION['cliente_id'],
                'nome' => $_SESSION['cliente_nome'] ?? ''
            ] : null
        ]);
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Ação inválida'], 400);
}
