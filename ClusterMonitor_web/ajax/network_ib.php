
<?php 
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {

require('../include/config.php'); 
include('../include/function.php');
include('../langs/'.$lang.'.lang');

if (isset($_GET["cluster"]))
{
	$clustername=$_GET["cluster"];
	
	$sqlreqsqlinf="SELECT Last_refresh as lastrun, interconnect as ibopa from Clusters where idClusters = '$clustername'";
	$reqsqlinf= $mysqli->query($sqlreqsqlinf) or die ('Erreur '.$sqlreqsqlinf.' '.$mysqli->error);
	$fetchinf=$reqsqlinf->fetch_assoc();
	$lastrun=$fetchinf['lastrun'];
	$ibopa=$fetchinf['ibopa'];
	
}

?>

<div id="show">
	<!--Start Breadcrumb-->
	<div class="row">
		<div id="breadcrumb" class="col-xs-10">
			<a href="#" class="show-sidebar">
				<i class="fa fa-dedent fa-fw"></i>
			</a>
			<ol class="breadcrumb pull-left">
				<li><a href="index.php"><?php print $lang_global['home']; ?></a></li>
				<li><a href="#"><?php print $lang_global['network'];?> <?php echo $ibopa; ?></a></li>
			</ol>	
			<h5 class="maj pull-right"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>	
		</div>
	</div>
	<!--End Breadcrumb-->
	<!--Start Dashboard 1-->
	<!--iv id="dashboard-header" class="row">
		<div class="col-xs-12 col-sm-7">
			<h3><?php print $lang_global['network_ib']; ?></h3>
		</div>
		<div class="hidden-xs col-sm-5">
			<h5 style="text-align:right"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>
		</div>		
		<div class="clearfix visible-xs"></div>
	</div-->
	<!--End Dashboard 1-->
	<!--Start Dashboard 2-->
	<div class="row-fluid">
		<div id="dashboard_tabs" class="col-xs-12 col-sm-12">
			<div id="dashboard-loader" class="row" style="visibility: visible; position: relative;height:700px;">
				<center><i class="fa fa-spinner fa-spin fa-5x" style="margin-top:100px;"></i></center>
			</div>
			<div id="dashboard-Topology" class="row" style="visibility: hidden; position: absolute;">
				<?php
					Topology($clustername);
				?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<!--End Dashboard 2 -->

<?php
} else {
	header('Location: ../index.php');
}
?>
