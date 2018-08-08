
<?php 

//require('./include/config.php');
include('../../langs/fr_FR.lang');

$filename = './.lockinstall';
$configfile = '../config_test.php';

if (file_exists($filename)) {
    header('Location: ../../index.php');
}

session_start();
$_SESSION['install'] = 0;

?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title><?php print $lang_global['Cluster_Monitor']; ?></title>
		<meta name="description" content="description">
		<meta name="author" content="<?php echo $author ; ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="../../plugins/bootstrap/bootstrap.css" rel="stylesheet">
		<link href="../../plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet">
		<link href="../../css/font-awesome.css" rel="stylesheet">
		<link href="../../fonts/open-sans-v13-latin/open-sans.css" rel="stylesheet">
		<link href="../../css/style_black.css" rel="stylesheet">

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
						<div class="col-xs-9 col-sm-9">
							<span><b><?php print $lang_global['install']; ?></b></span>
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
			<br>
			<ul id="menu-install" class="nav nav-pills nav-stacked ">
				<li class="active"><a href="#" class="tab-link" id="etape1"><?php print $lang_global['etape1']; ?></a></li>
				<li><a href="#" class="tab-link" id="etape2"><?php print $lang_global['etape2']; ?></a></li>
				
					<li><a href="#" class="tab-link" id="etape3"><?php print $lang_global['etape3']; ?></a></li>
				
			</ul>
		</div>
		<!--Start Content-->
		<form id="install">
		<div id="content" class="col-xs-12 col-md-10">
			<center><img src="../../img/cluster-monitor.png"></center>
			<center><span>Version 1.5.2</span></center>
			<div class="preloader" id="preloader_full" style="display: none;">
				<!-- <img src="img/devoops_getdata.gif" class="devoops-getdata" alt="preloader">-->
				<center><i class="fa fa-spinner fa-spin fa-5x" style="margin-top:100px;"></i></center>
			</div>
			<div class="row">

				<div id="dashboard_tabs" class="col-xs-11 col-sm-11">

					<div id="dashboard-etape1" class="row" style="visibility: visible; position: relative;">
						<div class="col-xs-12 col-sm-12 installdiv">
					
							<div class="form-group">
    						  	   <br><label for="langue" class="col-sm-4 text-right">Choix de la langue</label>
    								<div class="col-sm-6">
      									<select class="form-control" id="langue" name="langue">
									   <option value="fr_FR">Français</option>
 									</select><br><br>
    								</div>
								<button id="blang" type="button" class="btn btn-primary pull-right">Next</button>
  							</div>
						</div>
					</div>

					<div id="dashboard-etape2" class="row" style="visibility: hidden; position: absolute;">
						
						<div class="col-xs-12 col-sm-12 installdiv">
								<h4><u>Vérification des prérequis :</u></h4><br>
								<?php 
								        // Check version php
								        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    									    echo '<h5><i class="fa fa-check fa-green"></i> PHP Version : ' . PHP_VERSION . ".</h5>";
									    ++$_SESSION['install'];
								        } else {
									    echo '<h5><i class="fa fa-times fa-red"></i> PHP Version : ' . PHP_VERSION . ".</h5>";
								        }
									// Check php support sessions enable
									session_start();
									if(!isset($_GET['reload']) OR $_GET['reload'] != 'true') {
	      									$_SESSION['MESSAGE'] = 'This PHP Session support ';
									   	if(isset($_SESSION['MESSAGE'])) {
									      		echo '<h5><i class="fa fa-check fa-green"></i> '.$_SESSION['MESSAGE'].'enabled.</h5>';
											++$_SESSION['install'];
									   	} else {
									      		echo '<h5><i class="fa fa-times fa-red"></i> '.$_SESSION['MESSAGE'].'not enabled.</h5>';
   									   	}
									}
									// Check php utf8 function enable
									mb_internal_encoding('UTF-8'); 
									if (mb_strlen($string) == strlen($string)) {
 										echo '<h5><i class="fa fa-check fa-green"></i> This PHP Support UTF8 fonctions enabled.</h5>';
										++$_SESSION['install'];
									} else {
									      	echo '<h5><i class="fa fa-times fa-red"></i> This PHP Support UTF8 fonctions not enabled.</h5>';
   									}
									// Check php support post get
									if (ini_get('enable_post_data_reading') == 1 ) {
										echo '<h5><i class="fa fa-check fa-green"></i> This PHP Support POST and GET variable.</h5>';
										++$_SESSION['install'];
									} else {
									      	echo '<h5><i class="fa fa-times fa-red"></i> This PHP Support POST and GET variable.</h5>';
   									}
									// Check php memory_limit
									if (preg_replace('#[A-Za-z]#u', '', ini_get('memory_limit')) >= '128' ) {
										echo '<h5><i class="fa fa-check fa-green"></i> This PHP Max session memory is set to '.ini_get('memory_limit').'.</h5>';
										++$_SESSION['install'];
									} else {
									      	echo '<h5><i class="fa fa-times fa-red"></i> This PHP Max session memory is set to '.ini_get('memory_limit').'.</h5>';
   									}
									// Check apache version	
									$search  = array('Apache/', '(Debian)');
									if (str_replace($search, '', apache_get_version()) >= '2' ) {
										echo '<h5><i class="fa fa-check fa-green"></i> Apache Version '.apache_get_version().'.</h5>';
										++$_SESSION['install'];
									} else {
									      	echo '<h5><i class="fa fa-times fa-red"></i> Apache Version '.apache_get_version().'.</h5>';
   									}
									// Check create fichier de conf
									$fichier = fopen($configfile, 'w+');
									if (fwrite($fichier,'# Configuration cluster-monitor')) { 
										echo '<h5><i class="fa fa-check fa-green"></i> Configuration file <b> /include/config.php</b> could be created.</h5>';
										echo '<h5><i class="fa fa-check fa-green"></i> Configuration file <b> /include/config.php</b> is writable.</h5>';
										++$_SESSION['install'];
									} else { 
	 									echo '<h5><i class="fa fa-times fa-red"></i> Configuration file could not be created ( open folder in 777 )<b> /include/config.php</b>"; .</h5>';
									}
									fclose($fichier); 
									// Check create fichier de conf
									$fichier = fopen('./.lockinstall', 'w+');
									if (fwrite($fichier,'# Configuration cluster-monitor')) { 
										echo '<h5><i class="fa fa-check fa-green"></i> Lock file <b> /include/install/.lockinstall</b> could be created.</h5>';
										echo '<h5><i class="fa fa-check fa-green"></i> Lock file <b> /include/install/.lockinstall</b> is writable.</h5>';
										++$_SESSION['install'];
									} else { 
	 									echo '<h5><i class="fa fa-times fa-red"></i> Lock file could not be created ( open folder in 777 ) <b> /include/install/.lockinstall </b> .</h5>';
									}
									fclose($fichier);
								
									unlink('./.lockinstall');
								
								?>
    					<?php if ($_SESSION['install'] == "8") {  ?>	  	   
								<button id="bck" type="button" class="btn btn-primary pull-right">Next</button>
					<?php } else { ?>
								<br><div class="alert alert-danger" role="alert">
									<h5 class="text-center"><i class="fa fa-times fa-red"></i> Installation is unavailable ...</h5><br>
								</div>
					<?php } ?>
						</div>
					</div>

					<div id="dashboard-etape3" class="row" style="visibility: hidden; position: absolute;">
						<div class="col-xs-12 col-sm-12 installdiv">
							<h4><center><u>Database Cluster Monitor</u></center></h4>

							<div class="form-group">
    						  	   <br><label for="databasename" class="col-sm-2 ">Database Name</label>
    								<div class="col-sm-4">
      									<input type="text" class="form-control" id="databasename" >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Database name </p>
  							</div><br>

							<div class="form-group">
    						  	   <br><label for="drivertype" class="col-sm-2 ">Driver type</label>
    								<div class="col-sm-4">
      									<input type="text" class="form-control" id="drivertype" value="mysqli" placeholder="mysqli (MYSQL >= 4.1.0 )" disabled >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Database type</p>
  							</div><br>

							<div class="form-group">
    						  	   <br><label for="Server" class="col-sm-2 ">Server</label>
    								<div class="col-sm-4">
      									<input type="text" class="form-control" id="Server" value="localhost" placeholder="localhost" disabled>
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Name or ip adress for database server</p>
  							</div><br>

							<div class="form-group">
    						  	   <br><label for="port" class="col-sm-2 ">Port</label>
    								<div class="col-sm-4">
      									<input type="text" class="form-control" id="port" value="3306" placeholder="3306" disabled>
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Database server port</p>
  							</div><br>

							<div class="form-group">
							  <br><label for="cdb" class="col-sm-2 ">Create database</label>
							    <div class="col-sm-4">
							       <input type="checkbox" name="cdb" value="createdb" id="cdb">
							    </div>
							    <p class="col-sm-5 col-sm-offset-1 text-left">Do we must create database ?</p>
							</div><br>

							<div class="form-group">
    						  	   <br><label for="logindb" class="col-sm-2 ">Login</label>
    								<div class="col-sm-4">
      									<input type="text" class="form-control" id="logindb" >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Login for cluster-monitor database owner</p>
  							</div><br>

							<div class="form-group">
    						  	   <br><label for="pwddb" class="col-sm-2 ">Password</label>
    								<div class="col-sm-4">
      									<input type="password" class="form-control" id="pwddb" >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Password for cluster-monitor database owner</p>
  							</div><br>

							<div class="form-group">
							  <br><label for="cowner" class="col-sm-2 ">Create owner</label>
							    <div class="col-sm-4">
							       <input type="checkbox" name="cowner" value="createowner" id="cowner">
							    </div>
							    <p class="col-sm-5 col-sm-offset-1 text-left">Do we must create the account ?</p>
							</div><br>

							<h4><center><u>Database Server - Superuser access</u></center></h4>

							<div class="form-group">
    						  	   <br><label for="loginsu" class="col-sm-2 ">Login</label>
    								<div class="col-sm-4">
      									<input type="text" class="form-control" id="loginsu" >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Login of the user allowed to create new databases</p>
  							</div><br>

							<div class="form-group">
    						  	   <br><label for="pwdsu" class="col-sm-2 ">Password</label>
    								<div class="col-sm-4">
      									<input type="password" class="form-control" id="pwdsu" >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Password of the login allowed to create new databases</p>
  							</div><br>
			
							<h4><center><u>Cluster Monitor - Compte Admin</u></center></h4>

							<div class="form-group">
    						  	   <br><label for="loginad" class="col-sm-2 ">Login</label>
    								<div class="col-sm-4">
      									<input type="text" class="form-control" id="loginad" >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Login for cluster-monitor web application </p>
  							</div><br>

							<div class="form-group">
    						  	   <br><label for="pwdad" class="col-sm-2 ">Password</label>
    								<div class="col-sm-4">
      									<input type="password" class="form-control" id="pwdad" >
    								</div>
								<p class="col-sm-5 col-sm-offset-1 text-left">Password for cluster-monitor web application</p><br><br>
  							</div><br>
						<?php if ($_SESSION['install'] == "8") {  ?>
							<center><button id="vform" type="button" class="btn btn-primary pull-center">Créer</button></center><br><br><br>
						<?php } ?>
						</div>
					</div>

				</div>
			</div>
		</div></form>
		<!--End Content-->
	</div>
</div>

<!--End Container-->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../plugins/bootstrap/bootstrap.min.js"></script>
<script src="../../plugins/toaster/jquery.toaster.js"></script>
<!-- put your locale files after bootstrap-table.js -->

<script>
$(document).ready(function() {	
	
	$('#menu-install').on('click', 'a.tab-link', function(e){
        e.preventDefault();
       		
        	$('div#dashboard_tabs').find('div[id^=dashboard]').each(function(){
           	 $(this).css('visibility', 'hidden').css('position', 'absolute');
        	});
        	var attr = $(this).attr('id');
        	$('#'+'dashboard-'+attr).css('visibility', 'visible').css('position', 'relative');
		$(this).closest('.nav').find('li').removeClass('active');
        	$(this).closest('li').addClass('active');
	});
	$('#blang').on('click', function(){
		document.getElementById('etape2').click();
        });
	$('#bck').on('click', function(){
		document.getElementById('etape3').click();
        });

});

$("#vform").on('click',function() {

	if( $('input[name=cdb]').is(':checked') ){ 
		$createdb="createdb";
	} else {
    		$createdb="nocreatedb";
	}
	if( $('input[name=cowner]').is(':checked') ){ 
		$createow="createow";
	} else {
    		$createow="nocreateow";
	}

        if (($("#langue").val()!="") && ($("#databasename").val()!="") && ($("#drivertype").val()!="") && ($("#Server").val()!="") && ($("#port").val()!="") && ($("#logindb").val()!="") && ($("#pwddb").val()!="") && ($("#loginsu").val()!="") && ($("#loginad").val()!="") && ($("#pwdad").val()!="")) {
        $.ajax({
            url: "./ConfigCM.php",
            type: "POST",
            async: true, 
            data: { langue:$("#langue").val(), databasename:$("#databasename").val(), drivertype:$("#drivertype").val(), Server:$("#Server").val(), port:$("#port").val(), cdb:$createdb, logindb:$("#logindb").val(), pwddb:$("#pwddb").val(), cowner:$createow, loginsu:$("#loginsu").val(), pwdsu:$("#pwdsu").val(), loginad:$("#loginsu").val(), pwdad:$("#pwdsu").val()}, //your form data to post goes here as a json object
            dataType: "json",
            success: function(response) {
		console.log(response.data);
		if (response.data == "ok") {
            		$.toaster({ priority : 'success', title : 'Success ', message : "Création base "+$("#databasename").val()+" & configuration est réussit"});
            		setTimeout(function () {
                   		 location.reload(true);
                	}, 3000);
		} else {
			$.toaster({ priority : 'danger', title : 'Echec ', message : "Création base "+$("#databasename").val()+" & configuration est réussit"});
		}
            },  
        });
      } else {
       		$.toaster({ priority : 'danger', title : 'Echec ', message : "Création base "+$("#databasename").val()+" est imposssible"});
      }
})

</script>

</body>
</html>
