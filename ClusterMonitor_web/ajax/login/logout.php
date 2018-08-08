<?php
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {
session_start(); 

// On détruit les variables de notre session
session_unset ();

// On détruit notre session
session_destroy ();

return "ok";
} else {
	header('Location: ../../../index.php');
}
?>
