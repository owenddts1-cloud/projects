<?php
require 'header.php';
require_once 'src/db.php';

$pdo = Database::getInstance();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM manutencao_logs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    echo "<script>window.location.href='ordem_servico.php';</script>";
}

// Handle Add New Chamado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = $_POST['asset_id'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $data_prevista = $_POST['data_prevista'];

    // Default status Pendente
    $sql = "INSERT INTO manutencao_logs (asset_id, tipo, descricao, data_limite, status, custo_pecas, tempo_parada_horas) 
            VALUES (:asset, :tipo, :desc, :limit, 'Pendente', 0, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['asset' => $asset_id, 'tipo' => $tipo, 'desc' => $descricao, 'limit' => $data_prevista]);
    echo "<script>window.location.href='ordem_servico.php';</script>";
}

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');

// Fetch Logs
// Update: Added filter by 'criado_em' so recently created tickets appear even if deadline is far future
$sql = "SELECT m.*, a.nome as asset_nome, a.patrimonio 
        FROM manutencao_logs m 
        JOIN assets a ON m.asset_id = a.id 
        WHERE (m.data_execucao BETWEEN :inicio AND :fim) 
           OR (m.data_limite BETWEEN :inicio2 AND :fim2)
           OR (m.criado_em BETWEEN :inicio3 AND :fim3)
        ORDER BY m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'inicio' => $data_inicio . ' 00:00:00',
    'fim' => $data_fim . ' 23:59:59',
    'inicio2' => $data_inicio,
    'fim2' => $data_fim,
    'inicio3' => $data_inicio . ' 00:00:00',
    'fim3' => $data_fim . ' 23:59:59'
]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$assets = $pdo->query("SELECT * FROM assets ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold text-white tracking-tight">Chamados</h2>
        <p class="text-slate-400">Histórico de Manutenções e Solicitações</p>
    </div>

    <div class="flex gap-2">
        <form class="glass-panel p-1 rounded-xl flex items-center gap-2">
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>"
                class="bg-slate-900/50 text-white rounded-lg px-2 py-2 text-sm border border-slate-700/50 outline-none">
            <span class="text-slate-500 text-xs font-bold">-</span>
            <input type="date" name="data_fim" value="<?php echo $data_fim; ?>"
                class="bg-slate-900/50 text-white rounded-lg px-2 py-2 text-sm border border-slate-700/50 outline-none">
            <button type="submit" class="bg-sky-600 hover:bg-sky-500 text-white p-2 text-sm rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </form>
        <button onclick="document.getElementById('modal-chamado').classList.remove('hidden')"
            class="bg-sky-600 hover:bg-sky-500 text-white font-bold p-3 rounded-xl shadow-lg transition-all flex items-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
        </button>
    </div>
</div>

<div class="glass-panel rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap">
            <thead>
                <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-700/50">
                    <th class="px-6 py-4 bg-slate-900/50">ID</th>
                    <th class="px-6 py-4 bg-slate-900/50">Limite / Execução</th>
                    <th class="px-6 py-4 bg-slate-900/50">Ativo</th>
                    <th class="px-6 py-4 bg-slate-900/50">Tipo</th>
                    <th class="px-6 py-4 bg-slate-900/50">Descrição</th>
                    <th class="px-6 py-4 bg-slate-900/50">Status</th>
                    <th class="px-6 py-4 bg-slate-900/50">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50 text-sm">
                <?php foreach ($logs as $log): ?>
                    <?php
                    // Logic Status
                    $statusLabel = 'PENDENTE';
                    $statusClass = 'text-amber-400 bg-amber-500/10 border-amber-500/20';
                    $dateDisplay = $log['data_limite'] ? date('d/m/Y', strtotime($log['data_limite'])) : '-';

                    if ($log['status'] == 'Realizado') {
                        $statusLabel = 'REALIZADO';
                        $statusClass = 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
                        $dateDisplay = date('d/m/Y', strtotime($log['data_execucao']));
                    } elseif ($log['status'] == 'Pendente' && $log['data_limite'] && $log['data_limite'] < date('Y-m-d')) {
                        $statusLabel = 'NÃO REALIZADO';
                        $statusClass = 'text-rose-400 bg-rose-500/10 border-rose-500/20';
                    }
                    ?>
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-slate-400">#<?php echo $log['id']; ?></td>
                        <td class="px-6 py-4 text-slate-300"><?php echo $dateDisplay; ?></td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-white"><?php echo htmlspecialchars($log['asset_nome']); ?></div>
                            <div class="text-xs text-slate-500 font-mono"><?php echo $log['patrimonio']; ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-300 bg-slate-700/50 px-2 py-1 rounded text-xs border border-slate-600">
                                <?php echo strtoupper($log['tipo']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-400 max-w-xs truncate"
                            title="<?php echo htmlspecialchars($log['descricao']); ?>">
                            <?php echo htmlspecialchars($log['descricao']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="<?php echo $statusClass; ?> px-2 py-1 rounded text-xs font-bold border border-current">
                                <?php echo $statusLabel; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 flex gap-3">
                            <?php if ($log['status'] == 'Pendente'): ?>
                                <a href="concluir_chamado.php?id=<?php echo $log['id']; ?>"
                                    class="text-emerald-400 hover:text-emerald-300" title="Concluir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $log['id']; ?>" onclick="return confirm('Excluir este chamado?')"
                                class="text-rose-400 hover:text-rose-300" title="Excluir">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-chamado"
    class="absolute inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
    <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-700 m-4">
        <h3 class="text-xl font-bold text-white mb-6">Novo Chamado</h3>
        <form method="POST" class="space-y-4">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Equipamento</label>
                <select name="asset_id"
                    class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                    <?php foreach ($assets as $a): ?>
                        <option value="<?php echo $a['id']; ?>"><?php echo $a['nome'] . ' (' . $a['patrimonio'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Tipo</label>
                    <select name="tipo"
                        class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                        <option value="Corretiva">Corretiva</option>
                        <option value="Preventiva">Preventiva</option>
                        <option value="Melhoria">Melhoria</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Data Limite</label>
                    <input type="date" name="data_prevista"
                        class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500"
                        required>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Descrição</label>
                <textarea name="descricao" rows="3"
                    class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modal-chamado').classList.add('hidden')"
                    class="px-4 py-2 text-slate-400 hover:text-white">Cancelar</button>
                <button type="submit"
                    class="bg-sky-600 px-6 py-2 rounded text-white font-bold hover:bg-sky-500">Salvar</button>
            </div>
        </form>
    </div>
</div>
</body>

</html>