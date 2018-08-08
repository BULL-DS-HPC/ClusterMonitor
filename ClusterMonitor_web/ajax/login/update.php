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

$sqldel= "update Clusters set is_active = 1 where idClusters='".$_POST['Cluster']."'";
$mysqli->query($sqldel) or die ('Erreur '.$sqldel.' '.$mysqli->error);
} else {
	header('Location: ../../../index.php');
}
?>
