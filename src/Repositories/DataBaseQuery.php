<?php


interface DataBaseQueryDaemon
{
    public function addToPidList($id): bool;
}

interface DataBaseQueryAdd
{
    public function addToQueueList(string $path, string $mask): bool;
}

interface DataBaseQueryKill
{
    public function getPidList(): array;

    public function clearPidList(): bool;
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
    private $connect;

    public function __construct(DataBase $db)
    {
        $this->db = $db;
    }

    public function getConnect()
    {
        return $this->connect = $this->db->getConnect();
    }

    public function addToPidList($id): bool
    {
        $sql = "INSERT INTO `pid_table` (`pid`) Value(?)";
        $stmt = $this->connect->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function closeConnect()
    {
        return $this->connect->close();
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
        while ($row = $stmt->fetch_assoc()) {
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
