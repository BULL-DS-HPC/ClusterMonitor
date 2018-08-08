<?php
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {
require('../../include/config.php'); 

$mysqli = new mysqli($hostmysql, $loginmysql, $passmysql, $dbmysql);
$mysqli->set_charset("utf8");
if ($mysqli->connect_error) {
        die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
}

$cluster=$_POST['Cluster'];

$sqlinsert= "insert into Clusters (idClusters, SlurmVersion, is_active) values ('".$cluster."', 'version', 1)";
$mysqli->query($sqlinsert) or die ('Erreur '.$sqlinsert.' '.$mysqli->error);

$sqlcreatedb1 = "CREATE TABLE IF NOT EXISTS Jobs_History_$cluster (
  idJobs varchar(256) NOT NULL,
  id_Clusters varchar(50) NOT NULL,
  JobName varchar(50) NOT NULL,
  UserId varchar(50) NOT NULL,
  GroupId varchar(50) NOT NULL,
  Priority int(11) NOT NULL,
  Nice int(11) NOT NULL,
  Account varchar(50) NOT NULL,
  QOS varchar(50) NOT NULL,
  WCKey varchar(50) NOT NULL,
  JobState varchar(50) NOT NULL,
  Requeue int(11) NOT NULL,
  Restarts int(11) NOT NULL,
  BatchFlag int(11) NOT NULL,
  Reboot int(11) NOT NULL,
  ExitCode varchar(50) NOT NULL,
  DerivedExitCode varchar(50) NOT NULL,
  RunTime varchar(50) NOT NULL,
  TimeLimit varchar(50) NOT NULL,
  TimeMin varchar(50) NOT NULL,
  SubmitTime varchar(50) NOT NULL,
  EligibleTime varchar(50) NOT NULL,
  StartTime varchar(50) NOT NULL,
  EndTime varchar(50) NOT NULL,
  PreemptTime varchar(50) NOT NULL,
  SuspendTime varchar(50) NOT NULL,
  SecsPreSuspend varchar(50) NOT NULL,
  Partitions varchar(50) NOT NULL,
  AllocNodeSid varchar(50) NOT NULL,
  ReqNodeList varchar(50) NOT NULL,
  ExcNodeList varchar(50) NOT NULL,
  NodeList varchar(50) NOT NULL,
  BatchHost varchar(50) NOT NULL,
  NumNodes int(11) NOT NULL,
  NumCPUs int(11) NOT NULL,
  CPUsTask int(11) NOT NULL,
  ReqBSCT varchar(50) NOT NULL,
  SocksNode varchar(50) NOT NULL,
  NtasksPerNBSC varchar(50) NOT NULL,
  CoreSpec varchar(50) NOT NULL,
  Nodes varchar(50) NOT NULL,
  CPUIDs varchar(50) NOT NULL,
  Mem varchar(50) NOT NULL,
  MinCPUsNode varchar(50) NOT NULL,
  MinMemoryCPU varchar(50) NOT NULL,
  MinTmpDiskNode varchar(50) NOT NULL,
  Features varchar(50) NOT NULL,
  Gres varchar(50) NOT NULL,
  Reservation varchar(50) NOT NULL,
  Shared varchar(50) NOT NULL,
  Contiguous varchar(50) NOT NULL,
  Licenses varchar(50) NOT NULL,
  Network varchar(50) NOT NULL,
  Command varchar(50) NOT NULL,
  WorkDir varchar(50) NOT NULL,
  StdErr varchar(50) NOT NULL,
  StdIn varchar(50) NOT NULL,
  StdOut varchar(50) NOT NULL,
  MaxVMSize varchar(50) NOT NULL,
  MaxVMSizeTask varchar(50) NOT NULL,
  AveVMSize varchar(50) NOT NULL,
  MaxRSS varchar(50) NOT NULL,
  MaxRSSTask varchar(50) NOT NULL,
  AveRSS varchar(50) NOT NULL,
  MaxPages varchar(50) NOT NULL,
  MaxPagesTask varchar(50) NOT NULL,
  AvePages varchar(50) NOT NULL,
  MinCPU varchar(50) NOT NULL,
  MinCPUTask varchar(50) NOT NULL,
  AveCPU varchar(50) NOT NULL,
  NTasks varchar(50) NOT NULL,
  AveCPUFreq varchar(50) NOT NULL,
  ReqCPUFreq varchar(50) NOT NULL,
  ConsumedEnergy varchar(50) NOT NULL,
  MaxDiskRead varchar(50) NOT NULL,
  MaxDiskReadTask varchar(50) NOT NULL,
  AveDiskRead varchar(50) NOT NULL,
  MaxDiskWrite varchar(50) NOT NULL,
  MaxDiskWriteTask varchar(50) NOT NULL,
  AveDiskWrite varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$sqlcreatedb2 = "CREATE TABLE IF NOT EXISTS Jobs_Metricsdet_$cluster (
  id int(11) NOT NULL,
  idJobs varchar(256) NOT NULL,
  datetime datetime NOT NULL,
  stepid varchar(100) NOT NULL,
  MaxVMSize varchar(50) NOT NULL,
  MaxVMSizeNode varchar(50) NOT NULL,
  MaxVMSizeTask varchar(50) NOT NULL,
  AveVMSize varchar(50) NOT NULL,
  MaxRSS varchar(50) NOT NULL,
  MaxRSSNode varchar(50) NOT NULL,
  MaxRSSTask varchar(50) NOT NULL,
  AveRSS varchar(50) NOT NULL,
  MaxPages varchar(50) NOT NULL,
  MaxPagesNode varchar(50) NOT NULL,
  MaxPagesTask varchar(50) NOT NULL,
  AvePages varchar(50) NOT NULL,
  MinCPU varchar(50) NOT NULL,
  MinCPUNode varchar(50) NOT NULL,
  MinCPUTask varchar(50) NOT NULL,
  AveCPU varchar(50) NOT NULL,
  NTasks varchar(50) NOT NULL,
  AveCPUFreq varchar(50) NOT NULL,
  ReqCPUFreq varchar(50) NOT NULL,
  ConsumedEnergy varchar(50) NOT NULL,
  MaxDiskRead varchar(50) NOT NULL,
  MaxDiskReadNode varchar(50) NOT NULL,
  MaxDiskReadTask varchar(50) NOT NULL,
  AveDiskRead varchar(50) NOT NULL,
  MaxDiskWrite varchar(50) NOT NULL,
  MaxDiskWriteNode varchar(50) NOT NULL,
  MaxDiskWriteTask varchar(50) NOT NULL,
  AveDiskWrite varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=574404 DEFAULT CHARSET=latin1;";

$sqlcreatedb3 = "CREATE TABLE IF NOT EXISTS Nodes_Metrics_$cluster (
  id_Clusters varchar(50) NOT NULL,
  id_Node varchar(11) NOT NULL,
  NUM_week int(11) NOT NULL,
  MaxVMSize bigint(32) DEFAULT '0',
  MaxRSS bigint(32) DEFAULT '0',
  MaxPages bigint(32) DEFAULT '0',
  MinCPU bigint(32) DEFAULT '0',
  MaxDiskRead bigint(32) DEFAULT '0',
  MaxDiskWrite bigint(32) DEFAULT '0',
  Timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$sqlcreatedb4 = "ALTER TABLE Jobs_History_$cluster
 ADD UNIQUE KEY fk_jh_cluster (idJobs,id_Clusters);";
$sqlcreatedb5 = "ALTER TABLE Jobs_Metricsdet_$cluster
 ADD PRIMARYKEY (id);";
$sqlcreatedb6 = "ALTER TABLE Nodes_Metrics_$cluster
 ADD UNIQUE KEY fk_Collect_nodes_1 (id_Clusters,id_Node,NUM_week);";

$mysqli->query($sqlcreatedb1) or die ('Erreur '.$sqlcreatedb1.' '.$mysqli->error);
$mysqli->query($sqlcreatedb2) or die ('Erreur '.$sqlcreatedb2.' '.$mysqli->error);
$mysqli->query($sqlcreatedb3) or die ('Erreur '.$sqlcreatedb3.' '.$mysqli->error);
$mysqli->query($sqlcreatedb4) or die ('Erreur '.$sqlcreatedb4.' '.$mysqli->error);
$mysqli->query($sqlcreatedb5) or die ('Erreur '.$sqlcreatedb5.' '.$mysqli->error);
$mysqli->query($sqlcreatedb6) or die ('Erreur '.$sqlcreatedb6.' '.$mysqli->error);

} else {
	header('Location: ../../../index.php');
}
?>
