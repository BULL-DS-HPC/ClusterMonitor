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

	$Login = mysqli_real_escape_string($_POST['Login']);
	$sqlcheckuser = "SELECT * FROM $dbauth WHERE Login='$Login'";
	$reqsqlcheckuser = $mysqli->query($sqlcheckuser) or die ('Erreur '.$sqlcheckuser.' '.$mysqli->error);		
	$checkloginexist = $reqsqlcheckuser->fetch_assoc();		
        $utilisateur = checkloginexist['Login'];
	
	if ($utilisateur) {
	       	echo json_encode(["data"=>"already_exist"]);
		exit;
	} else {
		$password_string = mysqli_real_escape_string($_POST["Mdp"]);
	        $password_hash = password_hash($password_string, PASSWORD_BCRYPT);
		$Nom = $_POST['Nom'];
		$Prenom = $_POST['Prenom'];
		$Groupe = $_POST['Groupe'];
		$insertuser = "insert into $dbauth (Login, Mdp, Nom, Prenom, Groupe) VALUES ('$Login', '$password_hash', '$Nom', '$Prenom', '$Groupe')";
		$reqinsertuser $mysqli->query($insertuser) or die ('Erreur '.$insertuser.' '.$mysqli->error);
		echo json_encode(["data"=>"ok"]);
		exit;
		}
} else {
	header('Location: ../../../index.php');
}
?>
