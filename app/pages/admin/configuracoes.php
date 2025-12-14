<?php
/**
 * TOTEM LUXX - Configurações
 */
requireAuth();
$cliente = getCliente();
$success = '';
$error = '';

// Salvar configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'perfil') {
        $nome = sanitize($_POST['nome_local'] ?? '');
        $telefone = sanitize($_POST['telefone'] ?? '');
        $cidade = sanitize($_POST['cidade'] ?? '');
        $estado = sanitize($_POST['estado'] ?? '');
        
        db()->update('clientes', [
            'nome_local' => $nome,
            'telefone' => $telefone,
            'cidade' => $cidade,
            'estado' => $estado
        ], 'id = ?', [$cliente['id']]);
        
        $success = 'Perfil atualizado com sucesso!';
        $cliente = getCliente(); // Recarregar
        
    } elseif ($action === 'pagamento') {
        $mpToken = sanitize($_POST['mp_access_token'] ?? '');
        $mpPublic = sanitize($_POST['mp_public_key'] ?? '');
        $pinMaquininha = sanitize($_POST['pin_maquininha'] ?? '');
        
        db()->update('clientes', [
            'mp_access_token' => $mpToken,
            'mp_public_key' => $mpPublic,
            'pin_maquininha' => $pinMaquininha
        ], 'id = ?', [$cliente['id']]);
        
        $success = 'Configurações de pagamento salvas!';
        $cliente = getCliente();
        
    } elseif ($action === 'seguranca') {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $novoPin = $_POST['novo_pin'] ?? '';
        
        if (!password_verify($senhaAtual, $cliente['senha'])) {
            $error = 'Senha atual incorreta!';
        } else {
            $updates = [];
            if (!empty($novaSenha)) {
                $updates['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
            }
            if (!empty($novoPin)) {
                $updates['pin_admin'] = $novoPin;
            }
            
            if (!empty($updates)) {
                db()->update('clientes', $updates, 'id = ?', [$cliente['id']]);
                $success = 'Credenciais atualizadas!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - TOTEM LUXX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background: #0f172a; }</style>
</head>
<body class="min-h-screen text-white">
    
    <!-- Sidebar igual ao dashboard -->
    <aside class="fixed left-0 top-0 bottom-0 w-64 bg-slate-900/50 border-r border-white/10 p-4">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">⚙️</div>
            <div>
                <p class="font-bold text-sm">TOTEM LUXX</p>
                <p class="text-gray-400 text-xs">Configurações</p>
            </div>
        </div>
        
        <nav class="space-y-1">
            <a href="/admin" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl">🏠 Dashboard</a>
            <a href="/admin/produtos" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl">📦 Produtos</a>
            <a href="/admin/configuracoes" class="flex items-center gap-3 px-4 py-3 bg-purple-600/20 text-purple-400 rounded-xl">⚙️ Configurações</a>
        </nav>
        
        <div class="absolute bottom-4 left-4 right-4">
            <a href="/modulos" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white rounded-xl">← Voltar aos Módulos</a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="ml-64 p-8">
        <h1 class="text-2xl font-bold mb-8">Configurações</h1>
        
        <?php if ($success): ?>
        <div class="bg-green-500/20 border border-green-500/30 rounded-xl p-4 mb-6 text-green-300"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-4 mb-6 text-red-300"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="grid gap-6 max-w-2xl">
            <!-- Perfil -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                <h2 class="text-lg font-semibold mb-4">👤 Perfil do Estabelecimento</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="perfil">
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Nome do Local</label>
                        <input type="text" name="nome_local" value="<?= htmlspecialchars($cliente['nome_local']) ?>" class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Cidade</label>
                            <input type="text" name="cidade" value="<?= htmlspecialchars($cliente['cidade']) ?>" class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Estado</label>
                            <input type="text" name="estado" value="<?= htmlspecialchars($cliente['estado']) ?>" maxlength="2" class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Telefone</label>
                        <input type="text" name="telefone" value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>" class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                    </div>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-500 px-6 py-3 rounded-xl font-medium">Salvar Perfil</button>
                </form>
            </div>
            
            <!-- Pagamento -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                <h2 class="text-lg font-semibold mb-4">💳 Integração Mercado Pago</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="pagamento">
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Access Token</label>
                        <input type="password" name="mp_access_token" value="<?= htmlspecialchars($cliente['mp_access_token'] ?? '') ?>" placeholder="APP_USR-..." class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                        <p class="text-gray-500 text-xs mt-1">Obtido em developers.mercadopago.com</p>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Public Key</label>
                        <input type="text" name="mp_public_key" value="<?= htmlspecialchars($cliente['mp_public_key'] ?? '') ?>" placeholder="APP_USR-..." class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">PIN da Maquininha</label>
                        <input type="text" name="pin_maquininha" value="<?= htmlspecialchars($cliente['pin_maquininha'] ?? '') ?>" class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                    </div>
                    <button type="submit" class="bg-teal-600 hover:bg-teal-500 px-6 py-3 rounded-xl font-medium">Salvar Pagamento</button>
                </form>
            </div>
            
            <!-- Segurança -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                <h2 class="text-lg font-semibold mb-4">🔒 Segurança</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="seguranca">
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Senha Atual *</label>
                        <input type="password" name="senha_atual" required class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Nova Senha (deixe vazio para manter)</label>
                        <input type="password" name="nova_senha" class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Novo PIN Admin (deixe vazio para manter)</label>
                        <input type="password" name="novo_pin" maxlength="6" class="w-full bg-slate-800 border border-white/20 rounded-xl py-3 px-4">
                    </div>
                    <button type="submit" class="bg-red-600 hover:bg-red-500 px-6 py-3 rounded-xl font-medium">Atualizar Credenciais</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
