<?php
/**
 * TOTEM LUXX - Página de Cadastro
 */

// Se já está logado, redireciona
if (isLoggedIn()) {
    redirect('/modulos');
}

$error = '';
$success = '';
$formData = [];

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'nome_local' => sanitize($_POST['nome_local'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'cidade' => sanitize($_POST['cidade'] ?? ''),
        'estado' => sanitize($_POST['estado'] ?? ''),
        'telefone' => sanitize($_POST['telefone'] ?? ''),
    ];
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $pin_admin = sanitize($_POST['pin_admin'] ?? '');
    
    // Validações
    if (empty($formData['nome_local']) || empty($formData['email']) || empty($senha) || empty($formData['cidade']) || empty($formData['estado'])) {
        $error = 'Preencha todos os campos obrigatórios.';
    } elseif (!validarEmail($formData['email'])) {
        $error = 'Email inválido.';
    } elseif (strlen($senha) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $error = 'As senhas não conferem.';
    } elseif (strlen($pin_admin) < 4 || strlen($pin_admin) > 6) {
        $error = 'O PIN deve ter entre 4 e 6 dígitos.';
    } else {
        // Verificar se email já existe
        $existe = db()->fetchOne("SELECT id FROM clientes WHERE email = ?", [$formData['email']]);
        
        if ($existe) {
            $error = 'Este email já está cadastrado.';
        } else {
            // Criar conta
            try {
                $clienteId = db()->insert('clientes', [
                    'nome_local' => $formData['nome_local'],
                    'email' => $formData['email'],
                    'senha' => password_hash($senha, PASSWORD_DEFAULT),
                    'pin_admin' => $pin_admin,
                    'cidade' => $formData['cidade'],
                    'estado' => $formData['estado'],
                    'telefone' => $formData['telefone'],
                    'plano' => 'basic',
                    'data_vencimento' => date('Y-m-d', strtotime('+30 days')), // 30 dias grátis
                    'is_owner' => false,
                    'ativo' => true
                ]);
                
                registrarLog('info', 'auth', 'cadastro', 'Novo cliente cadastrado', ['cliente_id' => $clienteId]);
                
                $success = 'Conta criada com sucesso! Você tem 30 dias grátis para testar.';
                $formData = []; // Limpa form
                
            } catch (Exception $e) {
                $error = 'Erro ao criar conta. Tente novamente.';
                if (APP_DEBUG) $error .= ' ' . $e->getMessage();
            }
        }
    }
}

// Estados brasileiros
$estados = [
    'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
    'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
    'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
    'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
    'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
    'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
    'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TOTEM LUXX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                        },
                        dark: {
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glow { box-shadow: 0 0 60px rgba(139, 92, 246, 0.3); }
        .input-glow:focus { box-shadow: 0 0 20px rgba(139, 92, 246, 0.4); }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        .floating { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    
    <!-- Background Effects -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl floating"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl floating" style="animation-delay: -3s;"></div>
    </div>

    <!-- Card -->
    <div class="relative z-10 w-full max-w-lg">
        <!-- Logo -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl shadow-lg shadow-purple-500/30 mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-1">Criar Conta</h1>
            <p class="text-dark-400 text-sm">30 dias grátis para testar</p>
        </div>
        
        <!-- Card -->
        <div class="glass-card rounded-3xl p-6 glow">
            
            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-3 mb-4">
                <p class="text-red-300 text-sm text-center"><?= $error ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-green-500/20 border border-green-500/30 rounded-xl p-3 mb-4">
                <p class="text-green-300 text-sm text-center"><?= $success ?></p>
                <a href="/login" class="block text-center text-green-400 hover:text-green-300 mt-2 text-sm font-medium">Ir para o Login →</a>
            </div>
            <?php else: ?>
            
            <form method="POST" class="space-y-4">
                <!-- Nome do Local -->
                <div>
                    <label class="block text-dark-300 text-sm font-medium mb-1.5">Nome do Estabelecimento *</label>
                    <input 
                        type="text" 
                        name="nome_local" 
                        required
                        value="<?= $formData['nome_local'] ?? '' ?>"
                        class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                        placeholder="Bar do João, Lanchonete X..."
                    >
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-dark-300 text-sm font-medium mb-1.5">Email *</label>
                    <input 
                        type="email" 
                        name="email" 
                        required
                        value="<?= $formData['email'] ?? '' ?>"
                        class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                        placeholder="seu@email.com"
                    >
                </div>
                
                <!-- Telefone -->
                <div>
                    <label class="block text-dark-300 text-sm font-medium mb-1.5">Telefone</label>
                    <input 
                        type="tel" 
                        name="telefone"
                        value="<?= $formData['telefone'] ?? '' ?>"
                        class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                        placeholder="(11) 99999-9999"
                    >
                </div>
                
                <!-- Cidade / Estado -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-dark-300 text-sm font-medium mb-1.5">Cidade *</label>
                        <input 
                            type="text" 
                            name="cidade" 
                            required
                            value="<?= $formData['cidade'] ?? '' ?>"
                            class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                            placeholder="São Paulo"
                        >
                    </div>
                    <div>
                        <label class="block text-dark-300 text-sm font-medium mb-1.5">Estado *</label>
                        <select 
                            name="estado" 
                            required
                            class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-purple-500 input-glow transition-all"
                        >
                            <option value="">Selecione</option>
                            <?php foreach ($estados as $sigla => $nome): ?>
                            <option value="<?= $sigla ?>" <?= ($formData['estado'] ?? '') === $sigla ? 'selected' : '' ?>><?= $sigla ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Senhas -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-dark-300 text-sm font-medium mb-1.5">Senha *</label>
                        <input 
                            type="password" 
                            name="senha" 
                            required
                            minlength="6"
                            class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                            placeholder="Mín. 6 caracteres"
                        >
                    </div>
                    <div>
                        <label class="block text-dark-300 text-sm font-medium mb-1.5">Confirmar Senha *</label>
                        <input 
                            type="password" 
                            name="confirmar_senha" 
                            required
                            class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                </div>
                
                <!-- PIN Admin -->
                <div>
                    <label class="block text-dark-300 text-sm font-medium mb-1.5">PIN do Admin (4-6 dígitos) *</label>
                    <input 
                        type="password" 
                        name="pin_admin" 
                        required
                        minlength="4"
                        maxlength="6"
                        pattern="[0-9]{4,6}"
                        class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3 px-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                        placeholder="Ex: 1234"
                    >
                    <p class="text-dark-500 text-xs mt-1">Este PIN será usado para sair dos módulos do totem</p>
                </div>
                
                <!-- Botão -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-300 transform hover:scale-[1.02]"
                >
                    Criar Conta Grátis
                </button>
            </form>
            
            <?php endif; ?>
            
            <!-- Voltar -->
            <p class="text-center text-dark-400 text-sm mt-4">
                Já tem conta? <a href="/login" class="text-purple-400 hover:text-purple-300">Fazer login</a>
            </p>
        </div>
    </div>

</body>
</html>
