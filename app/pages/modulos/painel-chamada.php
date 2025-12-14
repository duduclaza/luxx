<?php
/**
 * TOTEM LUXX - Painel de Chamada de Clientes
 * Exibe os clientes chamados para retirada
 */
requireAuth();
$cliente = getCliente();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - <?= htmlspecialchars($cliente['nome_local']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            overflow: hidden;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 30px rgba(34, 197, 94, 0.3); }
            50% { box-shadow: 0 0 60px rgba(34, 197, 94, 0.6); }
        }
        
        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slide-out {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(-100%); opacity: 0; }
        }
        
        .call-card {
            animation: slide-in 0.5s ease-out, pulse-glow 2s infinite;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05));
            border: 2px solid rgba(34, 197, 94, 0.5);
        }
        
        .call-card.exiting {
            animation: slide-out 0.5s ease-in forwards;
        }
        
        .timer-bar {
            transition: width 1s linear;
        }
    </style>
</head>
<body class="text-white p-8">
    
    <!-- Header -->
    <header class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <img src="/assets/img/logo.png" alt="Logo" class="h-12" onerror="this.style.display='none'">
            <h1 class="text-2xl font-bold"><?= htmlspecialchars($cliente['nome_local']) ?></h1>
        </div>
        <div class="text-right">
            <p class="text-gray-400 text-sm">Hora</p>
            <p id="clock" class="text-3xl font-bold">--:--</p>
        </div>
        <button onclick="openExitModal()" class="absolute top-4 right-4 p-2 opacity-30 hover:opacity-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </header>
    
    <!-- Área de chamadas -->
    <main id="callsContainer" class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">
        <!-- Chamadas aparecem aqui -->
    </main>
    
    <!-- Mensagem quando vazio -->
    <div id="emptyState" class="flex flex-col items-center justify-center" style="height: calc(100vh - 200px);">
        <div class="text-8xl mb-6">🍺</div>
        <p class="text-2xl text-gray-400">Aguardando chamadas...</p>
    </div>
    
    <!-- Modal PIN -->
    <div id="exitModal" class="fixed inset-0 bg-black/80 backdrop-blur hidden items-center justify-center z-50">
        <div class="bg-slate-800/90 rounded-2xl p-6 w-full max-w-sm border border-white/10">
            <h3 class="text-xl font-bold text-center mb-4">🔒 PIN do Admin</h3>
            <form onsubmit="return handleExit(event)">
                <input type="password" id="pinInput" maxlength="6" inputmode="numeric"
                    class="w-full bg-slate-900 border border-white/20 rounded-xl py-4 text-center text-2xl tracking-widest focus:outline-none focus:border-purple-500"
                    placeholder="••••">
                <p id="pinError" class="text-red-400 text-sm text-center mt-2 hidden">PIN incorreto</p>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeExitModal()" class="flex-1 py-3 bg-slate-700 rounded-xl">Cancelar</button>
                    <button type="submit" class="flex-1 py-3 bg-purple-600 rounded-xl font-medium">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Som -->
    <audio id="dingSound" preload="auto">
        <source src="/assets/audio/ding-dong.mp3" type="audio/mpeg">
    </audio>
    
    <script>
        let chamadas = [];
        const DURACAO_PADRAO = 30; // segundos
        
        // Demo
        // setTimeout(() => addChamada({ codigo: 'A-45', pedido: 72 }), 2000);
        // setTimeout(() => addChamada({ codigo: 'B-12', pedido: 85 }), 5000);
        
        function addChamada(data) {
            const chamada = {
                id: Date.now(),
                codigo: data.codigo,
                pedido: data.pedido,
                tempoRestante: data.duracao || DURACAO_PADRAO
            };
            
            chamadas.push(chamada);
            renderChamadas();
            playSound();
        }
        
        function renderChamadas() {
            const container = document.getElementById('callsContainer');
            const empty = document.getElementById('emptyState');
            
            if (chamadas.length === 0) {
                container.style.display = 'none';
                empty.style.display = 'flex';
                return;
            }
            
            container.style.display = 'grid';
            empty.style.display = 'none';
            
            container.innerHTML = chamadas.map(c => `
                <div class="call-card rounded-3xl p-8" id="call-${c.id}">
                    <div class="text-center">
                        <p class="text-green-400 text-lg mb-2">🔔 RETIRE SEU PEDIDO</p>
                        <div class="text-7xl font-extrabold mb-4">${c.codigo}</div>
                        <p class="text-2xl text-gray-300">Pedido #${c.pedido}</p>
                        <p class="text-green-400 text-xl mt-4">✅ PRONTO PARA RETIRADA</p>
                        <div class="mt-6 bg-white/10 rounded-full h-2 overflow-hidden">
                            <div class="timer-bar h-full bg-green-500" style="width: ${(c.tempoRestante / DURACAO_PADRAO) * 100}%"></div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function playSound() {
            const audio = document.getElementById('dingSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            }
        }
        
        // Atualizar tempos
        setInterval(() => {
            chamadas = chamadas.filter(c => {
                c.tempoRestante--;
                if (c.tempoRestante <= 0) {
                    const el = document.getElementById(`call-${c.id}`);
                    if (el) el.classList.add('exiting');
                    return false;
                }
                return true;
            });
            renderChamadas();
        }, 1000);
        
        // Relógio
        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }
        updateClock();
        setInterval(updateClock, 1000);
        
        // Polling para buscar chamadas
        function fetchChamadas() {
            fetch('/api/chamadas')
                .then(r => r.json())
                .then(data => {
                    if (data.chamadas) {
                        data.chamadas.forEach(c => {
                            if (!chamadas.find(x => x.pedido === c.pedido)) {
                                addChamada(c);
                            }
                        });
                    }
                })
                .catch(() => {});
        }
        setInterval(fetchChamadas, 3000);
        
        // Modal
        function openExitModal() {
            document.getElementById('exitModal').classList.remove('hidden');
            document.getElementById('exitModal').classList.add('flex');
            document.getElementById('pinInput').focus();
        }
        
        function closeExitModal() {
            document.getElementById('exitModal').classList.add('hidden');
            document.getElementById('exitModal').classList.remove('flex');
        }
        
        function handleExit(e) {
            e.preventDefault();
            const pin = document.getElementById('pinInput').value;
            if (pin === '1234') window.location.href = '/modulos';
            else {
                document.getElementById('pinError').classList.remove('hidden');
                document.getElementById('pinInput').value = '';
            }
            return false;
        }
        
        // Inicializar
        renderChamadas();
    </script>
</body>
</html>
