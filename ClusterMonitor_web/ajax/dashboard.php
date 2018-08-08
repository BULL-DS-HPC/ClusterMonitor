<?php 
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {
require('../include/config.php'); 
include('../include/function.php');
include('../langs/'.$lang.'.lang');

?>

<div id="show">
	<!--Start Breadcrumb-->
	<div class="row">
		<div id="breadcrumb" class="col-xs-12">
			<a href="#" class="show-sidebar">
				<i class="fa fa-dedent fa-fw"></i>
			</a>
			<ol class="breadcrumb pull-left">
				<li><a href="index.php"><?php print $lang_global['home']; ?></a></li>
				<li><a href="#"><?php print $lang_global['dashboard']; ?></a></li>
			</ol>		
		</div>
	</div>
	<!--End Breadcrumb-->
	<!--Start Dashboard 1>
	<div id="dashboard-header" class="row">
		<div class="col-xs-12">
			<h3><?php print $lang_global['dashboard']; ?> Cluster</h3>
		</div>
		<div class="clearfix visible-xs"></div>
	</div-->
	<!--End Dashboard 1-->
	<!--Start Dashboard 2-->
	<div class="row-fluid">
		<div id="dashboard_tabs" class="col-xs-12 col-sm-12 ">
			<!--Start Utilization Tab 1-->
			<div class="row" style="visibility: visible; position: relative;">
			<?php
				$sqlcluster = "select idClusters from Clusters where is_active=1 and SlurmVersion!='version'";
				$cluster = $mysqli->query($sqlcluster) or die ('Erreur '.$sqlcluster.' '.$mysqli->error);
				while ($clustername = $cluster->fetch_row()) 
				{
					$fclustername = $clustername['0'];
					echo '
					<div class="col-xs-12 col-sm-6 col-md-4 ow-server dashboarddiv">
						<h4 class="page-header text-right"><i class="fa fa-linux"></i>'.$fclustername.'</h4>
						<div class="ow-server-bottom">
							<div class="row">
							<div class="col-sm-12 text-center majdash">';
										statecollect($fclustername);
				echo'				</div>
								<div class="col-sm-6">
									<div class="box-header">
										<div class="box-name">
											<i class="fa fa-bar-chart-o"></i>
											<span>'; print $lang_global['loadcpu']; echo '</span>
										</div>
									</div>
									<div class="box-content"> ';
										dashboardutilization($fclustername,150);
				echo' 				</div>				
								</div>
								<div class="col-sm-6">
									<div class="box-header">
										<div class="box-name">
											<i class="fa fa-pie-chart"></i>
											<span>'; print $lang_global['loadnode']; echo '</span>
										</div>
									</div>
									<div class="box-content"> ';
										dashboardnodes($fclustername,150);
				echo'				</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="box-header">
										<div class="box-name">
											<i class="fa fa-laptop"></i>
											<span>'; print $lang_global['statefrt']; echo '</span>
										</div>
									</div>
									<div class="box-content"> ';
										dashboardgetfrontaux($fclustername);			
				echo'				</div>
								</div>
								<div class="col-sm-6">
									<div class="box-header">
										<div class="box-name">
											<i class="fa fa-info-circle"></i>
											<span>'; print $lang_global['statefs']; echo '</span>
										</div>
									</div>
									<div class="box-content">';
										dashboardFS($fclustername);
				echo'				</div>
								</div>
							</div>
						</div>
					</div>';
				}
			?>
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
<?php


if ($_SESSION['authentification'] == "1" && $_SESSION['Groupe'] == "admin") { 

	//Check protection file of config 
	$dconf=substr(sprintf('%o', fileperms('../include/config.php')), -4);
	$dli=substr(sprintf('%o', fileperms('../include/install/.lockinstall')), -4);

	if ($dconf > '0440') {
	echo'<br><div class="alert alert-danger" role="alert">
			<h5 class="text-center">
			<i class="fa fa-times fa-red"></i> Attention le fichier /include/config.php n\'est pas correctement protégé ('.$dconf.') !!! 
			</h5>
     		</div>';
	}
	if ($dli > '0440') {
	echo'<div class="alert alert-danger" role="alert">
			<h5 class="text-center">
			<i class="fa fa-times fa-red"></i> Attention le fichier /include/install/.lockinstall n\'est pas correctement protégé ('.$dli.') !!! </h5>
     	</div>';
	}
}

?>

</div>
<?php
} else {
	header('Location: ../index.php');
}
?>
