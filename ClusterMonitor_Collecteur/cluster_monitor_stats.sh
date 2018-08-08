#!/bin/bash -x

# 
# Params slurm.conf necessaire ....
#
# 	JobAcctGatherType=jobacct_gather/linux
#	JobAcctGatherFrequency=energy=5,lustre=60,network=60,task=60
#	AcctGatherEnergyType=acct_gather_energy/ipmi
#	AcctGatherFilesystemType=acct_gather_filesystem/lustre
#	AcctGatherInfinibandType=acct_gather_infiniband/ofed
#

# --------------------- Definition des variables --------------------- #

# Repertoire de travail du collecteur
WORK_DIR=/srv/cluster_monitor

# Nom du fichier de configuration
CONFIG_FILE_NAME="cluster_monitor.conf"
source ${WORK_DIR}/${CONFIG_FILE_NAME} || exit 1
logrun="${WORK_DIR}/stats/run"
loglastrun="${WORK_DIR}/stats/lastrun"

if [[ ! -e ${WORK_DIR}/stats ]] ;then mkdir ${WORK_DIR}/stats ;fi
if [[ ! -e ${WORK_DIR}/stats/jobs ]] ;then mkdir ${WORK_DIR}/stats/jobs ;fi

LOCKFILEJOBSTATS="${WORK_DIR}/cluster-monitor-stats.lock"

# Verifie fichier de lock
if [[ -f "{LOCKFILEJOBSTATS}" ]];then
        echo -e ""
        echo -e "${FAIL} - Error fichier de lock présent => {LOCKFILEJOBSTATS}"
        echo -e ""
        exit 1
else
        touch ${LOCKFILEJOBSTATS}
fi

# trap ctrl-c and call ctrl_c()
trap ctrl_c 15 2

function ctrl_c () {
  echo -e ""
  echo -e "Arret de la collecte"
  rm -f ${LOCKFILEJOBSTATS}
  exit 1
}

# --------------------- Definition des functions --------------------- #

function paramsjob () {
        /usr/bin/scontrol -o show jobs $1 --details | awk -F '[= ]' '{ for(o=1;o<=NF;o++) \
                if ($o =="JobId") {JobId=$(o+1)}\
                else if ($o =="JobName") {JobName=$(o+1)} else if ($o =="UserId") {UserId=$(o+1)} else if ($o =="GroupId") {GroupId=$(o+1)} \
                else if ($o =="Priority") {Priority=$(o+1)} else if ($o =="Nice") {Nice=$(o+1)} else if ($o =="Account") {Account=$(o+1)} \
                else if ($o =="QOS") {QOS=$(o+1)} else if ($o =="WCKey") {WCKey=$(o+1)} else if ($o =="Requeue") {Requeue=$(o+1)} \
                else if ($o =="Restarts") {Restarts=$(o+1)} else if ($o =="BatchFlag") {BatchFlag=$(o+1)} else if ($o =="Reboot") {Reboot=$(o+1)} \
                else if ($o =="ExitCode") {ExitCode=$(o+1)} else if ($o =="DerivedExitCode") {DerivedExitCode=$(o+1)} else if ($o =="RunTime") {RunTime=$(o+1)} \
                else if ($o =="TimeLimit") {TimeLimit=$(o+1)} else if ($o =="TimeMin") {TimeMin=$(o+1)} else if ($o =="SubmitTime") {SubmitTime=$(o+1)} \
                else if ($o =="EligibleTime") {EligibleTime=$(o+1)} else if ($o =="StartTime") {StartTime=$(o+1)} else if ($o =="EndTime") {EndTime=$(o+1)} \
                else if ($o =="PreemptTime") {PreemptTime=$(o+1)} else if ($o =="SuspendTime") {SuspendTime=$(o+1)} else if ($o =="SecsPreSuspend") {SecsPreSuspend=$(o+1)} \
                else if ($o =="Partition") {Partition=$(o+1)} else if ($o =="AllocNode:Sid") {AllocNodeSid=$(o+1)} else if ($o =="ReqNodeList") {ReqNodeList=$(o+1)} \
                else if ($o =="ExcNodeList") {ExcNodeList=$(o+1)} else if ($o =="NodeList") {NodeList=$(o+1)} else if ($o =="BatchHost") {BatchHost=$(o+1)} \
                else if ($o =="NumNodes") {NumNodes=$(o+1)} else if ($o =="NumCPUs") {NumCPUs=$(o+1)} else if ($o =="CPUs/Task") {CPUsTask=$(o+1)} \
                else if ($o =="ReqB:S:C:T") {ReqBSCT=$(o+1)} else if ($o =="Socks/Node") {SocksNode=$(o+1)} else if ($o =="NtasksPerN:B:S:C") {NtasksPerNBSC=$(o+1)} \
                else if ($o =="CoreSpec") {CoreSpec=$(o+1)} else if ($o =="Nodes") {Nodes=$(o+1)} else if ($o =="CPU_IDs") {CPUIDs=$(o+1)} \
                else if ($o =="Mem") {Mem=$(o+1)} else if ($o =="MinCPUsNode") {MinCPUsNode=$(o+1)} else if ($o =="MinMemoryCPU") {MinMemoryCPU=$(o+1)} \
                else if ($o =="MinTmpDiskNode") {MinTmpDiskNode=$(o+1)} else if ($o =="Features") {Features=$(o+1)} else if ($o =="Gres") {Gres=$(o+1)} \
                else if ($o =="Reservation") {Reservation=$(o+1)} else if ($o =="Shared") {Shared=$(o+1)} else if ($o =="Contiguous") {Contiguous=$(o+1)} \
                else if ($o =="Licenses") {Licenses=$(o+1)} else if ($o =="Network") {Network=$(o+1)} else if ($o =="Command") {Command=$(o+1)} \
                else if ($o =="WorkDir") {WorkDir=$(o+1)} else if ($o =="StdErr") {StdErr=$(o+1)} else if ($o =="StdIn") {StdIn=$(o+1)} else if ($o =="StdOut") {StdOut=$(o+1)} \
                {print JobId"|"JobName"|"UserId"|"GroupId"|"Priority"|"Nice"|"Account"|"QOS"|"WCKey"|"Requeue"|"Restarts"|"BatchFlag"|"Reboot\
                "|"ExitCode"|"DerivedExitCode"|"RunTime"|"TimeLimit"|"TimeMin"|"SubmitTime"|"EligibleTime"|"StartTime"|"EndTime\
                "|"PreemptTime"|"SuspendTime"|"SecsPreSuspend"|"Partition"|"AllocNodeSid"|"ReqNodeList"|"ExcNodeList"|"NodeList"|"BatchHost\
                "|"NumNodes"|"NumCPUs"|"CPUsTask"|"ReqBSCT"|"SocksNode"|"NtasksPerNBSC"|"CoreSpec"|"Nodes"|"CPUIDs"|"Mem"|"MinCPUsNode"|"MinMemoryCPU\
                "|"MinTmpDiskNode"|"Features"|"Gres"|"Reservation"|"Shared"|"Contiguous"|"Licenses"|"Network"|"Command"|"WorkDir"|"StdErr"|"StdIn"|"StdOut}}'
}

function sstattokb () {

for i in $( echo $1 |tr "|" " " );do echo $i | awk 'BEGIN{IGNORECASE = 1} function printpower(n,b,p) {printf "%u\n", n*b^p; next}
	/[0-9]$/{print $1;next};
	/[0-9].batch$/{print $1;next};
	/K(iB)?$/{printpower($1,  1, 10)};
	/M(iB)?$/{printpower($1,  2, 10)};
	/G(iB)?$/{printpower($1,  2, 20)};
	/T(iB)?$/{printpower($1,  2, 30)};
	/KB$/{	  printpower($1,  1, 10)};
	/MB$/{    printpower($1, 10,  3)};
	/GB$/{    printpower($1, 10,  6)};
	/TB$/{    printpower($1, 10, 9)}'; done | tr "\n" "|" 

}


function sendtodb () {

	prepasenddb="${WORK_DIR}/tmp/sendtodb.$1"
	iidjob=$1
	echo "START TRANSACTION;" > ${prepasenddb}
	echo "update Clusters set last_refresh_jobstats=\"$(date "+%F %T")\" where idClusters=\"${CLUSTER}\";" >> "${prepasenddb}"
     	# Recuperer params jobs
	echo "INSERT INTO Jobs_Metrics (id_Clusters, idJobs, JobName, UserId, GroupId, Priority, Nice, Account, QOS, WCKey, Requeue, Restarts, BatchFlag, Reboot, ExitCode, DerivedExitCode, RunTime, TimeLimit, TimeMin, SubmitTime, EligibleTime, StartTime, EndTime, PreemptTime, SuspendTime, SecsPreSuspend, Partitions, AllocNodeSid, ReqNodeList, ExcNodeList, NodeList, BatchHost, NumNodes, NumCPUs, CPUsTask, ReqBSCT, SocksNode, NtasksPerNBSC, CoreSpec, Nodes, CPUIDs, Mem, MinCPUsNode, MinMemoryCPU, MinTmpDiskNode, Features, Gres, Reservation, Shared, Contiguous, Licenses, Network, Command, WorkDir, StdErr, StdIn, StdOut) VALUES " >> ${prepasenddb}
	sed -n '/params/,/[:blank:]/p' ${WORK_DIR}/stats/jobs/${iidjob} | sed "1d;s/|/', '/g;s/$/'),/;s/^/('${CLUSTER}', '/;$ s/.$/;/" >> ${prepasenddb}

	countlg=$(sed -n '/monitor/,/$./p' ${WORK_DIR}/stats/jobs/${iidjob} | wc -l)	

	if [[ ${countlg} > 2 ]];then
		# Recuperer monitor jobs
		echo "INSERT INTO Jobs_Metricsdet_${CLUSTER} (idJobs, datetime, stepid, MaxVMSize, MaxVMSizeNode, MaxVMSizeTask, AveVMSize, MaxRSS, MaxRSSNode, MaxRSSTask, AveRSS, MaxPages, MaxPagesNode, MaxPagesTask, AvePages, MinCPU, MinCPUNode, MinCPUTask, AveCPU, NTasks, AveCPUFreq, ReqCPUFreq, ConsumedEnergy, MaxDiskRead, MaxDiskReadNode, MaxDiskReadTask, AveDiskRead, MaxDiskWrite, MaxDiskWriteNode, MaxDiskWriteTask, AveDiskWrite) VALUES" >> ${prepasenddb}
		sed -n '/monitor/,/$./p' ${WORK_DIR}/stats/jobs/${iidjob} | sed "1d;s/|/', '/g;s/$/'),/;s/^/('${iidjob}', '/;$ s/.$/;/" >> ${prepasenddb}
	fi

	IFS="|" read dateend stateend <<< "$(/usr/bin/sacct -a -t --duplicates -j ${iidjob} -n --fields=End,State -P)"
        echo "UPDATE Jobs_Metrics set EndTime = '${dateend}', JobState = '${stateend}' where idJobs = '${iidjob}';" >> ${prepasenddb}

	echo "COMMIT;" >> ${prepasenddb}

        ${MYSQL_FILE} < ${prepasenddb}
	#cp ${prepasenddb} ${prepasenddb}.sav
        if [ $? = 0 ];then
                echo -e "${OK} ${1} Insertion en base réussit "
        else
                echo -e "${FAIL} ${1} Insertion en base echec "
        fi

	rm -f ${WORK_DIR}/stats/jobs/${iidjob} ${prepasenddb}

}



# --------------------- Main --------------------- #


while true
do

        touch ${logrun} ${loglastrun}
        /bin/cp ${logrun} ${loglastrun}
        /usr/bin/squeue --noheader -o "%.12i" -t running | sed 's/^[ \t]*//;s/[ \t]*$//' > ${logrun}
        jobstoprun=$(grep -Fxv -f ${logrun} ${loglastrun})
        newjobrun=$(grep -Fxv -f ${loglastrun} ${logrun})
        jobrun=$(cat ${loglastrun} ${logrun})

        # Marquer fin job
        for i in ${jobstoprun};do
		sendtodb $i &         
        done

        # Marquer Debut job
        for a in ${newjobrun};do

                # Lister detail of job
                echo -e "[params]" >> ${WORK_DIR}/stats/jobs/$a
                paramsjob ${a} >> ${WORK_DIR}/stats/jobs/$a
                echo -e "\n[monitor]" >> ${WORK_DIR}/stats/jobs/$a

        done

       	datetime="$(date +"%Y-%m-%d %H:%M:%S")"
       	for x in ${jobrun};do

       	        monitor=$( /usr/bin/sstat -j $x -a -n --parsable2 2>/dev/null)
       	        if [[ -z "${monitor}" ]];then
       	                monitor=$(/usr/bin/sstat -j $x.batch -a -n --parsable2 2>/dev/null)
       	        fi
		monitortokb=$(sstattokb $monitor)
	
		if [[ ! -z "${monitor}" ]];then
			echo "${datetime}|${monitortokb}" >> ${WORK_DIR}/stats/jobs/$x
		fi
       	done

        sleep 10s
done

