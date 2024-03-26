<?php
require_once "src/Repositories/QueueTableMysql.php";
interface WorkerInterface
{
    public function run();
}


class Spider implements WorkerInterface
{

    public function __construct()
    {
    }

    public function run($dir = '', $mask = '')
    {

        if ($dir === '' && $mask === '') {
            $database = new DataBaseMysql(HOST, DATA_BASE_NAME, USERNAME, PASSWORD);
            $query = new QueueTableMysql($database);

            $query->getConnect();
            $in_db = true;
            $arr = $query->getOneUpdateStatus();
            if ($arr === NULL) {
                return false;
            } else {
                $dir = $arr["path"];
                $mask = $arr["mask"];
            }
        }
        $catalog = scandir($dir);
        array_map('unlink', glob($dir . "/" . $mask));
        foreach ($catalog as $value) {
            if ($value === "." || $value === "..") {
                continue;
            }
            if (is_dir("$dir/$value")) {
                $this->run("$dir/$value", $mask);
            }
        }
        if (isset($in_db)) {
            $query->delete($arr["id"]);
            $query->closeConnect();
            return $arr;
        }
        return true;
    }
}