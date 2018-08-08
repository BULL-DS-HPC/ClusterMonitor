
ClusterMonitor
==============

ClusterMonitor is a web application for viewing various information from one or more scientific computing clusters.

The web application is written in php and relies on a database mariadb / mysql which stores information from the various collectors of the clusters.

With each addition of new cluster several tables are created automatically, Jobs_Metrics_clustername - Jobs_History_clustername - Nodes_Metrics_clustername, allowing the archiving of the performances and consumption of each jobs.

The collector (shell) has been developed to work with SLURM but can very easily be adapted to other.

![ScreenShot](https://github.com/BULL-DS-HPC/ClusterMonitor/blob/master/documentation/_img/Shema_cluster_monitor.jpg)

The next versions will incorporate a finer part on the statistics...


Documentation
--------------

The documentation is available online on website : https://bull-ds-hpc.github.io/ClusterMonitor/

Licence
--------

ClusterMonitor is released under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version (GPL-3+).

Support
-------

For bug reports and any questions, please open issues on GitHub project: https://github.com/BULL-DS-HPC/ClusterMonitor/issues


