<?php
require 'header.php';
require_once 'src/db.php';
$pdo = Database::getInstance();

$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT) ?: (int) date('m');
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT) ?: (int) date('Y');

// Safe bounds
if ($month < 1 || $month > 12) {
    $month = (int) date('m');
}
if ($year < 2000 || $year > 3000) {
    $year = (int) date('Y');
}

// Handle New Programming Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = $_POST['asset_id'];
    $freq = (int) $_POST['frequencia'];

    // Calculate initial proxima_data
    $proxima = date('Y-m-d', strtotime("+$freq days"));

    $stmt = $pdo->prepare("INSERT INTO preventivas (asset_id, frequencia_dias, proxima_data, ultima_data) VALUES (?, ?, ?, CURDATE())");
    $stmt->execute([$asset_id, $freq, $proxima]);
    echo "<script>window.location.href='manutencao.php?month=$month&year=$year';</script>";
    exit;
}

// Dates
$start = sprintf('%04d-%02d-01', $year, $month);
$end = date("Y-m-t", strtotime($start));

// 1. Fetch Scheduled Preventives
$sqlPrev = "SELECT p.*, a.nome, a.patrimonio 
        FROM preventivas p 
        JOIN assets a ON p.asset_id = a.id 
        WHERE p.proxima_data BETWEEN :start AND :end";
$stmtPrev = $pdo->prepare($sqlPrev);
$stmtPrev->execute(['start' => $start, 'end' => $end]);
$schedEvents = $stmtPrev->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch Actual Work Orders (Chamados)
$sqlLogs = "SELECT m.*, a.nome, a.patrimonio 
            FROM manutencao_logs m 
            JOIN assets a ON m.asset_id = a.id 
            WHERE (m.data_execucao BETWEEN :start AND :end) 
               OR (m.data_limite BETWEEN :start2 AND :end2)";
$stmtLogs = $pdo->prepare($sqlLogs);
$stmtLogs->execute(['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59', 'start2' => $start, 'end2' => $end]);
$logEvents = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

$assets = $pdo->query("SELECT * FROM assets ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Map events
$calendar = [];

// Map Schedules (Gray/Blue)
foreach ($schedEvents as $evt) {
    $day = (int) date('d', strtotime($evt['proxima_data']));
    $calendar[$day][] = [
        'type' => 'schedule',
        'title' => 'Prog: ' . $evt['nome'],
        'color' => 'bg-slate-700 text-slate-300 border-slate-600',
        'tooltip' => 'Programado: ' . $evt['patrimonio']
    ];
}

// Map Logs (Color coded by status/type)
foreach ($logEvents as $evt) {
    // Determine date to show: Execution if done, Limit if pending
    $useDate = ($evt['status'] == 'Realizado' && $evt['data_execucao']) ? $evt['data_execucao'] : $evt['data_limite'];

    if ($useDate) {
        $day = (int) date('d', strtotime($useDate));

        $color = 'bg-sky-500/20 text-sky-300 border-sky-500/30'; // Default
        if ($evt['tipo'] == 'Corretiva')
            $color = 'bg-rose-500/20 text-rose-300 border-rose-500/30';
        if ($evt['tipo'] == 'Melhoria')
            $color = 'bg-purple-500/20 text-purple-300 border-purple-500/30';
        if ($evt['status'] == 'Realizado')
            $color = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';

        $calendar[$day][] = [
            'type' => 'ticket',
            'title' => '#' . $evt['id'] . ' ' . $evt['nome'],
            'color' => $color,
            'tooltip' => $evt['tipo'] . ': ' . $evt['descricao']
        ];
    }
}

$daysInMonth = date('t', strtotime($start));
$firstDow = date('w', strtotime($start));

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold text-white tracking-tight">Agenda</h2>
        <p class="text-slate-400">Visão Geral de Programações e Chamados</p>
    </div>

    <div class="flex flex-wrap justify-center gap-4">
        <div class="flex items-center gap-4 bg-slate-900/50 p-2 rounded-xl border border-slate-700/50">
            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>"
                class="text-slate-400 hover:text-white p-2 hover:bg-slate-800 rounded-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <span class="text-lg font-bold text-white min-w-[140px] text-center capitalize">
                <?php
                $months = [
                    'January' => 'Janeiro',
                    'February' => 'Fevereiro',
                    'March' => 'Março',
                    'April' => 'Abril',
                    'May' => 'Maio',
                    'June' => 'Junho',
                    'July' => 'Julho',
                    'August' => 'Agosto',
                    'September' => 'Setembro',
                    'October' => 'Outubro',
                    'November' => 'Novembro',
                    'December' => 'Dezembro'
                ];
                $monthName = date('F', strtotime($start));
                echo ($months[$monthName] ?? $monthName) . " " . $year;
                ?>
            </span>
            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>"
                class="text-slate-400 hover:text-white p-2 hover:bg-slate-800 rounded-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        <button onclick="document.getElementById('modal-prev').classList.remove('hidden')"
            class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-4 rounded-xl shadow-lg transition-all flex items-center gap-2">
            <span>+ Programação</span>
        </button>
    </div>
</div>

<div class="glass-panel p-4 md:p-6 rounded-2xl shadow-xl overflow-hidden">
    <div class="grid grid-cols-7 gap-1 mb-4 text-center border-b border-slate-700/50 pb-2">
        <div class="text-slate-500 text-[10px] md:text-xs font-bold uppercase">Dom</div>
        <div class="text-slate-500 text-[10px] md:text-xs font-bold uppercase">Seg</div>
        <div class="text-slate-500 text-[10px] md:text-xs font-bold uppercase">Ter</div>
        <div class="text-slate-500 text-[10px] md:text-xs font-bold uppercase">Qua</div>
        <div class="text-slate-500 text-[10px] md:text-xs font-bold uppercase">Qui</div>
        <div class="text-slate-500 text-[10px] md:text-xs font-bold uppercase">Sex</div>
        <div class="text-slate-500 text-[10px] md:text-xs font-bold uppercase">Sáb</div>
    </div>

    <div class="grid grid-cols-7 gap-1 md:gap-2">
        <?php
        for ($i = 0; $i < $firstDow; $i++)
            echo '<div class="h-24 md:h-32 bg-slate-900/30 rounded-lg border border-slate-800/50"></div>';

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $isToday = ($day == date('d') && $month == date('m') && $year == date('Y'));
            $borderClass = $isToday ? 'border-sky-500 ring-1 ring-sky-500/50' : 'border-slate-700/50';
            $bgClass = $isToday ? 'bg-slate-800/80' : 'bg-slate-900/50 hover:bg-slate-800/50';
            $dayEvents = $calendar[$day] ?? [];

            echo "<div class='h-24 md:h-32 p-1 md:p-2 rounded-lg border $borderClass $bgClass transition-all relative group overflow-hidden flex flex-col'>";
            echo "<span class='text-xs md:text-sm font-bold " . ($isToday ? 'text-sky-400' : 'text-slate-400') . "'>$day</span>";

            if (count($dayEvents) > 0) {
                echo "<div class='mt-1 flex-1 overflow-y-auto space-y-1 custom-scrollbar'>";
                foreach ($dayEvents as $evt) {
                    echo "<div class='text-[9px] md:text-[10px] leading-tight {$evt['color']} px-1 py-1 rounded border truncate' title='{$evt['tooltip']}'>";
                    echo $evt['title'];
                    echo "</div>";
                }
                echo "</div>";
            }
            echo "</div>";
        }
        ?>
    </div>
</div>

<!-- Modal Absolute -->
<div id="modal-prev"
    class="absolute inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
    <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700 m-4 relative">
        <button onclick="document.getElementById('modal-prev').classList.add('hidden')"
            class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
        <h3 class="text-xl font-bold text-white mb-6">Nova Programação</h3>
        <p class="text-slate-400 text-sm mb-4">Defina ciclos de manutenção preventiva automática.</p>
        <form method="POST" class="space-y-4">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Ativo</label>
                <select name="asset_id"
                    class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                    <?php foreach ($assets as $a): ?>
                        <option value="<?php echo $a['id']; ?>"><?php echo $a['nome'] . ' (' . $a['patrimonio'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Frequência (Dias)</label>
                <input type="number" name="frequencia" placeholder="Ex: 30"
                    class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500"
                    required>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modal-prev').classList.add('hidden')"
                    class="px-4 py-2 text-slate-400 hover:text-white">Cancelar</button>
                <button type="submit"
                    class="bg-sky-600 px-6 py-2 rounded text-white font-bold hover:bg-sky-500">Agendar</button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 2px;
    }
</style>
</body>

</html>