<?php

// Cron à executer toutes les jours toutes les deux heures + à 23h55
// 00 */2 * * * php5 /var/www/cluster-monitor/cron/clean_dbd.php 1>/dev/null 2>&1


require('/var/www/cluster-monitor/include/config.php');

$dernier_lundi = date("Y-m-j 00:00:00", time() - ( date("N") -1) *86400 );
$joursemaine = date ('N');
$date_jour = date("Y-m-d H:i:s");
$num_semaine = date ('YW');
$yesterday = date('Y-m-d',strtotime("-1 days"));

$clustername = $argv[1];
$callfunction = $argv[2];

$sqlparams = "select * from Config";
$reqsqlparams = $mysqli->query($sqlparams) or tracage ('Erreur '.$sqlparams.' '.$mysqli->error);
while ($params = $reqsqlparams->fetch_row()) {

	$historyretentionClusters = $params[3];
	$historyretentionFrontaux = $params[4];
	$historyretentionFS = $params[5];
	$historyretentionnodes = $params[6];
	$historyretentionpartitions = $params[7];
	$historyretentionJobsMetrics = $params[8]; // Doit être superieur à 1 jours
	$historyretentionJobsMetricsp = $params[8]; // Doit être superieur à 1 jours
	echo $historyretentionJobsMetricsp;
}

function statscollectcluster($clustername) {

	global $mysqli, $dernier_lundi, $joursemaine, $date_jour, $num_semaine, $yesterday, $historyretentionClusters;
	/* Stats AVG Collect_Clusters par semaine */

	$sqlAvgCollectClusters = "REPLACE INTO Collect_Clusters_History (id_Clusters, NUM_week, AVG_CPU_allocated, AVG_CPU_idle, AVG_CPU_other, CPU_total ) ( SELECT '$clustername', '$num_semaine', avg(CPU_allocated) as AVG_CPU_allocated, avg(CPU_idle) as AVG_CPU_idle, avg(CPU_other) as AVG_CPU_other, max(CPU_total) as CPU_total from Collect_Clusters where id_Clusters='$clustername' and Timestamp BETWEEN '$dernier_lundi' AND '$date_jour')";

	tracagecmd ('------------------------------ Stats AVG Collect_Clusters par semaine ------------------------------------------------------------ ');
	tracagecmd ($sqlAvgCollectClusters);
	tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqlAvgCollectClusters = $mysqli->query($sqlAvgCollectClusters) or tracage ('Erreur '.$sqlAvgCollectClusters.' '.$mysqli->error);

	/* Clean Collect_Clusters + n jours */

	$sqlCleanCollectClusters = "DELETE FROM Collect_Clusters WHERE id_Clusters='$clustername' AND TO_DAYS(now() ) - TO_DAYS(Timestamp) > $historyretentionClusters ";
	tracagecmd ('------------------------------ Clean Collect_Clusters ---------------------------------------------------------------------------- ');
	tracagecmd ($sqlCleanCollectClusters);
	tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqlCleanCollectClusters = $mysqli->query($sqlCleanCollectClusters) or tracage ('Erreur '.$sqlCleanCollectClusters.' '.$mysqli->error);
}
	function statscollectfrt($clustername) {

	global $mysqli, $dernier_lundi, $joursemaine, $date_jour, $num_semaine, $yesterday, $historyretentionFrontaux;
	/* Clean Collect_Frontaux + n jours */

	$sqlCleanCollectFrontaux = "DELETE FROM Collect_Frontaux WHERE id_Clusters='$clustername' AND TO_DAYS(now() ) - TO_DAYS(Timestamp) > $historyretentionFrontaux ";
	tracagecmd ('------------------------------ Clean Collect_Frontaux ---------------------------------------------------------------------------- ');
	tracagecmd ($sqlCleanCollectFrontaux);
	tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');


	$reqsqlCleanCollectFrontaux = $mysqli->query($sqlCleanCollectFrontaux) or tracage ('Erreur '.$sqlCleanCollectFrontaux.' '.$mysqli->error);

}

function statscollectfs($clustername) {

	global $mysqli, $dernier_lundi, $joursemaine, $date_jour, $num_semaine, $yesterday, $historyretentionFS;
	/* Stats MAX Collect_FS par semaine */

	$sqlfs="SELECT DISTINCT(id_Filesystems) from Collect_FS where id_Clusters = '$clustername'";

	tracagecmd ('------------------------------ Stats MAX Collect_FS---------------------------------------------------------------------------- ');
	tracagecmd ($sqlfs);
	tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqlfs = $mysqli->query($sqlfs) or tracage ('Erreur '.$sqlfs.' '.$mysqli->error);
	while ($fs = $reqsqlfs->fetch_row()) 
	{
		$id_Filesystems = $fs[0];
		$sqlArchCollectClusters = "REPLACE INTO Collect_FS_History (id_Filesystems, id_Clusters, NUM_week, MAX_disponible, MAX_utilise, MAX_disponible_inode, MAX_utilise_inode ) ( SELECT '$id_Filesystems','$clustername', '$num_semaine', MAX(disponible), MAX(utilise), MAX(disponible_inode), MAX(utilise_inode) from Collect_FS where id_Clusters='$clustername' and Timestamp BETWEEN '$dernier_lundi' AND '$date_jour')";

		tracagecmd ('------------------------------Stats MAX Collect_FS by FS ---------------------------------------------------------------------------- ');
		tracagecmd ($sqlArchCollectClusters);
	        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');
		
		$reqsqlArchCollectClusters = $mysqli->query($sqlArchCollectClusters) or tracage ('Erreur '.$sqlArchCollectClusters.' '.$mysqli->error);

	}

	/* Clean Collect_FS + n jours */

	$sqlCleanCollectFS = "DELETE FROM Collect_FS WHERE id_Clusters='$clustername' AND TO_DAYS(now() ) - TO_DAYS(Timestamp) > $historyretentionFS ";
	
	tracagecmd ('------------------------------ Clean Collect_FS ---------------------------------------------------------------------------- ');
        tracagecmd ($sqlCleanCollectFS);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');
	
	$reqsqlCleanCollectFS = $mysqli->query($sqlCleanCollectFS) or tracage ('Erreur '.$sqlCleanCollectFS.' '.$mysqli->error);

}

function statscollectnodes($clustername) {

	global $mysqli, $dernier_lundi, $joursemaine, $date_jour, $num_semaine, $yesterday, $historyretentionnodes;
	/* Stats MAX MIN AVG Collect_nodes par semaine */
	
	$sqlAvgCollectNodes = "REPLACE INTO Collect_nodes_History (id_Clusters, NUM_week, MAX_allocated, MIN_allocated, AVG_allocated, MAX_idle, MIN_idle, AVG_idle, MAX_other, MIN_other, AVG_other, MAX_total, MIN_total, AVG_total) ( SELECT '$clustername', '$num_semaine', MAX(allocated), MIN(allocated), AVG(allocated), MAX(idle), MIN(idle), AVG(idle), MAX(other), MIN(other), AVG(other), MAX(total), MIN(total), AVG(total) from Collect_nodes where id_Clusters='$clustername' and Timestamp BETWEEN '$dernier_lundi' AND '$date_jour')";

	tracagecmd ('------------------------------Stats MAX MIN AVG Collect_nodes par semaine---------------------------------------------------------------------------- ');
        tracagecmd ($sqlAvgCollectNodes);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqlAvgCollectNodes = $mysqli->query($sqlAvgCollectNodes) or tracage ('Erreur '.$sqlAvgCollectNodes.' '.$mysqli->error);

	/* Clean Collect_nodes + n jours */

	$sqlCleanCollectNodes = "DELETE FROM Collect_nodes WHERE id_Clusters='$clustername' AND TO_DAYS(now() ) - TO_DAYS(Timestamp) > $historyretentionnodes ";

	tracagecmd ('------------------------------ Clean Collect_nodes ---------------------------------------------------------------------------- ');
        tracagecmd ($sqlCleanCollectNodes);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqlCleanCollectNodes = $mysqli->query($sqlCleanCollectNodes) or tracage ('Erreur '.$sqlCleanCollectNodes.' '.$mysqli->error);

}

function statscollectpart($clustername) {

	global $mysqli, $dernier_lundi, $joursemaine, $date_jour, $num_semaine, $yesterday, $historyretentionpartitions;
	/* Stats MAX MIN AVG Collect_partitions par semaine */

	$sqlpartitions="SELECT DISTINCT(id_Partitions) from Collect_partitions where id_Clusters = '$clustername'";

	tracagecmd ('------------------------------Stats MAX MIN AVG Collect_partitions par semaine---------------------------------------------------------------------------- ');
	tracagecmd ($sqlpartitions);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqlpartitions = $mysqli->query($sqlpartitions) or tracage ('Erreur '.$sqlpartitions.' '.$mysqli->error);
	while ($partitions = $reqsqlpartitions->fetch_row()) 
	{
		$id_partitions = $partitions[0];
		$sqlArchpartitions = "REPLACE INTO Collect_partitions_History (id_Partitions, id_Clusters, NUM_week, MAX_Nombre_job_pd, MIN_Nombre_job_pd, AVG_Nombre_job_pd, MAX_CPU_allocated, MIN_CPU_allocated, AVG_CPU_allocated, MAX_CPU_idle, MIN_CPU_idle, AVG_CPU_idle, MAX_CPU_other, MIN_CPU_other, AVG_CPU_other, MAX_CPU_total, MIN_CPU_total, AVG_CPU_total) ( SELECT '$id_partitions', '$clustername', '$num_semaine', MAX(Nombre_job_pd), MIN(Nombre_job_pd), AVG(Nombre_job_pd), MAX(CPU_allocated), MIN(CPU_allocated), AVG(CPU_allocated), MAX(CPU_idle), MIN(CPU_idle), AVG(CPU_idle), MAX(CPU_other), MIN(CPU_other), AVG(CPU_other), MAX(CPU_total), MIN(CPU_total), AVG(CPU_total) from Collect_partitions where id_Clusters='$clustername' and Timestamp BETWEEN '$dernier_lundi' AND '$date_jour')";

		tracagecmd ('------------------------------Stats MAX MIN AVG Collect_partitions par semaine by part --------------------------------------------------- ');
	        tracagecmd ($sqlArchpartitions);
	        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

		$reqsqlArchpartitions = $mysqli->query($sqlArchpartitions) or tracage ('Erreur '.$sqlArchpartitions.' '.$mysqli->error);
	}

	/* Clean Collect_partitions + n jours */

	$sqlCleanCollectpartitions = "DELETE FROM Collect_partitions WHERE id_Clusters='$clustername' AND TO_DAYS(now() ) - TO_DAYS(Timestamp) > $historyretentionpartitions ";

	tracagecmd ('------------------------------  Clean Collect_partitions---------------------------------------------------------------------------------');
	tracagecmd ($sqlCleanCollectpartitions);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqlCleanCollectpartitions = $mysqli->query($sqlCleanCollectpartitions) or tracage ('Erreur '.$sqlCleanCollectpartitions.' '.$mysqli->error);

}

function statscollectMN($clustername) {

	global $mysqli, $dernier_lundi, $joursemaine, $date_jour, $num_semaine, $yesterday, $historyretentionJobsMetrics, $historyretentionJobsMetricsp;
	/* Stats MAX AVG Nodes_Metrics_<cluster> + n days */
	/* MaxDiskWriteNode & MaxDiskReadNode no active in cluster_conf */
	#$sqldisnodes = "SELECT distinct(MaxVMSizeNode) as nodes FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' UNION SELECT distinct(MaxRSSNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' UNION SELECT distinct(MaxPagesNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%'UNION SELECT distinct(MinCPUNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' UNION SELECT distinct(MaxDiskReadNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' UNION SELECT distinct(MaxDiskWriteNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' ORDER BY nodes"; 
	$sqldisnodes = "SELECT distinct(MaxVMSizeNode) as nodes FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' UNION SELECT distinct(MaxRSSNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' UNION SELECT distinct(MaxPagesNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%'UNION SELECT distinct(MinCPUNode) FROM Jobs_Metricsdet_$clustername WHERE datetime LIKE '$yesterday%' ORDER BY nodes";

	tracagecmd ('------------------------------Stats MAX AVG Nodes_Metrics distinct nodes ---------------------------------------------------------');
	tracagecmd ($sqldisnodes);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

	$reqsqldisnodes = $mysqli->query($sqldisnodes) or tracage ('Erreur '.$sqldisnodes.' '.$mysqli->error);
	while ($disnodes = $reqsqldisnodes->fetch_row())
        {
		$nodes = $disnodes[0];
		if ($nodes != '0' || !empty($nodes)){
                	$sqlnodes = "REPLACE INTO Nodes_Metrics_$clustername set id_Clusters = '$clustername',
				 id_Node = '$nodes',
				 NUM_week = '$num_semaine',
				 MaxVMSize = (SELECT COALESCE(MAX(MaxVMSize), 0) FROM Jobs_Metricsdet_$clustername WHERE MaxVMSizeNode = '$nodes' AND datetime LIKE '$yesterday%'),
				 MaxRSS = (SELECT COALESCE(MAX(MaxRSS), 0) FROM Jobs_Metricsdet_$clustername WHERE MaxRSSNode = '$nodes' AND datetime LIKE '$yesterday%'),
				 MaxPages = (SELECT COALESCE(MAX(MaxPages), 0) FROM Jobs_Metricsdet_$clustername WHERE MaxPagesNode = '$nodes' AND datetime LIKE '$yesterday%'),
				 MinCPU = (SELECT COALESCE(MAX(MinCPU), 0) FROM Jobs_Metricsdet_$clustername WHERE MinCPUNode = '$nodes' AND datetime LIKE '$yesterday%'),
				 MaxDiskRead = (SELECT COALESCE(MAX(MaxDiskRead), 0) FROM Jobs_Metricsdet_$clustername WHERE MaxDiskReadNode = '$nodes' AND datetime LIKE '$yesterday%'),
				 MaxDiskWrite = (SELECT COALESCE(MAX(MaxDiskWrite), 0) FROM Jobs_Metricsdet_$clustername WHERE MaxDiskWriteNode = '$nodes' AND datetime LIKE '$yesterday%')";

			tracagecmd ('------------------------------ Stats MAX AVG Nodes_Metrics by nodes -----------------------------------------------------------');
			tracagecmd ($sqlnodes);
		        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');

			$reqsqlnodes = $mysqli->query($sqlnodes) or tracage ('Erreur '.$sqlnodes.' '.$mysqli->error);
		}
	}

	/* Stats MAX AVG Jobs_Metricsdet_<cluster> par jobs + n days */

	$sqldisjobs="SELECT DISTINCT(idJobs) from Jobs_Metrics where id_Clusters = '$clustername' and TO_DAYS(now() ) - TO_DAYS(EndTime) > $historyretentionJobsMetrics";
	
	tracagecmd ('------------------------------ Stats MAX AVG Jobs_Metricsdet distinct jobs ---------------------------------------------------------');
        tracagecmd ($sqldisjobs);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');
	
	$reqsqldisjobs = $mysqli->query($sqldisjobs) or tracage ('Erreur '.$sqldisjobs.' '.$mysqli->error);
	while ($disjobs = $reqsqldisjobs->fetch_row()) 
	{
		$idjobs = $disjobs[0];
		$sqlArchMetrics1 = "REPLACE INTO Jobs_History_$clustername (idJobs, id_Clusters, JobName, UserId, GroupId, Priority, Nice, Account, QOS, WCKey, JobState, Requeue, Restarts, BatchFlag, Reboot, ExitCode, DerivedExitCode, RunTime, TimeLimit, TimeMin, SubmitTime, EligibleTime, StartTime, EndTime, PreemptTime, SuspendTime, SecsPreSuspend, Partition, AllocNodeSid, ReqNodeList, ExcNodeList, NodeList, BatchHost, NumNodes, NumCPUs, CPUsTask, ReqBSCT, SocksNode, NtasksPerNBSC, CoreSpec, Nodes, CPUIDs, Mem, MinCPUsNode, MinMemoryCPU, MinTmpDiskNode, Features, Gres, Reservation, Shared, Contiguous, Licenses, Network, Command, WorkDir, StdErr, StdIn, StdOut) ( SELECT idJobs, id_Clusters, JobName, UserId, GroupId, Priority, Nice, Account, QOS, WCKey, JobState, Requeue, Restarts, BatchFlag, Reboot, ExitCode, DerivedExitCode, RunTime, TimeLimit, TimeMin, SubmitTime, EligibleTime, StartTime, EndTime, PreemptTime, SuspendTime, SecsPreSuspend, Partition, AllocNodeSid, ReqNodeList, ExcNodeList, NodeList, BatchHost, NumNodes, NumCPUs, CPUsTask, ReqBSCT, SocksNode, NtasksPerNBSC, CoreSpec, Nodes, CPUIDs, Mem, MinCPUsNode, MinMemoryCPU, MinTmpDiskNode, Features, Gres, Reservation, Shared, Contiguous, Licenses, Network, Command, WorkDir, StdErr, StdIn, StdOut from Jobs_Metrics where id_Clusters='$clustername' and idJobs = '$idjobs')";
		$sqlArchMetrics2 = "REPLACE INTO Jobs_History_$clustername (MaxVMSize, MaxVMSizeTask, AveVMSize, MaxRSS, MaxRSSTask, AveRSS, MaxPages, MaxPagesTask, AvePages, MinCPU, MinCPUTask, AveCPU, NTasks, AveCPUFreq, ReqCPUFreq, ConsumedEnergy, MaxDiskRead, MaxDiskReadTask, AveDiskRead, MaxDiskWrite, MaxDiskWriteTask, AveDiskWrite) ( SELECT MAX(MaxVMSize), MAX(MaxVMSizeTask), MAX(AveVMSize), MAX(MaxRSS), MAX(MaxRSSTask), MAX(AveRSS), MAX(MaxPages), MAX(MaxPagesTask), MAX(AvePages), MAX(MinCPU), MAX(MinCPUTask), MAX(AveCPU), MAX(NTasks), MAX(AveCPUFreq), MAX(ReqCPUFreq), MAX(ConsumedEnergy), MAX(MaxDiskRead), MAX(MaxDiskReadTask), MAX(AveDiskRead), MAX(MaxDiskWrite), MAX(MaxDiskWriteTask), MAX(AveDiskWrite) from Jobs_Metricsdet_$clustername where idJobs = '$idjobs')";
		
		tracagecmd ('------------------------------ Stats MAX AVG Jobs_Metricsdet distinct jobs ---------------------------------------------------------');
	        tracagecmd ($sqlArchMetrics1);
		tracagecmd ($sqlArchMetrics2);
	        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');
		
		$reqsqlArchMetrics1 = $mysqli->query($sqlArchMetrics1) or tracage ('Erreur '.$sqlArchMetrics1.' '.$mysqli->error);
		$reqsqlArchMetrics1 = $mysqli->query($sqlArchMetrics2) or tracage ('Erreur '.$sqlArchMetrics2.' '.$mysqli->error);
	}

	/* Clean Jobs_Metricsdet_<cluster> + n jours */

	$sqlCleanJobsMetricsdet = "DELETE FROM Jobs_Metricsdet_$clustername WHERE TO_DAYS(now() ) - TO_DAYS(datetime) > $historyretentionJobsMetricsp ";
	
	tracagecmd ('------------------------------ Clean Jobs_Metricsdet------------------------------------------------------');
	tracagecmd ($sqlCleanJobsMetricsdet);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');
	
	$reqsqlCleanJobsMetricsdet = $mysqli->query($sqlCleanJobsMetricsdet) or tracage ('Erreur '.$sqlCleanJobsMetricsdet.' '.$mysqli->error);
	$sqlCleanJobsMetrics = "DELETE FROM Jobs_Metrics WHERE TO_DAYS(now() ) - TO_DAYS(EndTime) > $historyretentionJobsMetricsp ";
	
	tracagecmd ('------------------------------ Clean Jobs_Metricsdet------------------------------------------------------');
        tracagecmd ($sqlCleanJobsMetrics);
        tracagecmd ('----------------------------------------------------------------------------------------------------------------------------------');
	
	$reqsqlCleanJobsMetrics = $mysqli->query($sqlCleanJobsMetrics) or tracage ('Erreur '.$sqlCleanJobsMetrics.' '.$mysqli->error);

}

# Exec function

$callfunction($clustername);

?>

