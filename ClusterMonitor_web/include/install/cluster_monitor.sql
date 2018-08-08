-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Lun 28 Mai 2018 à 11:43
-- Version du serveur :  10.1.22-MariaDB-
-- Version de PHP :  5.6.33-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `cluster_monitor`
--

-- --------------------------------------------------------

--
-- Structure de la table `Auth`
--

CREATE TABLE IF NOT EXISTS `Auth` (
`idLogin` int(11) NOT NULL,
  `Login` varchar(45) NOT NULL,
  `Mdp` varchar(250) NOT NULL,
  `Nom` varchar(45) NOT NULL,
  `Prenom` varchar(45) DEFAULT NULL,
  `Groupe` varchar(45) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Clusters`
--

CREATE TABLE IF NOT EXISTS `Clusters` (
  `idClusters` varchar(50) NOT NULL,
  `SlurmVersion` varchar(45) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `Last_refresh` timestamp NULL DEFAULT NULL,
  `Last_refresh_jobstats` timestamp NULL DEFAULT NULL,
  `config` longtext,
  `interconnect` varchar(50) NOT NULL,
  `jobmetrics` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_Clusters`
--

CREATE TABLE IF NOT EXISTS `Collect_Clusters` (
  `id_Clusters` varchar(20) NOT NULL,
  `CPU_allocated` smallint(11) unsigned NOT NULL,
  `CPU_idle` smallint(11) unsigned NOT NULL,
  `CPU_other` smallint(11) unsigned NOT NULL,
  `CPU_total` smallint(11) unsigned NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_Clusters_History`
--

CREATE TABLE IF NOT EXISTS `Collect_Clusters_History` (
  `id_Clusters` varchar(20) NOT NULL,
  `NUM_week` smallint(11) unsigned NOT NULL,
  `AVG_CPU_allocated` smallint(11) unsigned NOT NULL,
  `AVG_CPU_idle` smallint(11) unsigned NOT NULL,
  `AVG_CPU_other` smallint(11) unsigned NOT NULL,
  `CPU_total` smallint(11) unsigned NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_Frontaux`
--

CREATE TABLE IF NOT EXISTS `Collect_Frontaux` (
  `id_Frontaux` varchar(20) NOT NULL,
  `id_Clusters` varchar(20) NOT NULL,
  `load1` varchar(10) DEFAULT NULL,
  `load5` varchar(10) DEFAULT NULL,
  `load15` varchar(10) DEFAULT NULL,
  `nb_user` varchar(11) DEFAULT NULL,
  `uptime` varchar(45) DEFAULT NULL,
  `Dispo` tinyint(1) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_FS`
--

CREATE TABLE IF NOT EXISTS `Collect_FS` (
  `id_Filesystems` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `disponible` bigint(20) NOT NULL,
  `utilise` bigint(20) NOT NULL,
  `disponible_inode` bigint(20) NOT NULL,
  `utilise_inode` bigint(20) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Dispo` tinyint(1) NOT NULL,
  `save` enum('y','n') DEFAULT 'n'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_FS_History`
--

CREATE TABLE IF NOT EXISTS `Collect_FS_History` (
  `id_Filesystems` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `NUM_week` int(11) NOT NULL,
  `MAX_disponible` bigint(20) NOT NULL,
  `MAX_utilise` bigint(20) NOT NULL,
  `MAX_disponible_inode` bigint(20) NOT NULL,
  `MAX_utilise_inode` bigint(20) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_Jobs`
--

CREATE TABLE IF NOT EXISTS `Collect_Jobs` (
  `Jobid` varchar(256) NOT NULL,
  `id_Partitions` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `id_QOS` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `User` varchar(45) NOT NULL,
  `State` varchar(45) NOT NULL,
  `Time` varchar(45) NOT NULL,
  `TimeLimit` varchar(45) NOT NULL,
  `Nodes` int(11) NOT NULL,
  `Cpus` int(11) NOT NULL,
  `StartTime` varchar(100) NOT NULL,
  `EndTime` varchar(100) NOT NULL,
  `Priority` int(11) NOT NULL,
  `Nodelist` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_nodes`
--

CREATE TABLE IF NOT EXISTS `Collect_nodes` (
  `id_Clusters` varchar(50) NOT NULL,
  `allocated` int(11) NOT NULL,
  `idle` int(11) NOT NULL,
  `other` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_nodes_History`
--

CREATE TABLE IF NOT EXISTS `Collect_nodes_History` (
  `id_Clusters` varchar(50) NOT NULL,
  `NUM_week` int(11) NOT NULL,
  `MAX_allocated` int(11) NOT NULL,
  `MIN_allocated` int(11) NOT NULL,
  `AVG_allocated` int(11) NOT NULL,
  `MAX_idle` int(11) NOT NULL,
  `MIN_idle` int(11) NOT NULL,
  `AVG_idle` int(11) NOT NULL,
  `MAX_other` int(11) NOT NULL,
  `MIN_other` int(11) NOT NULL,
  `AVG_other` int(11) NOT NULL,
  `MAX_total` int(11) NOT NULL,
  `MIN_total` int(11) NOT NULL,
  `AVG_total` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_partitions`
--

CREATE TABLE IF NOT EXISTS `Collect_partitions` (
  `id_Partitions` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `Nombre_job_pd` int(11) NOT NULL,
  `CPU_allocated` int(11) NOT NULL,
  `CPU_idle` int(11) NOT NULL,
  `CPU_other` int(11) NOT NULL,
  `CPU_total` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_partitions_History`
--

CREATE TABLE IF NOT EXISTS `Collect_partitions_History` (
  `id_Partitions` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `NUM_week` int(11) NOT NULL,
  `MAX_Nombre_job_pd` int(11) NOT NULL,
  `MIN_Nombre_job_pd` int(11) NOT NULL,
  `AVG_Nombre_job_pd` int(11) NOT NULL,
  `MAX_CPU_allocated` int(11) NOT NULL,
  `MIN_CPU_allocated` int(11) NOT NULL,
  `AVG_CPU_allocated` int(11) NOT NULL,
  `MAX_CPU_idle` int(11) NOT NULL,
  `MIN_CPU_idle` int(11) NOT NULL,
  `AVG_CPU_idle` int(11) NOT NULL,
  `MAX_CPU_other` int(11) NOT NULL,
  `MIN_CPU_other` int(11) NOT NULL,
  `AVG_CPU_other` int(11) NOT NULL,
  `MAX_CPU_total` int(11) NOT NULL,
  `MIN_CPU_total` int(11) NOT NULL,
  `AVG_CPU_total` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_Quota`
--

CREATE TABLE IF NOT EXISTS `Collect_Quota` (
  `id_Clusters` varchar(50) NOT NULL,
  `id_Filesystems` varchar(50) NOT NULL,
  `user_group` varchar(45) NOT NULL,
  `quota` bigint(20) NOT NULL,
  `utilise` bigint(20) NOT NULL,
  `disponible` bigint(20) NOT NULL,
  `quota_inode` bigint(20) NOT NULL,
  `utilise_inode` bigint(20) NOT NULL,
  `disponible_inode` bigint(20) NOT NULL,
  `quotatype` varchar(64) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Collect_rapport`
--

CREATE TABLE IF NOT EXISTS `Collect_rapport` (
  `id_Clusters` varchar(50) NOT NULL,
  `Nb_user_soumis` int(11) NOT NULL,
  `Nb_user_frontaux` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Config`
--

CREATE TABLE IF NOT EXISTS `Config` (
`idconfig` int(11) NOT NULL,
  `langue` varchar(11) NOT NULL,
  `Ret_CC` int(11) NOT NULL,
  `Ret_CFR` int(11) NOT NULL,
  `Ret_CFS` int(11) NOT NULL,
  `Ret_CN` int(11) NOT NULL,
  `Ret_CP` int(11) NOT NULL,
  `Ret_JM` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Filesystems`
--

CREATE TABLE IF NOT EXISTS `Filesystems` (
  `idFilesystems` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `type` varchar(45) NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Frontaux`
--

CREATE TABLE IF NOT EXISTS `Frontaux` (
  `idFrontaux` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Jobs_History_Athos`
--

CREATE TABLE IF NOT EXISTS `Jobs_History_Athos` (
  `idJobs` varchar(256) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `JobName` varchar(50) NOT NULL,
  `UserId` varchar(50) NOT NULL,
  `GroupId` varchar(50) NOT NULL,
  `Priority` int(11) NOT NULL,
  `Nice` int(11) NOT NULL,
  `Account` varchar(50) NOT NULL,
  `QOS` varchar(50) NOT NULL,
  `WCKey` varchar(50) NOT NULL,
  `JobState` varchar(50) NOT NULL,
  `Requeue` int(11) NOT NULL,
  `Restarts` int(11) NOT NULL,
  `BatchFlag` int(11) NOT NULL,
  `Reboot` int(11) NOT NULL,
  `ExitCode` varchar(50) NOT NULL,
  `DerivedExitCode` varchar(50) NOT NULL,
  `RunTime` varchar(50) NOT NULL,
  `TimeLimit` varchar(50) NOT NULL,
  `TimeMin` varchar(50) NOT NULL,
  `SubmitTime` varchar(50) NOT NULL,
  `EligibleTime` varchar(50) NOT NULL,
  `StartTime` varchar(50) NOT NULL,
  `EndTime` varchar(50) NOT NULL,
  `PreemptTime` varchar(50) NOT NULL,
  `SuspendTime` varchar(50) NOT NULL,
  `SecsPreSuspend` varchar(50) NOT NULL,
  `Partitions` varchar(50) NOT NULL,
  `AllocNodeSid` varchar(50) NOT NULL,
  `ReqNodeList` varchar(50) NOT NULL,
  `ExcNodeList` varchar(50) NOT NULL,
  `NodeList` varchar(50) NOT NULL,
  `BatchHost` varchar(50) NOT NULL,
  `NumNodes` int(11) NOT NULL,
  `NumCPUs` int(11) NOT NULL,
  `CPUsTask` int(11) NOT NULL,
  `ReqBSCT` varchar(50) NOT NULL,
  `SocksNode` varchar(50) NOT NULL,
  `NtasksPerNBSC` varchar(50) NOT NULL,
  `CoreSpec` varchar(50) NOT NULL,
  `Nodes` varchar(50) NOT NULL,
  `CPUIDs` varchar(50) NOT NULL,
  `Mem` varchar(50) NOT NULL,
  `MinCPUsNode` varchar(50) NOT NULL,
  `MinMemoryCPU` varchar(50) NOT NULL,
  `MinTmpDiskNode` varchar(50) NOT NULL,
  `Features` varchar(50) NOT NULL,
  `Gres` varchar(50) NOT NULL,
  `Reservation` varchar(50) NOT NULL,
  `Shared` varchar(50) NOT NULL,
  `Contiguous` varchar(50) NOT NULL,
  `Licenses` varchar(50) NOT NULL,
  `Network` varchar(50) NOT NULL,
  `Command` varchar(50) NOT NULL,
  `WorkDir` varchar(50) NOT NULL,
  `StdErr` varchar(50) NOT NULL,
  `StdIn` varchar(50) NOT NULL,
  `StdOut` varchar(50) NOT NULL,
  `MaxVMSize` varchar(50) NOT NULL,
  `MaxVMSizeTask` varchar(50) NOT NULL,
  `AveVMSize` varchar(50) NOT NULL,
  `MaxRSS` varchar(50) NOT NULL,
  `MaxRSSTask` varchar(50) NOT NULL,
  `AveRSS` varchar(50) NOT NULL,
  `MaxPages` varchar(50) NOT NULL,
  `MaxPagesTask` varchar(50) NOT NULL,
  `AvePages` varchar(50) NOT NULL,
  `MinCPU` varchar(50) NOT NULL,
  `MinCPUTask` varchar(50) NOT NULL,
  `AveCPU` varchar(50) NOT NULL,
  `NTasks` varchar(50) NOT NULL,
  `AveCPUFreq` varchar(50) NOT NULL,
  `ReqCPUFreq` varchar(50) NOT NULL,
  `ConsumedEnergy` varchar(50) NOT NULL,
  `MaxDiskRead` varchar(50) NOT NULL,
  `MaxDiskReadTask` varchar(50) NOT NULL,
  `AveDiskRead` varchar(50) NOT NULL,
  `MaxDiskWrite` varchar(50) NOT NULL,
  `MaxDiskWriteTask` varchar(50) NOT NULL,
  `AveDiskWrite` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Jobs_History_Eole`
--

CREATE TABLE IF NOT EXISTS `Jobs_History_Eole` (
  `idJobs` varchar(256) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `JobName` varchar(50) NOT NULL,
  `UserId` varchar(50) NOT NULL,
  `GroupId` varchar(50) NOT NULL,
  `Priority` int(11) NOT NULL,
  `Nice` int(11) NOT NULL,
  `Account` varchar(50) NOT NULL,
  `QOS` varchar(50) NOT NULL,
  `WCKey` varchar(50) NOT NULL,
  `JobState` varchar(50) NOT NULL,
  `Requeue` int(11) NOT NULL,
  `Restarts` int(11) NOT NULL,
  `BatchFlag` int(11) NOT NULL,
  `Reboot` int(11) NOT NULL,
  `ExitCode` varchar(50) NOT NULL,
  `DerivedExitCode` varchar(50) NOT NULL,
  `RunTime` varchar(50) NOT NULL,
  `TimeLimit` varchar(50) NOT NULL,
  `TimeMin` varchar(50) NOT NULL,
  `SubmitTime` varchar(50) NOT NULL,
  `EligibleTime` varchar(50) NOT NULL,
  `StartTime` varchar(50) NOT NULL,
  `EndTime` varchar(50) NOT NULL,
  `PreemptTime` varchar(50) NOT NULL,
  `SuspendTime` varchar(50) NOT NULL,
  `SecsPreSuspend` varchar(50) NOT NULL,
  `Partitions` varchar(50) NOT NULL,
  `AllocNodeSid` varchar(50) NOT NULL,
  `ReqNodeList` varchar(50) NOT NULL,
  `ExcNodeList` varchar(50) NOT NULL,
  `NodeList` varchar(50) NOT NULL,
  `BatchHost` varchar(50) NOT NULL,
  `NumNodes` int(11) NOT NULL,
  `NumCPUs` int(11) NOT NULL,
  `CPUsTask` int(11) NOT NULL,
  `ReqBSCT` varchar(50) NOT NULL,
  `SocksNode` varchar(50) NOT NULL,
  `NtasksPerNBSC` varchar(50) NOT NULL,
  `CoreSpec` varchar(50) NOT NULL,
  `Nodes` varchar(50) NOT NULL,
  `CPUIDs` varchar(50) NOT NULL,
  `Mem` varchar(50) NOT NULL,
  `MinCPUsNode` varchar(50) NOT NULL,
  `MinMemoryCPU` varchar(50) NOT NULL,
  `MinTmpDiskNode` varchar(50) NOT NULL,
  `Features` varchar(50) NOT NULL,
  `Gres` varchar(50) NOT NULL,
  `Reservation` varchar(50) NOT NULL,
  `Shared` varchar(50) NOT NULL,
  `Contiguous` varchar(50) NOT NULL,
  `Licenses` varchar(50) NOT NULL,
  `Network` varchar(50) NOT NULL,
  `Command` varchar(50) NOT NULL,
  `WorkDir` varchar(50) NOT NULL,
  `StdErr` varchar(50) NOT NULL,
  `StdIn` varchar(50) NOT NULL,
  `StdOut` varchar(50) NOT NULL,
  `MaxVMSize` varchar(50) NOT NULL,
  `MaxVMSizeTask` varchar(50) NOT NULL,
  `AveVMSize` varchar(50) NOT NULL,
  `MaxRSS` varchar(50) NOT NULL,
  `MaxRSSTask` varchar(50) NOT NULL,
  `AveRSS` varchar(50) NOT NULL,
  `MaxPages` varchar(50) NOT NULL,
  `MaxPagesTask` varchar(50) NOT NULL,
  `AvePages` varchar(50) NOT NULL,
  `MinCPU` varchar(50) NOT NULL,
  `MinCPUTask` varchar(50) NOT NULL,
  `AveCPU` varchar(50) NOT NULL,
  `NTasks` varchar(50) NOT NULL,
  `AveCPUFreq` varchar(50) NOT NULL,
  `ReqCPUFreq` varchar(50) NOT NULL,
  `ConsumedEnergy` varchar(50) NOT NULL,
  `MaxDiskRead` varchar(50) NOT NULL,
  `MaxDiskReadTask` varchar(50) NOT NULL,
  `AveDiskRead` varchar(50) NOT NULL,
  `MaxDiskWrite` varchar(50) NOT NULL,
  `MaxDiskWriteTask` varchar(50) NOT NULL,
  `AveDiskWrite` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Jobs_History_Porthos`
--

CREATE TABLE IF NOT EXISTS `Jobs_History_Porthos` (
  `idJobs` varchar(256) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `JobName` varchar(50) NOT NULL,
  `UserId` varchar(50) NOT NULL,
  `GroupId` varchar(50) NOT NULL,
  `Priority` int(11) NOT NULL,
  `Nice` int(11) NOT NULL,
  `Account` varchar(50) NOT NULL,
  `QOS` varchar(50) NOT NULL,
  `WCKey` varchar(50) NOT NULL,
  `JobState` varchar(50) NOT NULL,
  `Requeue` int(11) NOT NULL,
  `Restarts` int(11) NOT NULL,
  `BatchFlag` int(11) NOT NULL,
  `Reboot` int(11) NOT NULL,
  `ExitCode` varchar(50) NOT NULL,
  `DerivedExitCode` varchar(50) NOT NULL,
  `RunTime` varchar(50) NOT NULL,
  `TimeLimit` varchar(50) NOT NULL,
  `TimeMin` varchar(50) NOT NULL,
  `SubmitTime` varchar(50) NOT NULL,
  `EligibleTime` varchar(50) NOT NULL,
  `StartTime` varchar(50) NOT NULL,
  `EndTime` varchar(50) NOT NULL,
  `PreemptTime` varchar(50) NOT NULL,
  `SuspendTime` varchar(50) NOT NULL,
  `SecsPreSuspend` varchar(50) NOT NULL,
  `Partitions` varchar(50) NOT NULL,
  `AllocNodeSid` varchar(50) NOT NULL,
  `ReqNodeList` varchar(50) NOT NULL,
  `ExcNodeList` varchar(50) NOT NULL,
  `NodeList` varchar(50) NOT NULL,
  `BatchHost` varchar(50) NOT NULL,
  `NumNodes` int(11) NOT NULL,
  `NumCPUs` int(11) NOT NULL,
  `CPUsTask` int(11) NOT NULL,
  `ReqBSCT` varchar(50) NOT NULL,
  `SocksNode` varchar(50) NOT NULL,
  `NtasksPerNBSC` varchar(50) NOT NULL,
  `CoreSpec` varchar(50) NOT NULL,
  `Nodes` varchar(50) NOT NULL,
  `CPUIDs` varchar(50) NOT NULL,
  `Mem` varchar(50) NOT NULL,
  `MinCPUsNode` varchar(50) NOT NULL,
  `MinMemoryCPU` varchar(50) NOT NULL,
  `MinTmpDiskNode` varchar(50) NOT NULL,
  `Features` varchar(50) NOT NULL,
  `Gres` varchar(50) NOT NULL,
  `Reservation` varchar(50) NOT NULL,
  `Shared` varchar(50) NOT NULL,
  `Contiguous` varchar(50) NOT NULL,
  `Licenses` varchar(50) NOT NULL,
  `Network` varchar(50) NOT NULL,
  `Command` varchar(50) NOT NULL,
  `WorkDir` varchar(50) NOT NULL,
  `StdErr` varchar(50) NOT NULL,
  `StdIn` varchar(50) NOT NULL,
  `StdOut` varchar(50) NOT NULL,
  `MaxVMSize` varchar(50) NOT NULL,
  `MaxVMSizeTask` varchar(50) NOT NULL,
  `AveVMSize` varchar(50) NOT NULL,
  `MaxRSS` varchar(50) NOT NULL,
  `MaxRSSTask` varchar(50) NOT NULL,
  `AveRSS` varchar(50) NOT NULL,
  `MaxPages` varchar(50) NOT NULL,
  `MaxPagesTask` varchar(50) NOT NULL,
  `AvePages` varchar(50) NOT NULL,
  `MinCPU` varchar(50) NOT NULL,
  `MinCPUTask` varchar(50) NOT NULL,
  `AveCPU` varchar(50) NOT NULL,
  `NTasks` varchar(50) NOT NULL,
  `AveCPUFreq` varchar(50) NOT NULL,
  `ReqCPUFreq` varchar(50) NOT NULL,
  `ConsumedEnergy` varchar(50) NOT NULL,
  `MaxDiskRead` varchar(50) NOT NULL,
  `MaxDiskReadTask` varchar(50) NOT NULL,
  `AveDiskRead` varchar(50) NOT NULL,
  `MaxDiskWrite` varchar(50) NOT NULL,
  `MaxDiskWriteTask` varchar(50) NOT NULL,
  `AveDiskWrite` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Jobs_Metrics`
--

CREATE TABLE IF NOT EXISTS `Jobs_Metrics` (
  `idJobs` varchar(256) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `JobName` varchar(50) NOT NULL,
  `UserId` varchar(50) NOT NULL,
  `GroupId` varchar(50) NOT NULL,
  `Priority` int(11) NOT NULL,
  `Nice` int(11) NOT NULL,
  `Account` varchar(50) NOT NULL,
  `QOS` varchar(50) NOT NULL,
  `WCKey` varchar(50) NOT NULL,
  `JobState` varchar(50) NOT NULL,
  `Requeue` int(11) NOT NULL,
  `Restarts` int(11) NOT NULL,
  `BatchFlag` int(11) NOT NULL,
  `Reboot` int(11) NOT NULL,
  `ExitCode` varchar(50) NOT NULL,
  `DerivedExitCode` varchar(50) NOT NULL,
  `RunTime` varchar(50) NOT NULL,
  `TimeLimit` varchar(50) NOT NULL,
  `TimeMin` varchar(50) NOT NULL,
  `SubmitTime` varchar(50) NOT NULL,
  `EligibleTime` varchar(50) NOT NULL,
  `StartTime` varchar(50) NOT NULL,
  `EndTime` varchar(50) NOT NULL,
  `PreemptTime` varchar(50) NOT NULL,
  `SuspendTime` varchar(50) NOT NULL,
  `SecsPreSuspend` varchar(50) NOT NULL,
  `Partitions` varchar(50) NOT NULL,
  `AllocNodeSid` varchar(50) NOT NULL,
  `ReqNodeList` varchar(50) NOT NULL,
  `ExcNodeList` varchar(50) NOT NULL,
  `NodeList` varchar(50) NOT NULL,
  `BatchHost` varchar(50) NOT NULL,
  `NumNodes` int(11) NOT NULL,
  `NumCPUs` int(11) NOT NULL,
  `CPUsTask` int(11) NOT NULL,
  `ReqBSCT` varchar(50) NOT NULL,
  `SocksNode` varchar(50) NOT NULL,
  `NtasksPerNBSC` varchar(50) NOT NULL,
  `CoreSpec` varchar(50) NOT NULL,
  `Nodes` varchar(50) NOT NULL,
  `CPUIDs` varchar(50) NOT NULL,
  `Mem` varchar(50) NOT NULL,
  `MinCPUsNode` varchar(50) NOT NULL,
  `MinMemoryCPU` varchar(50) NOT NULL,
  `MinTmpDiskNode` varchar(50) NOT NULL,
  `Features` varchar(50) NOT NULL,
  `Gres` varchar(50) NOT NULL,
  `Reservation` varchar(50) NOT NULL,
  `Shared` varchar(50) NOT NULL,
  `Contiguous` varchar(50) NOT NULL,
  `Licenses` varchar(50) NOT NULL,
  `Network` varchar(50) NOT NULL,
  `Command` varchar(50) NOT NULL,
  `WorkDir` varchar(50) NOT NULL,
  `StdErr` varchar(50) NOT NULL,
  `StdIn` varchar(50) NOT NULL,
  `StdOut` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Jobs_Metricsdet_Athos`
--

CREATE TABLE IF NOT EXISTS `Jobs_Metricsdet_Athos` (
`id` int(11) NOT NULL,
  `idJobs` varchar(256) NOT NULL,
  `datetime` datetime NOT NULL,
  `stepid` varchar(100) NOT NULL,
  `MaxVMSize` varchar(50) NOT NULL,
  `MaxVMSizeNode` varchar(50) NOT NULL,
  `MaxVMSizeTask` varchar(50) NOT NULL,
  `AveVMSize` varchar(50) NOT NULL,
  `MaxRSS` varchar(50) NOT NULL,
  `MaxRSSNode` varchar(50) NOT NULL,
  `MaxRSSTask` varchar(50) NOT NULL,
  `AveRSS` varchar(50) NOT NULL,
  `MaxPages` varchar(50) NOT NULL,
  `MaxPagesNode` varchar(50) NOT NULL,
  `MaxPagesTask` varchar(50) NOT NULL,
  `AvePages` varchar(50) NOT NULL,
  `MinCPU` varchar(50) NOT NULL,
  `MinCPUNode` varchar(50) NOT NULL,
  `MinCPUTask` varchar(50) NOT NULL,
  `AveCPU` varchar(50) NOT NULL,
  `NTasks` varchar(50) NOT NULL,
  `AveCPUFreq` varchar(50) NOT NULL,
  `ReqCPUFreq` varchar(50) NOT NULL,
  `ConsumedEnergy` varchar(50) NOT NULL,
  `MaxDiskRead` varchar(50) NOT NULL,
  `MaxDiskReadNode` varchar(50) NOT NULL,
  `MaxDiskReadTask` varchar(50) NOT NULL,
  `AveDiskRead` varchar(50) NOT NULL,
  `MaxDiskWrite` varchar(50) NOT NULL,
  `MaxDiskWriteNode` varchar(50) NOT NULL,
  `MaxDiskWriteTask` varchar(50) NOT NULL,
  `AveDiskWrite` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Jobs_Metricsdet_Eole`
--

CREATE TABLE IF NOT EXISTS `Jobs_Metricsdet_Eole` (
  `id` int(11) NOT NULL,
  `idJobs` varchar(256) NOT NULL,
  `datetime` datetime NOT NULL,
  `stepid` varchar(100) NOT NULL,
  `MaxVMSize` varchar(50) NOT NULL,
  `MaxVMSizeNode` varchar(50) NOT NULL,
  `MaxVMSizeTask` varchar(50) NOT NULL,
  `AveVMSize` varchar(50) NOT NULL,
  `MaxRSS` varchar(50) NOT NULL,
  `MaxRSSNode` varchar(50) NOT NULL,
  `MaxRSSTask` varchar(50) NOT NULL,
  `AveRSS` varchar(50) NOT NULL,
  `MaxPages` varchar(50) NOT NULL,
  `MaxPagesNode` varchar(50) NOT NULL,
  `MaxPagesTask` varchar(50) NOT NULL,
  `AvePages` varchar(50) NOT NULL,
  `MinCPU` varchar(50) NOT NULL,
  `MinCPUNode` varchar(50) NOT NULL,
  `MinCPUTask` varchar(50) NOT NULL,
  `AveCPU` varchar(50) NOT NULL,
  `NTasks` varchar(50) NOT NULL,
  `AveCPUFreq` varchar(50) NOT NULL,
  `ReqCPUFreq` varchar(50) NOT NULL,
  `ConsumedEnergy` varchar(50) NOT NULL,
  `MaxDiskRead` varchar(50) NOT NULL,
  `MaxDiskReadNode` varchar(50) NOT NULL,
  `MaxDiskReadTask` varchar(50) NOT NULL,
  `AveDiskRead` varchar(50) NOT NULL,
  `MaxDiskWrite` varchar(50) NOT NULL,
  `MaxDiskWriteNode` varchar(50) NOT NULL,
  `MaxDiskWriteTask` varchar(50) NOT NULL,
  `AveDiskWrite` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Jobs_Metricsdet_Porthos`
--

CREATE TABLE IF NOT EXISTS `Jobs_Metricsdet_Porthos` (
`id` int(11) NOT NULL,
  `idJobs` varchar(256) NOT NULL,
  `datetime` datetime NOT NULL,
  `stepid` varchar(100) NOT NULL,
  `MaxVMSize` varchar(50) NOT NULL,
  `MaxVMSizeNode` varchar(50) NOT NULL,
  `MaxVMSizeTask` varchar(50) NOT NULL,
  `AveVMSize` varchar(50) NOT NULL,
  `MaxRSS` varchar(50) NOT NULL,
  `MaxRSSNode` varchar(50) NOT NULL,
  `MaxRSSTask` varchar(50) NOT NULL,
  `AveRSS` varchar(50) NOT NULL,
  `MaxPages` varchar(50) NOT NULL,
  `MaxPagesNode` varchar(50) NOT NULL,
  `MaxPagesTask` varchar(50) NOT NULL,
  `AvePages` varchar(50) NOT NULL,
  `MinCPU` varchar(50) NOT NULL,
  `MinCPUNode` varchar(50) NOT NULL,
  `MinCPUTask` varchar(50) NOT NULL,
  `AveCPU` varchar(50) NOT NULL,
  `NTasks` varchar(50) NOT NULL,
  `AveCPUFreq` varchar(50) NOT NULL,
  `ReqCPUFreq` varchar(50) NOT NULL,
  `ConsumedEnergy` varchar(50) NOT NULL,
  `MaxDiskRead` varchar(50) NOT NULL,
  `MaxDiskReadNode` varchar(50) NOT NULL,
  `MaxDiskReadTask` varchar(50) NOT NULL,
  `AveDiskRead` varchar(50) NOT NULL,
  `MaxDiskWrite` varchar(50) NOT NULL,
  `MaxDiskWriteNode` varchar(50) NOT NULL,
  `MaxDiskWriteTask` varchar(50) NOT NULL,
  `AveDiskWrite` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=155599590 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Liens`
--

CREATE TABLE IF NOT EXISTS `Liens` (
  `source` varchar(50) NOT NULL,
  `destination` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Nodes_Metrics_Athos`
--

CREATE TABLE IF NOT EXISTS `Nodes_Metrics_Athos` (
  `id_Clusters` varchar(50) NOT NULL,
  `id_Node` varchar(11) NOT NULL,
  `NUM_week` int(11) NOT NULL,
  `MaxVMSize` bigint(32) DEFAULT '0',
  `MaxRSS` bigint(32) DEFAULT '0',
  `MaxPages` bigint(32) DEFAULT '0',
  `MinCPU` bigint(32) DEFAULT '0',
  `MaxDiskRead` bigint(32) DEFAULT '0',
  `MaxDiskWrite` bigint(32) DEFAULT '0',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Nodes_Metrics_Eole`
--

CREATE TABLE IF NOT EXISTS `Nodes_Metrics_Eole` (
  `id_Clusters` varchar(50) NOT NULL,
  `id_Node` varchar(11) NOT NULL,
  `NUM_week` int(11) NOT NULL,
  `MaxVMSize` bigint(32) DEFAULT '0',
  `MaxRSS` bigint(32) DEFAULT '0',
  `MaxPages` bigint(32) DEFAULT '0',
  `MinCPU` bigint(32) DEFAULT '0',
  `MaxDiskRead` bigint(32) DEFAULT '0',
  `MaxDiskWrite` bigint(32) DEFAULT '0',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Nodes_Metrics_Porthos`
--

CREATE TABLE IF NOT EXISTS `Nodes_Metrics_Porthos` (
  `id_Clusters` varchar(50) NOT NULL,
  `id_Node` varchar(11) NOT NULL,
  `NUM_week` int(11) NOT NULL,
  `MaxVMSize` bigint(32) DEFAULT '0',
  `MaxRSS` bigint(32) DEFAULT '0',
  `MaxPages` bigint(32) DEFAULT '0',
  `MinCPU` bigint(32) DEFAULT '0',
  `MaxDiskRead` bigint(32) DEFAULT '0',
  `MaxDiskWrite` bigint(32) DEFAULT '0',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Noeuds`
--

CREATE TABLE IF NOT EXISTS `Noeuds` (
  `idNoeuds` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `id_Switch` varchar(50) DEFAULT NULL,
  `RealMemory` int(11) DEFAULT NULL,
  `FrequencyMemory` int(11) DEFAULT NULL,
  `Sockets` int(11) DEFAULT NULL,
  `CoresPerSocket` int(11) DEFAULT NULL,
  `ProductSerial` varchar(60) DEFAULT NULL,
  `ProductPartNumber` varchar(60) DEFAULT NULL,
  `ProductName` varchar(60) DEFAULT NULL,
  `Typecpu` varchar(256) NOT NULL,
  `TypeNode` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Partitions`
--

CREATE TABLE IF NOT EXISTS `Partitions` (
  `idPartitions` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `DefaultTime` varchar(45) DEFAULT NULL,
  `DefMemPerCPU` int(11) DEFAULT NULL,
  `Shared` varchar(10) NOT NULL,
  `isDefault` varchar(10) NOT NULL,
  `State` varchar(10) NOT NULL,
  `Hidden` varchar(10) NOT NULL,
  `AllowGroups` varchar(100) DEFAULT NULL,
  `Nodes` varchar(200) NOT NULL,
  `TotalNodes` varchar(45) NOT NULL,
  `TotalCPUs` varchar(45) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `AllowAccounts` varchar(45) DEFAULT NULL,
  `AllowQos` varchar(45) DEFAULT NULL,
  `AllocNodes` varchar(45) DEFAULT NULL,
  `QoS` varchar(45) DEFAULT NULL,
  `DisableRootJobs` varchar(45) DEFAULT NULL,
  `ExclusiveUser` varchar(45) DEFAULT NULL,
  `GraceTime` varchar(45) DEFAULT NULL,
  `PriorityJobFactor` varchar(45) DEFAULT NULL,
  `PriorityTier` varchar(45) DEFAULT NULL,
  `RootOnly` varchar(45) DEFAULT NULL,
  `ReqResv` varchar(45) DEFAULT NULL,
  `OverSubscribe` varchar(45) DEFAULT NULL,
  `OverTimeLimit` varchar(45) DEFAULT NULL,
  `PreemptMode` varchar(45) DEFAULT NULL,
  `SelectTypeParameters` varchar(45) DEFAULT NULL,
  `DefMemPerNode` varchar(45) DEFAULT NULL,
  `MaxMemPerNode` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `QOS`
--

CREATE TABLE IF NOT EXISTS `QOS` (
  `idQOS` varchar(100) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `Flags` int(30) DEFAULT NULL,
  `GraceTime` int(30) DEFAULT NULL,
  `GrpTRESMins` varchar(50) DEFAULT NULL,
  `GrpTRESRunMins` varchar(50) DEFAULT NULL,
  `GrpTRES` varchar(50) DEFAULT NULL,
  `GrpJobs` varchar(50) DEFAULT NULL,
  `GrpSubmitJobs` varchar(50) DEFAULT NULL,
  `GrpWall` varchar(50) DEFAULT NULL,
  `ID` int(30) DEFAULT NULL,
  `MaxTRESMins` varchar(50) DEFAULT NULL,
  `MaxTRESPerAccount` varchar(50) DEFAULT NULL,
  `MaxTRESPerJob` varchar(50) DEFAULT NULL,
  `MaxTRESPerNode` varchar(50) DEFAULT NULL,
  `MaxTRESPerUser` varchar(50) DEFAULT NULL,
  `MaxJobsPerAccount` varchar(50) DEFAULT NULL,
  `MaxJobsPerUser` varchar(50) DEFAULT NULL,
  `MaxCPUsPerJob` varchar(50) DEFAULT NULL,
  `MaxNodesPerJob` varchar(50) DEFAULT NULL,
  `MinTRESPerJob` varchar(50) DEFAULT NULL,
  `MaxSubmitJobsPerAccount` varchar(50) DEFAULT NULL,
  `MaxSubmitJobsPerUser` varchar(50) DEFAULT NULL,
  `MaxWall` varchar(50) DEFAULT NULL,
  `Preempt` varchar(50) DEFAULT NULL,
  `PreemptMode` varchar(50) DEFAULT NULL,
  `Priority` int(30) DEFAULT NULL,
  `UsageFactor` varchar(50) DEFAULT NULL,
  `UsageThreshold` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Reservations`
--

CREATE TABLE IF NOT EXISTS `Reservations` (
  `ReservationName` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `StartTime` varchar(45) NOT NULL,
  `EndTime` varchar(45) NOT NULL,
  `Duration` varchar(45) NOT NULL,
  `Nodes` varchar(150) NOT NULL,
  `NodeCnt` int(11) NOT NULL,
  `CoreCnt` int(11) NOT NULL,
  `Users` varchar(150) NOT NULL,
  `State` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Switch`
--

CREATE TABLE IF NOT EXISTS `Switch` (
  `idSwitch` varchar(50) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL,
  `level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `idUsers` varchar(45) NOT NULL,
  `id_Clusters` varchar(45) NOT NULL,
  `uid` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `home` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `employetype` varchar(50) DEFAULT NULL,
  `grp_principale` varchar(50) DEFAULT NULL,
  `grp_secondary` longtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `WCkeys`
--

CREATE TABLE IF NOT EXISTS `WCkeys` (
  `idWCkeys` varchar(100) NOT NULL,
  `id_Clusters` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `Auth`
--
ALTER TABLE `Auth`
 ADD PRIMARY KEY (`idLogin`);

--
-- Index pour la table `Clusters`
--
ALTER TABLE `Clusters`
 ADD PRIMARY KEY (`idClusters`);

--
-- Index pour la table `Collect_Clusters`
--
ALTER TABLE `Collect_Clusters`
 ADD KEY `fk_Collect_Clusters_1` (`id_Clusters`);

--
-- Index pour la table `Collect_Clusters_History`
--
ALTER TABLE `Collect_Clusters_History`
 ADD UNIQUE KEY `fk_CC_History` (`id_Clusters`,`NUM_week`);

--
-- Index pour la table `Collect_Frontaux`
--
ALTER TABLE `Collect_Frontaux`
 ADD KEY `fk_Collect_Frontaux_1` (`id_Clusters`,`id_Frontaux`);

--
-- Index pour la table `Collect_FS`
--
ALTER TABLE `Collect_FS`
 ADD KEY `fk_Collect_FS_1` (`id_Filesystems`,`id_Clusters`);

--
-- Index pour la table `Collect_FS_History`
--
ALTER TABLE `Collect_FS_History`
 ADD UNIQUE KEY `fk_CFS_History` (`id_Filesystems`,`id_Clusters`,`NUM_week`);

--
-- Index pour la table `Collect_Jobs`
--
ALTER TABLE `Collect_Jobs`
 ADD PRIMARY KEY (`id_Clusters`,`Jobid`);

--
-- Index pour la table `Collect_nodes`
--
ALTER TABLE `Collect_nodes`
 ADD KEY `fk_Collect_nodes_1` (`id_Clusters`);

--
-- Index pour la table `Collect_nodes_History`
--
ALTER TABLE `Collect_nodes_History`
 ADD UNIQUE KEY `fk_CN_History` (`id_Clusters`,`NUM_week`);

--
-- Index pour la table `Collect_partitions_History`
--
ALTER TABLE `Collect_partitions_History`
 ADD UNIQUE KEY `fk_CP_History` (`id_Partitions`,`id_Clusters`,`NUM_week`);

--
-- Index pour la table `Collect_Quota`
--
ALTER TABLE `Collect_Quota`
 ADD KEY `fk_Collect_Quota_2` (`id_Filesystems`,`id_Clusters`);

--
-- Index pour la table `Collect_rapport`
--
ALTER TABLE `Collect_rapport`
 ADD KEY `fk_Collect_rapport_1` (`id_Clusters`);

--
-- Index pour la table `Config`
--
ALTER TABLE `Config`
 ADD PRIMARY KEY (`idconfig`);

--
-- Index pour la table `Filesystems`
--
ALTER TABLE `Filesystems`
 ADD PRIMARY KEY (`idFilesystems`,`id_Clusters`), ADD KEY `fk_Filesystems_1` (`id_Clusters`);

--
-- Index pour la table `Frontaux`
--
ALTER TABLE `Frontaux`
 ADD PRIMARY KEY (`idFrontaux`,`id_Clusters`), ADD KEY `fk_Frontaux_1` (`id_Clusters`);

--
-- Index pour la table `Jobs_History_Athos`
--
ALTER TABLE `Jobs_History_Athos`
 ADD UNIQUE KEY `fk_jh_cluster` (`idJobs`,`id_Clusters`);

--
-- Index pour la table `Jobs_History_Eole`
--
ALTER TABLE `Jobs_History_Eole`
 ADD UNIQUE KEY `fk_jh_cluster` (`idJobs`,`id_Clusters`);

--
-- Index pour la table `Jobs_History_Porthos`
--
ALTER TABLE `Jobs_History_Porthos`
 ADD UNIQUE KEY `fk_jh_cluster` (`idJobs`,`id_Clusters`);

--
-- Index pour la table `Jobs_Metrics`
--
ALTER TABLE `Jobs_Metrics`
 ADD UNIQUE KEY `fk_JM` (`idJobs`,`id_Clusters`);

--
-- Index pour la table `Jobs_Metricsdet_Athos`
--
ALTER TABLE `Jobs_Metricsdet_Athos`
 ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Jobs_Metricsdet_Porthos`
--
ALTER TABLE `Jobs_Metricsdet_Porthos`
 ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Liens`
--
ALTER TABLE `Liens`
 ADD PRIMARY KEY (`source`,`destination`,`id_Clusters`), ADD KEY `fk_Liens_3` (`id_Clusters`), ADD KEY `fk_Liens_2` (`destination`,`id_Clusters`), ADD KEY `fk_Liens_1` (`source`,`id_Clusters`);

--
-- Index pour la table `Nodes_Metrics_Athos`
--
ALTER TABLE `Nodes_Metrics_Athos`
 ADD UNIQUE KEY `fk_Collect_nodes_1` (`id_Clusters`,`id_Node`,`NUM_week`);

--
-- Index pour la table `Nodes_Metrics_Porthos`
--
ALTER TABLE `Nodes_Metrics_Porthos`
 ADD UNIQUE KEY `fk_Collect_nodes_1` (`id_Clusters`,`id_Node`,`NUM_week`);

--
-- Index pour la table `Noeuds`
--
ALTER TABLE `Noeuds`
 ADD PRIMARY KEY (`idNoeuds`,`id_Clusters`), ADD KEY `fk_Noeuds_1` (`id_Clusters`), ADD KEY `fk_Noeuds_2` (`id_Switch`,`id_Clusters`);

--
-- Index pour la table `Partitions`
--
ALTER TABLE `Partitions`
 ADD PRIMARY KEY (`idPartitions`,`id_Clusters`);

--
-- Index pour la table `Reservations`
--
ALTER TABLE `Reservations`
 ADD KEY `fk_Reservations_1` (`id_Clusters`);

--
-- Index pour la table `Switch`
--
ALTER TABLE `Switch`
 ADD PRIMARY KEY (`idSwitch`,`id_Clusters`), ADD KEY `fk_Switch_1` (`id_Clusters`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
 ADD PRIMARY KEY (`idUsers`,`id_Clusters`), ADD KEY `fk_Users_1` (`id_Clusters`);

--
-- Index pour la table `WCkeys`
--
ALTER TABLE `WCkeys`
 ADD PRIMARY KEY (`idWCkeys`,`id_Clusters`), ADD KEY `fk_WCkeys_1` (`id_Clusters`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `Auth`
--
ALTER TABLE `Auth`
MODIFY `idLogin` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `Config`
--
ALTER TABLE `Config`
MODIFY `idconfig` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `Jobs_Metricsdet_Athos`
--
ALTER TABLE `Jobs_Metricsdet_Athos`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=265;
--
-- AUTO_INCREMENT pour la table `Jobs_Metricsdet_Porthos`
--
ALTER TABLE `Jobs_Metricsdet_Porthos`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=155599590;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `Collect_Clusters`
--
ALTER TABLE `Collect_Clusters`
ADD CONSTRAINT `fk_Collect_Clusters_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Collect_Frontaux`
--
ALTER TABLE `Collect_Frontaux`
ADD CONSTRAINT `fk_Collect_Frontaux_1` FOREIGN KEY (`id_Clusters`, `id_Frontaux`) REFERENCES `Frontaux` (`id_Clusters`, `idFrontaux`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Collect_FS`
--
ALTER TABLE `Collect_FS`
ADD CONSTRAINT `fk_Collect_FS_1` FOREIGN KEY (`id_Filesystems`, `id_Clusters`) REFERENCES `Filesystems` (`idFilesystems`, `id_Clusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Collect_nodes`
--
ALTER TABLE `Collect_nodes`
ADD CONSTRAINT `fk_Collect_nodes_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Collect_Quota`
--
ALTER TABLE `Collect_Quota`
ADD CONSTRAINT `fk_Collect_Quota_2` FOREIGN KEY (`id_Filesystems`, `id_Clusters`) REFERENCES `Filesystems` (`idFilesystems`, `id_Clusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Collect_rapport`
--
ALTER TABLE `Collect_rapport`
ADD CONSTRAINT `fk_Collect_rapport_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Filesystems`
--
ALTER TABLE `Filesystems`
ADD CONSTRAINT `fk_Filesystems_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Frontaux`
--
ALTER TABLE `Frontaux`
ADD CONSTRAINT `fk_Frontaux_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Liens`
--
ALTER TABLE `Liens`
ADD CONSTRAINT `fk_Liens_1` FOREIGN KEY (`source`, `id_Clusters`) REFERENCES `Switch` (`idSwitch`, `id_Clusters`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_Liens_2` FOREIGN KEY (`destination`, `id_Clusters`) REFERENCES `Switch` (`idSwitch`, `id_Clusters`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_Liens_3` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Noeuds`
--
ALTER TABLE `Noeuds`
ADD CONSTRAINT `fk_Noeuds_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_Noeuds_2` FOREIGN KEY (`id_Switch`, `id_Clusters`) REFERENCES `Switch` (`idSwitch`, `id_Clusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Reservations`
--
ALTER TABLE `Reservations`
ADD CONSTRAINT `fk_Reservations_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Switch`
--
ALTER TABLE `Switch`
ADD CONSTRAINT `fk_Switch_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `Users`
--
ALTER TABLE `Users`
ADD CONSTRAINT `fk_Users_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `WCkeys`
--
ALTER TABLE `WCkeys`
ADD CONSTRAINT `fk_WCkeys_1` FOREIGN KEY (`id_Clusters`) REFERENCES `Clusters` (`idClusters`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
