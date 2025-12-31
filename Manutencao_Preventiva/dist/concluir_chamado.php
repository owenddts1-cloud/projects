<?php
require 'src/db.php';
$pdo = Database::getInstance();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data_exec = $_POST['data_execucao'];
        $desc = $_POST['descricao'];
        $custo = $_POST['custo'];
        $tempo = $_POST['tempo'];
        
        // 1. Update the Log (Chamado)
        $sql = "UPDATE manutencao_logs 
                SET status='Realizado', 
                    data_execucao=:data, 
                    descricao=:desc, 
                    custo_pecas=:custo, 
                    tempo_parada_horas=:tempo 
                WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'data' => $data_exec, 
            'desc' => $desc, 
            'custo' => $custo, 
            'tempo' => $tempo, 
            'id' => $id
        ]);
        
        // 2. Automate Cycle for Preventives
        // Fetch trigger info
        $stmtInfo = $pdo->prepare("SELECT asset_id, tipo FROM manutencao_logs WHERE id = :id");
        $stmtInfo->execute(['id' => $id]);
        $info = $stmtInfo->fetch();

        if ($info && $info['tipo'] == 'Preventiva') {
            // Find the schedule configuration for this asset
            // Assuming one active schedule per asset for MVP simplicity
            $stmtSched = $pdo->prepare("SELECT * FROM preventivas WHERE asset_id = :aid LIMIT 1");
            $stmtSched->execute(['aid' => $info['asset_id']]);
            $schedule = $stmtSched->fetch();

            if ($schedule) {
                // Calculate next date form Execution Date + Frequency
                $freq = $schedule['frequencia_dias'];
                $nextDate = date('Y-m-d', strtotime($data_exec . " + $freq days"));

                // Update Schedule
                $updateSched = $pdo->prepare("UPDATE preventivas SET ultima_data = :last, proxima_data = :next WHERE id = :pid");
                $updateSched->execute([
                    'last' => $data_exec,
                    'next' => $nextDate,
                    'pid' => $schedule['id']
                ]);
            }
        }
        
        header("Location: ordem_servico.php");
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM manutencao_logs WHERE id=:id");
    $stmt->execute(['id'=>$id]);
    $chamado = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Concluir Chamado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { slate: { 850: '#151e32', 950: '#020617' } } } } }
    </script>
</head>
<body class="bg-slate-950 text-white flex items-center justify-center h-screen font-sans">
    <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700">
        <div class="flex items-center justify-between mb-6">
             <h2 class="text-xl font-bold">Concluir Chamado #<?php echo $id; ?></h2>
             <span class="bg-emerald-500/10 text-emerald-400 text-xs font-bold px-2 py-1 rounded border border-emerald-500/20">Finalização</span>
        </div>
       
        <form method="POST" class="space-y-4">
             <div>
                <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Data Execução</label>
                <input type="date" name="data_execucao" value="<?php echo date('Y-m-d'); ?>" class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500" required>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Relatório Técnico</label>
                <textarea name="descricao" rows="3" class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500"><?php echo htmlspecialchars($chamado['descricao']); ?></textarea>
            </div>
             <div class="grid grid-cols-2 gap-4">
                 <div>
                    <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Custo (R$)</label>
                    <input type="number" step="0.01" name="custo" value="0.00" class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase block mb-1">Tempo (Hrs)</label>
                    <input type="number" step="0.1" name="tempo" value="0" class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800 mt-4">
                <a href="ordem_servico.php" class="px-4 py-2.5 text-slate-400 hover:text-white font-medium transition-colors">Cancelar</a>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 px-6 py-2.5 rounded-lg text-white font-bold shadow-lg shadow-emerald-600/20 transition-all transform hover:-translate-y-1">Confirmar Conclusão</button>
            </div>
        </form>
    </div>
</body>
</html>