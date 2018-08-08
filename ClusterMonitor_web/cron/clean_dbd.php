
<?php

#Cron à executer toutes les jours toutes les deux heures + à 23h55
# 00 */2 * * * php5 /var/www/cluster-monitor/cron/clean_dbd.php 1>/dev/null 2>&1


$require=('/var/www/cluster-monitor/include/config.php');
$myFile='/var/log/cm.log';
$lockfile = '/var/www/cluster-monitor/cron/cron.lock';
if (file_exists($lockfile)) {
	exit;
} else {
	$txtlog='['.date("d/m/Y H:i:s").'] File lock......';
	$myFile=fopen($lockfile,'a+');
	fputs($myFile,$txtlog);
	fclose($myFile);
}

$mysqli = new mysqli($hostmysql, $loginmysql, $passmysql, $dbmysql);
$mysqli->set_charset("utf8");
if ($mysqli->connect_error) {
        die('Erreur de connexion ('.$mysqli->connect_errno.')'. $mysqli->connect_error);
}

$sqlclusteractive = "select idClusters from Clusters where is_active=1";
$clusteractive = $mysqli->query($sqlclusteractive) or tracage ('Erreur '.$sqlclusteractive.' '.$mysqli->error);

while ($fclustername = $clusteractive->fetch_row()) {

	$clustername = $fclustername[0];
	shell_exec("/usr/bin/php5 /var/www/cluster-monitor/cron/function_cron.php $clustername statscollectcluster > /dev/null 2>&1 & ");
	shell_exec("/usr/bin/php5 /var/www/cluster-monitor/cron/function_cron.php $clustername statscollectfrt > /dev/null 2>&1 & ");
	shell_exec("/usr/bin/php5 /var/www/cluster-monitor/cron/function_cron.php $clustername statscollectfs > /dev/null 2>&1 & ");
	shell_exec("/usr/bin/php5 /var/www/cluster-monitor/cron/function_cron.php $clustername statscollectnodes > /dev/null 2>&1 & ");
	shell_exec("/usr/bin/php5 /var/www/cluster-monitor/cron/function_cron.php $clustername statscollectpart > /dev/null 2>&1 & ");
	shell_exec("/usr/bin/php5 /var/www/cluster-monitor/cron/function_cron.php $clustername statscollectMN > /dev/null 2>&1 & ");
}

shell_exec("
STATE_OK=0
STATE_WARNING=1
STATE_CRITICAL=2
message=
errorfind=
fqdnhost=$(hostname -f)

if [ -z "${message}" ]; then
        message="All is good"          
        status=${STATE_OK}
	errorfind="!!!"
elif [ $message = "1" ]; then
	status=${STATE_WARNING}
	errorfind="Erreur apparue"
else
        status=${STATE_CRITICAL}
	errorfind="Erreurs apparues"
fi
");

shell_exec('printf "%s\t%s\t%d\t%s\n" "${fqdnhost}" "check_clean_monitor" ${status} "${message} ${errorfind}"|/usr/sbin/send_nsca -H zara ');

//Clean file lock
unlink($lockfile);

?>

