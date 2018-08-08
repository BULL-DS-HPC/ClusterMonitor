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
session_start();

if (isset($_POST['Login'])){ 
	$Login = $_POST['Login'];
	$password_string = mysqli_real_escape_string($_POST["Mdp"]);

	$sqlrecupauth = "select * from $dbauth WHERE Login = '$Login'";
	$reqsqlrecupauth = $mysqli->query($sqlrecupauth) or die ('Erreur '.$sqlrecupauth.' '.$mysqli->error);
	$authcheck = $reqsqlrecupauth->fetch_assoc();

	$password_hash = $authcheck['Mdp'];	
		
	if (password_verify($password_string, $password_hash)) {
	
		$_SESSION['authentification'] = 1;
                $_SESSION['Groupe'] = $authcheck['Groupe'];
                $_SESSION['Nom'] = $authcheck['Nom'];
                $_SESSION['Prenom'] = $authcheck['Prenom'];
                $_SESSION['Login'] = $authcheck['Login'];   
		echo json_encode(["data"=>"ok"]);
                exit;	

  	} else {
  	
		echo json_encode(["data"=>"ko"]);
                exit;

 	}


}
} else {
	header('Location: ../../../index.php');
}
?>
