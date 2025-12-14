<?php
/**
 * TOTEM LUXX - Seleção de Módulos
 * 
 * Esta é a tela inicial após o login onde o admin
 * pode escolher qual módulo abrir no totem
 */

requireAuth();

$cliente = getCliente();
$error = '';

// Processar seleção de módulo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modulo = sanitize($_POST['modulo'] ?? '');
    
    $modulosValidos = ['cozinha', 'bar', 'cardapio', 'bilheteria', 'painel', 'admin'];
    
    if (in_array($modulo, $modulosValidos)) {
        $_SESSION['modulo_atual'] = $modulo;
        $_SESSION['modulo_iniciado_em'] = time();
        
        registrarLog('info', $modulo, 'modulo_aberto', "Módulo $modulo iniciado");
        
        redirect('/' . $modulo);
    } else {
        $error = 'Módulo inválido.';
    }
}

// Módulos disponíveis
$modulos = [
    [
        'id' => 'cozinha',
        'nome' => 'Cozinha',
        'descricao' => 'Kanban de pedidos para preparação',
        'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>',
        'cor' => 'from-orange-500 to-red-500',
        'shadow' => 'shadow-orange-500/30'
    ],
    [
        'id' => 'bar',
        'nome' => 'Bar',
        'descricao' => 'Controle de bebidas e atendimento',
        'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'cor' => 'from-amber-500 to-yellow-500',
        'shadow' => 'shadow-amber-500/30'
    ],
    [
        'id' => 'cardapio',
        'nome' => 'Cardápio',
        'descricao' => 'Menu digital para clientes',
        'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
        'cor' => 'from-green-500 to-emerald-500',
        'shadow' => 'shadow-green-500/30'
    ],
    [
        'id' => 'bilheteria',
        'nome' => 'Bilheteria',
        'descricao' => 'Venda de ingressos',
        'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>',
        'cor' => 'from-purple-500 to-pink-500',
        'shadow' => 'shadow-purple-500/30'
    ],
    [
        'id' => 'painel',
        'nome' => 'Painel de Chamada',
        'descricao' => 'Exibe clientes para retirada',
        'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
        'cor' => 'from-cyan-500 to-blue-500',
        'shadow' => 'shadow-cyan-500/30'
    ],
    [
        'id' => 'admin',
        'nome' => 'Administração',
        'descricao' => 'Configurações e relatórios',
        'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'cor' => 'from-slate-500 to-slate-700',
        'shadow' => 'shadow-slate-500/30'
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Módulo - TOTEM LUXX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        dark: {
                            300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b',
                            600: '#475569', 700: '#334155', 800: '#1e293b',
                            900: '#0f172a', 950: '#020617'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .module-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .module-card:hover { transform: translateY(-8px) scale(1.02); }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-15px); } }
        .floating { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    
    <!-- Background Effects -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-72 h-72 bg-purple-500/10 rounded-full blur-3xl floating"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl floating" style="animation-delay: -3s;"></div>
    </div>

    <div class="relative z-10 min-h-screen p-6">
        <!-- Header -->
        <header class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white"><?= htmlspecialchars($cliente['nome_local']) ?></h1>
                    <p class="text-dark-400 text-sm"><?= $cliente['cidade'] ?> - <?= $cliente['estado'] ?></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-dark-400 text-sm"><?= $cliente['email'] ?></span>
                <a href="/logout" class="p-2 hover:bg-dark-800 rounded-lg transition-colors text-dark-400 hover:text-white" title="Sair">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </header>
        
        <!-- Title -->
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-white mb-2">Selecione o Módulo</h2>
            <p class="text-dark-400">Escolha qual módulo deseja abrir neste dispositivo</p>
        </div>
        
        <?php if ($error): ?>
        <div class="max-w-4xl mx-auto bg-red-500/20 border border-red-500/30 rounded-xl p-4 mb-6">
            <p class="text-red-300 text-center"><?= $error ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Modules Grid -->
        <div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($modulos as $modulo): ?>
            <form method="POST" class="module-card">
                <input type="hidden" name="modulo" value="<?= $modulo['id'] ?>">
                <button type="submit" class="w-full text-left glass-card rounded-2xl p-6 hover:border-white/20 group cursor-pointer">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br <?= $modulo['cor'] ?> rounded-xl flex items-center justify-center <?= $modulo['shadow'] ?> shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?= $modulo['icone'] ?>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-dark-600 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-1"><?= $modulo['nome'] ?></h3>
                    <p class="text-dark-400 text-sm"><?= $modulo['descricao'] ?></p>
                </button>
            </form>
            <?php endforeach; ?>
        </div>
        
        <!-- Footer Info -->
        <div class="max-w-5xl mx-auto mt-10 text-center">
            <div class="glass-card rounded-xl p-4 inline-flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-dark-300 text-sm">
                    <strong class="text-amber-400">Atenção:</strong> 
                    Para sair de um módulo será necessário inserir o PIN do administrador.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
