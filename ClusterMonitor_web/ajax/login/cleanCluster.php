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

$sqlcomd = "select concat('delete from ',TABLE_NAME,\" where id_Clusters = '".$_POST['Cluster']."';\") comd from information_schema.columns where TABLE_SCHEMA = 'cluster_monitor' AND COLUMN_NAME ='id_Clusters';";
$resultsqlcomd = $mysqli->query($sqlcomd) or die ('Erreur '.$sqlcomd.' '.$mysqli->error);

while ($row = $resultsqlcomd->fetch_array())
{
	$sqlreqrow = $row['0'];
	$mysqli->query($sqlreqrow) or die ('Erreur '.$sqlreqrow.' '.$mysqli->error);
	
}

$sqlcluster = "delete from Clusters where idClusters = '".$_POST['Cluster']."';";
$mysqli->query($sqlcluster) or die ('Erreur '.$sqlcluster.' '.$mysqli->error);

$sqlclusterdet1 = "drop table Jobs_History_".$_POST['Cluster'].";";
$sqlclusterdet2 = "drop table Jobs_Metricsdet_".$_POST['Cluster'].";";
$sqlclusterdet3 = "drop table Nodes_Metrics_".$_POST['Cluster'].";";

$mysqli->query($sqlclusterdet1) or die ('Erreur '.$sqlclusterdet1.' '.$mysqli->error);
$mysqli->query($sqlclusterdet2) or die ('Erreur '.$sqlclusterdet2.' '.$mysqli->error);
$mysqli->query($sqlclusterdet3) or die ('Erreur '.$sqlclusterdet3.' '.$mysqli->error);

} else {
	header('Location: ../../../index.php');
}
?>
