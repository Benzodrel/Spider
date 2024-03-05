<?php


interface DataBase
{
    public function getConnect();
}

class DataBaseMysql implements DataBase
{
    private mysqli $db;

    public function __construct(private $host,
                                private $dbname,
                                private $login,
                                private $password)
    {
        $this->db = new mysqli($this->host, $this->login, $this->password, $this->dbname);
    }

    public function getConnect(): Mysqli
    {
        return $this->db;
    }
}