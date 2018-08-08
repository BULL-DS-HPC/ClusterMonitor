
<?php 

$configfile = './include/config.php';

if (!file_exists($configfile)) {
    header('Location: ./include/install/install.php');
}

require('./include/config.php');
include('./langs/'.$lang.'.lang');

$mysqli = new mysqli($hostmysql, $loginmysql, $passmysql, $dbmysql);
$mysqli->set_charset("utf8");
if ($mysqli->connect_error) {
        die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
}
session_start();

$sqlclusteractive = "select * from Clusters where is_active=1";
$clusteractive = $mysqli->query($sqlclusteractive) or die ('Erreur '.$sqlclusteractive.' '.$mysqli->error);

$sqlclusterinactive = "select idClusters from Clusters where is_active=0";
$clusterinactive = $mysqli->query($sqlclusterinactive) or die ('Erreur '.$sqlclusterinactive.' '.$mysqli->error);

$sqlconfigCM = "select * from Config";
$reqsqlconfigCM = $mysqli->query($sqlconfigCM) or die ('Erreur '.$sqlconfigCM.' '.$mysqli->error);
while ($params = $reqsqlconfigCM->fetch_row()) {
      $langs = $params[1];
      $retcc = $params[2];
      $retcfr = $params[3];
      $retcfs = $params[4];
      $retcn = $params[5];
      $retcp = $params[6];
      $retjm = $params[7];
}

?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title><?php print $lang_global['Cluster_Monitor']; ?></title>
		<meta name="description" content="description">
		<meta name="author" content="<?php echo $author ; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style type="text/css" title="currentStyle">
       			@import "plugins/bootstrap/bootstrap.min.css";
       			@import "plugins/jquery-ui/jquery-ui.min.css";
       			@import "css/font-awesome.min.css";
       			@import "fonts/open-sans-v13-latin/open-sans.css";
       			@import "css/style_black.min.css";
       			@import "plugins/morris.js-0.5.1/morris.css";
       			@import "plugins/bootstrap-table/bootstrap-table.min.css";
       			@import "plugins/bootstrap-switch/css/bootstrap-switch.min.css";
			@import "plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css";
       			@import "plugins/visjs/dist/vis.min.css";
		</style>
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
				<script src="js/html5shiv.js"></script>
				<script src="js/respond.min.js"></script>
		<![endif]-->
	</head>
<body>
<!--Start Header-->
<header class="navbar">
	<div class="container-fluid expanded-panel">
		<div class="row">
			<div id="logo" class="col-xs-12 col-sm-12 col-md-2">
				<a href="index.php"><?php print $lang_global['Cluster_Monitor']; ?></a>
			</div>
			<div id="top-panel" class="col-xs-12 col-sm-12 col-md-10">
				<div class="row">
					<div class="col-xs-12 col-sm-12 top-panel-right">
						<div class="col-xs-2 col-sm-2">
							<a href="#" class="about"><?php print $lang_global['about']; ?></a>
						</div>
						<div class="col-xs-3 col-sm-5">
							<span class="refresh hidden-xs"><?php print $lang_global['refresh']; ?></span>
							<input id="refreshoff" type="checkbox" data-size="mini" name="checkbox-refresh" checked>
						</div>
						<div class="col-xs-7 col-sm-5">
							<ul class="nav navbar-nav pull-right panel-menu">
							<?php if ($_SESSION['authentification'] == "1" && $_SESSION['Groupe'] == "admin") {  ?>
								<li class="dropdown">
									<a href="#" style="margin-left:10px" class="dropdown-toggle account" data-toggle="dropdown">
										<i class="fa fa-gears"></i>
										<i class="fa fa-angle-down pull-right"></i>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="#" data-toggle="modal" data-target="#modalAjoutCluster">
												<i class="fa fa-plus-square-o"></i>
												<span><?php print $lang_global['add']; ?> Cluster</span>
											</a>
										</li>
										<li>
											<a href="#" data-toggle="modal" data-target="#modalDesactivationCluster">
												<i class="fa fa-minus-square"></i>
												<span><?php print $lang_global['deactivation']; ?> Cluster</span>
											</a>
										</li>
										<li>
											<a href="#" data-toggle="modal" data-target="#modalcleanCluster">
												<i class="fa fa-minus-square"></i>
												<span><?php print $lang_global['clean']; ?> Cluster</span>
											</a>
										</li>
										<li>
											<a href="#" data-toggle="modal" data-target="#modalActivationCluster">
												<i class="fa fa-plus-square"></i>
												<span><?php print $lang_global['activation']; ?> Cluster</span>
											</a>
										</li>
										<li>
                                                                                        <a href="#" data-toggle="modal" data-target="#modalConfigCM">
                                                                                                <i class="fa fa-plus-square"></i>
                                                                                                <span><?php print $lang_global['params']; ?></span>
                                                                                        </a>
                                                                                </li>
									</ul>
								</li>
							<?php } ?> 
							<?php if ($_SESSION['authentification'] == "1") {  ?>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle account" data-toggle="dropdown">
										<div class="avatar">
											<img src="./img/avatar.png" class="img-circle" alt="avatar" />
										</div>
										<i class="fa fa-angle-down pull-right"></i>
										<div class="user-mini pull-right">
											<span class="welcome"><br></span>
											<span><?php echo $_SESSION['Login']; ?></span>
										</div>
									</a>
									<ul class="dropdown-menu">
							<?php if ($_SESSION['Groupe'] == "admin") {  ?>
										<li>
											<a href="#" data-toggle="modal" id="gestionuser" data-target="#modalgestionuser">
												<i class="fa fa-sign-in"></i>
												<span><?php print $lang_global['mgmtcluster']; ?></span>
											</a>
										</li>
							<?php } ?>
										<li>
											<a href="#" data-toggle="modal" id="chpass" data-target="#modalchpass">
												<i class="fa fa-sign-in"></i>
												<span><?php print $lang_global['chgpasswd']; ?></span>
											</a>
										</li>
										<li>
											<a href="#" id="logout">
												<i class="fa fa-sign-out"></i>
												<span><?php print $lang_global['logout']; ?></span>
											</a>
										</li>	
									</ul>
								</li>
							<?php } ?>
							<?php if ($_SESSION['authentification'] != "1") {  ?>
								<li class="dropdown">
									<a href="#" style="margin-left:10px" class="dropdown-toggle account" data-toggle="dropdown">
										<i class="fa fa-user"></i>
										<i class="fa fa-angle-down pull-right"></i>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="#" data-toggle="modal" data-target="#modallogin">
												<i class="fa fa-sign-in"></i>
												<span><?php print $lang_global['login']; ?></span>
											</a>
										</li>										
									</ul>
								</li>
							<?php } ?>
							</ul>
						</div>													
					</div>
				</div>
			</div>
		</div>
	</div>
</header>
<!--End Header-->
<!--Start Container-->
<div id="main" class="container-fluid">
	<div class="row">
		<div id="sidebar-left" class="col-xs-2 col-md-2">
			<ul class="nav main-menu">
				<li>
					<a href="ajax/dashboard.php" class="active ajax-link">
						<i class="fa fa-dashboard"></i>
						<span class="hidden-xs"><?php print $lang_global['dashboard']; ?></span>
					</a>
				</li>
			  	<?php
					mysqli_data_seek($clusteractive, 0);
					while ($clustername = $clusteractive->fetch_assoc()) {
						echo '
						<li>
							<a class="ajax-link dropdown-toggle" href="ajax/cluster.php?cluster='.$clustername['idClusters'].'">
								<i class="fa fa-linux"></i>
								<span class="hidden-xs">Cluster '.$clustername['idClusters'].'</span>
							</a>
						</li>';
					}
				?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle">
						<i class="fa fa-area-chart"></i>
						 <span class="pull-right text-muted"><i class="fa fa-fw fa-angle-right"></i></span>
						 <span class="hidden-xs"><?php print $lang_global['report']; ?></span>
					</a>
					<ul class="dropdown-menu">
						<?php
							mysqli_data_seek($clusteractive, 0);
							while ($clustername = $clusteractive->fetch_assoc()) {
								echo '
								<li>
									<a class="ajax-link" href="ajax/tendances.php?cluster='.$clustername['idClusters'].'">'.$clustername['idClusters'].'</a></li>
								</li>';
							}
						?>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle">
						<i class="fa fa-sitemap"></i>
						 <span class="pull-right text-muted"><i class="fa fa-fw fa-angle-right"></i></span>
						 <span class="hidden-xs"><?php print $lang_global['network']; ?> </span>
					</a>
					<ul class="dropdown-menu">
						<?php
							mysqli_data_seek($clusteractive, 0);
							while ($clustername = $clusteractive->fetch_assoc()) {
								echo '
								<li>
									<a class="ajax-link" href="ajax/network_ib.php?cluster='.$clustername['idClusters'].'">'.$clustername['interconnect'].' '.$clustername['idClusters'].'</a></li>
								</li>';
							}
						?>
					</ul>
				</li>
				<li>
					<a href="ajax/users.php" class="ajax-link">
						<i class="fa fa-users"></i>
						<span class="hidden-xs"><?php print $lang_global['users']; ?></span>
					</a>
				</li>
				<li>
					<a href="ajax/detail.php" class="ajax-link">
						<i class="fa fa-eye"></i>
						<span class="hidden-xs"><?php print $lang_global['details']; ?> clusters</span>
					</a>
				</li>	
			</ul>
		</div>
		<!--Start Content-->
		<div id="content" class="col-xs-12 col-md-10">
			<div id="about">
				<div class="about-inner">
					<h4 class="page-header"> Cluster Monitor by Bull </h4>
					<p><img src="img/cluster-monitor.png" class="img-rounded" alt="logo" /></p><br/>
					<p>Bull HPC</p>
					<p>Version : <?php echo $version; ?></p>
					<p>Date de mise à jour : <?php echo $majdate; ?></p>
					<p>Email - <?php echo $mailto; ?></p>
					<p> <a href="./changelog">Changelog</a> </p>
				</div>
			</div>
			<div class="preloader" id="preloader_full" style="display: none;">
				<!-- <img src="img/devoops_getdata.gif" class="devoops-getdata" alt="preloader">-->
				<center><i class="fa fa-spinner fa-spin fa-5x" style="margin-top:100px;"></i></center>
			</div>
			<div class="row">
				<div id="ajax-content" class="col-xs-12 col-md-12"></div>
				<div id="ajax-content-menu" class="col-xs-12 col-md-0"></div>
			</div>
		</div>
		<!--End Content-->
	</div>
</div>

<?php
if ($_SESSION['authentification'] == "1" && $_SESSION['Groupe'] == "admin") {  ?>

<!-- Modal Creation Cluster-->
<div class="modal fade" id="modalAjoutCluster" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel"><?php print $lang_global['add_cluster']; ?></h4>
	  </div>
	  <div class="modal-body">
		<form class="form-horizontal">
		  <div class="form-group">
			<label for="inputCluster" class="col-sm-3 control-label"><?php print $lang_global['name_cluster']; ?></label>
			<div class="col-sm-9">
			  <input type="text" class="form-control" id="inputCluster" placeholder="NomCluster">
			</div>
		  </div>
		</form>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
		<button id="insertdata" type="button" class="btn btn-primary" data-dismiss="modal"><?php print $lang_global['save']; ?></button>
	  </div>
	</div>
  </div>
</div>

<!-- Modal Désactivation Cluster-->
<div class="modal fade" id="modalDesactivationCluster" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel"><?php print $lang_global['del_cluster']; ?></h4>
	  </div>
	  <div class="modal-body">
		  <form class="form-horizontal">
			  <div class="form-group">
				<label for="deleteCluster" class="col-sm-3 control-label"><?php print $lang_global['name_cluster']; ?></label>
				<div class="col-sm-9">
					<select class="form-control" id="deleteCluster" placeholder="NomCluster">
						<option value=""><?php print $lang_global['choice_cluster']; ?></option>
						<?php
							mysqli_data_seek($clusteractive, 0);
							while ($clustername = $clusteractive->fetch_assoc()) {
								echo '
										<option value="'.$clustername['idClusters'].'">'.$clustername['idClusters'].'</option>';
							}
						?>
					</select>
				</div>
			</div>
		</form>
		</div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
		<button id="deletedata" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['unsave']; ?></button>
	  </div>
	</div>
  </div>
</div>

<!-- Modal Suppression Cluster-->
<div class="modal fade" id="modalcleanCluster" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php print $lang_global['clean_cluster']; ?></h4>
          </div>
           <div class="modal-body">
                   <form class="form-horizontal">
			<p><i class="fa fa-warning" style="font-size:15px;color:red;"></i> <?php print $lang_global['warningcleancluster']; ?> </p><br>
                           <div class="form-group">
                                 <label for="cleanCluster" class="col-sm-3 control-label"><?php print $lang_global['name_cluster']; ?></label>
                                 <div class="col-sm-9">
                                         <select class="form-control" id="cleanCluster" placeholder="NomCluster">
                                                 <option value=""><?php print $lang_global['choice_cluster']; ?></option>
                                                 <?php
                                                         mysqli_data_seek($clusterinactive, 0);
                                                         while ($clustername = $clusterinactive->fetch_assoc()) {
                                                                 echo '
                                                                                 <option value="'.$clustername['idClusters'].'">'.$clustername['idClusters'].'</option>';
                                                         }
                                                 ?>
                                         </select>
                                 </div>
                         </div>
                 </form>
                 </div>
           <div class="modal-footer">
                 <button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
                 <button id="cleandata" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['clean']; ?></button>
           </div>
         </div>
   </div>
 </div>



<!-- Modal activation cluster-->
<div class="modal fade" id="modalActivationCluster" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel"><?php print $lang_global['add_cluster']; ?></h4>
	  </div>
	  <div class="modal-body">
		  <form class="form-horizontal">
		      <p><i class="fa fa-warning" style="font-size:15px;color:red;"></i> <?php print $lang_global['infocreatecluster']; ?> </p><br>
			  <div class="form-group">
				<label for="updateCluster" class="col-sm-3 control-label"><?php print $lang_global['name_cluster']; ?></label>
				<div class="col-sm-9">
					<select class="form-control" id="updateCluster" placeholder="NomCluster">
						<option value=""><?php print $lang_global['choice_cluster']; ?></option>
						<?php
							mysqli_data_seek($clusterinactive, 0);
							while ($clustername = $clusterinactive->fetch_assoc()) {
								echo '
										<option value="'.$clustername['idClusters'].'">'.$clustername['idClusters'].'</option>';
							}
						?>
					</select>
				</div>
			</div>
		</form>
		</div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
		<button id="updatedata" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['activate']; ?></button>
	  </div>
	</div>
  </div>
</div>

<!-- Modal Config Cluster-Monitor-->
<div class="modal fade" id="modalConfigCM" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel"><?php print $lang_global['params']; ?></h4>
	  </div>
	  <div class="modal-body">
		  <form class="form-horizontal">
			  <div class="form-group">
				<div class="col-md-12">
					<label class="col-sm-8 control-label"><?php print $lang_global['langs']; ?></label>
					<div class="col-sm-4">
						<select class="form-control" name="langs" id="langs">
                                                     <option value="<?php echo $langs; ?>"><?php print $lang_global['fr']; ?></option>
	                                        </select>
					</div>
					<label class="col-sm-8 control-label"><?php print $lang_global['retcc']; ?></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="retcc" id="retcc" value="<?php echo $retcc; ?>" />
					</div>
					<label class="col-sm-8 control-label"><?php print $lang_global['retcfr']; ?></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="retcfr" id="retcfr" value="<?php echo $retcfr; ?>" />
					</div>
					<label class="col-sm-8 control-label"><?php print $lang_global['retcfs']; ?></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="retcfs" id="retcfs" value="<?php echo $retcfs; ?>" />
					</div>
					<label class="col-sm-8 control-label"><?php print $lang_global['retcn']; ?></label>
                                        <div class="col-sm-4">
                                                <input type="text" class="form-control" name="retcn" id="retcn" value="<?php echo $retcn; ?>" />
                                        </div>
					<label class="col-sm-8 control-label"><?php print $lang_global['retcp']; ?></label>
                                        <div class="col-sm-4">
                                                <input type="text" class="form-control" name="retcp" id="retcp" value="<?php echo $retcp; ?>" />
                                        </div>
					<label class="col-sm-8 control-label"><?php print $lang_global['retjm']; ?></label>
                                        <div class="col-sm-4">
                                                <input type="text" class="form-control" name="retjm" id="retjm" value="<?php echo $retjm; ?>" />
                                        </div>
				   </div>	
				</div>
			</form>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
		<button id="setconfig" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['modif']; ?></button>
	  </div>
	</div>
  </div>
</div>

<?php } ?>

<!-- Modal Login -->
<div class="modal fade" id="modallogin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel">Connexion</h4>
	  </div>
	  <div class="modal-body">
		  <form class="form-horizontal" class="col-sm-12">
			  <div class="form-group">
					<label class="col-sm-3 control-label"><?php print $lang_global['sign']; ?></label>
					<div class="col-sm-9">
						<div class="input-group">
							<span class="input-group-addon"><i style="width:16px;" class="fa fa-user"></i></span>
							<input type="text" class="form-control" name="LLogin" id="LLogin" />
						</div>
					</div>
					<label class="col-sm-3 control-label"><?php print $lang_global['passwd']; ?></label>
					<div class="col-sm-9">
						<div class="input-group">
							<span class="input-group-addon"><i style="width:16px;"class="fa fa-lock"></i></span>
							<input type="password" class="form-control " name="MMdp" id="MMdp"/>
						</div>
					</div>
				</div>
			</form>
		</div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
		<button id="login" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['login']; ?></button>
	  </div>
	</div>
  </div>
</div>

<?php
if ($_SESSION['authentification'] == "1" && $_SESSION['Groupe'] == "admin") {  ?>

<!-- Modal Gestion utilisateurs -->
<div class="modal fade" id="modalgestionuser" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel"><?php print $lang_global['mgmt_users']; ?></h4>
	  </div>
	  <div class="modal-body">
		  <form class="form-horizontal">
			  <div class="form-group">

				<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#Creation" aria-controls="Creation" role="tab" data-toggle="tab"><?php print $lang_global['create']; ?></a></li>
				<li role="presentation"><a href="#Suppression" aria-controls="Suppression" role="tab" data-toggle="tab"><?php print $lang_global['delete']; ?></a></li>
			  </ul>

			  <!-- Tab panes -->
			  <div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="Creation">
					<div class="col-md-12">
							<label class="col-sm-3 control-label"><?php print $lang_global['login']; ?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="llogin" id="llogin" />
							</div>
							<label class="col-sm-3 control-label"><?php print $lang_global['passwd']; ?></label>
							<div class="col-sm-9">
								<input type="password" class="form-control" name="mmdp" id="mmdp"/>
							</div>
							<label class="col-sm-3 control-label"><?php print $lang_global['passwd']; ?></label>
							<div class="col-sm-9">
								<input type="password" class="form-control" name="Mdp2" id="Mdp2"/>
							</div>
							<label class="col-sm-3 control-label"><?php print $lang_global['name']; ?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="Nom" id="Nom"/>
							</div>
							<label class="col-sm-3 control-label"><?php print $lang_global['lastname']; ?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="Prenom" id="Prenom"/>
							</div>
							<label class="col-sm-3 control-label"><?php print $lang_global['groupes']; ?></label>
							<div class="col-sm-9">
								<select class="form-control" name="Groupe" id="Groupe">
									<option value="user"><?php print $lang_global['users']; ?></option>
	   								<option value="admin"><?php print $lang_global['admin']; ?></option>
      							</select>
							</div>
							<center>
								<button id="createusers" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['add']; ?></button>
							</center>
						</div>	
				</div>
				<div role="tabpanel" class="tab-pane" id="Suppression">
						<div class="col-md-12">
						<center>
                				<select class="form-control" style="width:90%; height : 205px" name="idsuppr" size="10" id="idsuppr">
                 			<?php
						$sqlusers = "SELECT * FROM $dbauth ORDER BY nom ASC";
						$reqsqlusers = $mysqli->query($sqlusers) or die ('Erreur '.$sqlusers.' '.$mysqli->error);

						while ($row_users = $reqsqlusers->fetch_row()) {

							echo '<option value="'.$row_users[0].'">';
							if($row_users[5]== "admin"){
								echo '***';
							}
							echo $row_users['3'].' '.$row_users['4'].' ('.$row_users['1'].')';
			 				echo '</option>';
						}
					?>
						</select>
							<button id="deleteusers" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['del']; ?></button>
						</center>
						</div>
					</div>
				  </div>
				</div>
			</form>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
	  </div>
	</div>
  </div>
</div>

<?php } ?>

<?php
if ($_SESSION['authentification'] == "1") {  ?>

<!-- Modal Change password -->
<div class="modal fade" id="modalchpass" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel"><?php print $lang_global['chgpasswd']; ?></h4>
	  </div>
	  <div class="modal-body">
		  <form class="form-horizontal" class="col-sm-12">
			  <div class="form-group">
					<label class="col-sm-3 control-label"><?php print $lang_global['passwd']; ?></label>
					<div class="col-sm-9">
						<div class="input-group">
							<span class="input-group-addon"><i style="width:16px;" class="fa fa-lock"></i></span>
							<input type="password" class="form-control" name="pass1" id="pass1" required/>
						</div>
					</div>
					<label class="col-sm-3 control-label"><?php print $lang_global['passwd']; ?> *</label>
					<div class="col-sm-9">
						<div class="input-group">
							<span class="input-group-addon"><i style="width:16px;"class="fa fa-lock"></i></span>
							<input type="password" class="form-control " name="pass2" id="pass2" required/>
						</div>
					</div>
				</div>
			</form>
		</div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php print $lang_global['cancel']; ?></button>
		<button id="chgpass" type="button" data-dismiss="modal" class="btn btn-primary"><?php print $lang_global['save']; ?></button>
	  </div>
	</div>
  </div>
</div>

<?php } ?>

<!--End Container-->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="plugins/bootstrap/bootstrap.min.js"></script>
<script src="plugins/raphael/raphael.2.2.1.min.js"></script>
<script src="plugins/morris.js-0.5.1/morris.min.js"></script>
<script src="plugins/toaster/jquery.toaster.min.js"></script>
<script src="plugins/bootstrap-table/bootstrap-table.min.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="plugins/bootstrap-table/locale/bootstrap-table-fr-FR.min.js"></script>
<script src="plugins/bootstrap-table/extensions/export/bootstrap-table-export.min.js"></script>
<script src="plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="plugins/visjs/dist/vis.min.js"></script>
<script src="plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script>
	$("[name='checkbox-refresh']").bootstrapSwitch();
</script>

<script src="js/cluster-monitor.min.js"></script>

<script>
$(document).ready(function() {	
	// Make all JS-activity for dashboard
	DashboardTabChecker();
});
</script>

<?php mysqli_close($mysqli); ?>

</body>
</html>
