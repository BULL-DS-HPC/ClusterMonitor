<?php 
//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (is_ajax()) {
require('../include/config.php'); 
include('../include/function.php');
include('../langs/'.$lang.'.lang');

$sqllastrun="SELECT MAX(Last_refresh) as lastrun from Clusters";
$lastrun= $mysqli->query($sqllastrun) or die ('Erreur '.$sqllastrun.' '.$mysqli->error);
$lastrun=$lastrun->fetch_assoc();
$lastrun=$lastrun['lastrun'];

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
				<li><a href="#"><?php print $lang_global['details']; ?> clusters</a></li>
			</ol>
			<h5 class="maj pull-right"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>		
		</div>
	</div>
	<!--End Breadcrumb-->
	<!--Start Dashboard 1-->
	<!--div id="dashboard-header" class="row">
		<div class="col-xs-12 col-sm-7">
			<h3><?php print $lang_global['details']; ?> clusters</h3>
		</div>
		<div class="hidden-xs col-sm-5">
			<h5 style="text-align:right"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>
		</div>		
		<div class="clearfix visible-xs"></div>	
		<div class="clearfix visible-xs"></div>
	</div-->
	<!--End Dashboard 1-->
	<!--Start Dashboard 2-->
	<div class="row-fluid">
		<div id="dashboard_tabs" class="col-xs-12 col-sm-12">
			<!--Start Utilization Tab 1-->
			<div class="row" style="visibility: visible; position: relative;">
				<?php

				echo '<br><br>
					<div class="col-lg-3 col-md-3 col-sm-5 col-xs-2 ow-server">
						<h4 class="page-header text-left">Nombre/Conf</h4>
						<div class="ow-server-bottom col-xs-12">
							<div class="row detail-liste">
								<div class="col-xs-8"><b>'; print $lang_global['versionofbatch']; echo '</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8 "><b> QOS</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8 "><b>'; print $lang_global['partition']; echo '</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8 "><b>'; print $lang_global['reservation']; echo '</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8 "><b> CPU</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8 "><b>'; print $lang_global['users']; echo '</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8"><b>'; print $lang_global['jobs']; echo '</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8"><b>'; print $lang_global['frontal']; echo '</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8"><b>'; print $lang_global['node']; echo '</b></div>
							</div>
							<!--div class="row detail-liste">
								<div class="col-xs-8"><b>'; print $lang_global['filesystem']; echo '</b></div>
							</div-->
							<div class="row detail-liste">
								<div class="col-xs-8"><b>'; print $lang_global['filling']; echo ' '; print $lang_global['filesystem']; echo '</b></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-8"><b>'; print $lang_global['filling']; echo ' '; print $lang_global['filesystem']; echo '</b></div>
							</div>
						  </div>
					 </div>';

				$sqlcluster = "select idClusters from Clusters where is_active=1 and SlurmVersion!='version'";
				$cluster = $mysqli->query($sqlcluster) or die ('Erreur '.$sqlcluster.' '.$mysqli->error);
				while ($clustername = $cluster->fetch_assoc()) 
				{
					$fclustername = $clustername['idClusters'];

					$sqlslurmversion="select SlurmVersion from Clusters where idClusters='$fclustername'";
					$slurmversion=$mysqli->query($sqlslurmversion) or die ('Erreur '.$sqlslurmversion.' '.$mysqli->error);

					$sqlnbqos="select count(*) as nbqos from QOS where id_Clusters='$fclustername' and is_active=1";
					$nbqos=$mysqli->query($sqlnbqos) or die ('Erreur '.$sqlnbqos.' '.$mysqli->error);

					$sqlnbpartition="select count(*) as nbpartition from Partitions where id_Clusters='$fclustername' and is_active=1";
					$nbpartition=$mysqli->query($sqlnbpartition) or die ('Erreur '.$sqlnbpartition.' '.$mysqli->error);

					$sqlnbreservation="select count(*) as nbreservation from Reservations where id_Clusters='$fclustername'";
					$nbreservation=$mysqli->query($sqlnbreservation) or die ('Erreur '.$sqlnbreservation.' '.$mysqli->error);

					$sqlnbCPU="select CPU_total as nbCPU from Collect_Clusters where id_Clusters='$fclustername' order by Timestamp desc limit 1";
					$nbCPU=$mysqli->query($sqlnbCPU) or die ('Erreur '.$sqlnbCPU.' '.$mysqli->error);

					$sqlnbutilisateurs="select count(*) as nbutilisateurs from Users where id_Clusters='$fclustername'";
					$nbutilisateurs=$mysqli->query($sqlnbutilisateurs) or die ('Erreur '.$sqlnbutilisateurs.' '.$mysqli->error);

					$sqlnbjobs="select count(*) as nbjobs from Collect_Jobs where id_Clusters='$fclustername'";
					$nbjobs=$mysqli->query($sqlnbjobs) or die ('Erreur '.$sqlnbjobs.' '.$mysqli->error);

					$sqlnbfrontaux="select count(*) as nbfrontaux from Frontaux where id_Clusters='$fclustername'";
					$nbfrontaux=$mysqli->query($sqlnbfrontaux) or die ('Erreur '.$sqlnbfrontaux.' '.$mysqli->error);

					$sqlnbnoeuds="select count(*) as nbnoeuds from Noeuds where id_Clusters='$fclustername'";
					$nbnoeuds=$mysqli->query($sqlnbnoeuds) or die ('Erreur '.$sqlnbnoeuds.' '.$mysqli->error);

					$sqlnbfs="SELECT count(idFilesystems) as nbfs FROM Filesystems where id_Clusters = '$fclustername' and is_active=1";
					$nbfs=$mysqli->query($sqlnbfs) or die ('Erreur '.$sqlnbfs.' '.$mysqli->error);
					$nbfs=$nbfs->fetch_assoc();
					$nbfs=$nbfs['nbfs'];
					
					$sqlfs="SELECT id_Filesystems,Dispo,Timestamp,round((utilise/(disponible+utilise)*100),0) as percent FROM Collect_FS where id_Clusters = '$fclustername' order by Timestamp desc, id_Filesystems limit $nbfs";
					$fs=$mysqli->query($sqlfs) or die ('Erreur '.$sqlfs.' '.$mysqli->error);

					$slurmversion=$slurmversion->fetch_assoc();
					$nbqos=$nbqos->fetch_assoc();
					$nbpartition=$nbpartition->fetch_assoc();
					$nbreservation=$nbreservation->fetch_assoc();
					$nbCPU=$nbCPU->fetch_assoc();
					$nbutilisateurs=$nbutilisateurs->fetch_assoc();
					$nbjobs=$nbjobs->fetch_assoc();
					$nbfrontaux=$nbfrontaux->fetch_assoc();
					$nbnoeuds=$nbnoeuds->fetch_assoc();

					$slurmversion=$slurmversion['SlurmVersion'];
					$nbqos=$nbqos['nbqos'];
					$nbpartition=$nbpartition['nbpartition'];
					$nbreservation=$nbreservation['nbreservation'];
					$nbCPU=$nbCPU['nbCPU'];
					$nbutilisateurs=$nbutilisateurs['nbutilisateurs'];
					$nbjobs=$nbjobs['nbjobs'];
					$nbfrontaux=$nbfrontaux['nbfrontaux'];
					$nbnoeuds=$nbnoeuds['nbnoeuds'];
					
					
					echo '
					<div class="col-lg-1 col-md-4 col-sm-6 col-xs-12 ow-server" style="width:170px">
						<h4 class="page-header text-center"></i>'.$fclustername.'</h4>
						<div class="ow-server-bottom col-xs-12">
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$slurmversion.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbqos.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbpartition.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbreservation.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbCPU.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbutilisateurs.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbjobs.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbfrontaux.'</span></div>
							</div>
							<div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbnoeuds.'</span></div>
							</div>
							<!--div class="row detail-liste">
								<div class="col-xs-4"><span class="badge">'.$nbfs.'</span></div>
							</div-->';
							while ($stateFS = $fs->fetch_assoc()) 
							{

								if ($stateFS['Dispo'] == 0)
								{
									echo '
									<div class="row detail-liste">
										<div class="col-xs-12">
											<div class="progress" style="margin-bottom:0px">
												<div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;color: #000;">'; print $lang_global['unavailable']; echo '</div>
											</div>
										</div>
									</div>';
								}
								elseif ($stateFS['Dispo'] == 1)
								{
									if ($stateFS['percent'] == 0 ){
										$stateFS['percent'] = 1;
									}
									echo '
									<div class="row detail-liste">
										<div class="col-xs-12">
											<b>/'.$stateFS['id_Filesystems'].'</b>
											<div class="progress" style="margin-bottom:0px">
												<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$stateFS['percent'].'" aria-valuemin="0" aria-valuemax="100" style="width: '.$stateFS['percent'].'%;color: #000;">'.$stateFS['percent'].'%</div>
											</div>
										</div>
									</div>';
								}
							}
							echo '
						</div>
					</div>';
				}
			?>
			</div>
		<div class="clearfix"></div>
	</div>
</div>
<?php
} else {
	header('Location: ../index.php');
}
?>
