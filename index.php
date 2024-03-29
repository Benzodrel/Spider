<?php
require_once "src/Repositories/config.php";
require_once "src/Repositories/DataBase.php";
require_once "src/Repositories/DataBaseQuery.php";
require_once "src/Heplers/CommandCollection.php";
require_once "src/Heplers/ConsoleCommand.php";
require_once "src/Heplers/ConsoleHelper.php";
require_once "src/Heplers/WorkerHelper.php";
$db = new DataBaseMysql(HOST, DATA_BASE_NAME,USERNAME , PASSWORD);
$dbQueryKill = new DataBaseQueryKillMysql($db);
$dbQueryAdd = new DataBaseQueryAddMysql($db);
$dbQueryDaemon = new DataBaseQueryDaemonMysql($db);
$dbQueryWorker = new DataBaseQueryWorkerMysql($db);
$collection = new CommandCollection(new Kill($dbQueryKill),
                                    new DaemonStart(new Spider($dbQueryWorker), $dbQueryDaemon),
                                    new Add($dbQueryAdd),
                                    new KillDaemons($dbQueryKill));
$array = $collection -> getCollection();
$enter = new ConsoleHelper($array);
$enter ->consoleStart();