<?php
/**
 * TOTEM LUXX - Funções Helper
 */

/**
 * Redireciona para uma URL
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn(): bool {
    return isset($_SESSION['cliente_id']) && !empty($_SESSION['cliente_id']);
}

/**
 * Obtém o cliente logado
 */
function getCliente(): ?array {
    if (!isLoggedIn()) return null;
    
    return db()->fetchOne(
        "SELECT * FROM clientes WHERE id = ? AND ativo = 1",
        [$_SESSION['cliente_id']]
    );
}

/**
 * Requer autenticação
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        redirect('/login');
    }
}

/**
 * Requer que esteja em um módulo específico
 */
function requireModulo(string $modulo): void {
    requireAuth();
    if (!isset($_SESSION['modulo_atual']) || $_SESSION['modulo_atual'] !== $modulo) {
        redirect('/modulos');
    }
}

/**
 * Verifica o PIN do admin
 */
function verificarPinAdmin(string $pin): bool {
    $cliente = getCliente();
    return $cliente && $cliente['pin_admin'] === $pin;
}

/**
 * Sanitiza input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Retorna resposta JSON
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Valida email
 */
function validarEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Gera código único do cliente (A-01, A-02... B-01...)
 */
function gerarCodigoCliente(int $clienteId): string {
    $hoje = date('Y-m-d');
    
    // Buscar ou criar registro de códigos
    $codigo = db()->fetchOne(
        "SELECT * FROM codigos_clientes WHERE cliente_id = ?",
        [$clienteId]
    );
    
    if (!$codigo || $codigo['data_reset'] !== $hoje) {
        // Reset diário
        if ($codigo) {
            db()->update('codigos_clientes', [
                'prefixo' => 'A',
                'numero_atual' => 1,
                'data_reset' => $hoje
            ], 'cliente_id = ?', [$clienteId]);
        } else {
            db()->insert('codigos_clientes', [
                'cliente_id' => $clienteId,
                'prefixo' => 'A',
                'numero_atual' => 1,
                'data_reset' => $hoje
            ]);
        }
        return 'A-01';
    }
    
    // Incrementar
    $prefixo = $codigo['prefixo'];
    $numero = $codigo['numero_atual'] + 1;
    
    // Se passar de 99, muda a letra
    if ($numero > 99) {
        $prefixo = chr(ord($prefixo) + 1);
        $numero = 1;
        
        // Se passar de Z, volta pro A (improvável em um dia)
        if ($prefixo > 'Z') $prefixo = 'A';
    }
    
    db()->update('codigos_clientes', [
        'prefixo' => $prefixo,
        'numero_atual' => $numero
    ], 'cliente_id = ?', [$clienteId]);
    
    return $prefixo . '-' . str_pad($numero, 2, '0', STR_PAD_LEFT);
}

/**
 * Formata preço
 */
function formatarPreco(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Registra log
 */
function registrarLog(string $tipo, string $modulo, string $acao, string $descricao = '', array $dados = []): void {
    $clienteId = $_SESSION['cliente_id'] ?? null;
    
    db()->insert('logs', [
        'cliente_id' => $clienteId,
        'tipo' => $tipo,
        'modulo' => $modulo,
        'acao' => $acao,
        'descricao' => $descricao,
        'dados' => json_encode($dados),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

/**
 * Obtém configuração do cliente
 */
function getConfig(string $chave, $default = null) {
    if (!isLoggedIn()) return $default;
    
    $config = db()->fetchOne(
        "SELECT valor, tipo FROM configuracoes WHERE cliente_id = ? AND chave = ?",
        [$_SESSION['cliente_id'], $chave]
    );
    
    if (!$config) return $default;
    
    $valor = $config['valor'];
    
    return match($config['tipo']) {
        'int' => (int) $valor,
        'bool' => $valor === '1' || $valor === 'true',
        'json' => json_decode($valor, true),
        default => $valor
    };
}

/**
 * Define configuração do cliente
 */
function setConfig(string $chave, $valor, string $tipo = 'string'): void {
    if (!isLoggedIn()) return;
    
    $valorString = match($tipo) {
        'bool' => $valor ? '1' : '0',
        'json' => json_encode($valor),
        default => (string) $valor
    };
    
    $exists = db()->fetchOne(
        "SELECT id FROM configuracoes WHERE cliente_id = ? AND chave = ?",
        [$_SESSION['cliente_id'], $chave]
    );
    
    if ($exists) {
        db()->update('configuracoes', [
            'valor' => $valorString,
            'tipo' => $tipo
        ], 'cliente_id = ? AND chave = ?', [$_SESSION['cliente_id'], $chave]);
    } else {
        db()->insert('configuracoes', [
            'cliente_id' => $_SESSION['cliente_id'],
            'chave' => $chave,
            'valor' => $valorString,
            'tipo' => $tipo
        ]);
    }
}

/**
 * Valida CSRF token
 */
function gerarCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Asset URL helper
 */
function asset(string $path): string {
    return '/assets/' . ltrim($path, '/');
}

/**
 * Include de view com dados
 */
function view(string $view, array $data = []): void {
    extract($data);
    include __DIR__ . '/views/' . $view . '.php';
}

/**
 * Tempo relativo
 */
function tempoRelativo(string $datetime): string {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'agora';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    return floor($diff / 86400) . ' dias';
}
