<?php
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {

$configfile = '../config.php';
$langue = $_POST['langue'];
$databasename = $_POST['databasename'];
$drivertype = $_POST['drivertype'];
$Server = $_POST['Server'];
$port = $_POST['port'];
$cdb = $_POST['cdb'];
$logindb = $_POST['logindb'];
$pwddb = $_POST['pwddb'];
$cowner = $_POST['cowner'];
$loginsu = $_POST['loginsu'];
$pwdsu = $_POST['pwdsu'];
$loginad = $_POST['loginad'];
$pwdad = sha1($_POST['pwdad']);

$mysqlic = new mysqli($Server, $loginsu, $pwdsu);
$mysqlic->set_charset("utf8");
if ($mysqlic->connect_error) {
        die('Erreur de connexion ('.$mysqlic->connect_errno.')'. $mysqlic->connect_error);
}

if ($cdb == 'createdb') {
	
	// Creation de la base de données
	$sqlcreate = "create database $databasename;";
	$mysqlic->query($sqlcreate) or die ('Erreur '.$sqlcreate.' '.$sqlcreate->error);
	
	// Create tables
	$insertdb=shell_exec("mysql -u $loginsu --password='$pwdsu' $databasename < cluster_monitor.sql");
	echo $insertdb;
}

$mysqli = new mysqli($Server, $loginsu, $pwdsu, $databasename);
$mysqli->set_charset("utf8");
if ($mysqli->connect_error) {
        die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
}

if ($cowner == 'createowner') {
	
	// Creation owner
	$sqlcreateownergall = "CREATE USER '$logindb'@'%' IDENTIFIED BY '$pwddb'; 
	$mysqli->query($sqlcreateownergall) or die ('Erreur '.$sqlcreateownergall.' '.$sqlcreateownergall->error);
	$sqlcreateownerg = "CREATE USER '$logindb'@'localhost' IDENTIFIED BY '$pwddb'; 
        $mysqli->query($sqlcreateownerg) or die ('Erreur '.$sqlcreateownerg.' '.$sqlcreateownerg->error);

	$sqlcreateownerp = "GRANT ALL PRIVILEGES ON $databasename.* TO '$logindb'@'localhost';
        $mysqli->query($sqlcreateownerp) or die ('Erreur '.$sqlcreateownerp.' '.$sqlcreateownerp->error);
	$sqlcreateownerpall = "GRANT ALL PRIVILEGES ON $databasename.* TO '$logindb'@'%'";
	$mysqli->query($sqlcreateownerp) or die ('Erreur '.$sqlcreateownerp.' '.$sqlcreateownerp->error);
	
	$sqlflush = "flush privileges";
	$mysqli->query($sqlflush) or die ('Erreur '.$sqlflush.' '.$sqlflush->error);
}

// insert langue
$sqllangue = "INSERT INTO `Config` (`langue`, `other`) VALUES ('$langue', '');";
$mysqli->query($sqllangue) or die ('Erreur '.$sqllangue.' '.$sqllangue->error);

// Create file conf
$contenuconfigfile = "<?php \r\n \$pathclustermonitor = '/cluster-monitor';\r\n \$loginmysql = '$logindb';\r\n \$passmysql = '$pwddb';\r\n \$hostmysql = '$Server';\r\n \$dbmysql = '$databasename';\r\n \$dbauth = 'Auth';\r\n \$author = 'MONTAGNE Vincent & GILBERT Nicolas';\r\r\n \$mysqli = new mysqli(\$hostmysql, \$loginmysql, \$passmysql, \$dbmysql);\r\n \$mysqli->set_charset('utf8'); \r\n if (\$mysqli->connect_error) { \r\r die('Erreur de connexion ('.\$mysqli->connect_errno.')'. \$mysqli->connect_error); \r\r\n } \r\n \$sqllangue='select langue from Config'; \r\n \$reqlangue= \$mysqli->query(\$sqllangue) or die ('Erreur '.\$sqllangue.' '.\$mysqli->error); \r\n \$flangue=\$reqlangue->fetch_assoc(); \r\n \$lang=\$flangue['langue']; \r\n\r ?>";

$fichier = fopen($configfile, 'w+');
fwrite($fichier,$contenuconfigfile);
chmod($fichier, 0600);
fclose($fichier);

// Create admin web applicatio
$sqlad = "INSERT INTO `Auth` (`Login`, `Mdp`, `Nom`, `Prenom`, `Groupe`) VALUES ('$loginad', '$pwdad', 'admin', 'admin', 'admin');";
$mysqli->query($sqlad) or die ('Erreur '.$sqlad.' '.$sqlad->error);

// Create file lock new install
$contenulockfichier = "# lock file new install";
$lockfichier = fopen('.lockinstall', 'w+');
fwrite($lockfichier,$contenulockfichier);
fclose($lockfichier);

echo json_encode(["data"=>"ok"]);

} else {
	header('Location: ../../../index.php');
}
?>
