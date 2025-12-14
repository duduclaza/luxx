<?php
/**
 * TOTEM LUXX - Entry Point
 * 
 * Todas as requisições passam por aqui
 */

session_start();

// Autoload e configurações
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';

// Router simples
$request = $_SERVER['REQUEST_URI'];
$basePath = '/'; // Ajuste se estiver em subdiretório

// Remove query string
$request = strtok($request, '?');

// Remove base path
if (strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath) - 1);
}

// Rotas
$routes = [
    // Páginas públicas
    '/' => 'pages/login.php',
    '/login' => 'pages/login.php',
    '/logout' => 'pages/logout.php',
    '/cadastro' => 'pages/cadastro.php',
    
    // Dashboard Admin
    '/admin' => 'pages/admin/dashboard.php',
    '/admin/configuracoes' => 'pages/admin/configuracoes.php',
    '/admin/produtos' => 'pages/admin/produtos.php',
    '/admin/categorias' => 'pages/admin/categorias.php',
    '/admin/ingressos' => 'pages/admin/ingressos.php',
    '/admin/relatorios' => 'pages/admin/relatorios.php',
    
    // Módulos do Totem
    '/modulos' => 'pages/modulos/selecao.php',
    '/cozinha' => 'pages/modulos/cozinha.php',
    '/bar' => 'pages/modulos/bar.php',
    '/cardapio' => 'pages/modulos/cardapio.php',
    '/bilheteria' => 'pages/modulos/bilheteria.php',
    '/painel' => 'pages/modulos/painel-chamada.php',
    
    // API
    '/api/pedidos' => 'api/pedidos.php',
    '/api/produtos' => 'api/produtos.php',
    '/api/chamadas' => 'api/chamadas.php',
    '/api/pagamento' => 'api/pagamento.php',
    '/api/auth' => 'api/auth.php',
];

// Encontrar rota
$found = false;
foreach ($routes as $route => $file) {
    if ($request === $route || $request === $route . '/') {
        $filePath = __DIR__ . '/../app/' . $file;
        if (file_exists($filePath)) {
            require_once $filePath;
            $found = true;
            break;
        }
    }
}

// 404
if (!$found) {
    http_response_code(404);
    include __DIR__ . '/../app/pages/404.php';
}
