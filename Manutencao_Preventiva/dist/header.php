<?php
require_once 'src/auth.php';
checkAuth();
$current_page = basename($_SERVER['PHP_SELF']);

function isActive($page, $current)
{
    return $page === $current
        ? 'bg-sky-600/20 text-sky-400 border-r-4 border-sky-500'
        : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 transition-all duration-200';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventiva 360</title>
    <link rel="manifest" href="manifest.json">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Scoped Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 3px;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: { 850: '#151e32', 950: '#020617' }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-950 text-slate-200 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <!-- z-index set to 30. Modal backdrop will be constrained to MAIN, but if we need a global modal it should be higher. 
         Customer requested modal NOT over menu. We will solve this in the active pages. -->
    <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col hidden md:flex shadow-2xl z-30">
        <div class="h-20 flex items-center justify-center border-b border-slate-800 bg-slate-900/50">
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-white leading-none">PREVENTIVA</h1>
                    <span class="text-xs text-sky-500 font-semibold tracking-widest">360 MANAGER</span>
                </div>
            </div>
        </div>

        <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Principal</p>

            <a href="dashboard.php"
                class="flex items-center px-4 py-3 rounded-lg group <?php echo isActive('dashboard.php', $current_page); ?>">
                <svg class="w-5 h-5 mr-3 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>

            <a href="ativos.php"
                class="flex items-center px-4 py-3 rounded-lg group <?php echo isActive('ativos.php', $current_page); ?>">
                <svg class="w-5 h-5 mr-3 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                <span class="font-medium">Ativos</span>
            </a>

            <a href="manutencao.php"
                class="flex items-center px-4 py-3 rounded-lg group <?php echo isActive('manutencao.php', $current_page); ?>">
                <svg class="w-5 h-5 mr-3 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <span class="font-medium">Agenda</span>
            </a>

            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mt-6 mb-2">Operacional</p>

            <a href="ordem_servico.php"
                class="flex items-center px-4 py-3 rounded-lg group <?php echo isActive('ordem_servico.php', $current_page); ?>">
                <!-- Changed Icon for 'Chamados' -->
                <svg class="w-5 h-5 mr-3 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                    </path>
                </svg>
                <span class="font-medium">Chamados</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800 bg-slate-900/50">
            <div class="flex items-center p-2 rounded-xl bg-slate-800/50">
                <div
                    class="w-10 h-10 rounded-full bg-gradient-to-r from-sky-400 to-blue-600 flex items-center justify-center text-white font-bold">
                    <?php echo strtoupper(substr($_SESSION['user_nome'], 0, 1)); ?>
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-sm font-semibold text-white truncate"><?php echo $_SESSION['user_nome']; ?></p>
                    <a href="logout.php" class="text-xs text-red-400 hover:text-red-300">Sair</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Mobile Header -->
    <div
        class="md:hidden fixed top-0 w-full bg-slate-900 border-b border-slate-800 z-50 flex items-center justify-between px-4 h-16">
        <span class="font-bold text-white tracking-tight">PREVENTIVA 360</span>
        <button id="mobile-menu-btn" class="text-slate-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
    </div>

    <!-- Main Content Container with relative positioning for Modals -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-950 pt-16 md:pt-0 relative w-full">
        <!-- Background Ambient Glow -->
        <div
            class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-sky-900/10 to-transparent pointer-events-none">
        </div>

        <div class="container mx-auto px-6 py-8 relative z-10 h-full">