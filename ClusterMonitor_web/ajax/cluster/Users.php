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

session_start();

$lastrun=$_SESSION['lastrun'];
$clustername=$_GET["cluster"];

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
				<li><a href="#"><?php echo $clustername;?></a></li>
				<li><a href="#"><?php print $lang_global['users']; ?></a></li>
			</ol>
			<h5 class="maj pull-right"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>			
		</div>
		</div>
	<!--End Breadcrumb-->
	<div>
		<div class="preloader" id="preloader_mini" style="display: none;">
			<!-- <img src="img/devoops_getdata.gif" class="devoops-getdata" alt="preloader">-->
			<center><i class="fa fa-spinner fa-spin fa-5x" style="margin-top:100px;"></i></center>
		</div>
		<!--Start Dashboard 1-->
		<!--div id="dashboard-header" class="row">
			<div class="col-xs-12 col-sm-7">
				<h3><?php print $lang_global['users']; ?></h3>
			</div>
			<div class="hidden-xs col-sm-5">
				<h5 style="text-align:right" class="maj"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>
			</div>		
			<div class="clearfix visible-xs"></div>
		</div-->
		<!--End Dashboard 1-->
		<!--Start Dashboard 2-left-->
			<div class="row">
			<!--Start Utilization Tab 1-->
				<div class="col-xs-12 col-sm-12 col-md-12 bg_page">
					<?php
						Users($clustername);
					?>
				</div>
			<!--End Dashboard Tab 1-->
			</div>
	</div>
</div>

<script>
	$(document).ready(function() {	
		$('#usershows').bootstrapTable(); // init via javascript
	});

	// Function pour cacher les colonnes vides des tableaux.
	$('#usershows th').each(function(i) {
		var bool = true;
		var tds = $(this).parents('table').find('tr td:nth-child(' + (i + 1) + ')');
		tds.each( function(j) { 
			if (this.innerHTML != '') {bool=false;return false;}; 
		});
		if (bool) 
		{
			$('#usershows').bootstrapTable('hideColumn', $(this).attr('data-field'));
		}
	});

	// Function d'affichage de la partie caché du tableau users
	function detailFormatteru(index, row) {
		return '<div class=col-md-12> \
					Groupes Secondaire : <b>'+row.GroupeS+'</b> \
				</div> \
    		';
	}
</script>

<?php
} else {
	header('Location: ../../index.php');
}
?>
