<?php
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {


	require('../../include/config.php'); 

	session_start();

	$_SESSION['action']=$_POST['action'];
	$_SESSION['jobid']=$_POST['jobid'];
	$_SESSION['userid']=$_POST['userid'];
	$_SESSION['from']=$_POST['from'];
	$_SESSION['to']=$_POST['to'];

	echo json_encode(["data"=>"ok"]);
	exit;

} else {
	header('Location: ../../../index.php');
}
?>
