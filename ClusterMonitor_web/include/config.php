<?php 
 $pathclustermonitor = '/cluster-monitor';
 $loginmysql = ''; 
 $passmysql = ''; 
 $hostmysql = 'localhost';
 $dbmysql = 'cluster_monitor';
 $dbauth = 'Auth';
 $version = '1.5.5';
 $majdate = '12 juin 2018';
 $mailto = '<a href="mailto:nicolas.gilbert@bull.net">nicolas.gilbert@bull.net </a> / <a href="mailto:    vincent.montagne@bull.net">vincent.montagne@bull.net</a>';
 $author = 'MONTAGNE Vincent & GILBERT Nicolas';
 $mysqli = new mysqli($hostmysql, $loginmysql = ''; 
 $mysqli->set_charset('utf8'); 
 if ($mysqli->connect_error) { 
	die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
 } 
 $sqllangue='select langue from Config';
 $reqlangue= $mysqli->query($sqllangue) or die ('Erreur '.$sqllangue.' '.$mysqli->error); 
 $flangue=$reqlangue->fetch_assoc(); 
 $lang=$flangue['langue']; 

 // Debug function ----------------------------------------------------------------

 function tracagecmd($texte) {
  
       $txtlog = '['.date("d/m/Y H:i:s").'] '.$texte."\n";
       // écriture dans un fichier de traçage
       $fichierlog = "/var/www/cluster-monitor/cron/cmd_archivage.log";
       $myFile=fopen($fichierlog,'a+');
       fputs($myFile,$txtlog);
       fclose($myFile);
 }

function tracage($texte) {
  
      $txtlog = '['.date("d/m/Y H:i:s").'] '.$texte."\n";
      // écriture dans un fichier de traçage
      $fichierlog = "/var/www/cluster-monitor/cron/error_cron_clean.log";
      $myFile=fopen($fichierlog,'a+');
      fputs($myFile,$txtlog);
      fclose($myFile);
 }

?>
