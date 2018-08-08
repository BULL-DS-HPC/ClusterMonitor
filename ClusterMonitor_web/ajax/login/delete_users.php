<?php
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {
require('../../include/config.php'); 

if (isset($_SESSION['authentification']) && isset($_SESSION['Groupe']) == "admin"){ 

	echo json_encode(["data"=>"not autorized"]);
        exit;
}

$mysqli = new mysqli($hostmysql, $loginmysql, $passmysql, $dbmysql);
$mysqli->set_charset("utf8");
if ($mysqli->connect_error) {
        die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
}
session_start();

	$idLogin = $_POST['idsuppr'];
	$sqldel = "DELETE FROM $dbauth WHERE idLogin='$idLogin'";
	$reqsqldel = $mysqli->query($sqldel) or die ('Erreur '.$sqldel.' '.$mysqli->error);
	echo json_encode(["data"=>"ok"]);
        exit;

} else {
	header('Location: ../../../index.php');
}
?>
