-- Database Schema for Preventiva 360
-- Compatible with Phase 2 Overhaul

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` enum('admin','tecnico','operador') DEFAULT 'operador',
  `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `assets`
--

CREATE TABLE IF NOT EXISTS `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patrimonio` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `setor` varchar(100) DEFAULT NULL,
  `criticidade` enum('A','B','C') DEFAULT 'C',
  `periodicidade` varchar(50) DEFAULT 'Mensal',
  `status` enum('Ativo','Desativado','Manutencao') DEFAULT 'Ativo',
  `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patrimonio` (`patrimonio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `preventivas`
--

CREATE TABLE IF NOT EXISTS `preventivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `frequencia_dias` int(11) NOT NULL,
  `ultima_data` date DEFAULT NULL,
  `proxima_data` date DEFAULT NULL,
  `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `fk_preventiva_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `manutencao_logs`
-- (Chamados)
--

CREATE TABLE IF NOT EXISTS `manutencao_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `tipo` enum('Preventiva','Corretiva','Melhoria') NOT NULL,
  `descricao` text NOT NULL,
  `data_execucao` datetime DEFAULT NULL,
  `data_limite` date DEFAULT NULL,
  `custo_pecas` decimal(10,2) DEFAULT '0.00',
  `tempo_parada_horas` decimal(10,1) DEFAULT '0.0',
  `status` enum('Pendente','Realizado') DEFAULT 'Realizado',
  `tecnico_id` int(11) DEFAULT NULL,
  `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `fk_log_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
