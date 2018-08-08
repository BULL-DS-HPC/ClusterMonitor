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

$sqlsetconfig= "UPDATE Config set langue = '".$_POST['langs']."', Ret_CC = '".$_POST['retcc']."', Ret_CFR = '".$_POST['retcfr']."', Ret_CFS = '".$_POST['retcfs']."', Ret_CN = '".$_POST['retcn']."', Ret_CP = '".$_POST['retcp']."', Ret_JM = '".$_POST['retjm']."'";

$mysqli->query($sqlsetconfig) or die ('Erreur '.$sqlsetconfig.' '.$mysqli->error);

echo json_encode(["data"=>"ok"]);
exit;

} else {
	header('Location: ../../../index.php');
}
?>
