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

<?php

function usersclusters()
{
	global $mysqli;
	global $lang_global;
	$sqlusersclusters="SELECT idUsers, uid, nom, home, email, employetype, grp_principale, grp_secondary from Users, Clusters where Users.id_clusters = Clusters.idclusters and Clusters.is_active = '1' GROUP BY idUsers ORDER BY idUsers ASC";
	$usersclusters = $mysqli->query($sqlusersclusters) or die ('Erreur '.$sqlusersclusters.' '.$mysqli->error);
	$sqlnbclusters="SELECT distinct(id_Clusters) as distclusters from Users, Clusters where Users.id_clusters = Clusters.idclusters and Clusters.is_active = '1' order by distclusters";
	$nbclusters = $mysqli->query($sqlnbclusters) or die ('Erreur '.$sqlnbclusters.' '.$mysqli->error);
	
	echo '
	<div class="row">
		<div class="col-xs-12">
			<div class="table-responsive">
				<table class="table table-condensed table-hover table-no-bordered" id="usercluster" data-show-columns="true" data-search="true" data-detail-view="true" data-detail-formatter="detailFormatter" data-pagination="true" data-show-export="true" style="font-size: 12px;word-wrap: break-word;word-break:break-word" data-page-list="[30,60,120,240,480,1000,2000,5000,10000]" data-page-size="20">
				<thead>
				<tr>
					<th data-sortable="true" data-field="idUsers" data-align="center">'; print $lang_global['users']; echo '</th>
					<th data-sortable="true" data-field="nom" data-align="center">'; print $lang_global['name']; echo '</th>
					<th data-sortable="true" data-field="uid" data-align="center">Uid</th>
					<th data-sortable="true" data-field="home" data-align="center">Home</th>
					<th data-sortable="true" data-visible="false" data-field="email">'; print $lang_global['email']; echo '</th>
					<th data-sortable="true" data-visible="false" data-field="employetype">'; print $lang_global['typeemploye']; echo 'Type employe</th>
					<th data-sortable="true" data-visible="false" data-field="GroupeP">'; print $lang_global['grpp']; echo 'Groupe Principale</th>
					<th data-sortable="true" data-visible="false" data-field="GroupeS">'; print $lang_global['grps']; echo 'Groupe Secondaire</th>';
		
				while ($distclusters = $nbclusters->fetch_array())
				{
					echo '<th data-sortable="true" data-field="'.$distclusters['distclusters'].'" data-align="center">'.$distclusters['distclusters'].'</th>';
					$cluster.=$distclusters['distclusters'].'-';
				}
				$cluster=rtrim($cluster, "-");
				$listcluster = explode("-", $cluster);
				$nb_cluster=sizeof($listcluster);
	echo '					
				</tr>
				</thead>
				<tbody>';
	
				while ($users = $usersclusters->fetch_array())
				{
					$sqlclusteruser="SELECT id_Clusters from Users where idUsers = '".$users['idUsers']."' order by id_Clusters";
					$clusteruser = $mysqli->query($sqlclusteruser) or die ('Erreur '.$sqlclusteruser.' '.$mysqli->error);
					$datausers=array();
					while ($data = $clusteruser->fetch_assoc())
					{
						$datausers[] = $data['id_Clusters'];		
					}
					echo '<tr>';
					echo '<td>'.$users['idUsers'].'</td>';
					echo '<td>'.$users['nom'].'</td>';
					echo '<td>'.$users['uid'].'</td>';
					echo '<td>'.$users['home'].'</td>';
					echo '<td>'.$users['email'].'</td>';
					echo '<td>'.$users['employetype'].'</td>';
					echo '<td>'.$users['grp_principale'].'</td>';
					echo '<td>'.$users['grp_secondary'].'</td>';
					
					$i=0;
					while ($i < $nb_cluster){
						if(in_array($listcluster[$i],$datausers)) {
							echo '<td class="ok">oui</td>';
						} else {
							echo '<td class="critical">non</td>';
						}
					$i++;
					}
					echo '</tr>';
				}
	echo '			
				</tbody>
				</table>
			</div>
		</div>
	</div>';
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
				<li><a href="#"><?php print $lang_global['users']; ?></a></li>
			</ol>
			<h5 class="maj pull-right"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>	
		</div>
	</div>
	<!--End Breadcrumb-->
	<!--Start Dashboard 1-->
	<!--div id="dashboard-header" class="row">
		<div class="col-xs-12 col-sm-7">
			<h3><?php print $lang_global['users']; ?></h3>
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
			<!--Start Utilization Tab 1-->
			<div class="row" style="visibility: visible; position: relative;">
				<div class="col-xs-12 col-sm-12 col-md-12">
				<?php
					usersclusters();
				?><br>
				</div>
			</div>
		<div class="clearfix"></div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {	
	// Make all JS-activity for dashboard
	$('#usercluster').bootstrapTable(); // init via javascript
	$("[data-toggle=popover]").popover({ html : true});
});

function detailFormatter(index, row) {
    return ' 	<div class=col-md-4> \
			Email : <b>'+row.email+'</b> \
		</div> \
		<div class=col-md-8> \
			Type employe : <b>'+row.employetype+'</b> \
		</div> \
		<div class=col-md-4> \
			Groupe Principale : <b>'+row.GroupeP+'</b> \
		</div> \
		<div class=col-md-12> \
			Groupes secondaire : <b>'+row.GroupeS+'</b> \
		</div> \
		';
}

</script>

<?php
} else {
	header('Location: ../index.php');
}
?>
