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

$action=$_SESSION['action'];
$jobid=$_SESSION['jobid'];
$userid=$_SESSION['userid'];
$from=$_SESSION['from'];
$to=$_SESSION['to'];
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
				<li><a href="#"><?php print $lang_global['statsjobs']; ?></a></li>
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
				<h3><?php print $lang_global['jobs']; ?></h3>
			</div>
			<div class="hidden-xs col-sm-5">
				<h5 style="text-align:right" class="maj"><i class="fa fa-refresh"></i> <?php print $lang_global['update']; ?> <?php echo $lastrun;?></h5>
			</div>		
			<div class="clearfix visible-xs"></div>
		</div-->
		<!--End Dashboard 1-->
		<!--Start Dashboard 2-left-->
			<div class="row">
			<div id="ajax-content-form" class="col-xs-12 col-sm-12 col-md-12 bg_page">
			<ul class="nav nav-tabs" role="tablist" id="mattab">
    	   			<li id="li1" role="presentation" ><a href="#search" aria-controls="search" role="tab" data-toggle="tab">Rechercher</a></li>
    	   			<li id="li2" role="presentation" class="active"><a href="#find" aria-controls="find" role="tab" data-toggle="tab">Result Recherche </a></li>
  			</ul>
			<!--Start Utilization Tab 1-->
			   <div class="tab-content" id="tabjobhistory">
			     <div role="tabpanel" class="tab-pane" id="search">
				<div class="col-xs-12 col-sm-12 col-md-12">
					<br><br>
                                                <div class="box-header">	
                                                        <div class="box-name">
                                                                <i class="fa fa-info-circle"></i>
                                                                <span><?php print $lang_global['searchbyjob']; ?></span>
                                                        </div>
                                                </div><br><br>
                                                <div class="box-content">
						  <form class="form-horizontal" class="col-sm-12">
			  			    <div class="form-group">  
						      <label class="col-sm-1 control-label"><?php print $lang_global['jobid']; ?></label>
                                                        <div class="col-sm-2">
                                                           <div class="input-group">
                                                               <input type="text" class="form-control noEnterSubmit" name=jobid" id="jobid" />
                                                           </div>
                                                        </div>
							<button id="okjobid" type="button" class="btn btn-primary tab-link" value=""><?php print $lang_global['ok']; ?></button>
						     </form>
						    </div>
						</div>

                                </div>
				<div class="col-xs-12 col-sm-12 col-md-12">
					<br><br>
                                                <div class="box-header">
                                                        <div class="box-name">
                                                                <i class="fa fa-info-circle"></i>
                                                                <span><?php print $lang_global['searchbyuserbytime']; ?></span>
                                                        </div>
                                                </div><br><br>
                                                <div class="box-content">

						<form class="form-horizontal" class="col-sm-12">
                                                    <div class="form-group">
                                                      <label class="col-sm-1 control-label"><?php print $lang_global['userid']; ?></label>
                                                        <div class="col-sm-2">
                                                           <div class="input-group">
                                                               <input type="text" class="form-control noEnterSubmit" name="userid" id="userid">
                                                           </div>
                                                        </div>
						      <label class="col-sm-1 control-label"><?php print $lang_global['from']; ?></label>
                                                        <div class="col-sm-2">
                                                           <div class="input-group">
                                                               <input type="text" class="form-control noEnterSubmit span2" name="from" id="from">
                                                           </div>
                                                        </div> 
						      <label class="col-sm-1 control-label"><?php print $lang_global['to']; ?></label>
                                                        <div class="col-sm-2">
                                                           <div class="input-group">
                                                               <input type="text" class="form-control noEnterSubmit span2" name="to" id="to">
                                                           </div>
                                                        </div> 
                                                        <button id="uftok" type="button" class="btn btn-primary"><?php print $lang_global['ok']; ?></button>
                                                    </div>
                                                  </form>
        					</div>
                                </div>
			      </div><br>
		              <div role="tabpanel" class="tab-pane active" id="find">
					<?php
						     Jobshistory($clustername,$action,$userid,$from,$to,$jobid);
					?>
			      </div>
			    </div>
			   </div>
			</div>
			<!--End Dashboard Tab 1-->
			</div>
	</div>
</div>

<script>
$('.noEnterSubmit').keypress(function(e){
    if ( e.which == 13 ) return false;
    if ( e.which == 13 ) e.preventDefault();
}); 

    $("#from").datetimepicker({
        format: "yyyy-mm-dd hh:ii",
        autoclose: true,
        todayBtn: true,
        pickerPosition: "bottom-left"
    });

    $("#to").datetimepicker({
        format: "yyyy-mm-dd hh:ii",
        autoclose: true,
        todayBtn: true,
        pickerPosition: "bottom-left"
    });

$(document).ready(function() {	
	$('#jobhistory').bootstrapTable(); // init via javascript
});
</script>

<?php

if(empty($action)){

        echo '<script>
                $("#find").removeClass("active");
                $("#li2").removeClass("active");
                $("#search").addClass("active");
                $("#li1").addClass("active");
        </script>';

}



} else {
	header('Location: ../../index.php');
}
?>
