<?php
require_once 'src/db.php';
require_once 'src/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::getInstance();
    if (login($_POST['email'], $_POST['password'], $pdo)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Credenciais inválidas!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Preventiva 360</title>
    <link rel="manifest" href="manifest.json">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-950 h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Background Effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-sky-600/20 rounded-full blur-[128px]"></div>
        <div class="absolute top-[40%] -right-[10%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[128px]"></div>
    </div>

    <div
        class="bg-slate-900/60 backdrop-blur-xl p-8 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-800 relative z-10">
        <div class="text-center mb-8">
            <div
                class="w-16 h-16 bg-gradient-to-br from-sky-500 to-indigo-600 rounded-xl mx-auto flex items-center justify-center shadow-lg shadow-sky-500/20 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">PREVENTIVA 360</h1>
            <p class="text-slate-400 text-sm mt-1">Gestão de Inteligente de Ativos</p>
        </div>

        <?php if (isset($error)): ?>
            <div
                class="bg-red-500/10 border border-red-500/50 text-red-400 p-3 rounded-lg mb-6 text-center text-sm font-medium">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-slate-400 text-xs uppercase font-bold mb-2 tracking-wider"
                    for="email">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                            </path>
                        </svg>
                    </div>
                    <input
                        class="w-full bg-slate-950/50 text-white border border-slate-700 rounded-lg py-3 pl-10 pr-3 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors placeholder-slate-600"
                        type="email" name="email" id="email" placeholder="nome@empresa.com" required>
                </div>
            </div>
            <div>
                <label class="block text-slate-400 text-xs uppercase font-bold mb-2 tracking-wider"
                    for="password">Senha</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <input
                        class="w-full bg-slate-950/50 text-white border border-slate-700 rounded-lg py-3 pl-10 pr-3 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors placeholder-slate-600"
                        type="password" name="password" id="password" placeholder="••••••••" required>
                </div>
            </div>
            <button
                class="w-full bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg shadow-sky-600/30 transform transition hover:-translate-y-0.5"
                type="submit">
                Entrar no Sistema
            </button>
        </form>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('service-worker.js');
            });
        }
    </script>
</body>

</html>