<?php 

/*--------------------------------------------------------------------------------------
		Function to check if the request is an AJAX request
----------------------------------------------------------------------------------------*/

function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {

require('../../include/config.php'); 
include('../../include/function.php');
include('../../langs/'.$lang.'.lang');

$mysqli = new mysqli($hostmysql, $loginmysql, $passmysql, $dbmysql);
$mysqli->set_charset("utf8");
if ($mysqli->connect_error) {
    die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
}

if (isset($_GET["cluster"]))
{
	$clustername=$_GET["cluster"];
	
	$sqllastrun="SELECT Last_refresh as lastrun from Clusters where idClusters = '$clustername'";
	$lastrun= $mysqli->query($sqllastrun) or die ('Erreur '.$sqllastrun.' '.$mysqli->error);
	$lastrun=$lastrun->fetch_assoc();
	$lastrun=$lastrun['lastrun'];

	$sqlconfigget="SELECT * from Clusters where idClusters = '$clustername'";
	$configget=$mysqli->query($sqlconfigget) or die ('Erreur '.$sqlconfigget.' '.$mysqli->error);
	$configget=$configget->fetch_assoc();
	$jmconfigget=$configget['jobmetrics'];
}	

?>
<div id="show">
	<!--Start Dashboard 2-right-->
		<div id="dashboard_links" class="col-xs-12">
			<ul id="nav_left" class="row nav nav-pills nav-stacked">
				<li class="active"><a href="#" class="tab-link" id="Tendance">Tendance</a></li>
			<?php if ( "$jmconfigget" == "yes" ) {  ?>
				<li><a href="#" class="tab-link" id="usage">Usage</a></li>
			<?php } ?>
				<li><a href="#" class="tab-link" id="jobs">Jobs</a></li>
			</ul>
		</div>
<!--End Dashboard 2-right-->
</div>
<script type="text/javascript">
$("#dashboard_links").affix({
            offset: {
                top: 50,
            }
        });
</script>
<?php
} else {
	header('Location: ../../index.php');
}
?>
