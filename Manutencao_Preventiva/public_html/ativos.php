<?php
require 'header.php';
require_once 'src/db.php';
$pdo = Database::getInstance();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM assets WHERE id = :id");
    $stmt->execute(['id' => $id]);
    echo "<script>window.location.href='ativos.php';</script>";
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patrimonio = $_POST['patrimonio'];
    $nome = $_POST['nome'];
    $setor = $_POST['setor'];
    $criticidade = $_POST['criticidade'];
    $periodicidade = $_POST['periodicidade'];
    $status = $_POST['status'];

    // Check if updating or inserting
    $sql = "INSERT INTO assets (patrimonio, nome, setor, criticidade, periodicidade, status) 
            VALUES (:pat, :nome, :set, :crit, :per, :st)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([
            'pat' => $patrimonio,
            'nome' => $nome,
            'set' => $setor,
            'crit' => $criticidade,
            'per' => $periodicidade,
            'st' => $status
        ]);
        echo "<script>window.location.href='ativos.php';</script>";
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

$assets = $pdo->query("SELECT * FROM assets ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold text-white tracking-tight">Gestão de Ativos</h2>
        <p class="text-slate-400">Patrimônio e Configurações</p>
    </div>
    <button onclick="document.getElementById('modal-novo').classList.remove('hidden')"
        class="bg-sky-600 hover:bg-sky-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Novo Ativo
    </button>
</div>

<div class="glass-panel rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-left">
            <thead>
                <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-700/50">
                    <th class="px-6 py-4 bg-slate-900/50">PATRIMÔNIO</th>
                    <th class="px-6 py-4 bg-slate-900/50">Nome</th>
                    <th class="px-6 py-4 bg-slate-900/50">Setor</th>
                    <th class="px-6 py-4 bg-slate-900/50">Criticidade</th>
                    <th class="px-6 py-4 bg-slate-900/50">Periodicidade</th>
                    <th class="px-6 py-4 bg-slate-900/50">Status</th>
                    <th class="px-6 py-4 bg-slate-900/50">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50 text-sm">
                <?php foreach ($assets as $asset): ?>
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-sky-400 font-medium"><?php echo $asset['patrimonio']; ?></td>
                        <td class="px-6 py-4 text-slate-200"><?php echo $asset['nome']; ?></td>
                        <td class="px-6 py-4 text-slate-400"><?php echo $asset['setor']; ?></td>
                        <td class="px-6 py-4">
                            <span
                                class="bg-slate-800 border border-slate-700 px-2 py-1 rounded text-xs font-bold"><?php echo $asset['criticidade']; ?></span>
                        </td>
                        <td class="px-6 py-4 text-slate-300"><?php echo $asset['periodicidade']; ?></td>
                        <td class="px-6 py-4">
                            <?php if ($asset['status'] == 'Ativo'): ?>
                                <span
                                    class="text-emerald-400 font-bold text-xs bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded">ATIVO</span>
                            <?php else: ?>
                                <span class="text-slate-500 font-bold text-xs bg-slate-800 px-2 py-1 rounded">DESATIVADO</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <a href="?delete=<?php echo $asset['id']; ?>"
                                onclick="return confirm('Tem certeza que deseja excluir?')"
                                class="text-rose-400 hover:text-rose-300 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                                Excluir
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Absolute -->
<div id="modal-novo"
    class="absolute inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
    <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-700 m-4">
        <h3 class="text-xl font-bold text-white mb-6">Novo Ativo</h3>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Patrimônio</label>
                    <input type="text" name="patrimonio"
                        class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500"
                        required>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Status</label>
                    <select name="status"
                        class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                        <option value="Ativo">Ativo</option>
                        <option value="Desativado">Desativado</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Nome</label>
                <input type="text" name="nome"
                    class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500"
                    required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Setor</label>
                    <input type="text" name="setor"
                        class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500"
                        required>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Periocidade</label>
                    <select name="periodicidade"
                        class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                        <option value="Diário">Diário</option>
                        <option value="Semanal">Semanal</option>
                        <option value="Mensal" selected>Mensal</option>
                        <option value="Trimestral">Trimestral</option>
                        <option value="Semestral">Semestral</option>
                        <option value="Anual">Anual</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Criticidade</label>
                <select name="criticidade"
                    class="w-full bg-slate-950 text-white p-3 rounded border border-slate-700 outline-none focus:border-sky-500">
                    <option value="A">A - Crítico</option>
                    <option value="B">B - Importante</option>
                    <option value="C" selected>C - Normal</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('modal-novo').classList.add('hidden')"
                    class="px-4 py-2 text-slate-400 hover:text-white">Cancelar</button>
                <button type="submit"
                    class="bg-sky-600 px-6 py-2 rounded text-white font-bold hover:bg-sky-500">Salvar</button>
            </div>
        </form>
    </div>
</div>
</body>

</html>