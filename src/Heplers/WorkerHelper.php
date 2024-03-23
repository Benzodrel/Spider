<?php

interface WorkerInterface
{
    public function run();
}


class Spider implements WorkerInterface
{

    private DataBaseQueryWorker $db;

    public function __construct(DataBaseQueryWorker $db)
    {
        $this->db = $db;
    }

    //TODO: reading from database path and mask for the worker
    public function run($dir = '', $mask = '')
    {

        $arr = $this->db->getQueryUnit();
        if ($arr === NULL) {
            return false;
        } else {
            if ($dir === '' && $mask === '') {

                $dir = $arr["path"];
                $mask = $arr["mask"];
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
            $this->db->deleteFromQueue($arr["id"]);
            return $arr;
        }
    }
}