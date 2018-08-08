#!/bin/bash

# --------------------- Definition des variables --------------------- #

# Repertoire de travail du collecteur
WORK_DIR=/srv/cluster_monitor

# Nom du fichier de configuration
CONFIG_FILE_NAME="cluster_monitor.conf"
source ${WORK_DIR}/${CONFIG_FILE_NAME} || exit 1

# Verifie fichier de lock
if [[ -f "${LOCKFILE}" ]];then 
	echo -e ""
	echo -e "${FAIL} - Error fichier de lock présent => ${LOCKFILE}"
	echo -e ""
	exit 1
else
	touch ${LOCKFILE}
fi

# trap ctrl-c and call ctrl_c()
trap ctrl_c 15 2 

function ctrl_c () {
  echo -e ""
  echo -e "Arret de la collecte"
  kill -9 $(ps -ef | grep "${WORK_DIR}" | grep "cluster_monitor_stats.sh" | awk '{print$2}')
  rm -f ${WORK_DIR}/*.lock
  exit 1
}

# --------------------- Definition des functions --------------------- #

# Log function, handles input from stdin or from arguments
# Usage: log message in parts 
# Usage2: echo message | log
function log 
{
  # If there are parameters read from parameters
  if [ $# -gt 0 ]; then
    echo "[$(date +"%D %T")] $@" >> $LOG_FILE
  else 
    # If there are no parameters read from stdin
    while read data
    do
      echo "[$(date +"%D %T")] $data" >> $LOG_FILE 
    done
  fi
}

#Verification de la disponibilitée du gestionnaire de batch
function testgestionnairebatch ()
{
  ${CMDVDGB}
  if [[ "$?" == 1 ]]
  then
    log "[ERROR] Gestionnaire de batch indisponible"
    return 1
  else
    return 0
  fi
}

#Recuperation de la configuration du gestionnaire de batch
function configClusters ()
{
  echo "-- configClusters" >> "${TEMP_FILE_BDD}"
	 
  local GBCONFIG=̀$(${CMDCONFGB})
  echo "update Clusters set config=\"${GBCONFIG}\" where idClusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"

  local GBVERSION=$(${CMDVGB})
  local DB_GBVERSION=$(${MYSQL} "select SlurmVersion from Clusters where idClusters=\"${CLUSTER}\";")
  if [[ ${GBVERSION} != ${DB_GBVERSION} ]]
  then
    echo "update Clusters set SlurmVersion=\"${GBVERSION}\" where idClusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  fi
  echo "update Clusters set interconnect=\"${INTERCONNECT}\", jobmetrics=\"${JOBSMETRICS}\" where idClusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
   
}

#Verification de la frontale a utiliser pour recuperer 
function getFrontalToUse ()
{
  for i in ${FRONTAUX}
  do
    ping -c 1 ${i} > /dev/null 2>&1 && FRONTAL=${i}
  done
  if [[ -z "${FRONTAL}" ]]
  then
    log "[ERROR] Pas de frontale disponible"
    return 1
  else
    return 0
  fi
}

#Obtention de la configuration des QOS
function configQOS ()
{
  echo "-- configQOS" >> "${TEMP_FILE_BDD}"
  while read LIGNE 
  do
    IFS="|" read Flags GraceTime GrpTRESMins GrpTRESRunMins GrpTRES GrpJobs GrpSubmitJobs GrpWall ID MaxTRESMins MaxTRESPerAccount MaxTRESPerJob MaxTRESPerNode MaxTRESPerUser  MaxJobsPerAccount MaxJobsPerUser MinTRESPerJob MaxSubmitJobsPerAccount MaxSubmitJobsPerUser MaxWall QOS Preempt PreemptMode Priority UsageFactor UsageThreshold MaxCPUsPerJob MaxNodesPerJob <<< "${LIGNE}"
    QOSACTIVE=${QOSACTIVE}" "${QOS}
    
	[[ -z "${Flags}" ]] && Flags="0"
	[[ -z "${GraceTime}" ]] && GraceTime="0"
	[[ -z "${GrpTRESMins}" ]] && GrpTRESMins="0"
	[[ -z "${GrpTRESRunMins}" ]] && GrpTRESRunMins="0"
	[[ -z "${GrpTRES}" ]] && GrpTRES="0"
	[[ -z "${GrpJobs}" ]] && GrpJobs="0"
	[[ -z "${GrpSubmitJobs}" ]] && GrpSubmitJobs="0"
	[[ -z "${GrpWall}" ]] && GrpWall="0"
	[[ -z "${ID}" ]] && ID="0"
	[[ -z "${MaxTRESMins}" ]] && MaxTRESMins="0"
	[[ -z "${MaxTRESPerAccount}" ]] && MaxTRESPerAccount="0"
	[[ -z "${MaxTRESPerJob}" ]] && MaxTRESPerJob="0"
	[[ -z "${MaxTRESPerNode}" ]] && MaxTRESPerNode="0"
	[[ -z "${MaxTRESPerUser}" ]] && MaxTRESPerUser="0"
	[[ -z "${MaxJobsPerAccount}" ]] && MaxJobsPerAccount="0"
	[[ -z "${MaxJobsPerUser}" ]] && MaxJobsPerUser="0"
	[[ -z "${MinTRESPerJob}" ]] && MinTRESPerJob="0"
	[[ -z "${MaxSubmitJobsPerAccount}" ]] && MaxSubmitJobsPerAccount="0"
	[[ -z "${MaxSubmitJobsPerUser}" ]] && MaxSubmitJobsPerUser="0"
	[[ -z "${MaxWall}" ]] && MaxWall="0"
	[[ -z "${MaxCPUsPerJob}" ]] && MaxCPUsPerJob="0"
	[[ -z "${MaxNodesPerJob}" ]] && MaxNodesPerJob="0"
	[[ -z "${Name}" ]] && Name="0"
	[[ -z "${Preempt}" ]] && Preempt="0"
	[[ -z "${PreemptMode}" ]] && PreemptMode="0"
	[[ -z "${Priority}" ]] && Priority="0"
	[[ -z "${UsageFactor}" ]] && UsageFactor="0"
	[[ -z "${UsageThreshold}" ]] && UsageThreshold="0"

     echo "REPLACE INTO QOS (Flags, GraceTime, GrpTRESMins, GrpTRESRunMins, GrpTRES, GrpJobs, GrpSubmitJobs, GrpWall, ID, MaxTRESMins, MaxTRESPerAccount, MaxTRESPerJob, MaxTRESPerNode, MaxTRESPerUser, MaxJobsPerAccount, MaxJobsPerUser, MinTRESPerJob, MaxSubmitJobsPerAccount, MaxSubmitJobsPerUser, MaxWall, idQOS, Preempt, PreemptMode, Priority, UsageFactor, UsageThreshold, MaxCPUsPerJob, MaxNodesPerJob, is_active, id_Clusters ) values (\"${Flags}\", \"${GraceTime}\", \"${GrpTRESMins}\", \"${GrpTRESRunMins}\", \"${GrpTRES}\", \"${GrpJobs}\", \"${GrpSubmitJobs}\", \"${GrpWall}\", \"${ID}\", \"${MaxTRESMins}\", \"${MaxTRESPerAccount}\", \"${MaxTRESPerJob}\", \"${MaxTRESPerNode}\", \"${MaxTRESPerUser}\", \"${MaxJobsPerAccount}\", \"${MaxJobsPerUser}\", \"${MinTRESPerJob}\", \"${MaxSubmitJobsPerAccount}\", \"${MaxSubmitJobsPerUser}\", \"${MaxWall}\", \"${QOS}\", \"${Preempt}\", \"${PreemptMode}\", \"${Priority}\", \"${UsageFactor}\", \"${UsageThreshold}\", \"${MaxCPUsPerJob}\", \"${MaxNodesPerJob}\", \"1\", \"${CLUSTER}\" ) ;" >> "${TEMP_FILE_BDD}"
     
  done <<< "$(sacctmgr -P -n list qos format=Flags,GraceTime,GrpTRESMins,GrpTRESRunMins,GrpTRES,GrpJobs,GrpSubmitJobs,GrpWall,ID,MaxTRESMins,MaxTRESPerAccount,MaxTRESPerJob,MaxTRESPerNode,MaxTRESPerUser,MaxJobsPerAccount,MaxJobsPerUser,MinTRESPerJob,MaxSubmitJobsPerAccount,MaxSubmitJobsPerUser,MaxWall,Name,Preempt,PreemptMode,Priority,UsageFactor,UsageThreshold,MaxCPUsPerJob,MaxNodesPerJob)"

  CONDITION=""
  for i in ${QOSACTIVE}
  do
    CONDITION=${CONDITION}"idQOS != \"${i}\" and "
  done

  REQ_REPONSE=$(${MYSQL} "select idQOS from QOS where ${CONDITION} id_Clusters=\"${CLUSTER}\" and is_active=1;")

  while read DB_QOS
  do
    [[ -z "${DB_QOS}" ]] || echo "update QOS set is_active=0 where id_Clusters=\"${CLUSTER}\" and idQOS=\"${DB_QOS}\";" >> "${TEMP_FILE_BDD}"
  done <<< "${REQ_REPONSE}"
}

#Obtention de la configuration des FS
function configFilesystems ()
{
  echo "-- configFilesystems" >> "${TEMP_FILE_BDD}"
  for VARFSS in ${FILESYSTEM}
  do
    MNTPT=$(echo ${VARFSS} | awk -F "|" '{print$2}')
    VARFS=$(echo ${VARFSS} | awk -F "|" '{print$1}')
    TYPE=$(ssh ${FRONTAL} mount | awk -v filesystem="/${MNTPT}" '{ for(o=1;o<=NF;o++) if ($o == filesystem) {print $(o+2)} }')
    FSACTIVE=${FSACTIVE}" "${VARFS}
    REQ_REPONSE=$(${MYSQL} "select idFilesystems, type, is_active from Filesystems where id_Clusters=\"${CLUSTER}\" and idFilesystems=\"${VARFS}\";" |tr '\t' '|')
    IFS="|" read DB_FS DB_TYPE ISACTIVE <<< "${REQ_REPONSE}"

    if [[ "${VARFS}" = "${DB_FS}" ]]
    then
      if [[ "${TYPE}" != "${DB_TYPE}" || "${ISACTIVE}" != 1 ]]
      then
        echo "update Filesystems set type=\"${TYPE}\", is_active=1 where id_Clusters=\"${CLUSTER}\" and idFilesystems=\"${VARFS}\";" >> "${TEMP_FILE_BDD}"
      fi
    else
      echo "insert into Filesystems (idFilesystems, id_Clusters, type, is_active) values (\"${VARFS}\", \"${CLUSTER}\", \"${TYPE}\", 1);" >> "${TEMP_FILE_BDD}"
    fi
  done

  CONDITION=""
  for i in ${FSACTIVE}
  do
    CONDITION=${CONDITION}"idFilesystems != \"${i}\" and "
  done

  REQ_REPONSE=$(${MYSQL} "select idFilesystems from Filesystems where ${CONDITION} id_Clusters=\"${CLUSTER}\" and is_active=1;")

  while read DB_FS
  do
    [[ -z "${DB_FS}" ]] || echo "update Filesystems set is_active=0 where id_Clusters=\"${CLUSTER}\" and idFilesystems=\"${DB_FS}\";" >> "${TEMP_FILE_BDD}"
  done <<< "${REQ_REPONSE}"
}

#Obtention de la configuration des account-slurm
function configAccount ()
{
  echo "-- configaccount" >> "${TEMP_FILE_BDD}"
  echo "DELETE FROM saccount where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  while read LIGNE 
  do
    IFS="|" read Account Description Organization Coordinators  <<< "${LIGNE}"

	IFS="|" read Account User RawShares NormShares RawUsage NormUsage EffectvUsage FairShare LevelFS GrpTRESMins TRESRunMins <<< "$(sshare  -l -P -n -A ${Account})"

        [[ -z "${Account}" ]] && Account="0"
        [[ -z "${Description}" ]] && Description="0"
        [[ -z "${Organization}" ]] && Organization="0"
        [[ -z "${Coordinators}" ]] && Coordinators="0"
	[[ -z "${RawShares}" ]] && RawShares="0"
	[[ -z "${NormShares}" ]] && NormShares="0"
	[[ -z "${RawUsage}" ]] && RawUsage="0"
	[[ -z "${NormUsage}" ]] && NormUsage="0"
	[[ -z "${EffectvUsage}" ]] && EffectvUsage="0"
	[[ -z "${FairShare}" ]] && FairShare="0"
	[[ -z "${LevelFS}" ]] && LevelFS="0"
	[[ -z "${GrpTRESMins}" ]] && GrpTRESMins="0"
	[[ -z "${TRESRunMins}" ]] && TRESRunMins="0"


     echo "INSERT INTO saccount (Account, Description, Organization, Coordinators, id_Clusters, RawShares, NormShares, RawUsage, NormUsage, EffectvUsage, FairShare, LevelFS, GrpTRESMins, TRESRunMins ) values (\"${Account}\", \"${Description}\", \"${Organization}\", \"${Coordinators}\", \"${CLUSTER}\", \"${RawShares}\", \"${NormShares}\", \"${RawUsage}\", \"${NormUsage}\", \"${EffectvUsage}\", \"${FairShare}\", \"${LevelFS}\", \"${GrpTRESMins}\", \"${TRESRunMins}\" ) ;" >> "${TEMP_FILE_BDD}"

  done <<< "$(sacctmgr list account -P -n format=Account,Description,Organization,Coordinators)"
}


#Obtention de la configuration des users-slurm
function configUser ()
{
  echo "-- configuser" >> "${TEMP_FILE_BDD}"
  echo "DELETE FROM suers where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  while read LIGNE 
  do
    IFS="|" read AdminLevel DefaultAccount Coordinators User  <<< "${LIGNE}"

        [[ -z "${AdminLevel}" ]] && AdminLevel="0"
        [[ -z "${DefaultAccount}" ]] && DefaultAccount="0"
        [[ -z "${Coordinators}" ]] && Coordinators="0"
        [[ -z "${User}" ]] && User="0"
 
     echo "INSERT INTO suers (AdminLevel, DefaultAccount, Coordinators, User, id_Clusters) values (\"${AdminLevel}\", \"${DefaultAccount}\", \"${Coordinators}\", \"${User}\", \"${CLUSTER}\" ) ;" >> "${TEMP_FILE_BDD}"

  done <<< "$(sacctmgr list user -P -n format=AdminLevel,DefaultAccount,Coordinators,User)"
}

#Obtention de la configuration des assoc
function configAssoc ()
{
  echo "-- configassoc" >> "${TEMP_FILE_BDD}"
  echo "DELETE FROM sassoc where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  while read LIGNE 
  do
    IFS="|" read Account DefaultQOS Fairshare GrpTRESMins GrpTRESRunMins GrpTRES GrpJobs GrpSubmitJobs GrpWall LFT MaxTRESMins MaxTRES MaxJobs MaxSubmitJobs MaxWall Qos ParentID ParentName Partitions RGT User <<< "${LIGNE}"

        [[ -z "${Account}" ]] && Account="0"
        [[ -z "${DefaultQOS}" ]] && DefaultQOS="0"
        [[ -z "${Fairshare}" ]] && Fairshare="0"
        [[ -z "${GrpTRESMins}" ]] && GrpTRESMins="0"
        [[ -z "${GrpTRESRunMins}" ]] && GrpTRESRunMins="0"
        [[ -z "${GrpTRES}" ]] && GrpTRES="0"
        [[ -z "${GrpJobs}" ]] && GrpJobs="0"
        [[ -z "${GrpSubmitJobs}" ]] && GrpSubmitJobs="0"
        [[ -z "${GrpWall}" ]] && GrpWall="0"
        [[ -z "${LFT}" ]] && LFT="0"
        [[ -z "${MaxTRESMins}" ]] && MaxTRESMins="0"
        [[ -z "${MaxTRES}" ]] && MaxTRES="0"
        [[ -z "${MaxJobs}" ]] && MaxJobs="0"
        [[ -z "${MaxSubmitJobs}" ]] && MaxSubmitJobs="0"
        [[ -z "${MaxWall}" ]] && MaxWall="0"
        [[ -z "${Qos}" ]] && Qos="0"
        [[ -z "${ParentID}" ]] && ParentID="0"
        [[ -z "${ParentName}" ]] && ParentName="0"
        [[ -z "${Partitions}" ]] && Partitions="0"
        [[ -z "${RGT}" ]] && RGT="0"
        [[ -z "${User}" ]] && User="0"

	echo "INSERT INTO sassoc (Account, DefaultQOS, Fairshare, GrpTRESMins, GrpTRESRunMins, GrpTRES, GrpJobs, GrpSubmitJobs, GrpWall, LFT, MaxTRESMins, MaxTRES, MaxJobs, MaxSubmitJobs, MaxWall, Qos, ParentID, ParentName, Partitions, RGT, User, id_Clusters) values (\"${Account}\", \"${DefaultQOS}\", \"${Fairshare}\", \"${GrpTRESMins}\", \"${GrpTRESRunMins}\", \"${GrpTRES}\", \"${GrpJobs}\", \"${GrpSubmitJobs}\", \"${GrpWall}\", \"${LFT}\", \"${MaxTRESMins}\", \"${MaxTRES}\", \"${MaxJobs}\", \"${MaxSubmitJobs}\", \"${MaxWall}\", \"${Qos}\", \"${ParentID}\", \"${ParentName}\", \"${Partitions}\", \"${RGT}\", \"${User}\", \"${CLUSTER}\" ) ;" >> "${TEMP_FILE_BDD}"

  done <<< "$(sacctmgr list assoc -P -n format=Account,DefaultQOS,Fairshare,GrpTRESMins,GrpTRESRunMins,GrpTRES,GrpJobs,GrpSubmitJobs,GrpWall,LFT,MaxTRESMins,MaxTRES,MaxJobs,MaxSubmitJobs,MaxWall,Qos,ParentID,ParentName,Partition,RGT,User
)"

}

#Obtention de la configuration des partitions
function configPartitions ()
{
  echo "-- configPartitions" >> "${TEMP_FILE_BDD}"
  while read LIGNE 
  do
    IFS="|" read PartitionName DefaultTime DefMemPerCPU Shared Default State Hidden AllowGroups Nodes TotalNodes TotalCPUs AllowAccounts AllowQos AllocNodes QoS DisableRootJobs ExclusiveUser GraceTime PriorityJobFactor PriorityTier RootOnly ReqResv OverSubscribe OverTimeLimit PreemptMode SelectTypeParameters DefMemPerNode MaxMemPerNode <<< "${LIGNE}"
    PARTITIONACTIVE=${PARTITIONACTIVE}" "${PartitionName}

        [[ -z "${PartitionName}" ]] && PartitionName="0"
        [[ -z "${DefaultTime}" ]] && DefaultTime="0"
        [[ -z "${DefMemPerCPU}" ]] && DefMemPerCPU="0"
        [[ -z "${Shared}" ]] && Shared="0"
        [[ -z "${Default}" ]] && Default="0"
        [[ -z "${State}" ]] && State="0"
        [[ -z "${Hidden}" ]] && Hidden="0"
        [[ -z "${AllowGroups}" ]] && AllowGroups="0"
        [[ -z "${Nodes}" ]] && Nodes="0"
        [[ -z "${TotalNodes}" ]] && TotalNodes="0"
        [[ -z "${TotalCPUs}" ]] && TotalCPUs="0"
        [[ -z "${AllowAccounts}" ]] && AllowAccounts="0"
        [[ -z "${AllowQos}" ]] && AllowQos="0"
        [[ -z "${AllocNodes}" ]] && AllocNodes="0"
        [[ -z "${QoS}" ]] && QoS="0"
        [[ -z "${DisableRootJobs}" ]] && DisableRootJobs="0"
        [[ -z "${ExclusiveUser}" ]] && ExclusiveUser="0"
        [[ -z "${GraceTime}" ]] && GraceTime="0"
        [[ -z "${PriorityJobFactor}" ]] && PriorityJobFactor="0"
        [[ -z "${PriorityTier}" ]] && PriorityTier="0"
        [[ -z "${RootOnly}" ]] && RootOnly="0"
        [[ -z "${ReqResv}" ]] && ReqResv="0"
        [[ -z "${OverSubscribe}" ]] && OverSubscribe="0"
        [[ -z "${OverTimeLimit}" ]] && OverTimeLimit="0"
        [[ -z "${PreemptMode}" ]] && PreemptMode="0"
        [[ -z "${SelectTypeParameters}" ]] && SelectTypeParameters="0"
        [[ -z "${DefMemPerNode}" ]] && DefMemPerNode="0"
        [[ -z "${MaxMemPerNode}" ]] && MaxMemPerNode="0"

    echo "REPLACE INTO Partitions (idPartitions, DefaultTime, DefMemPerCPU, Shared, isDefault, State, Hidden, AllowGroups, Nodes, TotalNodes, TotalCPUs, AllowAccounts, AllowQos, AllocNodes, QoS, DisableRootJobs, ExclusiveUser, GraceTime, PriorityJobFactor, PriorityTier, RootOnly, ReqResv, OverSubscribe, OverTimeLimit, PreemptMode, SelectTypeParameters, DefMemPerNode, MaxMemPerNode, is_active, id_Clusters) values (\"${PartitionName}\", \"${DefaultTime}\", \"${DefMemPerCPU}\", \"${Shared}\", \"${Default}\", \"${State}\", \"${Hidden}\", \"${AllowGroups}\", \"${Nodes}\", \"${TotalNodes}\", \"${TotalCPUs}\", \"${AllowAccounts}\", \"${AllowQos}\", \"${AllocNodes}\", \"${QoS}\", \"${DisableRootJobs}\", \"${ExclusiveUser}\", \"${GraceTime}\", \"${PriorityJobFactor}\", \"${PriorityTier}\", \"${RootOnly}\", \"${ReqResv}\", \"${OverSubscribe}\", \"${OverTimeLimit}\", \"${PreemptMode}\", \"${SelectTypeParameters}\", \"${DefMemPerNode}\", \"${MaxMemPerNode}\", \"1\", \"${CLUSTER}\" ) ;" >> "${TEMP_FILE_BDD}"

  done <<<  "$(scontrol -o show part | awk -F '[= ]' '{ for(o=1;o<=NF;o++) \
	if ($o =="PartitionName") {PartitionName=$(o+1)} \
	else if ($o =="DefaultTime") {DefaultTime=$(o+1)} \
	else if ($o =="DefMemPerCPU") {DefMemPerCPU=$(o+1)} \
	else if ($o =="Shared") {Shared=$(o+1)} \
	else if ($o =="Default") {Default=$(o+1)} \
	else if ($o =="State") {State=$(o+1)} \
	else if ($o =="Hidden") {Hidden=$(o+1)} \
	else if ($o =="AllowGroups") {AllowGroups=$(o+1)} \
	else if ($o =="Nodes") {Nodes=$(o+1)} \
	else if ($o =="TotalNodes") {TotalNodes=$(o+1)} \
	else if ($o =="TotalCPUs") {TotalCPUs=$(o+1)} \
	else if ($o =="AllowAccounts") {AllowAccounts=$(o+1)} \
	else if ($o =="AllowQos") {AllowQos=$(o+1)} \
	else if ($o =="AllocNodes") {AllocNodes=$(o+1)} \
	else if ($o =="QoS") {QoS=$(o+1)} \
	else if ($o =="DisableRootJobs") {DisableRootJobs=$(o+1)} \
	else if ($o =="ExclusiveUser") {ExclusiveUser=$(o+1)} \
	else if ($o =="GraceTime") {GraceTime=$(o+1)} \
	else if ($o =="PriorityJobFactor") {PriorityJobFactor=$(o+1)} \
	else if ($o =="PriorityTier") {PriorityTier=$(o+1)} \
	else if ($o =="RootOnly") {RootOnly=$(o+1)} \
	else if ($o =="ReqResv") {ReqResv=$(o+1)} \
	else if ($o =="OverSubscribe") {OverSubscribe=$(o+1)} \
	else if ($o =="OverTimeLimit") {OverTimeLimit=$(o+1)} \
	else if ($o =="PreemptMode") {PreemptMode=$(o+1)} \
	else if ($o =="SelectTypeParameters") {SelectTypeParameters=$(o+1)} \
	else if ($o =="DefMemPerNode") {DefMemPerNode=$(o+1)} \
	else if ($o =="MaxMemPerNode") {MaxMemPerNode=$(o+1)} \
{print PartitionName"|"DefaultTime"|"DefMemPerCPU"|"Shared"|"Default"|"State"|"Hidden"|"AllowGroups"|"Nodes"|"TotalNodes"|"TotalCPUs"|"AllowAccounts"|"AllowQos"|"AllocNodes"|"QoS"|"DisableRootJobs"|"ExclusiveUser"|"GraceTime"|"PriorityJobFactor"|"PriorityTier"|"RootOnly"|"ReqResv"|"OverSubscribe"|"OverTimeLimit"|"PreemptMode"|"SelectTypeParameters"|"DefMemPerNode"|"MaxMemPerNode}} ')"

  CONDITION=""
  for i in ${PARTITIONACTIVE}
  do
    CONDITION=${CONDITION}"idPartitions != \"${i}\" and "
  done

  REQ_REPONSE=$(${MYSQL} "select idPartitions from Partitions where ${CONDITION} id_Clusters=\"${CLUSTER}\" and is_active=1;")

  while read DB_PARTITION
  do
    [[ -z "${DB_PARTITION}" ]] || echo "update Partitions set is_active=0 where id_Clusters=\"${CLUSTER}\" and idPartitions=\"${DB_PARTITION}\";" >> "${TEMP_FILE_BDD}"
  done <<< "${REQ_REPONSE}"
}

#Insertion des Frontaux en bdd
function configFrontaux ()
{
  echo "-- configFrontaux" >> "${TEMP_FILE_BDD}"
  for i in ${FRONTAUX}
  do
    BDD_FRONTAL=$(${MYSQL} "select idFrontaux from Frontaux where idFrontaux=\"${i}\";")
    if [[ "${BDD_FRONTAL}" != "${i}" ]]
    then
      echo "insert into Frontaux (idFrontaux, id_Clusters) values (\"${i}\", \"${CLUSTER}\");" >> "${TEMP_FILE_BDD}"
    fi
  done
}

#Collecte des taux de remplissage des FS
function collectFS ()
{
  echo "-- collectFS" >> "${TEMP_FILE_BDD}"
  for VARFSS in ${FILESYSTEM}
  do
    MNTPT=$(echo ${VARFSS} | awk -F "|" '{print$2}')
    VARFS=$(echo ${VARFSS} | awk -F "|" '{print$1}')
    IFS="|" read UTIL DISPO UTILINODE DISPOINODE <<< "$(ssh ${FRONTAL} "${DF} /${MNTPT} | tail -n1 ;${DF} -i /${MNTPT} | tail -n1 " |awk '{if ($1 ~ /^[0-9]/) print $2"|"$3; else print $3"|"$4}' | tr "\n" "|" | sed 's/.$//')"
    if [[ -z "${DISPO}" || -z "${UTIL}" || -z "${DISPOINODE}" || -z "${UTILINODE}" ]]
    then
      echo "insert into Collect_FS (id_Filesystems, id_Clusters, disponible, utilise, disponible_inode, utilise_inode, Dispo) values (\"${VARFS}\", \"${CLUSTER}\", 0, 0, 0, 0, 0);" >> "${TEMP_FILE_BDD}"
    else 
      echo "insert into Collect_FS (id_Filesystems, id_Clusters, disponible, utilise, disponible_inode, utilise_inode, Dispo) values (\"${VARFS}\", \"${CLUSTER}\", \"${DISPO}\", \"${UTIL}\", \"${DISPOINODE}\", \"${UTILINODE}\", 1);" >> "${TEMP_FILE_BDD}"
    fi
  done
}

#Collecte de l'utilisation des noeuds
function collectNodes ()
{
  echo "-- collectNodes" >> "${TEMP_FILE_BDD}"
  IFS="|" read ALLOCATED IDLE OTHER TOTAL <<< "$(sinfo -h -o %F | awk -F '/' '{for (o=1;o<=NF;o++) if ($o ~ /.*K/) { $o=$o*1000 }} END {print $1"|"$2"|"$3"|"$4 }')"
  echo "insert into Collect_nodes (id_Clusters, allocated, idle, other, total) values (\"${CLUSTER}\", \"${ALLOCATED}\", \"${IDLE}\", \"${OTHER}\", \"${TOTAL}\");" >> "${TEMP_FILE_BDD}"
}

#Collecte du taux d'utilisation du clusters
function collectClusters ()
{
  echo "-- collectClusters" >> "${TEMP_FILE_BDD}"
  TOTAL_ALLOCATED=0
  TOTAL_IDLE=0
  TOTAL_OTHER=0
  TOTAL_TOTAL=0
  for i in ${PARTITION_CLUSTER}
  do
    IFS="|" read ALLOCATED IDLE OTHER TOTAL <<< "$(sinfo -h -p ${i} -o %C | awk -F '/' '{for (o=1;o<=NF;o++) if ($o ~ /.*K/) { $o=$o*1000 }} END {print $1"|"$2"|"$3"|"$4 }')"
    TOTAL_ALLOCATED=$(expr ${TOTAL_ALLOCATED} + ${ALLOCATED})
    TOTAL_IDLE=$(expr ${TOTAL_IDLE} + ${IDLE})
    TOTAL_OTHER=$(expr ${TOTAL_OTHER} + ${OTHER})
    TOTAL_TOTAL=$(expr ${TOTAL_TOTAL} + ${TOTAL})
    echo "insert into Collect_Clusters (id_Clusters, CPU_allocated, CPU_idle, CPU_other, CPU_total) values (\"${CLUSTER}\", \"${TOTAL_ALLOCATED}\", \"${TOTAL_IDLE}\", \"${TOTAL_OTHER}\", \"${TOTAL_TOTAL}\");" >> "${TEMP_FILE_BDD}"
  done
}

#Collecte des taux d'utilisation des partitions
function collectPartitions ()
{
  echo "-- collectPartitions" >> "${TEMP_FILE_BDD}"
  while read LIGNE
  do
    IFS="|" read ALLOCATED IDLE OTHER TOTAL <<< "$(sinfo -h -p ${LIGNE} -o %F | awk -F '/' '{for (o=1;o<=NF;o++) if ($o ~ /.*K/) { $o=$o*1000 }} END {print $1"|"$2"|"$3"|"$4 }')"
    NBJOBPENDING=$(squeue -h -t pd -p ${LIGNE} |wc -l)
    echo "insert into Collect_partitions (id_Partitions, id_Clusters, Nombre_job_pd, CPU_allocated, CPU_idle, CPU_other, CPU_total) values (\"${LIGNE}\", \"${CLUSTER}\", \"${NBJOBPENDING}\", \"${ALLOCATED}\", \"${IDLE}\", \"${OTHER}\", \"${TOTAL}\");" >> "${TEMP_FILE_BDD}"
  done <<< "$(sinfo -h -o %P | sed 's/*//g')" 
}

#Collecte des jobs sur le cluster
function collectJobs ()
{
  echo "-- collectJobs" >> "${TEMP_FILE_BDD}"
  echo "insert into Collect_Jobs (Jobid, id_Partitions, id_Clusters, id_QOS, Name, User, State, Time, TimeLimit, Nodes, Cpus, StartTime, EndTime, Priority, Nodelist) values" >> "${TEMP_FILE_BDD}"
  squeue --noheader -o '("%.12i","%.50P","'${CLUSTER}'","%.100q","%.100j","%.45u","%.45T","%.12M","%.12l","%.8D","%.8C","%.20S","%.20e","%.10Q","%.300R"),' |tr -d ' ' | sed '$ s/.$/;/' >> "${TEMP_FILE_BDD}"
}

#Collecte des informations frontaux
function collectFrontaux ()
{
  echo "-- collectFrontaux" >> "${TEMP_FILE_BDD}"
  for i in ${FRONTAUX}
  do
    ping -c 1 ${i} > /dev/null 2>&1
    if [[ "$?" == "0" ]]
    then
      IFS="|" read UPTIME NBUSER LOAD1 LOAD5 LOAD15 read <<< "$(ssh ${i} w |head -n 1 |awk '{print $3" "$4"|"$6"|"$10"|"$11"|"$12}'| sed 's/,|/|/g')"
      echo "insert into Collect_Frontaux (id_Frontaux, id_Clusters, load1, load5, load15, nb_user, uptime, Dispo) values (\"${i}\", \"${CLUSTER}\", \"${LOAD1}\", \"${LOAD5}\", \"${LOAD15}\", \"${NBUSER}\", \"${UPTIME}\", 1);" >> "${TEMP_FILE_BDD}"
    else
      echo "insert into Collect_Frontaux (id_Frontaux, id_Clusters, Dispo) values (\"${i}\", \"${CLUSTER}\", 0);" >> "${TEMP_FILE_BDD}"
    fi
  done
}

#Collecte des quota xfs
function collectQuotaXfs ()
{
  echo "-- collectQuotaXfs" >> "${TEMP_FILE_BDD}"
  LISTUSERGRP=$(getent group ${GROUPE} | awk -F ":" '{print$4}' | sed 's/,/ |/g')
  TYPEQUOTA="prjquota_xfs"
  for i in ${VOLFSXFS};do
	VGHOME=$(echo $i | awk -F "|" '{print$1}')
	FILESYS=$(echo $i | awk -F "|" '{print$2}')
	QUOTATYPE=$(echo $i | awk -F "|" '{print$3}')
  	echo "insert into Collect_Quota (id_Filesystems, user_group, id_Clusters, quota, disponible, utilise, quota_inode, disponible_inode, utilise_inode, quotatype) values" >> "${TEMP_FILE_BDD}"

	case $QUOTATYPE in
            p)   reporttype="-p"; TYPEQUOTA="prjquota_xfs";;
            u)   reporttype="-u"; TYPEQUOTA="uquota_xfs";;
            g)   reporttype="-g"; TYPEQUOTA="gquota_xfs";;
            *)   reporttype="-u"; TYPEQUOTA="uquota_xfs";;
        esac

  	ssh ${SRVNFS} "/usr/sbin/xfs_quota -x -c 'report ${reporttype} -bi' ${VGHOME} 2>/dev/null" | \
	grep -Ei "${LISTUSERGRP}" | \
	sed '1,4d;/^$/d;s/media'${FILESYS}'//g;s/'${FILESYS}'//g;s/\[[^]]*\]//g' | \
	awk '{rst=$3-$2}{rsti=$7-$6}{if (rsti<0) rsti=0} {print "(\"'${FILESYS}'\", \""tolower($1)"\", \"'${CLUSTER}'\", \""$3"\", \""rst"\", \""$2"\", \""$7"\", \""rsti"\", \""$6"\", \"'${TYPEQUOTA}'\"),"}' | \
	sed '$ s/.$/;/' >> "${TEMP_FILE_BDD}"
  done
}

#Collecte des quota gpfs
function collectQuotaGpfs ()
{
  echo "-- collectQuotaGpfs" >> "${TEMP_FILE_BDD}"
  
  TYPEQUOTA="fileset_gpfs"
  for i in ${VOLFSGPFS};do
	TYPEQUOTA=$(echo $i | awk -F "|" '{print$3}')
	VGHOME=$(echo $i | awk -F "|" '{print$1}')
	FILESYS=$(echo $i | awk -F "|" '{print$2}')
	echo "insert into Collect_Quota (id_Filesystems, user_group, id_Clusters, quota, disponible, utilise, quota_inode, disponible_inode, utilise_inode, quotatype) values" >> "${TEMP_FILE_BDD}"
	if [ "${TYPEQUOTA}" = "user" ];then
		ssh ${SRVGPFS} "/usr/lpp/mmfs/bin/mmrepquota -u ${VGHOME} | sed 's/Block Limits//g;s/grace//g;s/[0-9] days//g;s/expired//g;s/none//g;1,2d'"  | \
		awk '{rst=$6-$4;if (rst<0) rst=0;quota=$6;dispo=$4;quotai=$11;dispoi=$9}{rsti=$11-$10}{if (rsti<0) rsti=0}{$nni="getent passwd "$1"| cut -d: -f1"; $nni| getline var;} {print "(\"'${FILESYS}'\", \""tolower(var)"\", \"'${CLUSTER}'\", \""quota"\", \""rst"\", \""dispo"\", \""quotai"\", \""rsti"\", \""dispoi"\", \"'${TYPEQUOTA}'\"),"}' | \
        	sed '$ s/.$/;/' >> "${TEMP_FILE_BDD}"
	elif [ "${TYPEQUOTA}" = "group" ];then
		ssh ${SRVGPFS} "/usr/lpp/mmfs/bin/mmrepquota -g ${VGHOME} | sed 's/Block Limits//g;s/grace//g;s/[0-9] days//g;s/expired//g;s/none//g;1,2d'"  | \
		awk '{rst=$6-$4;if (rst<0) rst=0;quota=$6;dispo=$4;quotai=$11;dispoi=$9}{rsti=$11-$10}{if (rsti<0) rsti=0}{$nni="getent group "$1"| cut -d: -f1"; $nni| getline var;} {print "(\"'${FILESYS}'\", \""tolower(var)"\", \"'${CLUSTER}'\", \""quota"\", \""rst"\", \""dispo"\", \""quotai"\", \""rsti"\", \""dispoi"\", \"'${TYPEQUOTA}'\"),"}' | \
                sed '$ s/.$/;/' >> "${TEMP_FILE_BDD}"
	fi
  done

}

#Collecte des quota lustre
function collectQuotaLustre ()
{
  echo "-- collectQuotaLustre" >> "${TEMP_FILE_BDD}"
  TYPEQUOTA="lustre"	
  
	for i in ${VOLLUSTRE};do
		VGHOME=$(echo $i | awk -F "|" '{print$1}')
  		FILESYS=$(echo $i | awk -F "|" '{print$2}')
		echo "insert into Collect_Quota (id_Filesystems, user_group, id_Clusters, quota, disponible, utilise, quota_inode, disponible_inode, utilise_inode, quotatype) values" >> "${TEMP_FILE_BDD}"
		ssh ${SRVLUSTRE} 'for i in $(getent group '${GROUPE}' | cut -d: -f4 | tr "," " ");do echo -n $i;lfs quota -q -u $(id -u $i) '${VGHOME}' ;done' | \
		awk '{rst=$5-$3}{rsti=$9-$7}{if (rsti<0) rsti=0} {print "(\"'${FILESYS}'\", \""tolower($1)"\", \"'${CLUSTER}'\", \""$5"\", \""rst"\", \""$3"\", \""$7"\", \""rsti"\", \""$97"\", \"'${TYPEQUOTA}'\"),"}' | \
		sed '$ s/.$/;/' >> "${TEMP_FILE_BDD}"
	done
}

#Collecte des réservations
function collectReservation ()
{
  echo "-- collectReservation" >> "${TEMP_FILE_BDD}"
  while read LIGNE
  do
    IFS="|" read RESERVATIONNAME STARTTIME ENDTIME DURATION NODES NODECNT CORECNT USERS STATE <<< "${LIGNE}"
    [[ -z ${RESERVATIONNAME} ]] || echo "insert into Reservations (ReservationName,id_Clusters,StartTime,EndTime,Duration,Nodes,NodeCnt,CoreCnt,Users,State) values (\"${RESERVATIONNAME}\", \"${CLUSTER}\", \"${STARTTIME}\", \"${ENDTIME}\", \"${DURATION}\", \"${NODES}\", \"${NODECNT}\", \"${CORECNT}\", \"${USERS}\", \"${STATE}\");" >> "${TEMP_FILE_BDD}"
  done <<<  "$(scontrol -o show res | awk -F '[= ]' '{ for(o=1;o<=NF;o++) \
if ($o =="ReservationName") {ReservationName=$(o+1)} \
else if ($o =="StartTime") {StartTime=$(o+1)} \
else if ($o =="EndTime") {EndTime=$(o+1)} \
else if ($o =="Duration") {Duration=$(o+1)} \
else if ($o =="Nodes") {Nodes=$(o+1)} \
else if ($o =="NodeCnt") {NodeCnt=$(o+1)} \
else if ($o =="CoreCnt") {CoreCnt=$(o+1)} \
else if ($o =="Users") {Users=$(o+1)} \
else if ($o =="State") {State=$(o+1)} \
{print ReservationName"|"StartTime"|"EndTime"|"Duration"|"Nodes"|"NodeCnt"|"CoreCnt"|"Users"|"State}} ')"
}

#Collecte des infos users slapcat
function collectUsers ()
{
  echo "-- collectUsers" >> "${TEMP_FILE_BDD}"
  ssh $SRVLDAP "slapcat" > ${TEMP_DIR}/slapcat
  for grp in ${GROUPE};do
  	listnni+=$(awk "BEGIN {IGNORECASE=1} {RS=\"\"} /^dn: cn=${grp},${OUGROUPE}/" ${TEMP_DIR}/slapcat | sort -u -t, -k1,1 | awk '/member: uid/ {print$2}' | grep -v dummy)
  done
  listnni=$(echo ${listnni} | tr " " "\n"| sort -u | uniq)
  for NNI in ${listnni};do
	INFOUSERLDAP=$(awk "BEGIN {IGNORECASE=1} {RS=\"\"} /^dn: ${NNI}/" ${TEMP_DIR}/slapcat | awk '/^mail:/{mail=$2}; /^employeeType:/{et=$2}; /^gidNumber:/{gid=$2}; /^cn:/{name=$2" "$3" "$4};/^homeDirectory:/{homedir=$2};/^uid:/{nni=$2};/^uidNumber:/{uid=$2} {print mail"|"et"|"gid"|"name"|"homedir"|"nni"|"uid}' | tail -n1)
	IFS="|" read EMAIL EMPLOYETYPE GIDNUM USERNOM USERHOME USERUID UIDNUMBER <<< "${INFOUSERLDAP}"
	if [[ ! -z ${USERUID} ]];then
		GRPP=$(getent group ${GIDNUM} | awk -F ":" '{print$1}')
		GRPS=$(id ${USERUID})
		NNIMIN=$(echo ${USERUID} |tr [:upper:] [:lower:])
		echo "insert into Users (idUsers, id_Clusters, uid, nom, home, email, employetype, grp_principale, grp_secondary) values (\"${NNIMIN}\", \"${CLUSTER}\", \"${UIDNUMBER}\", \"${USERNOM}\", \"${USERHOME}\", \"${EMAIL}\", \"${EMPLOYETYPE}\", \"${GRPP}\", \"${GRPS}\" );" >> "${TEMP_FILE_BDD}"
	fi
  done
}

#Collecte des Wckey
function configWckey ()
{
  echo "-- configWckeys" >> "${TEMP_FILE_BDD}"
  echo "insert into WCkeys (idWCkeys, id_Clusters) values" >> "${TEMP_FILE_BDD}"
  sacctmgr show wckeys -n format=WCKey%50 --parsable2 | awk '{print"(\""$NF"\",\"'$CLUSTER'\"),"}' |  sed '$ s/.$/;/' >> "${TEMP_FILE_BDD}"
}

#Collecte des rapports et stats
function collectRapport ()
{
  echo "-- collectRapport" >> "${TEMP_FILE_BDD}"
  NBUSERSOUMIS=$(sacct -X -S $(date "+%Y-%m-%dT00:00:00" -d "yesterday") -E $(date "+%Y-%m-%dT23:59:59" -d "yesterday") -n -o user|sort -u|wc -l)
  CMD="("
  for i in ${FRONTAUX}
  do
    CMD=${CMD}"ssh ${i} last|grep -v wtmp|grep -v root|grep -v reboot | awk '{print \$1}'; "
  done
  CMD=${CMD}") |sort -u|wc -l"
  NBUSERFRONTAUX=$(${CMD})
  echo "insert into Collect_rapport (Nb_user_soumis, Nb_user_frontaux, id_Clusters) values (\"${NBUSERSOUMIS}\", \"${NBUSERFRONTAUX}\", \"${CLUSTER}\");" >> "${TEMP_FILE_BDD}"
}

function dateDiff ()
{
  case $1 in
    -s)   sec=1;      shift;;
    -m)   sec=60;     shift;;
    -h)   sec=3600;   shift;;
    -d)   sec=86400;  shift;;
    *)    sec=86400;;
  esac
  dte1=$1
  dte2=$2
  diffSec=$((dte2-dte1))
  if ((diffSec < 0)); then abs=-1; else abs=1; fi
  echo $((diffSec/sec*abs))
}

function prepBDD ()
{
  echo "-- Ouverture d'une transaction" > "${TEMP_FILE_BDD}"
  echo "START TRANSACTION;" >> "${TEMP_FILE_BDD}"
  echo "update Clusters set last_refresh=\"$(date "+%F %T")\" where idClusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
}

function sendBDD ()
{
  echo "-- Envoie en bdd" >> "${TEMP_FILE_BDD}"
  echo "COMMIT;" >> "${TEMP_FILE_BDD}"
  result=$(${MYSQL_FILE} < "${TEMP_FILE_BDD}")
  if [ $? = 0 ];then
	echo -e "[$(date)] ${OK} Insertion en base réussit"
	echo -e "[$(date)] ${OK} Insertion en base réussit" >> ${LOG_FILE}
	RESETCONFIG=0
  else
	echo -e "[$(date)] ${FAIL} Insertion en base echec, consult log ${LOG_FILE} et ${TEMP_FILE_BDD}" 
	echo "---------------------------------------------------------------------------------------------------------" >> ${LOG_FILE}
	echo ${RESULT} >> ${LOG_FILE}
	echo "---------------------------------------------------------------------------------------------------------" >> ${LOG_FILE}
	resetconfig
	# Peut etre rajouter avertissement mail ou base log
	# Pas d'exit car il peut s'agir d'un pb temporaire !!! exit 1
  fi
}

function delConfigBDD ()
{
  echo "-- Suppression des entree de config" >> "${TEMP_FILE_BDD}"
 # echo "delete from Partitions where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
 # echo "delete from QOS where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  echo "delete from Reservations where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  echo "delete from WCkeys where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  echo "delete from Users where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
}

function delTopologyBDD ()
{
  echo "-- Suppression des entree de topology" >> "${TEMP_FILE_BDD}"
  echo "delete from Noeuds where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  echo "delete from Liens where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  echo "delete from Switch where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
}

function delCollecteBDD ()
{
  echo "-- Suppression des entree de collecte" >> "${TEMP_FILE_BDD}"
  echo "delete from Reservations where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  echo "delete from Collect_Jobs where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
  echo "delete from Collect_Quota where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD}"
}

function resetconfig ()
{
  echo -e "[$(date)] - Insertion en base echec resetconfig" >> ${LOG_FILE}  
  RESETCONFIG="1"
}

function usage ()
{
    echo ""
	echo -e " Information : par défauts au premier lancement toutes les collectes sont éffectués"
    echo ""
    functionlist=$(grep "^function" `which $0` | awk '{print$2}' | tr "\n" ", ")
    echo -e " Usage : cluster_monitor.sh [-v] [-d function] [-o option]"
	echo -e ""
	echo -e " -v \t mode verbose"
        echo -e " -o \t option au lancement ( noconfig,notopology )"
	echo -e " -d \t mode debug function "
	echo -e " \n \t Liste function : $functionlist "
	echo -e ""
	rm -f ${LOCKFILE}
	exit 1
}
# --------------------- Programme principale --------------------- #


while getopts o:d:hv c 2> /dev/null
do
    case $c in 
     h )    usage                   ;;
	 o )    option="yes"            ; case $OPTARG in 
										noconfig )	CONFIG="0" 			;;
										notopology )	TOPOLOGY="0" 	;;
										noconfig,notopology ) CONFIG="0" ; TOPOLOGY="0" ;;
										notopology,noconfig ) CONFIG="0" ; TOPOLOGY="0" ;;
										* ) usage    					;;
									  esac ;;
	 v )    verbose="yes"       ;;
	 d )    debug="$OPTARG"	    ;;
    esac
done
shift $(($OPTIND - 1))

if [[ "${verbose}" == "yes" ]]; then set -x ;fi

if [[ ! -z "${debug}"  ]];then

	set -x
	getFrontalToUse
	TEMP_FILE_BDD=${TEMP_FILE_BDD}.debugfunction
	${debug}
	cat ${TEMP_FILE_BDD}
	rm -f ${LOCKFILE} ${TEMP_FILE_BDD}
	exit 0
fi

if [[ -z "${option}" ]];then
	# On effectue tout au lancement
	CONFIG=1
	TOPOLOGY=1
	COLLECTE=1
	echo -e ""
	echo -e "${OK} enable collecte default"
	echo -e "${OK} enable collecte topology"
	echo -e "${OK} enable collecte configuration"
	echo -e "${OK} start cluster monitor"
	echo -e ""
fi

# Lancement collect stats Jobs Metrics
if [[ ${JOBSMETRICS} == "yes" ]];then
	${WORK_DIR}/cluster_monitor_stats.sh & 
fi

while true
do
  if [[ $(dateDiff -d "${DATE_RUN_CONFIG}" "$(date "+%s")") > 5 ]]
  then
    CONFIG=1
    DATE_RUN_CONFIG=$(date "+%s")
  fi

  if [[ $(dateDiff -d "${DATE_RUN_TOPOLOGY}" "$(date "+%s")") > 3 ]]
  then
    TOPOLOGY=1
    DATE_RUN_TOPOLOGY=$(date "+%s")
  fi

  if [[ $(dateDiff -m "${DATE_RUN_COLLECTE}" "$(date "+%s")") > 10 ]]
  then
    COLLECTE=1
    DATE_RUN_COLLECTE=$(date "+%s")
  fi

  if [[ "${CONFIG}" == 1 || "${TOPOLOGY}" == 1 || "${COLLECTE}" == 1 ]]
  then
    testgestionnairebatch
    if [[ "$?" == 0 ]]
    then
      getFrontalToUse
      if [[ "$?" == 0 ]]
      then
        prepBDD
	if [[ "${RESETCONFIG}" == 1 ]]
	then
		CONFIG=1
		COLLECTE=1
	fi
        if [[ "${CONFIG}" == 1 && "${COLLECTE}" == 1 ]]
        then
          echo -e "[$(date)] - Reload config cluster" >> ${LOG_FILE}
          delConfigBDD
          collectUsers
          configFrontaux
          configClusters
          configFilesystems
          configQOS
          configPartitions
          configAccount
	  configUser
	  configAssoc
          if [[ "${REPORTWCKEYS}" == "yes" ]]   ;then configWckey ;fi
          collectRapport
          CONFIG=0
        fi
      
        if [[ "${TOPOLOGY}" == 1 && "${COLLECTE}" == 1 ]]
        then
	  ${WORK_DIR}/cluster_monitor_topocns.sh &
          TOPOLOGY=0
        fi
        
        if [[ "${COLLECTE}" == 1 ]]
        then
          delCollecteBDD
	  collectFS
	  if [[ "${COLLECTQUOTAXFS}" == "yes" ]]   ;then collectQuotaXfs    ;fi
	  if [[ "${COLLECTQUOTAGPFS}" == "yes" ]]  ;then collectQuotaGpfs   ;fi		
	  if [[ "${COLLECTQUOTALUSTRE}" == "yes" ]];then collectQuotaLustre ;fi		
          collectJobs
          collectNodes
          collectPartitions
          collectClusters
          collectFrontaux
          collectReservation
          COLLECTE=0
        fi
        sendBDD 
      fi
    fi
  fi

  sleep 1m
done

exit 0

