<?php


/*--------------------------------------------------------------------------------------
				Function generale/calcul
----------------------------------------------------------------------------------------*/


function formatBytes($bytes, $precision = 2) {
    $unit = ["KB", "MB", "GB", "TB"];
    $exp = floor(log($bytes, 1024)) | 0;
    return round($bytes / (pow(1024, $exp)), $precision)." ".$unit[$exp];
}

function formattoGBytes($from, $precision = 2){
	return round($from / pow(1024,2), $precision);
}

function Pourcentage($Nombre, $Total) {
	return round($Nombre * 100 / $Total);
}

/*--------------------------------------------------------------------------------------
			Function page dashboard.php
----------------------------------------------------------------------------------------*/

/*____________Affichage du pourcentage d'utilisation du cluster____________*/
/*        (utilisé également par la function General de cluster.php)       */     

function dashboardutilization($fclustername,$divheight)
{
	global $mysqli;
	global $lang_global;
	$sqlload = "select round((CPU_allocated/CPU_total*100),0) as percent from Collect_Clusters where id_Clusters ='$fclustername' order by Timestamp desc limit 1;";
	$load = $mysqli->query($sqlload) or die ('Erreur '.$sqlload.' '.$mysqli->error);
	$clusterload = $load->fetch_assoc();
	echo '
		<script>
			Morris.Bar({
				element: "utilization'.$fclustername.'",
				data: [ { y: "", a: '.$clusterload['percent'].' } ],
				xkey: "y",
				ykeys: ["a"],
				ymax: 100,
				labels: ["Use "],
				postUnits: " %",
				stacked : true,
				//grid: false,
				//axes: false,
				barColors: function(row, series, type) {
  				 if(row.y > 90) return "#7266ba";
				 else if(row.y > 60) return "#EF3695";
    				 else if(row.y > 30) return "#DEBB27";
				 else if(row.y > 0) return "rgb(25, 130, 200)";
				 else return "#fff";
				}
			});
		</script>
		
		<div class="monsvg" id="utilization'.$fclustername.'" style="height:'.$divheight.'px"></div>
	';
}

/*____________Affichage de la disponibilitée des noeuds du cluster____________*/
/*          (utilisé également par la function General de cluster.php)        */  

function dashboardnodes($fclustername,$divheight)
{
	global $mysqli;
	global $lang_global;
	$sqlnode = "select allocated, idle, other from Collect_nodes where id_Clusters = '$fclustername' order by Timestamp desc limit 1;";
	$node = $mysqli->query($sqlnode) or die ('Erreur '.$sqlnode.' '.$mysqli->error);
	$clusternode = $node->fetch_assoc();
	echo '
		<script>
			Morris.Donut({
				element: "node'.$fclustername.'",
				data: [
					{label: "'; print $lang_global['alloc']; echo '", value: '.$clusternode['allocated'].'},
					{label: "'; print $lang_global['idle']; echo '", value: '.$clusternode['idle'].'},
					{label: "'; print $lang_global['other']; echo '", value: '.$clusternode['other'].'}
				],
				colors: ["#007bff","#4dbd74","#dc3545"],
			});
		</script>

		<div class="monsvg" id="node'.$fclustername.'" style="height:'.$divheight.'px" ></div>
	';
}

/*____________Affichage de la disponibilitée des noeuds du cluster____________*/
/*          (utilisé également par la function General de cluster.php)        */  

function statecollect($clustername)
{
	global $mysqli;
	global $lang_global;
	$sqlsc = "select Last_refresh from Clusters where idClusters = '$clustername';";
	$node = $mysqli->query($sqlsc) or die ('Erreur '.$sqlsc.' '.$mysqli->error);
	$collectstate = $node->fetch_assoc();
	$datelr = strtotime($collectstate['Last_refresh']);
	$datej = strtotime(date("d-m-Y H:i:s"));
        $lastrun=$collectstate['Last_refresh'];
	$interval = $datej - $datelr;
	if ( $interval > '1800') { 
		echo '<p class="bg-danger"><i class="fa fa-warning"></i> '; print $lang_global['update']; echo ': '.$collectstate['Last_refresh'].'... </p>';
	} else { 
		echo '<p><i class="fa fa-refresh"></i> '; print $lang_global['update']; echo ': '.$collectstate['Last_refresh'].' </p>';
	}
	
}


/*____________Affichage de la disponibilité des frontaux____________*/


function dashboardgetfrontaux($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlnbfront="SELECT count(idFrontaux) as nbfront FROM Frontaux where id_Clusters = '$fclustername'";
	$nbfront= $mysqli->query($sqlnbfront) or die ('Erreur '.$sqlnbfront.' '.$mysqli->error);
	$nbfront = $nbfront->fetch_assoc();
	$sqlstatefront="SELECT id_Frontaux,Dispo,Timestamp FROM Collect_Frontaux where id_Clusters = '$fclustername' order by Timestamp desc, id_Frontaux asc  limit $nbfront[nbfront]";
	$statefront=$mysqli->query($sqlstatefront) or die ('Erreur '.$sqlstatefront.' '.$mysqli->error);

	while ($statefrontal = $statefront->fetch_assoc()) 
	{
		if ($statefrontal['Dispo'] == 0)
		{
			echo '<button type="button" title="'.$statefrontal['id_Frontaux'].'" class="btn btn-danger btn-app-xs disabled btn-block">'.$statefrontal['id_Frontaux'].' <i class="fa fa-warning"></i></button>';
		}
		elseif ($statefrontal['Dispo'] == 1)
		{
			echo '<button type="button" title="'.$statefrontal['id_Frontaux'].'" class="btn btn-success btn-app-xs disabled btn-block">'.$statefrontal['id_Frontaux'].' <i class="fa fa-check"></i></button>';
		}
	}	
}

/*____________Affichage disponibilité des systèmes de fichiers____________*/


function dashboardFS($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlnbfs="SELECT count(idFilesystems) as nbfs FROM Filesystems where id_Clusters = '$fclustername' and is_active=1";
	$nbfs= $mysqli->query($sqlnbfs) or die ('Erreur '.$sqlnbfs.' '.$mysqli->error);
	$nbfs = $nbfs->fetch_assoc();
	$sqldashFS = "SELECT id_Filesystems,Dispo,Timestamp,round((utilise/(disponible+utilise)*100),0) as percent FROM Collect_FS where id_Clusters = '$fclustername' order by Timestamp desc, id_Filesystems asc limit $nbfs[nbfs]";
	$dashFS = $mysqli->query($sqldashFS) or die ('Erreur '.$sqldashFS.' '.$mysqli->error);
		
	while ($stateFS = $dashFS->fetch_assoc()) 
	{
		if ($stateFS['Dispo'] == 0)
		{
			echo '<button type="button" title="'.$stateFS['id_Filesystems'].'" class="btn btn-danger btn-app-xs disabled btn-block">'.$stateFS['id_Filesystems'].' <i class="fa fa-warning"></i></button>';
		}
		elseif ($stateFS['Dispo'] == 1)
		{
		if ($stateFS['percent'] == 0 ){
			$stateFS['percent'] = 1;
		}
			echo '<button type="button" title="'.$stateFS['id_Filesystems'].'" class="btn btn-success btn-app-xs disabled btn-block">'.$stateFS['id_Filesystems'].' <i class="fa fa-check"></i><span class="badge pull-right">'.$stateFS['percent'].' %</span></button>';
		}
	}	
}


/*--------------------------------------------------------------------------------------
			Function page cluster.php
----------------------------------------------------------------------------------------*/

/*______________________Affichage onglet général______________________*/


function General($fclustername,$divheight)
{
	global $lang_global;
	echo '<h4 class="page-header text-right"><i class="fa fa-linux"></i> '.$fclustername.'</h4>
			<div class="ow-server-bottom">
				<div class="row">
					<div class="col-sm-6">
						<div class="box-header">
							<div class="box-name">
								<i class="fa fa-bar-chart-o"></i>
								<span>'; print $lang_global['cpuusage']; echo '</span>
							</div>
						</div>
						<div class="box-content">';
							dashboardutilization($fclustername,$divheight);
	echo'				</div>				
					</div>
					<div class="col-sm-6">
						<div class="box-header">
							<div class="box-name">
								<i class="fa fa-pie-chart"></i>
								<span>'; print $lang_global['allocatenode']; echo '</span>
							</div>
						</div>
						<div class="box-content"> ';
							dashboardnodes($fclustername,$divheight);
	echo'				</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="box-header">
							<div class="box-name">
								<i class="fa fa-laptop"></i>
								<span>'; print $lang_global['frontalstate']; echo '</span>
							</div>
						</div>
						<div class="box-content"> ';
							getfrontaux($fclustername);			
	echo'				</div>
					</div>
					<div class="col-sm-6">
						<div class="box-header">
							<div class="box-name">
								<i class="fa fa-info-circle"></i>
								<span>'; print $lang_global['reportallocatenode']; echo '</span>
							</div>
						</div>
						<div class="box-content">';
							TendanceGeneral($fclustername,$divheight);
	echo'				</div>
					</div>
				</div>
			</div>';
}


/*_____Sous function onglets general_____*/


function getfrontaux($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlnbfront="SELECT count(idFrontaux) as nbfront FROM Frontaux where id_Clusters = '$fclustername' ";
	$nbfront= $mysqli->query($sqlnbfront) or die ('Erreur '.$sqlnbfront.' '.$mysqli->error);
	$nbfront = $nbfront->fetch_assoc();
	$sqlstatefront="SELECT id_Frontaux,Dispo,Timestamp FROM Collect_Frontaux where id_Clusters = '$fclustername' order by Timestamp desc, id_Frontaux asc limit $nbfront[nbfront]";
	$statefront=$mysqli->query($sqlstatefront) or die ('Erreur '.$sqlstatefront.' '.$mysqli->error);

	
	$sqlstatefront="SELECT id_Frontaux,Dispo,Timestamp,load1,load5,load15,nb_user,uptime FROM Collect_Frontaux where id_Clusters = '$fclustername' order by Timestamp desc,id_Frontaux asc limit $nbfront[nbfront]";
	$statefront=$mysqli->query($sqlstatefront) or die ('Erreur '.$sqlstatefront.' '.$mysqli->error);

	$larg=12/$nbfront[nbfront];
	echo '	<div class="row">
				<div class="col-sm-12">
					<div class="row detail-liste">
						<div class="col-xs-3"></div>
						<div class="col-xs-3">'; print $lang_global['load']; echo '</div>
						<div class="col-xs-3">'; print $lang_global['users']; echo '</div>
						<div class="col-xs-3">'; print $lang_global['uptime']; echo '</div>
					</div>';
	while ($statefrontal = $statefront->fetch_assoc()) 
	{
		if ($statefrontal['Dispo'] == 1)
		{
			echo '
					<div class="row detail-liste">';
						if ($statefrontal['Dispo'] == 0)
                {    
                        echo '<div class="col-xs-3"><button type="button" title="'.$statefrontal['id_Frontaux'].'" class="btn btn-danger btn-app-xs disabled btn-block" style="margin-bottom: 0px;">'.$statefrontal['id_Frontaux'].' <i class="fa fa-warning"></i></button></div>';
                }
                elseif ($statefrontal['Dispo'] == 1)
                {    
                        echo '<div class="col-xs-3"><button type="button" title="'.$statefrontal['id_Frontaux'].'" class="btn btn-success btn-app-xs disabled btn-block" style="margin-bottom: 0px;">'.$statefrontal['id_Frontaux'].' <i class="fa fa-check"></i></button></div>';
                }


			echo '			<div class="col-xs-3">
							<span data-toggle="jqstooltip" data-placement="right" title="1 min : '.$statefrontal['load1'].', 5 min : '.$statefrontal['load5'].', 15 min : '.$statefrontal['load15'].'">'.$statefrontal['load1'].' *</i></span>
						</div>
						<div class="col-xs-3">'.$statefrontal['nb_user'].'</div>
						<div class="col-xs-3">'.$statefrontal['uptime'].'</div>
					</div>				
			';
		}
	}
	echo '		</div>
			</div>';
}


/*_____Sous function onglets general_____*/


function TendanceGeneral($fclustername,$divheight)
{
	global $mysqli;
	global $lang_global;
	$sqltrend = "SELECT * from (SELECT avg(CPU_allocated) as avgallocated, CPU_total, DATE_FORMAT(Timestamp, '%Y-%m-%d') as date_jour FROM Collect_Clusters where id_Clusters='$fclustername' group by date_jour desc limit 20 ) as t order by t.date_jour asc";
	$trend = $mysqli->query($sqltrend) or die ('Erreur '.$sqltrend.' '.$mysqli->error);
	
	echo '
		<script>
			Morris.Area({
				element: "trend'.$fclustername.'",
				data: [ ';
	while ($onetrend = $trend->fetch_row()) 
	{
		$val=round(($onetrend['0']/$onetrend['1'])*100,0);
		echo ' { y: "'.$onetrend['2'].'", a: '.$val.'},';
	}
    echo ' 
				],
				xkey: "y",
				ykeys: ["a"],
				labels: ["'; print $lang_global['usetrend']; echo '"],
				parseTime:false,
				xLabelAngle:80,
				lineColors: ["#1C84C6"],
				postUnits:"%",
			});
		</script>
		
		<div id="trend'.$fclustername.'" style="height:'.$divheight.'px"></div>
	';
}


/*______________________Affichage onglet jobs history______________________*/


function Jobshistory($clustername,$action,$userid,$from,$to,$jobid)
{
	global $redrawlist;
	global $mysqli;
        global $lang_global;

	if ($action == 'okjobid') {

        	$sqljobs="SELECT * from Jobs_Metrics where idjobs = '$jobid' and id_clusters = '$clustername'";
		$reqjobs= $mysqli->query($sqljobs) or die ('Erreur '.$sqljobs.' '.$mysqli->error);

		// En fontion du nombre de ligne , definir un nombre de ligne à affiché 
		$sqljobsdet="SELECT * from Jobs_Metricsdet_$clustername where idjobs = '$jobid' ";
                $reqjobsdet= $mysqli->query($sqljobsdet) or die ('Erreur '.$sqljobsdet.' '.$mysqli->error);
		$num_rows_reqjobsdet = mysqli_num_rows($reqjobsdet);
                $numdeclrows = $num_rows_reqjobsdet / ( $num_rows_reqjobsdet * 25 / 100 );
		
		$num_rows_reqjobs = mysqli_num_rows($reqjobs);
		if ($num_rows_reqjobs == '0') {
	
			echo '<p>No search init or no result found</p>';		
			
/////////////////////////////////////////////////////////// A develloper rechercher dans la table history .....

		} else {

		echo '<div class="table-responsive">

                            <div class="col-xs-12 col-sm-12 col-md-12 ow-server jobshistoryheader">
                                <h4 class="page-header text-left"><b>JobID </b>'.$jobid.'</h4>
                                <div class="ow-server-bottom col-xs-10 col-sm-10 col-md-10">';
								
					while ($jobs = $reqjobs->fetch_assoc()) 
					{
					echo '<div class="row"><div class="col-xs-12 col-sm-12 col-md-12 "><b>Jobname :</b> '.$jobs['JobName'].' </div></div>
						<div class="row">
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Userid :</b> '.$jobs['UserId'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Account :</b> '.$jobs['Account'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>WCKey :</b> '.$jobs['WCKey'].' </div>
						</div>
						<div class="row">
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Partition :</b> '.$jobs['Partition'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Qos :</b> '.$jobs['QOS'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Shared :</b> '.$jobs['Shared'].' </div>
						</div>
						<div class="row">
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Nombre Noeuds :</b> '.$jobs['NumNodes'].' </div>
                                                        <div class="col-xs-3 col-sm-3 col-md-3 "> <b>Nombre Cpus :</b> '.$jobs['NumCPUs'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Tâche par Cpus :</b> '.$jobs['CPUsTask'].' </div>
                                                        <div class="col-xs-2 col-sm-2 col-md-2 "> <b>Mémoire :</b> '.$jobs['Mem'].' </div>
						</div>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>Node Liste :</b> '.$jobs['NodeList'].' </div>
						</div>
						<div class="row">
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Submit :</b> '.$jobs['SubmitTime'].' </div>
                                                        <div class="col-xs-3 col-sm-3 col-md-3 "> <b>Start :</b> '.$jobs['StartTime'].' </div>
                                                        <div class="col-xs-3 col-sm-3 col-md-3 "> <b>End :</b> '.$jobs['EndTime'].' </div>
                                                </div><br>';

					}

                                echo '</div>
				<div class="ow-server-bottom col-xs-2 col-sm-2 col-md-2">
				<a href="#" data-toggle="modal" id="bmoredetail" class="btn btn-primary" data-target="#moredetail">'.$lang_global['moredetail'].'</a>
				</div>
                            </div>
                        </div>
			<div class="modal fade" id="moredetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  			   <div class="modal-dialog" style="width:1500px;">
			      <div class="modal-content">
	  			<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel"> Détails supplémentaire</h4>
	  			</div>
	  			<div class="modal-body">
		  			<div class="row">';
						mysqli_data_seek($reqjobs, 0);
						while ($jobs = $reqjobs->fetch_assoc()) {
						echo '
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>JobID :</b> '.$jobs['idJobs'].' </div>
							<div class="col-xs-9 col-sm-9 col-md-9 "> <b>JobName :</b> '.$jobs['JobName'].' </div>	
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>UserId :</b> '.$jobs['UserId'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Account :</b> '.$jobs['Account'].' </div>	
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>WCKey :</b> '.$jobs['WCKey'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>GroupId :</b> '.$jobs['GroupId'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Priority :</b> '.$jobs['Priority'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Nice :</b> '.$jobs['Nice'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Requeue :</b> '.$jobs['Requeue'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Restarts :</b> '.$jobs['Restarts'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>BatchFlag :</b> '.$jobs['BatchFlag'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Partition :</b> '.$jobs['Partition'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Qos :</b> '.$jobs['QOS'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Shared :</b> '.$jobs['Shared'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Nombre Noeuds :</b> '.$jobs['NumNodes'].' </div>
                                                        <div class="col-xs-3 col-sm-3 col-md-3 "> <b>Nombre Cpus :</b> '.$jobs['NumCPUs'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Tâche par Cpus :</b> '.$jobs['CPUsTask'].' </div>
                                                        <div class="col-xs-3 col-sm-3 col-md-3 "> <b>Mémoire :</b> '.$jobs['Mem'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Reboot :</b> '.$jobs['Reboot'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>ExitCode :</b> '.$jobs['ExitCode'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>DerivedExitCode :</b> '.$jobs['DerivedExitCode'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>RunTime :</b> '.$jobs['RunTime'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>SubmitTime :</b> '.$jobs['SubmitTime'].' </div>
                                                        <div class="col-xs-3 col-sm-3 col-md-3 "> <b>StartTime :</b> '.$jobs['StartTime'].' </div>
                                                        <div class="col-xs-3 col-sm-3 col-md-3 "> <b>EndTime :</b> '.$jobs['EndTime'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>TimeLimit :</b> '.$jobs['TimeLimit'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>TimeMin :</b> '.$jobs['TimeMin'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>EligibleTime :</b> '.$jobs['EligibleTime'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>PreemptTime :</b> '.$jobs['PreemptTime'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>SuspendTime :</b> '.$jobs['SuspendTime'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>SecsPreSuspend :</b> '.$jobs['SecsPreSuspend'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>AllocNodeSid :</b> '.$jobs['AllocNodeSid'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>ReqNodeList :</b> '.$jobs['ReqNodeList'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>ExcNodeList :</b> '.$jobs['ExcNodeList'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>BatchHost :</b> '.$jobs['BatchHost'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Requeue :</b> '.$jobs['Requeue'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Restarts :</b> '.$jobs['Restarts'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>BatchFlag :</b> '.$jobs['BatchFlag'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>ReqBSCT :</b> '.$jobs['ReqBSCT'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>SocksNode :</b> '.$jobs['SocksNode'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>NtasksPerNBSC :</b> '.$jobs['NtasksPerNBSC'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>CoreSpec :</b> '.$jobs['CoreSpec'].' </div>
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>Nodes :</b> '.$jobs['Nodes'].' </div>
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>NodeList :</b> '.$jobs['NodeList'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>CPUIDs :</b> '.$jobs['CPUIDs'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>MinCPUsNode :</b> '.$jobs['MinCPUsNode'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>MinMemoryCPU :</b> '.$jobs['MinMemoryCPU'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>MinTmpDiskNode :</b> '.$jobs['MinTmpDiskNode'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Features :</b> '.$jobs['Features'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Gres :</b> '.$jobs['Gres'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Contiguous :</b> '.$jobs['Contiguous'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Licenses :</b> '.$jobs['Licenses'].' </div>
							<div class="col-xs-3 col-sm-3 col-md-3 "> <b>Network :</b> '.$jobs['Network'].' </div>
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>Command :</b> '.$jobs['Command'].' </div>
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>WorkDir :</b> '.$jobs['WorkDir'].' </div>
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>StdErr :</b> '.$jobs['StdErr'].' </div>
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>StdIn :</b> '.$jobs['StdIn'].' </div>
							<div class="col-xs-12 col-sm-12 col-md-12 "> <b>StdOut :</b> '.$jobs['StdOut'].' </div>';
						}
					echo '</div>
				</div>
	  			<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  			</div>
			      </div>
  			   </div>
			</div>';


		echo '<div><script>
			tendJOBS = Morris.Line({
				element: "tendJOBS",
  				data: [';
				$ligne = '0';
				while ($onetrend = $reqjobsdet->fetch_assoc()) {
				   if ($ligne % $numdeclrows == 0) //graph 25% des données
				     {
					$a = formattoGBytes($onetrend['MaxVMSize']);
					$b = formattoGBytes($onetrend['MaxRSS']);
					$c = formattoGBytes($onetrend['MaxDiskWrite']);
					$d = formattoGBytes($onetrend['MaxDiskRead']);
					echo ' { y: "'.$onetrend['datetime'].'", a: '.$a.', b: '.$b.', c: '.$c.', d: '.$d.' },';
				     }
				    $ligne++;
				}
    				echo '],
  				xkey: "y",
  				ykeys: ["a","b","c","d"],
  				labels: ["MaxVMSize", "MaxRSS", "MaxDiskWrite", "MaxDiskRead"],
  				parseTime:false,
  				xLabelAngle:80,
  				lineColors: ["#FF69DA","#FFB037","#37FFF9", "#3769FF"],
			});
			
			
		</script></div>';
		echo '<div class="row">
		    <div class="col-md-12">
			<div class="box-content"> 
				<div class="col-md-12 alert alert-info" role="alert" style="top:10px"> <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> Seul 25% de points sont représentés ( 1pts sur '.$numdeclrows.' ).</div>
				<div id="tendJOBS" style="height: 450px;"></div>
			</div>
		    </div>
		</div>';
	

			echo '<div class="row" style="margin-top: 100px;" ><div class="col-md-12">
				<table class="table table-condensed table-hover table-no-bordered" id="jobhistory" data-search="true" data-pagination="true" data-show-export="true" data-sort-name="State" data-show-columns="true" data-sort-order="desc" style="font-size: 12px;word-wrap: break-word;word-break:break-word" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
			<thead>
				<tr>
					<th data-field="datetime" data-align="center">datetime</th>
					<th data-field="stepid" data-align="center">stepid</th>
					<th data-field="MaxVMSize" data-align="center">MaxVMSize</th>
					<th data-visible="false" data-field="MaxVMSizeNode" data-align="center">MaxVMSizeNode</th>
					<th data-visible="false" data-field="MaxVMSizeTask" data-align="center">MaxVMSizeTask</th>
					<th data-field="AveVMSize" data-align="center">AveVMSize</th>
					<th data-field="MaxRSS" data-align="center">MaxRSS</th>
					<th data-visible="false" data-field="MaxRSSNode" data-align="center">MaxRSSNode</th>
					<th data-visible="false" data-field="MaxRSSTask" data-align="center">MaxRSSTask</th>
					<th data-visible="false" data-field="AveRSS">AveRSS</th>
					<th data-visible="false" data-field="MaxPages">MaxPages</th>
					<th data-visible="false" data-field="MaxPagesNode">MaxPagesNode</th>
					<th data-visible="false" data-field="MaxPagesTask">MaxPagesTask</th>
                                        <th data-visible="false" data-field="AvePages">AvePages</th>
                                        <th data-field="MinCPU">MinCPU</th>
                                        <th data-visible="false" data-field="MinCPUNode">MinCPUNode</th>
                                        <th data-visible="false" data-field="MinCPUTask">MinCPUTask</th>
                                        <th data-field="AveCPU">AveCPU</th>
                                        <th data-field="NTasks">NTasks</th>
                                        <th data-field="AveCPUFreq">AveCPUFreq</th>
                                        <th data-field="ReqCPUFreq">ReqCPUFreq</th>
                                        <th data-visible="false" data-field="ConsumedEnergy">ConsumedEnergy</th>
                                        <th data-field="MaxDiskRead">MaxDiskRead</th>
                                        <th data-visible="false" data-field="MaxDiskReadNode">MaxDiskReadNode</th>
                                        <th data-visible="false" data-field="MaxDiskReadTask">MaxDiskReadTask</th>
                                        <th data-field="AveDiskRead">AveDiskRead</th>
                                        <th data-field="MaxDiskWrite">MaxDiskWrite</th>
                                        <th data-visible="false" data-field="MaxDiskWriteNode">MaxDiskWriteNode</th>
                                        <th data-visible="false" data-field="MaxDiskWriteTask">MaxDiskWriteTask</th>
                                        <th data-field="AveDiskWrite">AveDiskWrite</th>
				</tr>
			</thead>
			<tbody>';
			mysqli_data_seek($reqjobsdet, 0);
			while ($datajobsdet = $reqjobsdet->fetch_array())
                                {			

				echo '<tr>
					<td>'.$datajobsdet[2].'</td>
                                        <td>'.$datajobsdet[3].'</td>
                                        <td>'.$datajobsdet[4].'</td>
                                        <td>'.$datajobsdet[5].'</td>
                                        <td>'.$datajobsdet[6].'</td>
                                        <td>'.$datajobsdet[7].'</td>
                                        <td>'.$datajobsdet[8].'</td>
                                        <td>'.$datajobsdet[9].'</td>
                                        <td>'.$datajobsdet[10].'</td>
                                        <td>'.$datajobsdet[11].'</td>
                                        <td>'.$datajobsdet[12].'</td>
                                        <td>'.$datajobsdet[13].'</td>
                                        <td>'.$datajobsdet[14].'</td>
                                        <td>'.$datajobsdet[15].'</td>
                                        <td>'.$datajobsdet[16].'</td>
                                        <td>'.$datajobsdet[17].'</td>
                                        <td>'.$datajobsdet[18].'</td>
                                        <td>'.$datajobsdet[19].'</td>
                                        <td>'.$datajobsdet[20].'</td>
                                        <td>'.$datajobsdet[21].'</td>
                                        <td>'.$datajobsdet[22].'</td>
                                        <td>'.$datajobsdet[23].'</td>
                                        <td>'.$datajobsdet[24].'</td>
                                        <td>'.$datajobsdet[25].'</td>
                                        <td>'.$datajobsdet[26].'</td>
                                        <td>'.$datajobsdet[27].'</td>
                                        <td>'.$datajobsdet[28].'</td>
					<td>'.$datajobsdet[29].'</td>
                                        <td>'.$datajobsdet[30].'</td>
                                        <td>'.$datajobsdet[31].'</td>
				    </tr>';

				}
                        echo '</tbody>
                  </table>
       		</div></div></div>';
	     }


	} elseif ($action == 'uftok') {

		$sqljobsuft="SELECT * from Jobs_Metrics where UserId like '$userid%' and id_clusters = '$clustername' and StartTime > '$from' and EndTime < '$to'"; 
		$reqjobsuft= $mysqli->query($sqljobsuft) or die ('Erreur '.$sqljobsuft.' '.$mysqli->error);

		echo '<div class="row"><div class="col-md-12">
				<table class="table table-condensed table-hover table-no-bordered" id="jobhistory" data-search="true" data-pagination="true" data-show-export="true" data-sort-name="State" data-show-columns="true" data-sort-order="desc" style="font-size: 12px;word-wrap: break-word;word-break:break-word" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
			<thead>
				<tr>
					<th data-field="JobID" data-align="center">JobID</th>
					<th data-field="Jobname" data-align="center">Jobname</th>
					<th data-field="Account" data-align="center">Account</th>
					<th data-field="WCKey" data-align="center">WCKey</th>
					<th data-field="Partition" data-align="center">Partition</th>
					<th data-field="Qos" data-align="center">Qos</th>
					<th data-field="NumNodes" data-align="center">Nombre Noeuds</th>
                                        <th data-field="NumCPUs">Nombre Cpus</th>
                                        <th data-field="CPUsTask">Tâche par Cpus</th>
                                        <th data-field="Mem">Mémoire</th>
                                        <th data-field="Start">Start</th>
                                        <th data-field="End">End</th>
				</tr>
			</thead>
			<tbody>';
		while ($jobsuft = $reqjobsuft->fetch_row()) {
			echo '<tr>
					<td><a class="jobidok" href="#'.$jobsuft[0].'">'.$jobsuft[0].'</a></td>
                                        <td>'.substr(strip_tags($jobsuft[2]), 0, 10).'</td>
                                        <td>'.$jobsuft[7].'</td>
                                        <td>'.$jobsuft[9].'</td>
                                        <td>'.$jobsuft[26].'</td>
                                        <td>'.$jobsuft[8].'</td>
                                        <td>'.$jobsuft[32].'</td>
                                        <td>'.$jobsuft[33].'</td>
                                        <td>'.$jobsuft[34].'</td>
                                        <td>'.$jobsuft[41].'</td>
                                        <td>'.$jobsuft[21].'</td>
                                        <td>'.$jobsuft[22].'</td>
				    </tr>';

				}
                        echo '</tbody>
                  </table>
       		</div></div></div>';

	} else {

		echo '<p>No search init or no result found</p>';
	}

}





/*______________________Affichage onglet jobs______________________*/


function jobs($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqljobs="SELECT * from Collect_Jobs where id_Clusters = '$fclustername' ";
	$jobs= $mysqli->query($sqljobs) or die ('Erreur '.$sqljobs.' '.$mysqli->error);

	$nbjobsrun='0';
	$nbjobspend='0';
	$nbjobsautre='0';

	while ($jobstabexp = $jobs->fetch_row()) 
	{
		switch ($jobstabexp[6]) {
		case 'RUNNING':
		$nbjobsrun++;
		break;
		case 'PENDING':
		$nbjobspend++;
		break;
		default :
		$nbjobsautre++;
		}
		$alljob++;
	}
	$Pourcentagerun =  Pourcentage($nbjobsrun, $alljob);
	$Pourcentagepend =  Pourcentage($nbjobspend, $alljob);
	$Pourcentageother =  Pourcentage($nbjobsautre, $alljob);
	
	echo '
	<div class="col-md-8">
	  <div class="progress-group">
            <span class="progress-text">Running</span>
            <span class="badge" style="margin-bottom: 3px;"><b>'.$nbjobsrun.'</b> / '.$alljob.'</span>
            <div class="progress sm">
                 <div class="progress-bar progress-bar-running" style="width: '.$Pourcentagerun.'%;background-color:#00a65a"></div>
            </div>
          </div>
	<div class="progress-group">
            <span class="progress-text">Pending</span>
            <span class="badge" style="margin-bottom: 3px;"><b>'.$nbjobspend.'</b> / '.$alljob.'</span>
            <div class="progress sm">
                 <div class="progress-bar" style="width: '.$Pourcentagepend.'%;background-color:#1982c8"></div>
            </div>
        </div>
	<div class="progress-group">
            <span class="progress-text">Other</span>
            <span class="badge" style="margin-bottom: 3px;"><b>'.$nbjobsautre.'</b> / '.$alljob.'</span><br>
            <div class="progress sm">
                 <div class="progress-bar progress-bar-completing" style="width: '.$Pourcentageother.'%;background-color:#a94442"></div>
            </div>
          </div>
	</div>
	<div>
		<table class="table table-condensed table-hover table-no-bordered" id="jobsshows" data-search="true" data-detail-view="true" data-detail-formatter="detailFormatter" data-pagination="true" data-show-export="true" data-sort-name="State" data-show-columns="true" data-sort-order="desc" style="font-size: 12px;word-wrap: break-word;word-break:break-word" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
			<thead>
				<tr>
					<th data-field="JobId" data-align="center">JobId</th>
					<th data-field="State" data-align="center">State</th>
					<th data-field="Partition" data-align="center">Partition</th>
					<th data-field="Qos" data-align="center">Qos</th>
					<th data-field="Name" data-align="center">Name</th>
					<th data-field="User" data-align="center">User</th>
					<th data-field="TimeLimit" data-align="center">TimeLimit</th>
					<th data-field="Nodes" data-align="center">Nodes</th>
					<th data-field="Cpus" data-align="center">Cpus</th>
					<th data-visible="false" data-field="Time">Time</th>
					<th data-field="StartTime">StartTime</th>
					<th data-field="EndTime">EndTime</th>
					<th data-visible="false" data-field="Priority">Priority</th>
					<th data-visible="false" data-field="Nodelist">Nodelist</th>
					<th data-visible="false" data-field="NodelistExpand" data-switchable="false">Nodelist Expand</th>			
				</tr>
			</thead>
			<tbody>';
	mysqli_data_seek($jobs, 0);
	while ($jobstabexp = $jobs->fetch_row()) 
	{
		switch ($jobstabexp[6]) {
		case 'RUNNING':
		$tr = '<tr class="running">';
		break;
		case 'PENDING':
		$tr = '<tr class="pending">';
		break;
		default :
		$tr = '<tr class="critical">';
		}
		echo $tr;

		echo '
			<td>'.$jobstabexp[0].'</td>
			<td>'.$jobstabexp[6].'</td>
			<td>'.$jobstabexp[1].'</td>
			<td>'.$jobstabexp[3].'</td>
			<td>'.substr(strip_tags($jobstabexp[4]), 0, 20).'</td>
			<td>'.$jobstabexp[5].'</td>
			<td>'.$jobstabexp[8].'</td>
			<td>'.$jobstabexp[9].'</td>
			<td>'.$jobstabexp[10].'</td>
			<td>'.$jobstabexp[7].'</td>
			<td>'.$jobstabexp[11].'</td>
			<td>'.$jobstabexp[12].'</td>
			<td>'.$jobstabexp[13].'</td>
			<td>'.$jobstabexp[14].'</td>';
		$s_matches = array();
		$varoutput = "";
		if (strstr($jobstabexp[14], "[")) {
			$nnode = explode("[", $jobstabexp[14]);
			preg_match_all("/\[([^\]]*)\]/", $jobstabexp[14], $matches);
			
			if (strstr($matches[1][0], ",")) {
			
				$s_matches = explode(",", $matches[1][0]);
			} else {
				$s_matches[0] = $matches[1][0];
			}
			
			foreach($s_matches as $row) {
				
				if (strstr($row, '-')) {
					$startend = explode("-", $row);
					foreach (range($startend[0],$startend[1]) as $number) {
    						$varoutput .= $nnode[0].$number." ";
					}
					
				} else {
					$varoutput .= $nnode[0].$row." ";			
				}
			}
			
			echo '  <td>'.$varoutput.'</td>';

		} else {

			echo '  <td>'.$jobstabexp[14].'</td>';

		}

		echo '</tr>';
	}
	echo '	</tbody>
		</table>
	</div><br>';
}


/*______________________Affichage onglet reservation______________________*/


function reservations($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlReservations="SELECT * from Reservations where id_Clusters = '$fclustername'";
	$Reservations= $mysqli->query($sqlReservations) or die ('Erreur '.$sqlReservations.' '.$mysqli->error);

	echo '	<div class="table-responsive"> 
				<table class="table table-condensed table-hover table-striped table-no-bordered" id="resshows" style="font-size: 12px;word-wrap: break-word;" data-search="true" data-pagination="true" data-show-export="true" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="15">
					<thead>
						<tr>
							<th data-align="center">ReservationName</th>
							<th data-align="center">StartTime</th>
							<th data-align="center">EndTime</th>
							<th data-align="center">Duration</th>
							<th data-align="center">Nodes</th>
							<th data-align="center">NodeCnt</th>
							<th data-align="center">CoreCnt</th>
							<th data-align="center">Users</th>
							<th data-align="center">State</th>
						</tr>
					</thead>
					<tbody>';
				while ($Reservationstabexp = $Reservations->fetch_row()) 
				{
					echo '	
						<tr>			
							<td>'.$Reservationstabexp[0].'</td>
							<td>'.$Reservationstabexp[2].'</td>
							<td>'.$Reservationstabexp[3].'</td>
							<td>'.$Reservationstabexp[4].'</td>
							<td>'.$Reservationstabexp[5].'</td>
							<td>'.$Reservationstabexp[6].'</td>
							<td>'.$Reservationstabexp[7].'</td>
							<td>'.$Reservationstabexp[8].'</td>
							<td>'.$Reservationstabexp[9].'</td>
						</tr>';
				}
	echo '			</tbody>
				</table>
			</div><br>';
}


/*______________________Affichage onglet File system______________________*/


function FS($fclustername)
{
	$redrawlist="";
	global $mysqli;
	global $lang_global;
	$sqlFSs="SELECT idFilesystems from Filesystems where id_Clusters = '$fclustername' and is_active=1 order by idFilesystems ASC";
	$FSs= $mysqli->query($sqlFSs) or die ('Erreur '.$sqlFSs.' '.$mysqli->error);
	
	stateFS($fclustername);
	
	while ($FS = $FSs->fetch_assoc()) 
	{
		$nomFS=$FS['idFilesystems'];
		$sqlinfoFS="SELECT disponible,utilise,disponible_inode, utilise_inode from Collect_FS where id_Clusters = '$fclustername' and id_Filesystems='$nomFS' order by Timestamp desc limit 1";
		$infoFS= $mysqli->query($sqlinfoFS) or die ('Erreur '.$sqlinfoFS.' '.$mysqli->error);
		
		$infoFS = $infoFS->fetch_assoc();
		
		if ($redrawlist!="")
			$redrawlist=$redrawlist.",";

		$redrawlist=$redrawlist."morrisinfoFS".$nomFS.",morrisinfoFSinode".$nomFS;
		echo '		
			<div class="col-md-6  top-panel-right">
				<div class="box-header">
					<div class="box-name">
						<i class="fa fa-pie-chart"></i>
						<span>'; print $lang_global['usevol']; echo ''.$nomFS.'</span>
					</div>
				</div>
				<div class="box-content"> 
					<div class="monsvg" id="infoFS'.$nomFS.'" style="height:250px"></div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="box-header">
					<div class="box-name">
						<i class="fa fa-pie-chart"></i>
						<span>'; print $lang_global['useio']; echo ''.$nomFS.'</span>
					</div>
				</div>
				<div class="box-content"> 
					<div class="monsvg" id="infoFSinode'.$nomFS.'" style="height:250px"></div>
				</div>
			</div>
		
		<script>
		$( document ).ready(function() {
			setTimeout(function () {
				
		  morrisinfoFS'.$nomFS.'=Morris.Donut({
		  element: "infoFS'.$nomFS.'",
		  data: [
		    {label: "Disp.", value: '.$infoFS['disponible'].'},
		    {label: "Util.", value: '.$infoFS['utilise'].'},
		  ],
		  colors: ["rgb(49, 183, 109)","rgb(25, 130, 200)"],   
		  formatter: function (aSize) { 
			aSize = Math.abs(parseInt(aSize, 10));
			var def = [[1, "ko"], [1024, "Mo"], [1024*1024, "Go"], [1024*1024*1024, "To"], [1024*1024*1024*1024, "Po"], [1024*1024*1024*1024*1024, "Eo"]];
			for(var i=0; i<def.length; i++){
				if(aSize<def[i][0]) return (aSize/def[i-1][0]).toFixed(2)+" "+def[i-1][1];
			}
 		  }		  
		});
		
		  morrisinfoFSinode'.$nomFS.'=Morris.Donut({
		  element: "infoFSinode'.$nomFS.'",
		  data: [
		    {label: "Disp.", value: '.$infoFS['disponible_inode'].'},
		    {label: "Util.", value: '.$infoFS['utilise_inode'].'},
		  ],
		  colors: ["rgb(49, 183, 109)","rgb(25, 130, 200)"],
		});
	}, 50);
		
	});
		</script>
';		
	}
echo '<div id="redrawmorris" morrisredraw="'.$redrawlist.'"></div>';
}


/*_____Sous function onglets File system_____*/


function stateFS($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlnbfs="SELECT count(idFilesystems) as nbfs FROM Filesystems where id_Clusters = '$fclustername' and is_active=1";
	$nbfs= $mysqli->query($sqlnbfs) or die ('Erreur '.$sqlnbfs.' '.$mysqli->error);
	$nbfs = $nbfs->fetch_assoc();
	$sqldashFS = "SELECT id_Filesystems,Dispo,Timestamp,round((utilise/(disponible+utilise)*100),0) as percent FROM Collect_FS where id_Clusters = '$fclustername' order by Timestamp desc, id_Filesystems asc limit $nbfs[nbfs]";
	$dashFS = $mysqli->query($sqldashFS) or die ('Erreur '.$sqldashFS.' '.$mysqli->error);
	
	$larg=12/$nbfs[nbfs];
	
	echo '	<div class="box-header">
				<div class="box-name">
					<i class="fa fa-floppy-o"></i>
					<span>'; print $lang_global['statefs']; echo '</span>
				</div>
			</div>
			<div class="box-content"> 		
	';
	
	while ($stateFS = $dashFS->fetch_assoc()) 
	{
		if ($stateFS['Dispo'] == 0)
		{
			echo '
			<div class="col-md-'.$larg.'">
			<button type="button" data-toggle="tooltip" data-placement="bottom" title="'.$stateFS['id_Filesystems'].'" class="btn btn-danger btn-app-xs pull-right disabled btn-block">'.$stateFS['id_Filesystems'].' <i class="fa fa-warning"></i></button>
			</div>';
		}
		elseif ($stateFS['Dispo'] == 1)
		{
		if ($stateFS['percent'] == 0 ){
			$stateFS['percent'] = 1;
		}
			echo '
			<div class="col-md-'.$larg.'">
			<button type="button" data-toggle="tooltip" data-placement="bottom" title="'.$stateFS['id_Filesystems'].'" class="btn btn-success btn-app-xs pull-right disabled btn-block">'.$stateFS['id_Filesystems'].' <i class="fa fa-check"></i><span class="badge pull-right">'.$stateFS['percent'].' %</span></button>
			</div>';
		}
	}
	echo '</div>';	
}



/*______________________Affichage onglet quota______________________*/


function tabQuota ($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlfsquota="SELECT DISTINCT(id_Filesystems) from Collect_Quota where id_Clusters = '$fclustername'";
	$fsquotas= $mysqli->query($sqlfsquota) or die ('Erreur '.$sqlfsquota.' '.$mysqli->error);
	$data = array();
	echo '
	<ul class="nav nav-tabs" role="tablist" id="quotatab">';

	$i=0;
	while ($fsquotastabexp = $fsquotas->fetch_array()) 
	{
		$data[] = $fsquotastabexp;
		echo '<li role="presentation" ';
		if ($i == 0) {echo 'class="active"';}
		echo '><a href="#'.$fsquotastabexp[0].'" aria-controls="'.$fsquotastabexp[0].'" role="tab" data-toggle="tab">'.$fsquotastabexp[0].'</a></li>';
		$i=$i+1;
	}

	echo '</ul>
		<!-- Tab panes -->
		<div class="tab-content" id="tabquota">';
	$i=0;
	foreach($data as $row)
	{
		echo '<div role="tabpanel" class="tab-pane';
		if ($i == 0) {echo ' active"';}
		echo '" id="'.$row[0].'">';
				Quota($fclustername,$row[0]);
		echo '</div>';
		$i=$i+1;
	}
	echo '</div><br>';
}


/*_____Sous function onglets quota_____*/


function Quota ($fclustername,$fs)
{
	global $mysqli;
	global $lang_global;
	$sqlquota="SELECT * from Collect_Quota where id_Clusters = '$fclustername' and id_Filesystems = '$fs'";
	$quotas= $mysqli->query($sqlquota) or die ('Erreur '.$sqlquota.' '.$mysqli->error);
	echo '<div class="table-responsive">
		<table class="table table-condensed" style="table-layout:fixed; font-size: 12px;word-wrap: break-word; ">
			<thead>
				<tr>
					<th colspan="3">'; print $lang_global['legend']; echo '</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>'; print $lang_global['fillingrate']; echo ' < 95%</td>
					<td class="warning">'; print $lang_global['fillingrate']; echo ' > 95%</td>
					<td class="critical">'; print $lang_global['fillingrate']; echo ' > 99%</td>
				</tr>
			</tbody>
		</table>
		<table class="table table-condensed table-hover table-no-bordered" id="quotashows'.$fs.'" style="font-size: 12px;word-wrap: break-word;" data-search="true" data-pagination="true" data-show-export="true" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="15">
			<thead>
				<tr>
					<th data-sortable="true" data-align="center">'; print $lang_global['users']; echo '</th>
					<th data-sortable="true" data-sorter="spaceSorter" data-align="center">Quota</th>
					<th data-sortable="true" data-sorter="spaceSorter" data-align="center">'; print $lang_global['alloc']; echo '</th>
					<th data-sortable="true" data-sorter="spaceSorter" data-align="center">'; print $lang_global['idle']; echo '</th>
					<th data-sortable="true" data-align="center">'; print $lang_global['Percentusage']; echo '</th>
					<th data-sortable="true" data-align="center">Quota (inode)</th>
					<th data-sortable="true" data-align="center">'; print $lang_global['alloc']; echo ' (inode)</th>
					<th data-sortable="true" data-align="center">'; print $lang_global['idle']; echo ' (inode)</th>
					<th data-sortable="true" data-align="center">'; print $lang_global['Percentusage']; echo ' (inode)</th>
					<th data-sortable="true" data-align="center">'; print $lang_global['quotatype']; echo '</th>
				</tr>
			</thead>
			<tbody>';
			while ($quotastabexp = $quotas->fetch_row()) {
				$quotavol =  Pourcentage($quotastabexp[4], $quotastabexp[3]);
				$quotaio =  Pourcentage($quotastabexp[6], $quotastabexp[7]);
				if ($quotavol >= 99) {
					$tr='<tr class="critcal">';
				} elseif ($quotavol >= 95) {
					$tr='<tr class="warning">';
				} else {
					$tr='<tr>';
				}
			echo '	
				'.$tr.'			
					<td>'.$quotastabexp[2].'</td>
					<td>'.formatBytes($quotastabexp[3]).'</td>
					<td>'.formatBytes($quotastabexp[4]).'</td>
					<td>'.formatBytes($quotastabexp[5]).'</td>
					<td>'.$quotavol.' %</td>
					<td>'.$quotastabexp[6].'</td>
					<td>'.$quotastabexp[7].'</td>
					<td>'.$quotastabexp[8].'</td>
					<td>'.$quotaio.' %</td>
					<td>'.$quotastabexp[9].'</td>
				</tr>';					
			}
		echo '</tbody>
		</table></div>';
}


/*______________________Affichage onglet Utilisateurs______________________*/


function Users ($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqluser="SELECT * from Users where id_Clusters = '$fclustername'";
	$users= $mysqli->query($sqluser) or die ('Erreur '.$sqluser.' '.$mysqli->error);
	$num_users = mysqli_num_rows($users);

	echo '<div class="box-header">
		<div class="box-name">
			<i class="fa fa-bar-chart-o"></i>
				<span>'.$num_users.' '; print $lang_global['users'];echo ' </span>
		</div>
	      </div>
              <div class="table-responsive">
		<table class="table table-condensed table-hover table-striped table-no-bordered" id="usershows" style="font-size: 12px;word-wrap: break-word;" data-search="true" data-detail-view="true" data-detail-formatter="detailFormatteru" data-pagination="true" data-show-columns="true" data-show-export="true" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="20">
			<thead>
				<tr>
					<th data-field="Utilisateur" data-sortable="true" data-align="center">'; print $lang_global['users']; echo '</th>
					<th data-field="Nom" data-sortable="true" data-align="center">'; print $lang_global['name']; echo '</th>
					<th data-field="Uid" data-align="center">Uid</th>
					<th data-field="Home" data-sortable="true" data-align="center">Home</th>
					<th data-field="Email" data-sortable="true" data-align="center">'; print $lang_global['email']; echo '</th>
					<th data-field="employetype" data-sortable="true" data-align="center">'; print $lang_global['typeemploye']; echo '</th>
					<th data-field="GroupeP" data-sortable="true" data-align="center">'; print $lang_global['grpp']; echo '</th>
					<th data-field="GroupeS" data-visible="false" data-sortable="true" data-align="center">'; print $lang_global['grps']; echo '</th>
				</tr>
			</thead>
			<tbody>';
			while ($usertabexp = $users->fetch_row()) {
				echo '<tr>			
					<td>'.$usertabexp[0].'</td>
					<td>'.$usertabexp[3].'</td>
					<td>'.$usertabexp[2].'</td>
					<td>'.$usertabexp[4].'</td>
					<td>'.$usertabexp[5].'</td>
					<td>'.$usertabexp[6].'</td>
					<td>'.$usertabexp[7].'</td>
					<td>'.$usertabexp[8].'</td>
				</tr>';					
			}
		  echo '</tbody>
		</table>
	     </div><br>';
}


/*______________________Affichage onglet partition______________________*/


function Partition($fclustername,$divheight)
{
	$redrawlist="";
	global $mysqli;
	global $lang_global;
	$sqlnbpart="SELECT count(idPartitions) as nbpart FROM Partitions where is_active=1 and id_Clusters='$fclustername'";
	$nbpart= $mysqli->query($sqlnbpart) or die ('Erreur '.$sqlnbpart.' '.$mysqli->error);
	$nbpart = $nbpart->fetch_assoc();
	$sqlpart = "SELECT id_Partitions,Nombre_job_pd,CPU_allocated,CPU_idle,CPU_other FROM Collect_partitions where id_Clusters='$fclustername' order by Timestamp desc limit $nbpart[nbpart]";
	$allpart = $mysqli->query($sqlpart) or die ('Erreur '.$sqlpart.' '.$mysqli->error);
	
	while ($part = $allpart->fetch_assoc()) 
	{
		
	if ($redrawlist!="")
	$redrawlist=$redrawlist.",";

	$redrawlist=$redrawlist."morrispart".$part['id_Partitions'];
	$nompart=$part['id_Partitions'];
	echo '
		<div class="col-lg-6">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-pie-chart"></i>
					<span>'; print $lang_global['usageCPU']; echo ' '; print $lang_global['partition']; echo ' : <b>'.$nompart.'</b></span><span class="pull-right">'; print $lang_global['jobs']; echo ' pending : <span class="badge" style="margin-right:8px">'.$part['Nombre_job_pd'].'</span></span>
				</div>
			</div>
			<div class="box-content">
				<div class="monsvg" id="part'.$part['id_Partitions'].'" style="height:'.$divheight.'px"></div>
			</div>
		</div>
		
		<script>
		$( document ).ready(function() {
			setTimeout(function () { 
			  morrispart'.$nompart.'=Morris.Donut({
				  element: "part'.$part['id_Partitions'].'",
				  data: [
					{label: "CPU Allocated", value: '.$part['CPU_allocated'].'},
					{label: "CPU Idle", value: '.$part['CPU_idle'].'},
					{label: "CPU Other", value: '.$part['CPU_other'].'}
				  ],
				  colors: ["#007bff","#4dbd74","#dc3545"],
			  });
			 }, 50);
		});
		</script>';
	}
	
	echo '<div id="redrawpart" partredraw="'.$redrawlist.'"></div>';
}

/*______________________Affichage onglet tendance jobs ______________________*/

function TendanceJobs($fclustername,$divheight)
{
        global $redrawlist;
        $redrawlist="";

        TendanceJobsok($fclustername,$divheight,30);

	echo '<div id="redrawfs" fsredraw="'.$redrawlist.'"></div>';
}

/*_____Sous function onglets tendance_____*/

function TendanceJobsok($fclustername,$divheight,$nbpoint)
{
        global $redrawlist;
        global $mysqli;
        global $lang_global;
        $sqltrend = "select * from (SELECT avg(CPU_other) as avgother,avg(CPU_allocated) as avgallocated,avg(CPU_idle) as avgidle,DATE_FORMAT(Timestamp, '%Y-%m-%d') as date_jour FROM Collect_Clusters where id_Clusters='$fclustername' group by date_jour desc limit $nbpoint ) as t order by date_jour asc ";
        $trend = $mysqli->query($sqltrend) or die ('Erreur '.$sqltrend.' '.$mysqli->error);

        $redrawlist="morristendCPU".$fclustername;

        echo '
        <script>
                morristendCPU'.$fclustername.'=Morris.Line({
                element: "tendCPU'.$fclustername.'",
                data: [
                ';
                   
                while ($onetrend = $trend->fetch_assoc()) 
                {
                        echo ' { y: "'.$onetrend['date_jour'].'", a: '.$onetrend['avgallocated'].', b: '.$onetrend['avgidle'].', c: '.$onetrend['avgother'].' },'; 
                }
                echo '
                ],
                xkey: "y",
                ykeys: ["a","b","c"],
                labels: ["allocated", "idle", "other"],
                parseTime:false,
                xLabelAngle:80,
                lineColors: ["#1C84C6","#1AB394","#7a92a3"],
                });
        </script>

        <div class="row">
                <div class="col-md-12">
                        <div class="box-header">
                                <div class="box-name">
                                        <i class="fa fa-line-chart"></i>
                                        <span>'; print $lang_global['evolcpu']; echo ' '; print $nbpoint; echo ' '; print $lang_global['days']; echo '</span>
                                </div>
                        </div>
                        <div class="box-content">
                                <div class="monsvg" id="tendCPU'.$fclustername.'" style="height:'.$divheight.'px"></div>
                        </div>
                </div>   
        </div>';                
}


/*______________________Affichage onglet tendance______________________*/


function Tendance($fclustername,$divheight)
{
	global $redrawlist;
	$redrawlist="";
	
	TendanceCPU($fclustername,$divheight,30);
	TendanceNODE($fclustername,$divheight,30);
	TendancetauxNODE($fclustername,$divheight,30);
	
	echo '<div id="redrawfs" fsredraw="'.$redrawlist.'"></div>';
}


/*_____Sous function onglets tendance_____*/

function TendanceCPU($fclustername,$divheight,$nbpoint)
{
	global $redrawlist;
	global $mysqli;
	global $lang_global;
	$sqltrend = "select * from (SELECT avg(CPU_other) as avgother,avg(CPU_allocated) as avgallocated,avg(CPU_idle) as avgidle,DATE_FORMAT(Timestamp, '%Y-%m-%d') as date_jour FROM Collect_Clusters where id_Clusters='$fclustername' group by date_jour desc limit $nbpoint ) as t order by date_jour asc ";
	$trend = $mysqli->query($sqltrend) or die ('Erreur '.$sqltrend.' '.$mysqli->error);

	$redrawlist="morristendCPU".$fclustername;

	echo '
	<script>
	 	morristendCPU'.$fclustername.'=Morris.Line({
  		element: "tendCPU'.$fclustername.'",
  		data: [
  		';
		
		while ($onetrend = $trend->fetch_assoc()) 
		{
			echo ' { y: "'.$onetrend['date_jour'].'", a: '.$onetrend['avgallocated'].', b: '.$onetrend['avgidle'].', c: '.$onetrend['avgother'].' },';
		}
    		echo ' 
     		],
  		xkey: "y",
  		ykeys: ["a","b","c"],
  		labels: ["allocated", "idle", "other"],
  		parseTime:false,
  		xLabelAngle:80,
  		lineColors: ["#1C84C6","#1AB394","#7a92a3"],
		});
	</script>
	
	<div class="row">
		<div class="col-md-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-line-chart"></i>
					<span>'; print $lang_global['evolcpu']; echo ' '; print $nbpoint; echo ' '; print $lang_global['days']; echo '</span>
				</div>
			</div>
			<div class="box-content"> 
				<div class="monsvg" id="tendCPU'.$fclustername.'" style="height:'.$divheight.'px"></div>
			</div>
		</div>
	</div>';
}


/*_____Sous function onglets tendance_____*/


function TendanceNODE($fclustername,$divheight,$nbpoint)
{
	global $redrawlist;
	global $mysqli;
	global $lang_global;
	$sqltrend = "select * from (SELECT avg(other) as avgother,avg(allocated) as avgallocated,avg(idle) as avgidle,DATE_FORMAT(Timestamp, '%Y-%m-%d') as date_jour FROM Collect_nodes where id_Clusters='$fclustername' group by date_jour desc limit $nbpoint) as t order by date_jour asc";
	$trend = $mysqli->query($sqltrend) or die ('Erreur '.$sqltrend.' '.$mysqli->error);

	$redrawlist=$redrawlist.",morristendNODE".$fclustername;

	echo '
	<script>
	 morristendNODE'.$fclustername.'=Morris.Line({
  		element: "tendNODE'.$fclustername.'",
  		data: [
  		';
		while ($onetrend = $trend->fetch_assoc()) 
		{
			echo ' { y: "'.$onetrend['date_jour'].'", a: '.$onetrend['avgallocated'].', b: '.$onetrend['avgidle'].', c: '.$onetrend['avgother'].' },';
		}
    		echo ' 
    		 ],
  		xkey: "y",
  		ykeys: ["a","b","c"],
  		labels: ["allocated", "idle", "other"],
  		parseTime:false,
  		xLabelAngle:80,
  		lineColors: ["#1C84C6","#1AB394","#7a92a3"],
	});
	</script>
	
	<div class="row">
		<div class="col-md-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-line-chart"></i>
					<span>'; print $lang_global['evolnode']; echo ' '; print $nbpoint; echo ' '; print $lang_global['days']; echo '</span>
				</div>
			</div>
			<div class="box-content"> 
				<div class="monsvg" id="tendNODE'.$fclustername.'" style="height:'.$divheight.'px"></div>
			</div>
		</div>
	</div>
	';
}


/*_____Sous function onglets tendance_____*/


function TendancetauxNODE($fclustername,$divheight,$nbpoint)
{
	global $redrawlist;
	global $mysqli;
	global $lang_global;
	$sqltrend = "select * from (SELECT avg(CPU_allocated) as avgallocated,CPU_total,DATE_FORMAT(Timestamp, '%Y-%m-%d') as date_jour FROM Collect_Clusters where id_Clusters='$fclustername' group by date_jour desc limit $nbpoint) as t order by date_jour asc";
	$trend = $mysqli->query($sqltrend) or die ('Erreur '.$sqltrend.' '.$mysqli->error);

	$redrawlist=$redrawlist.",morristendtauxNODE".$fclustername;

	echo '
	<script>
	 morristendtauxNODE'.$fclustername.'=Morris.Line({
  		element: "tendtauxNODE'.$fclustername.'",
  		data: [
  		';
		while ($onetrend = $trend->fetch_assoc()) 
		{
			$val=round(($onetrend['avgallocated']/$onetrend['CPU_total'])*100,0);
			echo ' { y: "'.$onetrend['date_jour'].'", a: '.$val.'},';
		}
   	 echo ' 
   	  ],
  	xkey: "y",
  	ykeys: ["a"],
  	labels: ["tendance utilisation"],
  	parseTime:false,
  	xLabelAngle:80,
  	lineColors: ["#1C84C6"],
  	postUnits:"%",
	});
	</script>
	
	<div class="row">
		<div class="col-md-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-line-chart"></i>
					<span>'; print $lang_global['evolusagecpu']; echo ' '; print $nbpoint; echo ' '; print $lang_global['days']; echo '</span>
				</div>
			</div>
			<div class="box-content"> 
				<div class="monsvg" id="tendtauxNODE'.$fclustername.'" style="height:'.$divheight.'px"></div>
			</div>
		</div>
	</div>
	';
}

/*_____Sous function onglets usage_____*/


function Usage($fclustername)
{
	global $mysqli;
        global $lang_global;	
	$num_semaine = date ('YW');
	$num_lastsemaine = date ('YW');
	$num_last2semaine = date ('YW');

	
	$sqlconsocluster = "SELECT MAX(MaxVMSize), MAX(MaxRSS), MAX(MaxDiskRead), MAX(MaxDiskWrite) from Nodes_Metrics_$fclustername where NUM_week = '$num_semaine'";
        $reqsqlconsocluster = $mysqli->query($sqlconsocluster) or die ('Erreur '.$sqlconsocluster.' '.$mysqli->error);

	echo '<script>
                consocluster = Morris.Bar({
                element: "consocluster",
                data: [';

        while ($conso = $reqsqlconsocluster->fetch_row()) {

                $y = $fclustername;
                $a = formattoGBytes($conso[0]);
                $b = formattoGBytes($conso[1]);
                $c = formattoGBytes($conso[2]);
                $d = formattoGBytes($conso[3]);

                echo '{ y: "'.$y.'", a: '.$a.', b: '.$b.', c: '.$c.', d: '.$d.'},';
        }

        echo '],
                xkey: "y",
                ykeys: ["a", "b", "c", "d"],
                labels: ["MaxVMSize", "MaxRSS", "MaxDiskRead", "MaxDiskWrite"],
                lineColors: ["#1C84C6","#1AB394","#7a92a3","#1AB394"],
                postUnits:" G",
                });
        </script>
        <div class="row">
                <div class="col-md-12">
                        <div class="box-header">
                                <div class="box-name">
                                        <i class="fa fa-line-chart"></i>
                                        <span>'; print $lang_global['consomaxcluster']; echo '</span>
                           </div>
                        </div>
                        <div class="box-content">
                                <div id="consocluster" style="height: 450px;"</div>
                        </div>
                </div>
        </div>';




        $sqldisnode = "SELECT id_Node, MaxVMSize, MaxRSS, MaxDiskRead, MaxDiskWrite from Nodes_Metrics_$fclustername where id_Node != '0' and id_Node is not null and NUM_week = '$num_semaine' order by MaxVMSize desc limit 70";
        $reqsqldisnode = $mysqli->query($sqldisnode) or die ('Erreur '.$sqldisnode.' '.$mysqli->error);

	echo '<script>
		usagenode = Morris.Bar({
		element: "usagenode",
  		data: [';

	while ($node = $reqsqldisnode->fetch_row()) {

		$y = $node[0];
		$a = formattoGBytes($node[1]);
		$b = formattoGBytes($node[2]);
		$c = formattoGBytes($node[3]);
		$d = formattoGBytes($node[4]);
		
		echo '{ y: "'.$y.'", a: '.$a.', b: '.$b.', c: '.$c.', d: '.$d.'},';
	}

	echo '],
  		xkey: "y",
		xLabelAngle:80,
  		ykeys: ["a", "b", "c", "d"],
  		labels: ["MaxVMSize", "MaxRSS", "MaxDiskRead", "MaxDiskWrite"],
		lineColors: ["#1C84C6","#1AB394","#7a92a3","#1AB394"],
		postUnits:" G",
		});
	</script>
	<div class="row">
                <div class="col-md-12">
                        <div class="box-header">
                                <div class="box-name">
                                        <i class="fa fa-line-chart"></i>
                                        <span>'; print $lang_global['evolusagenodeweek']; echo '</span>
                                </div>
                        </div>
                        <div class="box-content"> 
                                <div id="usagenode" style="height: 450px;"</div>
                        </div>
                </div>
        </div>';


}


/*______________________Affichage onglet configuration______________________*/



function Config ($fclustername)
{
	global $lang_global;
	echo '
	<ul class="nav nav-tabs" role="tablist" id="configtab">
           <li role="presentation" class="active"><a href="#confqos" aria-controls="confqos" role="tab" data-toggle="tab">Qos</a></li>
	   <li role="presentation"><a href="#confpart" aria-controls="confpart" role="tab" data-toggle="tab">'; print $lang_global['partition']; echo '</a></li>
	   <li role="presentation"><a href="#configslurm" aria-controls="configslurm" role="tab" data-toggle="tab">'; print $lang_global['config']; echo ' Slurm</a></li>
	   <li role="presentation"><a href="#configwckeys" aria-controls="configwckeys" role="tab" data-toggle="tab"> Wckeys </a></li>
	   <li role="presentation"><a href="#configaccount" aria-controls="configaccount" role="tab" data-toggle="tab"> Account </a></li>
	   <li role="presentation"><a href="#configusers" aria-controls="configusers" role="tab" data-toggle="tab"> Users </a></li>
           <li role="presentation"><a href="#configassoc" aria-controls="configassoc" role="tab" data-toggle="tab"> Assoc </a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content" id="configbatch">
        <div role="tabpanel" class="tab-pane active" id="confqos">';
							ConfQOS($fclustername);
	echo '</div>
	<div role="tabpanel" class="tab-pane" id="confpart">';
							ConfPartition($fclustername);
	echo '</div>
	<div role="tabpanel" class="tab-pane" id="configslurm">';
							ConfigSlurm($fclustername);
	echo '</div>
	<div role="tabpanel" class="tab-pane" id="configwckeys">';
							ConfigWckeys($fclustername);
	echo '</div>
	<div role="tabpanel" class="tab-pane" id="configaccount">';
							ConfigSAccount($fclustername);
	echo '</div>
	<div role="tabpanel" class="tab-pane" id="configusers">';
							ConfigSUsers($fclustername);
	echo '</div>
	<div role="tabpanel" class="tab-pane" id="configassoc">';
							ConfigSAssoc($fclustername);
	echo '</div></div><br>';
}

/*_____Sous function onglets configuration_____*/


function ConfigSAssoc($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlSAssoc="SELECT * from sassoc where id_Clusters = '$fclustername'";
	$SAssoc= $mysqli->query($sqlSAssoc) or die ('Erreur '.$sqlSAssoc.' '.$mysqli->error);

	echo '
	<div class="row">
		<div class="col-md-12">
			<div class="box-content table-responsive"> 
<table class="table table-condensed table-hover table-no-bordered" id="sassocshows" data-search="true" data-pagination="true" data-show-export="true" data-show-columns="true" data-sort-order="desc" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
					<tr>
							<th data-align="center">Account</th>
							<th data-align="center">User</th>
							<th data-align="center">DefaultQOS</th>
							<th data-align="center">Fairshare</th>
							<th data-visible="false" data-align="center">GrpTRESMins</th>
							<th data-visible="false" data-align="center">GrpTRESRunMins</th>
							<th data-align="center">GrpTRES</th>
							<th data-visible="false" data-align="center">GrpJobs</th>
							<th data-visible="false" data-align="center">GrpSubmitJobs</th>
							<th data-visible="false" data-align="center">GrpWall</th>
							<th data-align="center">LFT</th>
							<th data-visible="false" data-align="center">MaxTRESMins</th>
							<th data-visible="false" data-align="center">MaxTRES</th>
							<th data-visible="false" data-align="center">MaxJobs</th>
							<th data-align="center">MaxSubmitJobs</th>
							<th data-align="center">MaxWall</th>
							<th data-align="center">Qos</th>
							<th data-visible="false" data-align="center">ParentID</th>
							<th data-visible="false" data-align="center">ParentName</th>
							<th data-visible="false" data-align="center">Partitions</th>
							<th data-visible="false" data-align="center">RGT</th>

					</tr>
					</thead>
					<tbody>
	';

				while ($SAssocexp = $SAssoc->fetch_array()) 
				{
					echo '	
					<tr>	
						<td>'.$SAssocexp[0].'</td>
						<td>'.$SAssocexp[21].'</td>		
						<td>'.$SAssocexp[2].'</td>
						<td>'.$SAssocexp[3].'</td>
						<td>'.$SAssocexp[4].'</td>
						<td>'.$SAssocexp[5].'</td>
						<td>'.$SAssocexp[6].'</td>
						<td>'.$SAssocexp[7].'</td>
						<td>'.$SAssocexp[8].'</td>
						<td>'.$SAssocexp[9].'</td>
						<td>'.$SAssocexp[10].'</td>
						<td>'.$SAssocexp[11].'</td>
						<td>'.$SAssocexp[12].'</td>
						<td>'.$SAssocexp[13].'</td>
						<td>'.$SAssocexp[14].'</td>
						<td>'.$SAssocexp[15].'</td>
						<td>'.$SAssocexp[16].'</td>
						<td>'.$SAssocexp[17].'</td>
						<td>'.$SAssocexp[18].'</td>
						<td>'.$SAssocexp[19].'</td>
						<td>'.$SAssocexp[20].'</td>
					</tr>';
					
				}
	echo '	</tbody>
		</table>
		</div>
	</div>
	</div><br>';
}

/*_____Sous function onglets configuration_____*/


function ConfigSUsers($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlSUsers="SELECT * from suers where id_Clusters = '$fclustername'";
	$SUsers= $mysqli->query($sqlSUsers) or die ('Erreur '.$sqlSUsers.' '.$mysqli->error);

	echo '
	<div class="row">
		<div class="col-md-12">
			<div class="box-content table-responsive"> 
<table class="table table-condensed table-hover table-no-bordered" id="susershows" data-search="true" data-pagination="true" data-show-export="true" data-show-columns="true" data-sort-order="desc" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
					<tr>
							<th data-align="center">User</th>
							<th data-align="center">AdminLevel</th>
							<th data-align="center">DefaultAccount</th>
							<th data-align="center">Coordinators</th>

					</tr>
					</thead>
					<tbody>
	';

				while ($SUsersexp = $SUsers->fetch_array()) 
				{
					echo '	
					<tr>	
						<td>'.$SUsersexp[4].'</td>		
						<td>'.$SUsersexp[0].'</td>
						<td>'.$SUsersexp[2].'</td>
						<td>'.$SUsersexp[3].'</td>

					</tr>';
					
				}
	echo '	</tbody>
		</table>
		</div>
	</div>
	</div><br>';
}

/*_____Sous function onglets configuration_____*/


function ConfigSAccount($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlSAccount="SELECT * from saccount where id_Clusters = '$fclustername'";
	$SAccount= $mysqli->query($sqlSAccount) or die ('Erreur '.$sqlSAccount.' '.$mysqli->error);

	echo '
	<div class="row">
		<div class="col-md-12">
			<div class="box-content table-responsive"> 
<table class="table table-condensed table-hover table-no-bordered" id="saccountshows" data-search="true" data-pagination="true" data-show-export="true" data-show-columns="true" data-sort-order="desc" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
					<tr>
							<th data-align="center">Account</th>
							<th data-align="center">Description</th>
							<th data-align="center">Organization</th>
							<th data-align="center">Coordinators</th>
							<th data-align="center">RawShares</th>
							<th data-align="center">NormShares</th>
							<th data-align="center">RawUsage</th>
							<th data-align="center">NormUsage</th>
							<th data-align="center">EffectvUsage</th>
							<th data-visible="false" data-align="center">FairShare</th>
							<th data-align="center">LevelFS</th>
							<th data-visible="false" data-align="center">GrpTRESMins</th>
							<th data-align="center">TRESRunMins</th>
					</tr>
					</thead>
					<tbody>
	';

				while ($SAccountexp = $SAccount->fetch_array()) 
				{
					echo '	
					<tr>			
						<td>'.$SAccountexp[0].'</td>
						<td>'.$SAccountexp[2].'</td>
						<td>'.$SAccountexp[3].'</td>
						<td>'.$SAccountexp[4].'</td>
						<td>'.$SAccountexp[5].'</td>
						<td>'.$SAccountexp[6].'</td>
						<td>'.$SAccountexp[7].'</td>
						<td>'.$SAccountexp[8].'</td>
						<td>'.$SAccountexp[9].'</td>
						<td>'.$SAccountexp[10].'</td>
						<td>'.$SAccountexp[11].'</td>
						<td>'.$SAccountexp[12].'</td>
						<td>'.$SAccountexp[13].'</td>
					</tr>';
					
				}
	echo '	</tbody>
		</table>
		</div>
	</div>
	</div><br>';
}


/*_____Sous function onglets configuration_____*/


function ConfQOS($fclustername)
{
        global $mysqli;
        global $lang_global;
        $sqlQOS="SELECT * from QOS where id_Clusters = '$fclustername' and is_active=1";
        $QOS= $mysqli->query($sqlQOS) or die ('Erreur '.$sqlQOS.' '.$mysqli->error);

        echo '
        <div class="row">
                <div class="col-md-12">
                        <div class="table-responsive">

				<table class="table table-condensed table-hover table-no-bordered" id="qosshows" data-search="true" data-pagination="true" data-show-export="true" data-show-columns="true" data-sort-order="desc" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
                            
                                        <thead>
                                        <tr>
                                                        <th data-field="QualityoS" data-align="center">Qos</th>
                                                        <th data-field="Clusterq" data-visible="false" data-align="center">Cluster</th>
                                                        <th data-visible="false" data-field="isactive" data-align="center">Active</th>
                                                        <th data-visible="false" data-field="Flags" data-align="center">Flags</th>
                                                        <th data-visible="false" data-field="GraceTime" data-align="center">GraceTime</th>
                                                        <th data-visible="false" data-field="GrpTMins" data-align="center">GrpTRESMins</th>
                                                        <th data-visible="false" data-field="GrpTRMins" data-align="center">GrpTRESRunMins</th>
                                                        <th data-field="GrpTRES" data-align="center">GrpTRES</th>
							<th data-field="GrpJobs" data-align="center">GrpJobs</th>
							<th data-field="GrpSubmitJobs" data-align="center">GrpSubmitJobs</th>
                                                        <th data-visible="false" data-field="GrpWall" data-align="center">GrpWall</th>
                                                        <th data-visible="false" data-field="IDq" data-align="center">ID</th>
                                                        <th data-visible="false" data-field="MaxTMins" data-align="center">MaxTRESMins</th>
                                                        <th data-visible="false" data-field="MaxTPA" data-align="center">MaxTRESPerAccount</th>
                                                        <th data-visible="false" data-field="MaxTPJ" data-align="center">MaxTRESPerJob</th>
                                                        <th data-visible="false" data-field="MaxTPN" data-align="center">MaxTRESPerNode</th>
                                                        <th data-visible="false" data-field="MaxTPU" data-align="center">MaxTRESPerUser</th>
                                                        <th data-visible="false" data-field="MaxJPA" data-align="center">MaxJobsPerAccount</th>
							<th data-field="MaxJPU" data-align="center">MaxJobsPerUser</th>
							<th data-field="MaxCPJ" data-align="center">MaxCPUsPerJob</th>
							<th data-field="MaxNPJ" data-align="center">MaxNodesPerJob</th>
                                                        <th data-visible="false" data-field="MinTPJ" data-align="center">MinTRESPerJob</th>
                                                        <th data-visible="false" data-field="MaxSJPA" data-align="center">MaxSubmitJobsPerAccount</th>
                                                        <th data-visible="false" data-field="MaxSJPU" data-align="center">MaxSubmitJobsPerUser</th>
							<th data-field="MaxWall" data-align="center">MaxWall</th>
                                                        <th data-visible="false" data-field="Preempt" data-align="center">Preempt</th>
                                                        <th data-visible="false" data-field="PreemptM" data-align="center">PreemptMode</th>
                                                        <th data-field="Priorityq" data-align="center">Priority</th>
                                                        <th data-visible="false" data-field="UsageF" data-align="center">UsageFactor</th>
                                                        <th data-visible="false" data-field="UsageT" data-align="center">UsageThreshold</th>
                                        </tr>
                                        </thead>
                                        <tbody>';

                                while ($QOStabexp = $QOS->fetch_row())
                                {
                                        echo '
                                        <tr>
                                                <td style="text-align:center">'.$QOStabexp[0].'</td>
                                                <td style="text-align:center">'.$QOStabexp[1].'</td>
                                                <td style="text-align:center">'.$QOStabexp[2].'</td>
                                                <td style="text-align:center">'.$QOStabexp[3].'</td>
                                                <td style="text-align:center">'.$QOStabexp[4].'</td>
                                                <td style="text-align:center">'.$QOStabexp[5].'</td>
                                                <td style="text-align:center">'.$QOStabexp[6].'</td>
                                                <td style="text-align:center">'.$QOStabexp[7].'</td>
                                                <td style="text-align:center">'.$QOStabexp[8].'</td>
                                                <td style="text-align:center">'.$QOStabexp[9].'</td>
                                                <td style="text-align:center">'.$QOStabexp[10].'</td>
                                                <td style="text-align:center">'.$QOStabexp[11].'</td>
                                                <td style="text-align:center">'.$QOStabexp[12].'</td>
                                                <td style="text-align:center">'.$QOStabexp[13].'</td>
                                                <td style="text-align:center">'.$QOStabexp[14].'</td>
                                                <td style="text-align:center">'.$QOStabexp[15].'</td>
                                                <td style="text-align:center">'.$QOStabexp[16].'</td>
                                                <td style="text-align:center">'.$QOStabexp[17].'</td>
                                                <td style="text-align:center">'.$QOStabexp[18].'</td>
                                                <td style="text-align:center">'.$QOStabexp[19].'</td>
                                                <td style="text-align:center">'.$QOStabexp[20].'</td>
                                                <td style="text-align:center">'.$QOStabexp[21].'</td>
                                                <td style="text-align:center">'.$QOStabexp[22].'</td>
                                                <td style="text-align:center">'.$QOStabexp[23].'</td>
                                                <td style="text-align:center">'.$QOStabexp[24].'</td>
                                                <td style="text-align:center">'.$QOStabexp[25].'</td>
                                                <td style="text-align:center">'.$QOStabexp[26].'</td>
                                                <td style="text-align:center">'.$QOStabexp[27].'</td>
                                                <td style="text-align:center">'.$QOStabexp[28].'</td>
                                                <td style="text-align:center">'.$QOStabexp[29].'</td>
                                        </tr>';

                                }
        echo '                  </tbody>
                                </table>
                        </div>
                </div>
        </div>';
}

/*_____Sous function onglets configuration_____*/


function ConfPartition($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlPartition="SELECT * from Partitions where id_Clusters = '$fclustername' and is_active=1";
	$Partition= $mysqli->query($sqlPartition) or die ('Erreur '.$sqlPartition.' '.$mysqli->error);

	echo '
	<div class="row">
		<div class="col-md-12">
			<div class="box-content table-responsive"> 
<table class="table table-condensed table-hover table-no-bordered" id="partshows" data-search="true" data-pagination="true" data-show-export="true" data-show-columns="true" data-sort-order="desc" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
					<tr>
							<th data-align="center">Partition</th>
							<th data-align="center">DefaultTime</th>
							<th data-align="center">DefMemPerCPU</th>
							<th data-align="center">Shared</th>
							<th data-align="center">isDefault</th>
							<th data-align="center">State</th>
							<th data-align="center">Hidden</th>
							<th data-align="center">AllowGroups</th>
							<th data-align="center">Nodes</th>
							<th data-align="center">TotalNodes</th>
							<th data-align="center">TotalCPUs</th>
							<th data-visible="false" data-align="center">AllowAccounts</th>
							<th data-visible="false" data-align="center">AllowQos</th>
							<th data-visible="false" data-align="center">AllocNodes</th>
							<th data-visible="false" data-align="center">Qos</th>
							<th data-visible="false" data-align="center">DisableRootJobs</th>
							<th data-visible="false" data-align="center">ExclusiveUser</th>
							<th data-visible="false" data-align="center">GraceTime</th>
							<th data-visible="false" data-align="center">PriorityJobFactor</th>
							<th data-visible="false" data-align="center">PriorityTier</th>
							<th data-visible="false" data-align="center">RootOnly</th>
							<th data-visible="false" data-align="center">ReqResv</th>
							<th data-visible="false" data-align="center">OverSubscribe</th>
							<th data-visible="false" data-align="center">OverTimeLimit</th>
							<th data-visible="false" data-align="center">PreemptMode</th>
							<th data-visible="false" data-align="center">SelectTypeParameters</th>
							<th data-visible="false" data-align="center">DefMemPerNode</th>
							<th data-visible="false" data-align="center">MaxMemPerNode</th>
					</tr>
					</thead>
					<tbody>
	';

				while ($Partitiontabexp = $Partition->fetch_array()) 
				{
					echo '	
					<tr>			
						<td>'.$Partitiontabexp[0].'</td>
						<td>'.$Partitiontabexp[2].'</td>
						<td>'.$Partitiontabexp[3].'</td>
						<td>'.$Partitiontabexp[4].'</td>
						<td>'.$Partitiontabexp[5].'</td>
						<td>'.$Partitiontabexp[6].'</td>
						<td>'.$Partitiontabexp[7].'</td>
						<td>'.$Partitiontabexp[8].'</td>
						<td>'.$Partitiontabexp[9].'</td>
						<td>'.$Partitiontabexp[10].'</td>
						<td>'.$Partitiontabexp[11].'</td>
						<td>'.$Partitiontabexp[12].'</td>
						<td>'.$Partitiontabexp[13].'</td>
						<td>'.$Partitiontabexp[14].'</td>
						<td>'.$Partitiontabexp[15].'</td>
						<td>'.$Partitiontabexp[16].'</td>
						<td>'.$Partitiontabexp[17].'</td>
						<td>'.$Partitiontabexp[18].'</td>
						<td>'.$Partitiontabexp[19].'</td>
						<td>'.$Partitiontabexp[20].'</td>
						<td>'.$Partitiontabexp[21].'</td>
						<td>'.$Partitiontabexp[22].'</td>
						<td>'.$Partitiontabexp[23].'</td>
						<td>'.$Partitiontabexp[24].'</td>
						<td>'.$Partitiontabexp[25].'</td>
						<td>'.$Partitiontabexp[26].'</td>
						<td>'.$Partitiontabexp[27].'</td>
						<td>'.$Partitiontabexp[28].'</td>
					</tr>';
					
				}
	echo '	</tbody>
		</table>
		</div>
	</div>
	</div><br>';
}


/*_____Sous function onglets configuration_____*/


function ConfigSlurm ($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlConfSlurm="SELECT config from Clusters where idClusters = '$fclustername'";
	$ConfSlurm= $mysqli->query($sqlConfSlurm) or die ('Erreur '.$sqlConfSlurm.' '.$mysqli->error);

	echo '
	<div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12">
			<div class="table-responsive"> 
				<table class="table table-condensed table-hover table-striped table-no-bordered" id="confshows" style="font-size: 12px;word-wrap: break-word;" data-search="true" data-pagination="true" data-show-export="true" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
					<tr>
						<th data-align="center">'; print $lang_global['params']; echo '</th>
						<th data-align="center">'; print $lang_global['value']; echo '</th>
					</tr>
					</thead>
					<tbody>';
					$ConfSlurm = $ConfSlurm->fetch_assoc();
					$ConfSlurm = nl2br($ConfSlurm['config']);
					$ligne = preg_split("/[\n]+/", $ConfSlurm);
					foreach( $ligne as $row => $value ) {
						if (strstr($value, "=")) {
							$value = explode("=", $value);
							echo '	
							<tr>
								<td>'.$value[0].'</td><td>'.$value[1].'</td>
							</tr>';
						} else {
							echo '	
							<tr>
								<td colspan="2">'.$value.'</td>
							</tr>';

						}
					}
					
	echo '	</tbody>
		</table>
		</div>
	</div>
	</div>';
}

/*_____Sous function onglets configuration_____*/

function ConfigWckeys($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlWckeys="SELECT * from WCkeys where id_Clusters = '$fclustername'";
	$Wckeys= $mysqli->query($sqlWckeys) or die ('Erreur '.$sqlWckeys.' '.$mysqli->error);

	echo '
	<div class="row">
		<div class="col-md-12">
			<div class="box-content table-responsive">

				<table class="table table-condensed table-hover table-striped table-no-bordered" id="wckeysshows" style="font-size: 12px;word-wrap: break-word;" data-search="true" data-pagination="true" data-show-export="true" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
					<tr>
							<th style="text-align:center">Wckeys</th>
					</tr>
					</thead>
					<tbody>';

				while ($Wckeystabexp = $Wckeys->fetch_array()) 
				{
					echo '	
					<tr>			
						<td style="text-align:center">'.$Wckeystabexp[0].'</td>
					</tr>';
					
				}
	echo '			</tbody>
				</table>
			</div>
		</div>
	</div>';
}



/*______________________Affichage onglet Materiel______________________*/


function Materiel ($fclustername)
{
	global $lang_global;
	echo '
	<ul class="nav nav-tabs" role="tablist" id="mattab">
    	   <li role="presentation" class="active"><a href="#Resume" aria-controls="Resume" role="tab" data-toggle="tab">'; print $lang_global['resume']; echo '</a></li>
    	   <li role="presentation"><a href="#Noeuds" aria-controls="Noeuds" role="tab" data-toggle="tab">'; print $lang_global['node']; echo '</a></li>
    	   <li role="presentation"><a href="#Switchs" aria-controls="Switchs" role="tab" data-toggle="tab">'; print $lang_global['switch']; echo '</a></li>
  	</ul>

  	<!-- Tab panes -->
  	<div class="tab-content" id="tabmateriel">
    	   <div role="tabpanel" class="tab-pane active" id="Resume">';
							Noeuds($fclustername);
	echo '</div>
    	   <div role="tabpanel" class="tab-pane" id="Noeuds">';
							TabNoeuds($fclustername);
	echo '</div>
    	   <div role="tabpanel" class="tab-pane" id="Switchs">';
							TabSwitchs($fclustername);
	echo '</div>
        </div>';
}


/*_____Sous function onglets Materiel_____*/


function Noeuds($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlNoeuds="SELECT replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(idNoeuds,'0',''),'1',''),'2',''),'3',''),'4',''),'5',''),'6',''),'7',''),'8',''),'9','') as type,RealMemory,Sockets,CoresPerSocket,count(*) as nb FROM Noeuds where id_Clusters='$fclustername' and TypeNode = 'Compute' group by type,RealMemory,Sockets,CoresPerSocket";
	$Noeuds= $mysqli->query($sqlNoeuds) or die ('Erreur '.$sqlNoeuds.' '.$mysqli->error);
	echo '<div class="row col-sm-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-table"></i>
					<span>'; print $lang_global['descnc']; echo '</span>
				</div>
			</div>
			<div class="box-content">';
	while ($LigneNoeuds = $Noeuds->fetch_assoc()) 
	{
		echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
				<div class="box box-pricing">
					<div class="box-header">
						<div class="box-name">
							<h4>'.$LigneNoeuds['type'].'</h4>
						</div>
					</div>
					<div class="box-content no-padding">
						<div class="row-fluid centered">
							<div class="col-sm-12">'; print $lang_global['memory']; echo ' : '.$LigneNoeuds['RealMemory'].'</div>
							<div class="col-sm-12">'; print $lang_global['sockt']; echo ' : '.$LigneNoeuds['Sockets'].'</div>
							<div class="col-sm-12">'; print $lang_global['cps']; echo ' : '.$LigneNoeuds['CoresPerSocket'].'</div>
							<div class="clearfix"></div>
						</div>
						<div class="row-fluid bg-default">
							<div class="col-xs-8">'; print $lang_global['qty']; echo ' :</div>
							<div class="col-xs-4">
								<span class="badge">'.$LigneNoeuds['nb'].'</span>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		';
	}
	
	echo '</div></div>
	<div class="row col-sm-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-table"></i>
					<span>'; print $lang_global['descns']; echo '</span>
				</div>
			</div>
			<div class="box-content"> ';
	
	$sqlNoeuds="SELECT  replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(idNoeuds,'0',''),'1',''),'2',''),'3',''),'4',''),'5',''),'6',''),'7',''),'8',''),'9','') as type,count(*) as nb FROM Noeuds where id_Clusters='$fclustername' and TypeNode = 'Service' group by type";
	$Noeuds= $mysqli->query($sqlNoeuds) or die ('Erreur '.$sqlNoeuds.' '.$mysqli->error);
	while ($LigneNoeuds = $Noeuds->fetch_assoc()) 
	{
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
				<div class="box box-pricing">
					<div class="box-header">
						<div class="box-name">
							<h4>'.$LigneNoeuds['type'].'</h4>
						</div>
					</div>
					<div class="box-content no-padding">
						<div class="row-fluid bg-default">
							<div class="col-xs-8">'; print $lang_global['qty']; echo ' :</div>
							<div class="col-xs-4">
								<span class="badge">'.$LigneNoeuds['nb'].'</span>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		';
	}
	
	echo '</div></div><div class="row col-sm-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-table"></i>
					<span>'; print $lang_global['descsi']; echo '</span>
				</div>
			</div>
			<div class="box-content"> ';
		
	$sqlSW="SELECT count(*) as nb FROM Switch where id_Clusters='$fclustername' and level=1";
	$SW= $mysqli->query($sqlSW) or die ('Erreur '.$sqlSW.' '.$mysqli->error);
	$LigneSW = $SW->fetch_assoc();
	echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
				<div class="box box-pricing">
					<div class="box-header">
						<div class="box-name">
							<h4>Switch IB/OPA</h4>
						</div>
					</div>
					<div class="box-content no-padding">
						<div class="row-fluid bg-default">
							<div class="col-xs-8">'; print $lang_global['qty']; echo ' :</div>
							<div class="col-xs-4">
								<span class="badge">'.$LigneSW['nb'].'</span>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		';
		
	$sqlTopSW="SELECT count(*) as nb FROM Switch where id_Clusters='$fclustername' and level>1";
	$TopSW= $mysqli->query($sqlTopSW) or die ('Erreur '.$sqlTopSW.' '.$mysqli->error);
	$LigneTopSW = $TopSW->fetch_assoc();
		echo '
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
				<div class="box box-pricing">
					<div class="box-header">
						<div class="box-name">
							<h4>Core Switch IB/OPA</h4>
						</div>
					</div>
					<div class="box-content no-padding">
						<div class="row-fluid bg-default">
							<div class="col-xs-8">'; print $lang_global['qty']; echo ' :</div>
							<div class="col-xs-4">
								<span class="badge">'.$LigneTopSW['nb'].'</span>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		';
	
	echo '</div></div><br>';
}


/*_____Sous function onglets Materiel_____*/


function TabNoeuds ($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlNoeuds="SELECT * from Noeuds where id_Clusters = '$fclustername'";
	$Noeuds= $mysqli->query($sqlNoeuds) or die ('Erreur '.$sqlNoeuds.' '.$mysqli->error);

	echo '
	<div class="row">
		<div class="col-md-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-table"></i>
					<span>'; print $lang_global['descnc']; echo '</span>
				</div>
			</div>
			<div class="box-content table-responsive">
				<table class="table table-condensed table-hover table-striped table-no-bordered" id="nodeshows" style="font-size: 12px;word-wrap: break-word;" data-search="true" data-pagination="true" data-show-export="true" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
						<tr>
							<th data-align="center">Nom</th>	
							<th data-align="center">Real Memory</th>
							<th data-align="center">FreqMemory</th>
							<th data-align="center">Sockets</th>
							<th data-align="center">CoresPerSocket</th>
							<th data-align="center">Type CPU</th>
							<th data-align="center">Product Name</th>
							<th data-align="center">Product Type</th>
							<th data-align="center">Product Serial</th>
							<th data-align="center">Os</th>
							<th data-align="center">Type Machine</th>
							<th data-align="center">Switch</th>
						</tr>
					</thead>
				<tbody>';
				while ($Noeudstabexp = $Noeuds->fetch_array()) 
				{
					echo '	
					<tr>			
						<td>'.$Noeudstabexp[0].'</td>
						<td>'.$Noeudstabexp[3].' Mo</td>
						<td>'.$Noeudstabexp[4].' MHz</td>
						<td>'.$Noeudstabexp[5].'</td>
						<td>'.$Noeudstabexp[6].'</td>
						<td>'.$Noeudstabexp[10].'</td>
						<td>'.$Noeudstabexp[9].'</td>
						<td>'.$Noeudstabexp[8].'</td>
						<td>'.$Noeudstabexp[7].'</td>
						<td>'.$Noeudstabexp[11].'</td>
						<td>'.$Noeudstabexp[12].'</td>
						<td>'.$Noeudstabexp[2].'</td>
					</tr>';					
				}
	echo '		</tbody>
			</table>
		</div>
		</div>
	</div><br>';
}


/*_____Sous function onglets Materiel_____*/


function TabSwitchs ($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlSwitchs="SELECT * from Liens where id_Clusters = '$fclustername'";
	$Switchs= $mysqli->query($sqlSwitchs) or die ('Erreur '.$sqlSwitchs.' '.$mysqli->error);

	echo '
	<div class="row">
		<div class="col-md-12">
			<div class="box-header">
				<div class="box-name">
					<i class="fa fa-table"></i>
					<span>'; print $lang_global['descsi']; echo '</span>
				</div>
			</div>
			<div class="box-content table-responsive"> 
				<table class="table table-condensed table-hover table-striped table-no-bordered" id="swshows" style="font-size: 12px;word-wrap: break-word;" data-search="true" data-pagination="true" data-show-export="true" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="17">
					<thead>
					<tr>
							<th style="text-align:center">'; print $lang_global['name']; echo '</th>
							<th style="text-align:center">Switch Core</th>
					</tr>
					</thead>
					<tbody>
	';

				while ($Switchstabexp = $Switchs->fetch_array()) 
				{
					echo '	
					<tr>			
						<td style="text-align:center">'.$Switchstabexp[0].'</td>
						<td style="text-align:center">'.$Switchstabexp[1].'</td>
					</tr>';
				}
	echo '	</tbody>
		</table>
		</div>
	</div>
	</div><br>';
}



/*--------------------------------------------------------------------------------------
			Function page network_ib.php
----------------------------------------------------------------------------------------*/

/*____________Generation du schéma reseau InfiniBand____________*/


function Topology ($fclustername)
{
	global $mysqli;
	global $lang_global;
	$sqlnode="SELECT idNoeuds,id_Switch from Noeuds where id_Clusters = '$fclustername' and id_Switch IS NOT NULL order by id_Switch ASC";
	$node= $mysqli->query($sqlnode) or die ('Erreur '.$sqlnode.' '.$mysqli->error);
	$sqlswitch="SELECT idSwitch,level from Switch where id_Clusters = '$fclustername'";
	$switch= $mysqli->query($sqlswitch) or die ('Erreur '.$sqlswitch.' '.$mysqli->error);
	$sqllien="SELECT source,destination from Liens where id_Clusters = '$fclustername'";
	$lien= $mysqli->query($sqllien) or die ('Erreur '.$sqllien.' '.$mysqli->error);
	$sqlnbnode="SELECT level,count(level) FROM `Switch` WHERE `id_Clusters` = '$fclustername' group by level";
	$nbnode= $mysqli->query($sqlnbnode) or die ('Erreur '.$sqlnbnode.' '.$mysqli->error);
	$data = array();

echo '
<div class="row">
		<div class="col-xs-12" id="mynetwork'.$fclustername.'" style="height:700px">
		</div>
</div>


<script type="text/javascript">
    // create an array with nodes
    var nodes = new vis.DataSet([ ';

	$i=100;
	$position= array();
	$savenode=null;
	$nbnodepersw=0;
    while ($onenode = $node->fetch_assoc()) 
	{	
		echo '{id: "'.$onenode['idNoeuds'].'", label: "'.$onenode['idNoeuds'].'",level: 0, group: "nodes", parent: "'.$onenode['id_Switch'].'", x: '.$i.'},
		';
		if ($nbnode==0) {
			$savenode=$onenode['id_Switch'];
		}
		
		if ($onenode['id_Switch']==$savenode) {
			$position[$onenode['id_Switch']]=$position[$onenode['id_Switch']]+$i;
			$nbnodepersw=$nbnodepersw+1;
		}
		else {
			$position[$savenode]=$position[$savenode]/$nbnodepersw;
			$position[$onenode['id_Switch']]=$i;
			$savenode=$onenode['id_Switch'];
			$nbnodepersw=1;
		}
		$data[] = $onenode;
		$i=$i+100;
	}

	$position[$savenode]=$position[$savenode]/$nbnodepersw;
	
	$milieu=$i/2;
	$ecart = array();
	$pos= array();
	$espacelvl=0;
    while ($ligne = $nbnode->fetch_array()) 
	{
		$ecart[$ligne[0]]=$i/$ligne[1];
		$pos[$ligne[0]]=$milieu-($ecart[$ligne[0]]*($ligne[1]/2-0.5));
		if ($ligne[0]==1) {
			$espacelvl=$ligne[1]*100;
			if ($espacelvl<500) {
				$espacelvl=500;
			}
		}
	}

	$savelvlsw=null;
	while ($oneswitch = $switch->fetch_assoc()) 
	{
		if ($oneswitch['level']==1) {
			$j=$position[$oneswitch['idSwitch']];
		}
		else
		{
			if ($oneswitch['level']!=$savelvlsw) {
				$j=$pos[$oneswitch['level']];
				$savelvlsw=$oneswitch['level'];
			}
			else {
				$j=$j+$ecart[$oneswitch['level']];
			}
		}
		
		echo '{id: "'.$oneswitch['idSwitch'].'", label: "'.$oneswitch['idSwitch'].'", level: '.$oneswitch['level'].', group: "switchs", x: '.$j.'},
		';
		
	}
	
    echo ']);

    // create an array with edges
    var edges = new vis.DataSet([  ';
    foreach($data as $row)
	{
		echo '{from: "'.$row['idNoeuds'].'", to: "'.$row['id_Switch'].'"},
		';
	}
	 while ($onelien = $lien->fetch_assoc()) 
	{
		echo '{from: "'.$onelien['source'].'", to: "'.$onelien['destination'].'"},
		';
	}
    echo ']);

    // create a network
    var container = document.getElementById("mynetwork'.$fclustername.'");

    // provide the data in the vis format
    var data = {
        nodes: nodes,
        edges: edges
    };
    var options = {
                edges : {
					width : 5,
					selectionWidth : 5,
					color: {
						color:"#2B7CE9",
						highlight:"#2BE939",
					},
				},
                interaction:{
					dragNodes: false,
					dragView: true,
					zoomView: true,
					navigationButtons: true,
				},
				nodes : {
					font : {
						strokeWidth: 20,
						strokeColor: "#FFFFFF"
					},
				},
				groups:{
					switchs:{
						image:"img/SWIB.png",
						shape: "image",
						size: 40,
						font : {size : 75},
						fixed : {x:true},
					},
					nodes:{
						image:"img/Server_1.png",
						shape: "image",
						size: 40,
						fixed : {x:true},
						font : {size : 20},
					},
					cluster:{
						image:"img/Servercluster.png",
						shape: "image",
						size: 70,
						level : 0,
						fixed : {x:true},
						font : {size : 100},
					},
				},
				physics:{
						enabled : false,
				},
				layout: {
					hierarchical : {
						enabled : true,
						direction: "DU",
						levelSeparation:'.$espacelvl.',
					},
				},
			};

    // initialize your network!
    var network = new vis.Network(container, data, options);

    function docluster (comparevalue){
		var clusterOptionsByData = {
		  joinCondition:function(nodeOptions) {
			return nodeOptions.parent === comparevalue;
		  },
		  processProperties: function(clusterOptions, childNodes) {
			clusterOptions.label = "[" + childNodes.length + "]";
			var pos = 0;
			var title = " ";
			for (var i = 0; i < childNodes.length; i++) {
				pos += childNodes[i].x;
				title += childNodes[i].label+" ";
			}
			clusterOptions.x=pos/childNodes.length;
			clusterOptions.title=title;
			return clusterOptions;
		  },
		  clusterNodeProperties: {group:"cluster"}
	  }
	  network.clustering.cluster(clusterOptionsByData);
	}
	
	// clustering !
	
        ';
       /* $tempo=20;
		$datauniq=array_unique($data, SORT_REGULAR);
		foreach($datauniq as $row)
		{
			echo 'setTimeout(function () { docluster("'.$row['id_Switch'].'") }, '.$tempo.');
			';
			$tempo=$tempo+20;
		}*/

		/*
		// clustering !
	setTimeout(function () {
        ';
		$datauniq=array_unique($data, SORT_REGULAR);
		foreach($datauniq as $row)
		{
			echo 'docluster("'.$row['id_Switch'].'");
			';
		}
		echo '
	}, 50);*/
	
		echo '

	// if we click on a node, we want to open it up!
        network.on("selectNode", function (params) {
        if (params.nodes.length == 1) {
            if (network.isCluster(params.nodes[0]) == true) {
				var clusterOpenOptions = {
					releaseFunction: function releaseFunction (clusterPosition, containedNodesPositions) {
						return containedNodesPositions;
					}
				}
                network.openCluster(params.nodes[0],clusterOpenOptions);
            }
            else
            {
				if (network.body.nodes[params.nodes[0]].options.parent) {
					docluster (network.body.nodes[params.nodes[0]].options.parent);
				};
			}
        }
    });
    
	 network.once("stabilized", function (params) {
	        setTimeout(function () {
			$("#"+"dashboard-loader").css("visibility", "hidden").css("position", "absolute");
    			$("#"+"dashboard-Topology").css("visibility", "visible").css("position", "relative");
    			network.fit();
		}, 500);
		
	 });
	
	</script>';
}

?>
