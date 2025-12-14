<?php
/**
 * TOTEM LUXX - Módulo Cozinha (Kanban)
 */
requireAuth();
$cliente = getCliente();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cozinha - <?= htmlspecialchars($cliente['nome_local']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/modules.css">
</head>
<body class="gradient-bg min-h-screen text-white font-sans">
    
    <!-- Header -->
    <header class="header-bar">
        <div class="flex items-center gap-4">
            <div class="module-icon bg-gradient-to-br from-orange-500 to-red-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold">🍳 Cozinha</h1>
                <p class="text-gray-400 text-xs"><?= htmlspecialchars($cliente['nome_local']) ?></p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-xs text-gray-400">Atualizado</p>
                <p id="lastUpdate" class="text-sm font-medium">--:--:--</p>
            </div>
            <button onclick="openExitModal()" class="exit-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </div>
    </header>
    
    <!-- Kanban Board -->
    <main class="kanban-container">
        <div class="kanban-board">
            <!-- Coluna: Novos -->
            <div class="kanban-column">
                <div class="column-header bg-blue-500/10 border-blue-500/30">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <h2>Novos Pedidos</h2>
                    <span id="countNovos" class="badge bg-blue-500/20 text-blue-400">0</span>
                </div>
                <div id="columnNovos" class="column-content"></div>
            </div>
            
            <!-- Coluna: Preparando -->
            <div class="kanban-column">
                <div class="column-header bg-amber-500/10 border-amber-500/30">
                    <span class="w-3 h-3 bg-amber-500 rounded-full animate-pulse"></span>
                    <h2>Preparando</h2>
                    <span id="countPreparando" class="badge bg-amber-500/20 text-amber-400">0</span>
                </div>
                <div id="columnPreparando" class="column-content"></div>
            </div>
            
            <!-- Coluna: Pronto -->
            <div class="kanban-column">
                <div class="column-header bg-green-500/10 border-green-500/30">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    <h2>Pronto</h2>
                    <span id="countPronto" class="badge bg-green-500/20 text-green-400">0</span>
                </div>
                <div id="columnPronto" class="column-content"></div>
            </div>
        </div>
    </main>
    
    <!-- Modal PIN -->
    <div id="exitModal" class="modal-backdrop hidden">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-center mb-4">🔒 PIN do Admin</h3>
            <form id="exitForm" onsubmit="return handleExit(event)">
                <input type="password" id="pinInput" maxlength="6" inputmode="numeric" 
                    class="pin-input" placeholder="••••">
                <p id="pinError" class="text-red-400 text-sm text-center mt-2 hidden">PIN incorreto</p>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeExitModal()" class="btn-secondary flex-1">Cancelar</button>
                    <button type="submit" class="btn-primary flex-1">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/assets/js/cozinha.js"></script>
</body>
</html>
