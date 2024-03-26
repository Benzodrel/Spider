<?php
require_once "src/Repositories/QueueTable.php";

class QueueTableMysql implements QueueTable
{
    private DataBase $db;
    private Mysqli $connect;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getConnect()
    {
        return $this->connect = $this->db->getConnect();
    }

    public function getOne(): array|null
    {
        $sql = "SELECT * FROM `queue` WHERE `in_use` = 0 ORDER BY `entry_time` ASC LIMIT 1";
        return $this->connect->query($sql)->fetch_assoc();
    }

    public function add(string $path, string $mask): bool
    {
        $sql = "INSERT INTO `queue` (`path`, `mask`) Value(?, ?)";
        $stmt = $this->connect->prepare($sql);
        $stmt->bind_param('ss', $path, $mask);
        return $stmt->execute();
    }

    public function delete($id): bool
    {
        $sql = "DELETE FROM `queue` WHERE id = (?)";
        $stmt = $this->connect->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function updateStatus($id): bool
    {
        $sql = "UPDATE `queue` SET `in_use` = 1 where `id` = ?";
        $stmt = $this->connect->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getOneUpdateStatus(): array|null
    {
        $sql = " SELECT * FROM `queue`  WHERE `in_use` = 0 ORDER BY `entry_time` ASC LIMIT 1 FOR UPDATE";
        $this->connect->begin_transaction();
        $stmt = $this->connect->query($sql)->fetch_assoc();
        if (is_array($stmt)){
            $sql1 = "UPDATE `queue` SET `in_use` = 1 where `id` = ?";
            $stmt1 = $this->connect->prepare($sql1);
            $stmt1->bind_param('i', $stmt["id"]);
            $stmt1->execute();
        }
        $this->connect->commit();
        return $stmt;
    }

    public function closeConnect()
    {
        $this->connect->close();
    }
}