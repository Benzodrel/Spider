<?php


//interface DataBaseQuery
//{
//    public function getAll(string $table);
//
//    public function sqlExecute(string $sql, string $types, string ...$values);
//}


interface DataBaseQueryWorker
{

    public function __construct(DataBase $db);

    public function getQueryUnit();

    public function deleteFromQueue($id): bool;
}

interface DataBaseQueryDaemon
{
    public function __construct(DataBase $db);

    public function addToPidList($id): bool;
}

interface DataBaseQueryAdd
{
    public function __construct(DataBase $db);

    public function addToQueueList(string $path, string $mask): bool;
}

interface DataBaseQueryKill
{
    public function __construct(DataBase $db);

    public function getPidList(): array;

    public function clearPidList(): bool;
}

class DataBaseQueryWorkerMysql implements DataBaseQueryWorker
{
    private Mysqli $db;

    public function __construct(DataBase $db)
    {
        $this->db = $db->getConnect();
    }

    public function getQueryUnit()
    {
        $sql = "SELECT * FROM `queue` LIMIT 1";
        return $this->db->query($sql)->fetch_assoc();
    }

    public function deleteFromQueue($id): bool
    {
        $sql = "DELETE FROM `queue` WHERE id = (?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}

class DataBaseQueryAddMysql implements DataBaseQueryAdd
{
    private $db;

    public function __construct(DataBase $db)
    {
        $this->db = $db->getConnect();
    }

    public function addToQueueList(string $path, string $mask): bool
    {
        $sql = "INSERT INTO `queue` (`path`, `mask`) Value(?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $path, $mask);
        return $stmt->execute();
    }
}

class DataBaseQueryDaemonMysql implements DataBaseQueryDaemon
{
    private $db;

    public function __construct(DataBase $db)
    {
        $this->db = $db->getConnect();
    }

    public function addToPidList($id): bool
    {
        $sql = "INSERT INTO `pid_table` (`pid`) Value(?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}

class DataBaseQueryKillMysql implements DataBaseQueryKill
{
    private $db;

    public function __construct(DataBase $db)
    {
        $this->db = $db->getConnect();
    }

    public function getPidList(): array
    {
        $arr = [];
        $sql = "SELECT * FROM `pid_table`";
        $stmt = $this->db->query($sql);
        while($row = $stmt->fetch_assoc()){
            array_push($arr, $row);
        }
        return $arr;
    }

    public function clearPidList(): bool
    {
        $sql = "DELETE FROM `pid_table`";
        return $this->db->query($sql);
    }
}

//class DataBaseQueryMysql implements DataBaseQuery
//{
//    private $db;
//
//    public function __construct($db)
//    {
//        $this->db = $db->getConnect();
//    }
//
//    public function getAll($table): array
//    {
//        $sql = "SELECT * FROM `{$table}`";
//        return $this->db->query($sql)->fetch_assoc();
//    }
//
//    public function getOne($table): array
//    {
//        $sql = "SELECT * FROM `{$table}` LIMIT 1";
//        return $this->db->query($sql)->fetch_assoc();
//    }
//
//    public function sqlExecute(string $sql, string $types = '', string ...$values)
//    {
//        $sql = $this->db->prepare($sql);
//        $sql->bind_param($types, ...$values);
//        $sql->execute();
//    }
//
//    public function addToQuery(string $path, string $mask)
//    {
//        $sql = "INSERT INTO `queue` (`path`, `mask`) VALUES (?, ?)";
//        $sqlPrepare = $this->db->prepare($sql);
//        $sqlPrepare->bind_param("ss", $path, $mask);
//        $sqlPrepare->execute($sql);
//    }
//    public function delete($pid)
//    {
//        $sql = $this->db->prepare("DELETE FROM `queue` WHERE `id` = ?");
//        $sql->bind_param("i", $pid);
//        $sql->execute();
//    }
//}