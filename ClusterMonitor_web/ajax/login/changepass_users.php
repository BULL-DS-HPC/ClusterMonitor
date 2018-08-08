<?php


/*--------------------------------------------------------------------------------------
		Function to check if the request is an AJAX request
----------------------------------------------------------------------------------------*/


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
session_start();

$Login = $_SESSION['Login'];

if ($_SESSION['authentification'] == "1"){ 

	$password_string = mysqli_real_escape_string($_POST["Mdp"]);
  	$password_hash = password_hash($password_string, PASSWORD_BCRYPT);
	$sqlupauthmdp = "update Auth set Mdp = '$password_hash' where Login = '$Login'";
	$reqsqlupauthmdp = $mysqli->query($sqlupauthmdp) or die ('Erreur '.$sqlupauthmdp.' '.$mysqli->error);
	echo json_encode(["data"=>"ok"]);
        exit;
}

echo json_encode(["data"=>"erreur"]);
exit;

} else {
	header('Location: ../../../index.php');
}
?>
