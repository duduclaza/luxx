<?php
/**
 * TOTEM LUXX - Módulo Bilheteria
 * Venda de ingressos
 */
requireAuth();
$cliente = getCliente();

// Buscar ingressos disponíveis
$ingressos = db()->fetchAll(
    "SELECT * FROM ingressos WHERE cliente_id = ? AND disponivel = 1 AND ativo = 1 ORDER BY preco",
    [$cliente['id']]
);

// Ingressos demo
if (empty($ingressos)) {
    $ingressos = [
        ['id' => 1, 'nome' => 'Entrada Inteira', 'preco' => 40.00, 'descricao' => 'Acesso completo ao evento', 'cor' => '#8b5cf6'],
        ['id' => 2, 'nome' => 'Meia Entrada', 'preco' => 20.00, 'descricao' => 'Estudantes e idosos', 'cor' => '#22c55e'],
        ['id' => 3, 'nome' => 'VIP', 'preco' => 100.00, 'descricao' => 'Área exclusiva + open bar', 'cor' => '#f59e0b'],
        ['id' => 4, 'nome' => 'Camarote', 'preco' => 200.00, 'descricao' => 'Mesa reservada + atendimento', 'cor' => '#ef4444'],
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilheteria - <?= htmlspecialchars($cliente['nome_local']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%); min-height: 100vh; }
        .ticket-card { transition: all 0.3s; }
        .ticket-card:hover { transform: translateY(-4px); }
        .ticket-card.selected { ring: 2px; ring-color: white; }
    </style>
</head>
<body class="text-white p-6">
    
    <!-- Header -->
    <header class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold">🎫 Bilheteria</h1>
                <p class="text-gray-400 text-sm"><?= htmlspecialchars($cliente['nome_local']) ?></p>
            </div>
        </div>
        <button onclick="openExitModal()" class="p-2 opacity-50 hover:opacity-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
            </svg>
        </button>
    </header>
    
    <!-- Ingressos -->
    <main class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-center mb-8">Selecione seu Ingresso</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <?php foreach ($ingressos as $ing): ?>
            <div class="ticket-card bg-white/5 border-2 border-white/10 rounded-2xl p-6 cursor-pointer" 
                 onclick="selectTicket(<?= $ing['id'] ?>, '<?= htmlspecialchars($ing['nome']) ?>', <?= $ing['preco'] ?>)"
                 id="ticket-<?= $ing['id'] ?>">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" 
                         style="background: <?= $ing['cor'] ?? '#8b5cf6' ?>20; color: <?= $ing['cor'] ?? '#8b5cf6' ?>">
                        🎫
                    </div>
                    <span class="text-2xl font-bold" style="color: <?= $ing['cor'] ?? '#8b5cf6' ?>">
                        R$ <?= number_format($ing['preco'], 2, ',', '.') ?>
                    </span>
                </div>
                <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($ing['nome']) ?></h3>
                <p class="text-gray-400"><?= htmlspecialchars($ing['descricao'] ?? '') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Quantidade (aparece após selecionar) -->
        <div id="quantitySection" class="hidden bg-white/5 border border-white/10 rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Quantidade</h3>
            <div class="flex items-center justify-center gap-6">
                <button onclick="changeQuantity(-1)" class="w-14 h-14 bg-slate-700 hover:bg-slate-600 rounded-xl text-2xl font-bold">-</button>
                <span id="quantity" class="text-4xl font-bold w-20 text-center">1</span>
                <button onclick="changeQuantity(1)" class="w-14 h-14 bg-slate-700 hover:bg-slate-600 rounded-xl text-2xl font-bold">+</button>
            </div>
            <div class="text-center mt-6">
                <p class="text-gray-400">Total:</p>
                <p id="totalPrice" class="text-3xl font-bold text-green-400">R$ 0,00</p>
            </div>
        </div>
        
        <!-- Botão Comprar -->
        <button id="buyButton" onclick="comprar()" class="hidden w-full bg-gradient-to-r from-purple-600 to-pink-600 py-5 rounded-2xl font-bold text-xl shadow-lg shadow-purple-500/30">
            🛒 Comprar Agora
        </button>
    </main>
    
    <!-- Modal Pagamento -->
    <div id="paymentModal" class="fixed inset-0 bg-black/90 backdrop-blur hidden z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-3xl p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-2">💳 Pagamento</h2>
            <p id="paymentSummary" class="text-gray-400 text-center mb-8">1x Ingresso - R$ 40,00</p>
            
            <div class="space-y-4">
                <button onclick="pay('pix')" class="w-full bg-gradient-to-r from-teal-600 to-cyan-600 py-5 rounded-xl font-bold text-lg flex items-center justify-center gap-3">
                    <span class="text-3xl">📱</span> Pagar com PIX
                </button>
                <button onclick="pay('credito')" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 py-5 rounded-xl font-bold text-lg flex items-center justify-center gap-3">
                    <span class="text-3xl">💳</span> Cartão de Crédito
                </button>
                <button onclick="pay('debito')" class="w-full bg-gradient-to-r from-blue-600 to-sky-600 py-5 rounded-xl font-bold text-lg flex items-center justify-center gap-3">
                    <span class="text-3xl">💳</span> Cartão de Débito
                </button>
            </div>
            
            <button onclick="closePayment()" class="w-full mt-6 py-3 text-gray-400 hover:text-white">Cancelar</button>
        </div>
    </div>
    
    <!-- Modal Sucesso -->
    <div id="successModal" class="fixed inset-0 bg-black/95 hidden z-50 flex items-center justify-center p-4">
        <div class="text-center max-w-md">
            <div class="text-8xl mb-6">🎉</div>
            <h2 class="text-3xl font-bold mb-4">Compra Confirmada!</h2>
            <div class="bg-white/10 rounded-2xl p-6 mb-6">
                <p class="text-gray-400 mb-2">Seu código:</p>
                <p id="ticketCode" class="text-4xl font-extrabold text-purple-400 font-mono">XXXX-XXXX</p>
            </div>
            <p class="text-gray-400 mb-8">Apresente este código na entrada</p>
            <button onclick="novaCompra()" class="bg-purple-600 px-8 py-4 rounded-xl font-bold text-lg">Nova Compra</button>
        </div>
    </div>
    
    <!-- Modal PIN -->
    <div id="exitModal" class="fixed inset-0 bg-black/80 backdrop-blur hidden z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-2xl p-6 w-full max-w-sm border border-white/10">
            <h3 class="text-xl font-bold text-center mb-4">🔒 PIN do Admin</h3>
            <form onsubmit="return handleExit(event)">
                <input type="password" id="pinInput" maxlength="6" inputmode="numeric"
                    class="w-full bg-slate-900 border border-white/20 rounded-xl py-4 text-center text-2xl tracking-widest" placeholder="••••">
                <p id="pinError" class="text-red-400 text-sm text-center mt-2 hidden">PIN incorreto</p>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeExitModal()" class="flex-1 py-3 bg-slate-700 rounded-xl">Cancelar</button>
                    <button type="submit" class="flex-1 py-3 bg-purple-600 rounded-xl font-medium">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let selected = null;
        let quantity = 1;
        
        function selectTicket(id, nome, preco) {
            // Desselecionar anterior
            document.querySelectorAll('.ticket-card').forEach(c => c.classList.remove('ring-2', 'ring-white'));
            
            // Selecionar novo
            document.getElementById('ticket-' + id).classList.add('ring-2', 'ring-white');
            selected = { id, nome, preco };
            quantity = 1;
            
            document.getElementById('quantitySection').classList.remove('hidden');
            document.getElementById('buyButton').classList.remove('hidden');
            updateTotal();
        }
        
        function changeQuantity(delta) {
            quantity = Math.max(1, Math.min(10, quantity + delta));
            document.getElementById('quantity').textContent = quantity;
            updateTotal();
        }
        
        function updateTotal() {
            if (!selected) return;
            const total = selected.preco * quantity;
            document.getElementById('totalPrice').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
        }
        
        function comprar() {
            if (!selected) return;
            const total = selected.preco * quantity;
            document.getElementById('paymentSummary').textContent = `${quantity}x ${selected.nome} - R$ ${total.toFixed(2).replace('.', ',')}`;
            document.getElementById('paymentModal').classList.remove('hidden');
        }
        
        function closePayment() { document.getElementById('paymentModal').classList.add('hidden'); }
        
        function pay(method) {
            closePayment();
            // Simular processamento
            setTimeout(() => {
                const code = generateCode();
                document.getElementById('ticketCode').textContent = code;
                document.getElementById('successModal').classList.remove('hidden');
            }, 1000);
        }
        
        function generateCode() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let code = '';
            for (let i = 0; i < 8; i++) {
                if (i === 4) code += '-';
                code += chars[Math.floor(Math.random() * chars.length)];
            }
            return code;
        }
        
        function novaCompra() {
            document.getElementById('successModal').classList.add('hidden');
            document.querySelectorAll('.ticket-card').forEach(c => c.classList.remove('ring-2', 'ring-white'));
            document.getElementById('quantitySection').classList.add('hidden');
            document.getElementById('buyButton').classList.add('hidden');
            selected = null;
            quantity = 1;
        }
        
        // Exit
        function openExitModal() { document.getElementById('exitModal').classList.remove('hidden'); document.getElementById('pinInput').focus(); }
        function closeExitModal() { document.getElementById('exitModal').classList.add('hidden'); }
        function handleExit(e) {
            e.preventDefault();
            if (document.getElementById('pinInput').value === '1234') window.location.href = '/modulos';
            else { document.getElementById('pinError').classList.remove('hidden'); document.getElementById('pinInput').value = ''; }
            return false;
        }
    </script>
</body>
</html>
