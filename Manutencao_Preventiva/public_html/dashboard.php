<?php
require 'header.php';
require_once 'src/db.php';
require_once 'src/calculos_kpi.php';

$pdo = Database::getInstance();
$kpi = new KPI($pdo);

// Filters
$periodo = $_GET['periodo'] ?? 'mes';
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');

if ($periodo == 'mes') {
    $data_inicio = date('Y-m-01');
    $data_fim = date('Y-m-t');
} elseif ($periodo == 'manual') {
    // Keep GET params
}

// Cards (Now strictly Preventivas as requested)
$totalPrev = $kpi->getTotalPreventivas();
$pendentes = $kpi->getPreventivasPendentes();
$concluidas = $kpi->getPreventivasConcluidas($data_inicio, $data_fim);
$perc = $kpi->getPorcentagemConclusao($data_inicio, $data_fim);

// Chart 1: Comparison
$chartData = $kpi->getChartDataComparison($data_inicio, $data_fim);
$dates = array_column($chartData, 'data');
$prevCounts = array_column($chartData, 'prev');
$corrCounts = array_column($chartData, 'corr');

// Chart 2: Criticality (Real Data)
$critData = $kpi->getChartDataCriticality();
// Format for ChartJS
$critValues = [$critData['A'], $critData['B'], $critData['C']];
?>

<div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-white tracking-tight">Dashboard de Performance</h1>
        <p class="text-slate-400">Visão Geral de <span class="text-sky-400 font-bold">Preventivas</span></p>
    </div>
    
    <form class="glass-panel p-2 rounded-xl flex flex-wrap items-center gap-2">
         <select name="periodo" onchange="this.form.submit()" class="bg-slate-900/50 text-white rounded-lg px-3 py-2 text-sm border border-slate-700/50 outline-none focus:border-sky-500">
            <option value="mes" <?php echo $periodo=='mes'?'selected':''; ?>>Este Mês</option>
            <option value="manual" <?php echo $periodo=='manual'?'selected':''; ?>>Personalizado</option>
        </select>
        <?php if ($periodo == 'manual'): ?>
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>" class="bg-slate-900/50 text-white rounded-lg p-2 text-sm border border-slate-700/50">
            <input type="date" name="data_fim" value="<?php echo $data_fim; ?>" class="bg-slate-900/50 text-white rounded-lg p-2 text-sm border border-slate-700/50">
            <button type="submit" class="bg-sky-600 p-2 rounded text-white text-xs">Filtrar</button>
        <?php endif; ?>
    </form>
</div>

<!-- 4 Top Cards (Preventivas Only) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Preventivas -->
    <div class="glass-panel rounded-2xl p-6 border-b-4 border-slate-600">
        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Preventivas</h3>
        <p class="text-4xl font-bold text-white"><?php echo $totalPrev; ?></p>
        <p class="text-slate-500 text-sm mt-1">Cadastradas no Sistema</p>
    </div>
    
    <!-- Pendentes -->
    <div class="glass-panel rounded-2xl p-6 border-b-4 border-rose-500">
        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Prev. Pendentes</h3>
        <p class="text-4xl font-bold text-white"><?php echo $pendentes; ?></p>
        <p class="text-rose-400 text-sm mt-1">Atrasadas ou A Fazer</p>
    </div>

    <!-- Concluidas -->
    <div class="glass-panel rounded-2xl p-6 border-b-4 border-emerald-500">
        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Prev. Concluídas</h3>
        <p class="text-4xl font-bold text-white"><?php echo $concluidas; ?></p>
        <p class="text-emerald-400 text-sm mt-1">Neste Período</p>
    </div>

    <!-- % -->
    <div class="glass-panel rounded-2xl p-6 border-b-4 border-indigo-500">
        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Taxa de Conclusão</h3>
        <p class="text-4xl font-bold text-white"><?php echo $perc; ?>%</p>
        <div class="w-full bg-slate-800 h-2 mt-2 rounded-full overflow-hidden">
            <div class="bg-indigo-500 h-full" style="width: <?php echo $perc; ?>%"></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Chart Comparison -->
    <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-white mb-6">Volume: Preventivas vs Corretivas</h3>
        <div class="h-64 relative w-full">
            <canvas id="regChart"></canvas>
        </div>
    </div>

    <!-- Chart Criticality (Rosca) -->
     <div class="glass-panel rounded-2xl p-6">
        <h3 class="text-lg font-bold text-white mb-6">Criticidade dos Ativos</h3>
        <div class="h-64 relative w-full flex justify-center">
            <canvas id="critChart"></canvas>
        </div>
    </div>
</div>

<script>
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', sans-serif";

    // Chart Registration
    const ctx1 = document.getElementById('regChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [
                {
                    label: 'Preventivas',
                    data: <?php echo json_encode($prevCounts); ?>,
                    backgroundColor: '#0ea5e9',
                    borderRadius: 4,
                },
                {
                    label: 'Corretivas',
                    data: <?php echo json_encode($corrCounts); ?>,
                    backgroundColor: '#f43f5e',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#1e293b' },
                    ticks: { precision: 0 } // Integer only
                },
                x: { grid: { display: false } }
            }
        }
    });

    // Chart Criticality
    const ctx2 = document.getElementById('critChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['A (Crítico)', 'B (Médio)', 'C (Baixo)'],
            datasets: [{
                data: <?php echo json_encode($critValues); ?>, // Real Data
                backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { position: 'right' } }
        }
    });
</script>
</body>
</html>