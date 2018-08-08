#!/bin/bash -x

# Recuperation topology et config des noeuds de services

# --------------------- Definition des variables --------------------- #

# Repertoire de travail du collecteur
WORK_DIR=/srv/cluster_monitor

# Nom du fichier de configuration
CONFIG_FILE_NAME="cluster_monitor.conf"
source ${WORK_DIR}/${CONFIG_FILE_NAME} || exit 1

# Verifie fichier de lock
if [[ -f "${LOCKFILETOPOCNS}" ]];then 
	echo -e ""
	echo -e "${FAIL} - Error fichier de lock présent =>${LOCKFILETOPOCNS}"
	echo -e ""
	exit 1
else
	touch ${LOCKFILETOPOCNS}
fi

# trap ctrl-c and call ctrl_c()
trap ctrl_c 15 2 

function ctrl_c () {
  echo -e ""
  echo -e "Arret de la collecte"
  rm -f ${LOCKFILETOPOCNS}
  exit 1
}

# --------------------- Definition des functions --------------------- #

#Recuperation info noeuds 
function recupconfignode ()
{
        nodehardinfo=$(grep $1 ${TEMP_FILE_NODE_HARD})
        if [ $? != "0" ];then
                local nodeinfo=$(timeout 10 ssh $1 "LANG=C;
					 cat /sys/devices/virtual/dmi/id/sys_vendor /sys/devices/virtual/dmi/id/product_name | tr \"\\n\" \" \";echo '|';	
					 cat /sys/devices/virtual/dmi/id/product_name;echo '|'; 						
					 cat /sys/devices/virtual/dmi/id/product_serial;echo '|'; 						
					 cat /proc/meminfo | awk '/MemTotal:/ {printf \"%3.0f\\n\",\$2/1024}';echo '|'; 				
					 dmidecode -t 17 | awk -F \":\" '/Configured Clock Speed:*.*MHz/ { print \$2 }'| sort -u;echo '|';		
					 cat /proc/cpuinfo | awk '/physical id/ {print\$4}' | sort -u | wc -l;echo '|';				
					 cat /proc/cpuinfo | awk '/cpu cores/ {print\$4}' | sort -u;echo '|';					
					 cat /proc/cpuinfo | awk -F \":\" '/model name/ {print\$2}' | sort -u;echo '|';				
					 if [ \$(dmesg | grep -i 'Hypervisor detected') ];then echo Virtual ; else echo Physical;fi;echo '|';	
					 uname -srvm ")												
                if [ ! -z "${nodeinfo}" ];then
                        echo $1 "|" $nodeinfo >> ${TEMP_FILE_NODE_HARD}
                fi
                echo $nodeinfo | sed "s/'//g;/^ $/d" | tr -s "\n" "|"
        else
                echo $nodehardinfo | awk -F "|" '{print$2"|"$3"|"$4"|"$5"|"$6"|"$7"|"$8"|"$9"|"$10}'
        fi
}

#Obtention de la topology du cluster
function configTopology ()
{
  echo "-- configTopology" >> "${TEMP_FILE_BDD_TOPOCNS}"
  scontrol show topology > ${TEMP_FILE_TOPO} 2> /dev/null
  SAVE_LIST_SW=()
  if [[ -s ${TEMP_FILE_TOPO} ]]
  then
    SWLEVEL=$(grep -o "Level=." ${TEMP_FILE_TOPO}|sort -u |tail -n 1)
    scontrol -o show node |awk -F '[,= ]' '{ for(o=1;o<=NF;o++) if ($o =="NodeName") {NodeName=$(o+1)} else if ($o =="CoresPerSocket") {CoresPerSocket=$(o+1)} else if ($o =="RealMemory") {RealMemory=$(o+1)} else if ($o =="Sockets") {Sockets=$(o+1)} {print NodeName"|"RealMemory"|"Sockets"|"CoresPerSocket} }'> ${TEMP_FILE_NODE}
    if ! [[ "${SWLEVEL}" == "Level=0" ]]
    then
      GETSW=$(grep "${SWLEVEL}" ${TEMP_FILE_TOPO} |awk -F '[= ]' '{ for(o=1;o<=NF;o++) if ($o =="SwitchName") {SwitchName=$(o+1)} else if ($o =="Switches") {Switches=$(o+1)} {print SwitchName"|"Switches} }')
    else
      GETSW=$(grep "${SWLEVEL}" ${TEMP_FILE_TOPO} |awk -F '[= ]' '{ for(o=1;o<=NF;o++) if ($o =="SwitchName") {SwitchName=$(o+1)} else if ($o =="Nodes") {Nodes=$(o+1)} {print SwitchName"|"Nodes} }')
    fi

echo $GETSW | wc -l

    for LIGNE in ${GETSW} 
    do
      if [[ "${SWLEVEL}" == "Level=1" ]]
      then
        IFS="|" read TOPSW SW <<< "${LIGNE}"
        echo "insert into Switch (idSwitch, id_Clusters, level) values (\"${TOPSW}\", \"${CLUSTER}\", 2);" >> "${TEMP_FILE_BDD_TOPOCNS}"
        LIST_SW=$(nodeset -e ${SW})
      else
        IFS="|" read SW NODES <<< "${LIGNE}"
        LIST_SW=${SW}
      fi
 
      for i in ${LIST_SW}
      do
        if ! [[ "${SAVE_LIST_SW[*]}" =~ "${i}" ]]
        then
          echo "insert into Switch (idSwitch, id_Clusters, level) values (\"${i}\", \"${CLUSTER}\", 1);" >> "${TEMP_FILE_BDD_TOPOCNS}"
          LIST_NODE=$(grep "${i}" ${TEMP_FILE_TOPO}|awk -F '[= ]' '{ for(o=1;o<=NF;o++) if ($o =="Nodes") {print $(o+1)} }')
          LIST_NODE=$(nodeset -e ${LIST_NODE})
          for j in ${LIST_NODE}
          do
		IFS="|" read PRODUCTNAME PRODUCTYPE PRODUCSERIAL REALMEMORY MEMORYFREQ SOCKETS CORESPERSOCKET TYPECPU TYPEMACH TYPEOS <<< "$(recupconfignode ${j})"
            echo "insert into Noeuds (idNoeuds, id_Clusters, id_Switch, RealMemory, FrequencyMemory ,Sockets, CoresPerSocket, ProductName, ProductPartNumber, ProductSerial, Typecpu, TypeNode, TypeMach, Typeos) values (\"${j}\", \"${CLUSTER}\", \"${i}\", \"${REALMEMORY}\", \"${MEMORYFREQ}\", \"${SOCKETS}\", \"${CORESPERSOCKET}\", \"${PRODUCTNAME}\", \"${PRODUCTYPE}\", \"${PRODUCSERIAL}\", \"${TYPECPU}\", \"Compute\", \"${TYPEMACH}\", \"${TYPEOS}\");" >> "${TEMP_FILE_BDD_TOPOCNS}"

          done
        fi
        SAVE_LIST_SW+=("${i}")
        if [[ "${SWLEVEL}" == "Level=1" ]]
        then
          echo "insert into Liens (source, destination, id_Clusters) values (\"${i}\", \"${TOPSW}\", \"${CLUSTER}\");" >> "${TEMP_FILE_BDD_TOPOCNS}"
        fi
      done
    done 
  fi
}

#recuperation des infos sur les noeuds de service
function configNoeudService ()
{
  echo "-- configNoeudService" >> "${TEMP_FILE_BDD_TOPOCNS}"
  for i in ${NOEUDS_SERVICE}
  do
    for j in $(nodeset -e ${i})
    do
	IFS="|" read PRODUCTNAME PRODUCTYPE PRODUCSERIAL REALMEMORY MEMORYFREQ SOCKETS CORESPERSOCKET TYPECPU TYPEMACH TYPEOS <<< "$(recupconfignode ${j})"
        echo "insert into Noeuds (idNoeuds, id_Clusters, RealMemory, FrequencyMemory ,Sockets, CoresPerSocket, ProductName, ProductPartNumber, ProductSerial, Typecpu, TypeNode, TypeMach, Typeos) values (\"${j}\", \"${CLUSTER}\", \"${REALMEMORY}\", \"${MEMORYFREQ}\", \"${SOCKETS}\", \"${CORESPERSOCKET}\", \"${PRODUCTNAME}\", \"${PRODUCTYPE}\", \"${PRODUCSERIAL}\", \"${TYPECPU}\", \"Service\", \"${TYPEMACH}\", \"${TYPEOS}\");" >> "${TEMP_FILE_BDD_TOPOCNS}"
    done  
  done
}

function prepBDD ()
{
  echo "-- Ouverture d'une transaction topocns" > "${TEMP_FILE_BDD_TOPOCNS}"
  echo "START TRANSACTION;" >> "${TEMP_FILE_BDD_TOPOCNS}"
#  echo "update Clusters set last_refresh=\"$(date "+%F %T")\" where idClusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD_TOPOCNS}"
}

function sendBDD ()
{
  echo "-- Envoie en bdd topocns" >> "${TEMP_FILE_BDD_TOPOCNS}"
  echo "COMMIT;" >> "${TEMP_FILE_BDD_TOPOCNS}"
  ${MYSQL_FILE} < "${TEMP_FILE_BDD_TOPOCNS}" 
  if [ $? = 0 ];then
	echo -e "${OK} Insertion en base réussit "
  else
	echo -e "${FAIL} Insertion en base echec, consult log ${LOG_FILE} et ${TEMP_FILE_BDD_TOPOCNS}"
	# Peut etre rajouter avertissement mail ou base log
	# Pas d'exit car il peut s'agir d'un pb temporaire !!! exit 1
  fi
}

function delTopologyBDD ()
{
  echo "-- Suppression des entree de topology" >> "${TEMP_FILE_BDD_TOPOCNS}"
  echo "delete from Noeuds where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD_TOPOCNS}"
  echo "delete from Liens where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD_TOPOCNS}"
  echo "delete from Switch where id_Clusters=\"${CLUSTER}\";" >> "${TEMP_FILE_BDD_TOPOCNS}"
}

# --------------------- Programme principale --------------------- #

prepBDD
delTopologyBDD
configTopology
configNoeudService
sendBDD

rm -f ${LOCKFILETOPOCNS}

exit 0
