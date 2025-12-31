<?php

class KPI
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    private function getDateCondition($start, $end, $dateField = 'data_execucao')
    {
        if ($start && $end) {
            return " AND $dateField BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
        }
        return "";
    }

    // 1. Total Preventivas (Configurações Ativas)
    // "Total de Preventivas" often means how many assets are covered or how many tickets generated. 
    // Let's stick to "Total Configurations" as base capacity.
    public function getTotalPreventivas()
    {
        $sql = "SELECT COUNT(*) as qtd FROM preventivas";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['qtd'];
    }

    // 2. Preventivas Pendentes (Strictly Type='Preventiva')
    public function getPreventivasPendentes()
    {
        // 1. Schedules Due (Future generation)
        $sql1 = "SELECT COUNT(*) as qtd FROM preventivas WHERE proxima_data <= NOW()";
        $stmt1 = $this->pdo->query($sql1);
        $schedPending = $stmt1->fetch(PDO::FETCH_ASSOC)['qtd'];

        // 2. Open Tickets (Type='Preventiva' AND Status='Pendente')
        $sql2 = "SELECT COUNT(*) as qtd FROM manutencao_logs WHERE tipo = 'Preventiva' AND status = 'Pendente'";
        $stmt2 = $this->pdo->query($sql2);
        $logsPending = $stmt2->fetch(PDO::FETCH_ASSOC)['qtd'];

        return $schedPending + $logsPending;
    }

    // 3. Preventivas Concluídas (Strictly Type='Preventiva')
    public function getPreventivasConcluidas($start = null, $end = null)
    {
        $dateCond = $this->getDateCondition($start, $end, 'data_execucao');
        $sql = "SELECT COUNT(*) as qtd FROM manutencao_logs WHERE tipo = 'Preventiva' AND status = 'Realizado' $dateCond";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['qtd'];
    }

    // 4. Percentage Completed (Preventivas Only)
    public function getPorcentagemConclusao($start = null, $end = null)
    {
        $concluidas = $this->getPreventivasConcluidas($start, $end);
        $pendentes = $this->getPreventivasPendentes();
        // Note: 'pendentes' is a snapshot (now), 'concluidas' is a period flow. 
        // Mixing them is tricky but standard for simple dashboards.

        $total = $concluidas + $pendentes;

        if ($total == 0)
            return 0;
        return round(($concluidas / $total) * 100, 1);
    }

    // Chart: Chamados Comparison (Preventiva vs Corretiva)
    public function getChartDataComparison($start = null, $end = null)
    {
        $dateCond = $this->getDateCondition($start, $end, 'criado_em');
        if (!$start)
            $dateCond = " AND criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $sql = "SELECT DATE(criado_em) as data, 
                       SUM(CASE WHEN tipo = 'Preventiva' THEN 1 ELSE 0 END) as prev,
                       SUM(CASE WHEN tipo = 'Corretiva' THEN 1 ELSE 0 END) as corr
                FROM manutencao_logs 
                WHERE 1=1 $dateCond 
                GROUP BY DATE(criado_em) 
                ORDER BY data ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Chart: Criticality (Real Data from Assets)
    public function getChartDataCriticality()
    {
        // Count assets by criticality (A, B, C)
        $sql = "SELECT criticidade, COUNT(*) as total FROM assets GROUP BY criticidade";
        $stmt = $this->pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = ['A' => 0, 'B' => 0, 'C' => 0];
        foreach ($results as $row) {
            $key = strtoupper($row['criticidade']); // ensure case matches
            if (isset($data[$key])) {
                $data[$key] = $row['total'];
            }
        }
        return $data;
    }
}
?>