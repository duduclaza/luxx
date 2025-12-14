<?php
/**
 * TOTEM LUXX - Página de Login
 */

// Se já está logado, redireciona
if (isLoggedIn()) {
    redirect('/modulos');
}

$error = '';
$success = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $error = 'Preencha todos os campos.';
    } else {
        $cliente = db()->fetchOne(
            "SELECT * FROM clientes WHERE email = ? AND ativo = 1",
            [$email]
        );
        
        if ($cliente && password_verify($senha, $cliente['senha'])) {
            // Verificar vencimento (exceto owner)
            if (!$cliente['is_owner'] && $cliente['data_vencimento']) {
                if (strtotime($cliente['data_vencimento']) < time()) {
                    $error = 'Sua assinatura venceu. Entre em contato para renovar.';
                }
            }
            
            if (empty($error)) {
                // Login bem sucedido
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome_local'];
                $_SESSION['is_owner'] = $cliente['is_owner'];
                
                registrarLog('info', 'auth', 'login', 'Login realizado com sucesso');
                
                redirect('/modulos');
            }
        } else {
            $error = 'Email ou senha incorretos.';
            registrarLog('warning', 'auth', 'login_falha', 'Tentativa de login falhou', ['email' => $email]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TOTEM LUXX</title>
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
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glow {
            box-shadow: 0 0 60px rgba(139, 92, 246, 0.3);
        }
        
        .input-glow:focus {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.4);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    
    <!-- Background Effects -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl floating"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl floating" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-violet-500/10 rounded-full blur-3xl pulse-glow"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl shadow-lg shadow-purple-500/30 mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">TOTEM LUXX</h1>
            <p class="text-dark-400">Sistema de Autoatendimento</p>
        </div>
        
        <!-- Card -->
        <div class="glass-card rounded-3xl p-8 glow">
            <h2 class="text-xl font-semibold text-white mb-6 text-center">Entrar na sua conta</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-4 mb-6">
                <p class="text-red-300 text-sm text-center"><?= $error ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-green-500/20 border border-green-500/30 rounded-xl p-4 mb-6">
                <p class="text-green-300 text-sm text-center"><?= $success ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-dark-300 text-sm font-medium mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            value="<?= sanitize($_POST['email'] ?? '') ?>"
                            class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3.5 pl-12 pr-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                            placeholder="seu@email.com"
                        >
                    </div>
                </div>
                
                <!-- Senha -->
                <div>
                    <label for="senha" class="block text-dark-300 text-sm font-medium mb-2">Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            required
                            class="w-full bg-dark-900/50 border border-dark-700 rounded-xl py-3.5 pl-12 pr-4 text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 input-glow transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                </div>
                
                <!-- Lembrar / Esqueci -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-dark-400 cursor-pointer">
                        <input type="checkbox" name="lembrar" class="w-4 h-4 rounded border-dark-600 bg-dark-800 text-purple-500 focus:ring-purple-500 focus:ring-offset-0 mr-2">
                        Lembrar de mim
                    </label>
                    <a href="#" class="text-purple-400 hover:text-purple-300 transition-colors">Esqueceu a senha?</a>
                </div>
                
                <!-- Botão -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-300 transform hover:scale-[1.02]"
                >
                    Entrar
                </button>
            </form>
            
            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-dark-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-transparent text-dark-500">ou</span>
                </div>
            </div>
            
            <!-- Cadastro -->
            <a 
                href="/cadastro" 
                class="block w-full text-center border border-dark-600 hover:border-purple-500/50 text-dark-300 hover:text-white py-3.5 rounded-xl transition-all duration-300"
            >
                Criar nova conta
            </a>
        </div>
        
        <!-- Footer -->
        <p class="text-center text-dark-500 text-sm mt-6">
            © 2024 TOTEM LUXX. Todos os direitos reservados.
        </p>
    </div>

</body>
</html>
