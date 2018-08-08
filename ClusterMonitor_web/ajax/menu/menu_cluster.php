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
	
	$sqlnbjobs="SELECT count(*) as nbjobs from Collect_Jobs where id_Clusters = '$clustername'";
	$sqlnbreservation="SELECT count(*) as nbreservation from Reservations where id_Clusters = '$clustername'";
	$sqlconfigget="SELECT * from Clusters where idClusters = '$clustername'";

	$nbjobs=$mysqli->query($sqlnbjobs) or die ('Erreur '.$sqlnbjobs.' '.$mysqli->error);
	$nbreservation=$mysqli->query($sqlnbreservation) or die ('Erreur '.$sqlnbreservation.' '.$mysqli->error);
	$configget=$mysqli->query($sqlconfigget) or die ('Erreur '.$sqlconfigget.' '.$mysqli->error);

	$nbjobs=$nbjobs->fetch_assoc();
	$nbreservation=$nbreservation->fetch_assoc();
	$configget=$configget->fetch_assoc();

	$nbjobs=$nbjobs['nbjobs'];
	$nbreservation=$nbreservation['nbreservation'];
	$jmconfigget=$configget['jobmetrics'];
}	

?>
<div id="show">
	<!--Start Dashboard 2-right-->
		<div id="dashboard_links" class="col-xs-12">
			<ul id="nav_left" class="row nav nav-pills nav-stacked">
				<li class="active"><a href="#" class="tab-link" id="General"><?php print $lang_global['general']; ?></a></li>
				<li><a href="#" class="tab-link" id="Jobs"><?php print $lang_global['jobs']; ?> <span class="badge"><?php echo $nbjobs;?></span></a></li>
				<li><a href="#" class="tab-link" id="Reservations"><?php print $lang_global['reservation']; ?> <span class="badge"><?php echo $nbreservation;?></span></a></li>
	<?php if ( "$jmconfigget" == "yes" ) {  ?>
				<li><a href="#" class="tab-link" id="Jobshistory"><?php print $lang_global['jobshistory']; ?></a></li>
	<?php } ?>
				<li><a href="#" class="tab-link" id="Filesystem"><?php print $lang_global['filesystem']; ?></a></li>
				<li><a href="#" class="tab-link" id="Quota"><?php print $lang_global['quota']; ?></a></li>
				<li><a href="#" class="tab-link" id="Users"><?php print $lang_global['users']; ?></a></li>
				<li><a href="#" class="tab-link" id="Partition"><?php print $lang_global['partition']; ?></a></li>
				<li><a href="#" class="tab-link" id="Configuration"><?php print $lang_global['config']; ?></a></li>
				<li><a href="#" class="tab-link" id="Materiel"><?php print $lang_global['materiel']; ?></a></li>
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
