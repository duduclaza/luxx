<?php
/**
 * TOTEM LUXX - Dashboard Admin
 */
requireAuth();
$cliente = getCliente();

// Estatísticas básicas
$stats = [
    'pedidosHoje' => db()->fetchOne("SELECT COUNT(*) as total FROM pedidos WHERE cliente_id = ? AND DATE(created_at) = CURDATE()", [$cliente['id']])['total'] ?? 0,
    'faturamentoHoje' => db()->fetchOne("SELECT COALESCE(SUM(total), 0) as total FROM pedidos WHERE cliente_id = ? AND DATE(created_at) = CURDATE() AND status_pagamento = 'aprovado'", [$cliente['id']])['total'] ?? 0,
    'pedidosPendentes' => db()->fetchOne("SELECT COUNT(*) as total FROM pedidos WHERE cliente_id = ? AND status NOT IN ('entregue', 'cancelado')", [$cliente['id']])['total'] ?? 0,
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= htmlspecialchars($cliente['nome_local']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background: #0f172a; }</style>
</head>
<body class="min-h-screen text-white">
    
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 bottom-0 w-64 bg-slate-900/50 border-r border-white/10 p-4">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-sm">TOTEM LUXX</p>
                <p class="text-gray-400 text-xs">Admin</p>
            </div>
        </div>
        
        <nav class="space-y-1">
            <a href="/admin" class="flex items-center gap-3 px-4 py-3 bg-purple-600/20 text-purple-400 rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="/admin/produtos" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Produtos
            </a>
            <a href="/admin/categorias" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categorias
            </a>
            <a href="/admin/ingressos" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                Ingressos
            </a>
            <a href="/admin/configuracoes" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Configurações
            </a>
        </nav>
        
        <div class="absolute bottom-4 left-4 right-4">
            <a href="/modulos" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Voltar aos Módulos
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="ml-64 p-8">
        <header class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p class="text-gray-400"><?= htmlspecialchars($cliente['nome_local']) ?></p>
            </div>
            <div class="text-right">
                <p class="text-gray-400 text-sm"><?= date('d/m/Y') ?></p>
                <p class="font-medium"><?= $cliente['email'] ?></p>
            </div>
        </header>
        
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-600/20 to-blue-800/20 border border-blue-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-blue-400">Pedidos Hoje</span>
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold"><?= $stats['pedidosHoje'] ?></p>
            </div>
            
            <div class="bg-gradient-to-br from-green-600/20 to-green-800/20 border border-green-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-green-400">Faturamento</span>
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold">R$ <?= number_format($stats['faturamentoHoje'], 2, ',', '.') ?></p>
            </div>
            
            <div class="bg-gradient-to-br from-amber-600/20 to-amber-800/20 border border-amber-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-amber-400">Pedidos Pendentes</span>
                    <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold"><?= $stats['pedidosPendentes'] ?></p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <h2 class="text-lg font-semibold mb-4">Ações Rápidas</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="/admin/produtos" class="bg-white/5 border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors text-center">
                <div class="text-3xl mb-2">📦</div>
                <p class="font-medium">Gerenciar Produtos</p>
            </a>
            <a href="/admin/configuracoes" class="bg-white/5 border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors text-center">
                <div class="text-3xl mb-2">⚙️</div>
                <p class="font-medium">Configurações</p>
            </a>
            <a href="/admin/ingressos" class="bg-white/5 border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors text-center">
                <div class="text-3xl mb-2">🎫</div>
                <p class="font-medium">Ingressos</p>
            </a>
            <a href="/modulos" class="bg-white/5 border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors text-center">
                <div class="text-3xl mb-2">📱</div>
                <p class="font-medium">Abrir Módulo</p>
            </a>
        </div>
    </main>
    
</body>
</html>
