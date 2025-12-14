<?php
/**
 * TOTEM LUXX - Módulo Cardápio
 * Menu digital com carrinho de compras
 */
requireAuth();
$cliente = getCliente();

// Buscar categorias e produtos
$categorias = db()->fetchAll(
    "SELECT * FROM categorias WHERE cliente_id = ? AND tipo IN ('cozinha', 'bar') AND ativo = 1 ORDER BY ordem, nome",
    [$cliente['id']]
);

$produtos = db()->fetchAll(
    "SELECT * FROM produtos WHERE cliente_id = ? AND disponivel = 1 AND ativo = 1 ORDER BY destaque DESC, ordem, nome",
    [$cliente['id']]
);

// Se não tem produtos, criar alguns de exemplo
if (empty($produtos)) {
    $produtosDemo = [
        ['nome' => 'Hambúrguer Artesanal', 'preco' => 28.90, 'descricao' => 'Pão brioche, blend 180g, queijo, bacon', 'destino' => 'cozinha', 'destaque' => 1],
        ['nome' => 'Porção de Batata', 'preco' => 22.00, 'descricao' => 'Batata frita crocante com molho', 'destino' => 'cozinha', 'destaque' => 0],
        ['nome' => 'Asa de Frango', 'preco' => 32.00, 'descricao' => '12 unidades empanadas', 'destino' => 'cozinha', 'destaque' => 0],
        ['nome' => 'Cerveja Heineken', 'preco' => 12.00, 'descricao' => 'Long neck 330ml', 'destino' => 'bar', 'destaque' => 1],
        ['nome' => 'Caipirinha', 'preco' => 18.00, 'descricao' => 'Limão, cachaça, açúcar', 'destino' => 'bar', 'destaque' => 0],
        ['nome' => 'Coca-Cola', 'preco' => 8.00, 'descricao' => '350ml', 'destino' => 'bar', 'destaque' => 0],
    ];
    $produtos = $produtosDemo;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cardápio - <?= htmlspecialchars($cliente['nome_local']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        .product-card { transition: transform 0.2s; }
        .product-card:active { transform: scale(0.98); }
        .cart-badge { animation: bounce 0.3s; }
        @keyframes bounce { 0%,100% { transform: scale(1); } 50% { transform: scale(1.2); } }
        .modal-slide { animation: slideUp 0.3s ease-out; }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    </style>
</head>
<body class="min-h-screen text-white">
    
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-slate-900/95 backdrop-blur-lg border-b border-white/10 px-4 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold">📋 Cardápio</h1>
                    <p class="text-gray-400 text-xs"><?= htmlspecialchars($cliente['nome_local']) ?></p>
                </div>
            </div>
            
            <button onclick="openExitModal()" class="p-2 opacity-50 hover:opacity-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                </svg>
            </button>
        </div>
    </header>
    
    <!-- Produtos -->
    <main class="p-4 pb-24">
        <h2 class="text-lg font-semibold mb-4">🍔 Lanches & Porções</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
            <?php foreach ($produtos as $p): if (($p['destino'] ?? 'cozinha') === 'cozinha'): ?>
            <div class="product-card bg-white/5 border border-white/10 rounded-2xl p-3 cursor-pointer" onclick='addToCart(<?= json_encode($p) ?>)'>
                <div class="aspect-square bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl mb-3 flex items-center justify-center text-4xl">
                    🍔
                </div>
                <h3 class="font-semibold text-sm mb-1"><?= htmlspecialchars($p['nome']) ?></h3>
                <p class="text-gray-400 text-xs mb-2 line-clamp-2"><?= htmlspecialchars($p['descricao'] ?? '') ?></p>
                <p class="text-green-400 font-bold">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
            </div>
            <?php endif; endforeach; ?>
        </div>
        
        <h2 class="text-lg font-semibold mb-4">🍺 Bebidas</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php foreach ($produtos as $p): if (($p['destino'] ?? 'cozinha') === 'bar'): ?>
            <div class="product-card bg-white/5 border border-white/10 rounded-2xl p-3 cursor-pointer" onclick='addToCart(<?= json_encode($p) ?>)'>
                <div class="aspect-square bg-gradient-to-br from-amber-500/20 to-yellow-500/20 rounded-xl mb-3 flex items-center justify-center text-4xl">
                    🍺
                </div>
                <h3 class="font-semibold text-sm mb-1"><?= htmlspecialchars($p['nome']) ?></h3>
                <p class="text-gray-400 text-xs mb-2"><?= htmlspecialchars($p['descricao'] ?? '') ?></p>
                <p class="text-green-400 font-bold">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
            </div>
            <?php endif; endforeach; ?>
        </div>
    </main>
    
    <!-- Carrinho Flutuante -->
    <div id="cartBar" class="fixed bottom-0 left-0 right-0 bg-slate-800/95 backdrop-blur-lg border-t border-white/10 p-4 hidden">
        <button onclick="openCart()" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 py-4 rounded-xl flex items-center justify-between px-6 font-semibold">
            <div class="flex items-center gap-3">
                <span class="bg-white/20 px-3 py-1 rounded-lg" id="cartCount">0</span>
                <span>Ver Carrinho</span>
            </div>
            <span id="cartTotal">R$ 0,00</span>
        </button>
    </div>
    
    <!-- Modal Carrinho -->
    <div id="cartModal" class="fixed inset-0 bg-black/80 backdrop-blur hidden z-50">
        <div class="absolute bottom-0 left-0 right-0 bg-slate-900 rounded-t-3xl max-h-[90vh] overflow-hidden modal-slide">
            <div class="p-4 border-b border-white/10 flex justify-between items-center">
                <h2 class="text-xl font-bold">🛒 Seu Pedido</h2>
                <button onclick="closeCart()" class="p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="cartItems" class="p-4 overflow-y-auto" style="max-height: 50vh;"></div>
            <div class="p-4 border-t border-white/10">
                <div class="flex justify-between text-lg font-bold mb-4">
                    <span>Total</span>
                    <span id="cartTotalModal">R$ 0,00</span>
                </div>
                <button onclick="finalizarPedido()" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 py-4 rounded-xl font-bold text-lg">
                    Finalizar Pedido
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Pagamento -->
    <div id="paymentModal" class="fixed inset-0 bg-black/80 backdrop-blur hidden z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-3xl p-6 w-full max-w-md">
            <h2 class="text-xl font-bold text-center mb-6">💳 Forma de Pagamento</h2>
            <div class="space-y-3">
                <button onclick="processPayment('pix')" class="w-full bg-teal-600 hover:bg-teal-500 py-4 rounded-xl font-semibold flex items-center justify-center gap-3">
                    <span class="text-2xl">📱</span> PIX
                </button>
                <button onclick="processPayment('credito')" class="w-full bg-purple-600 hover:bg-purple-500 py-4 rounded-xl font-semibold flex items-center justify-center gap-3">
                    <span class="text-2xl">💳</span> Crédito
                </button>
                <button onclick="processPayment('debito')" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-xl font-semibold flex items-center justify-center gap-3">
                    <span class="text-2xl">💳</span> Débito
                </button>
            </div>
            <button onclick="closePayment()" class="w-full mt-4 py-3 text-gray-400">Cancelar</button>
        </div>
    </div>
    
    <!-- Modal Sucesso -->
    <div id="successModal" class="fixed inset-0 bg-black/90 hidden z-50 flex items-center justify-center p-4">
        <div class="text-center">
            <div class="text-8xl mb-6">✅</div>
            <h2 class="text-3xl font-bold mb-2">Pedido Confirmado!</h2>
            <p class="text-xl text-gray-300 mb-2">Seu código:</p>
            <p id="codigoCliente" class="text-5xl font-extrabold text-green-400 mb-6">A-00</p>
            <p id="successMessage" class="text-gray-400 mb-8">Aguarde seu pedido no painel</p>
            <button onclick="novoPedido()" class="bg-green-600 px-8 py-4 rounded-xl font-bold text-lg">Fazer Novo Pedido</button>
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
        let cart = [];
        
        function addToCart(product) {
            const existing = cart.find(i => i.nome === product.nome);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({ ...product, qty: 1 });
            }
            updateCartUI();
        }
        
        function updateCartUI() {
            const count = cart.reduce((sum, i) => sum + i.qty, 0);
            const total = cart.reduce((sum, i) => sum + (i.preco * i.qty), 0);
            
            document.getElementById('cartCount').textContent = count;
            document.getElementById('cartTotal').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
            document.getElementById('cartTotalModal').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
            document.getElementById('cartBar').classList.toggle('hidden', count === 0);
            
            // Render items
            document.getElementById('cartItems').innerHTML = cart.map((item, idx) => `
                <div class="flex items-center justify-between py-3 border-b border-white/10">
                    <div class="flex-1">
                        <h4 class="font-semibold">${item.nome}</h4>
                        <p class="text-green-400">R$ ${(item.preco * item.qty).toFixed(2).replace('.', ',')}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="changeQty(${idx}, -1)" class="w-8 h-8 bg-slate-700 rounded-lg">-</button>
                        <span class="w-8 text-center">${item.qty}</span>
                        <button onclick="changeQty(${idx}, 1)" class="w-8 h-8 bg-slate-700 rounded-lg">+</button>
                    </div>
                </div>
            `).join('') || '<p class="text-gray-400 text-center">Carrinho vazio</p>';
        }
        
        function changeQty(idx, delta) {
            cart[idx].qty += delta;
            if (cart[idx].qty <= 0) cart.splice(idx, 1);
            updateCartUI();
        }
        
        function openCart() { document.getElementById('cartModal').classList.remove('hidden'); }
        function closeCart() { document.getElementById('cartModal').classList.add('hidden'); }
        
        function finalizarPedido() {
            if (cart.length === 0) return;
            closeCart();
            document.getElementById('paymentModal').classList.remove('hidden');
        }
        
        function closePayment() { document.getElementById('paymentModal').classList.add('hidden'); }
        
        function processPayment(method) {
            closePayment();
            // Criar pedido
            const itens = cart.map(i => ({
                produto_id: i.id || null,
                nome: i.nome,
                quantidade: i.qty,
                preco: i.preco,
                destino: i.destino || 'cozinha'
            }));
            
            fetch('/api/pedidos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'criar', itens, forma_pagamento: method, origem: 'cardapio' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.codigo_cliente, cart.some(i => i.destino === 'cozinha'));
                    cart = [];
                    updateCartUI();
                }
            })
            .catch(() => showSuccess('A-' + String(Math.floor(Math.random()*99)).padStart(2,'0'), true));
        }
        
        function showSuccess(codigo, temCozinha) {
            document.getElementById('codigoCliente').textContent = codigo;
            document.getElementById('successMessage').textContent = temCozinha 
                ? 'Aguarde seu pedido aparecer no painel!' 
                : 'Retire sua bebida no balcão do bar!';
            document.getElementById('successModal').classList.remove('hidden');
        }
        
        function novoPedido() { document.getElementById('successModal').classList.add('hidden'); }
        
        // Exit modal
        function openExitModal() { document.getElementById('exitModal').classList.remove('hidden'); document.getElementById('pinInput').focus(); }
        function closeExitModal() { document.getElementById('exitModal').classList.add('hidden'); }
        function handleExit(e) {
            e.preventDefault();
            const pin = document.getElementById('pinInput').value;
            if (pin === '1234') window.location.href = '/modulos';
            else { document.getElementById('pinError').classList.remove('hidden'); document.getElementById('pinInput').value = ''; }
            return false;
        }
    </script>
</body>
</html>
