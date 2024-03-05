<?php

interface ConsoleCommand
{
    public function run();
}

class Add implements ConsoleCommand
{

    public function __construct(private DataBaseQueryAdd $db)
    {

    }

    public function run()
    {
        $path = readline("Enter Path:");
        $mask = readline("Enter Mask:");
        $this->db->addToQueueList($path, $mask);
    }
}

class Kill implements ConsoleCommand
{
    public function __construct(private DataBaseQueryKill $db)
    {

    }

    public function run()
    {
        $arr = $this->db->getPidList();
        foreach ($arr as $key => $value) {
            posix_kill($value[1], SIGHUP);
        }
        $this->db->clearPidList();
        exit("Process Shutdown");
    }
}

class KillDaemons implements ConsoleCommand
{
    public function __construct(private DataBaseQueryKill $db)
    {

    }

    public function run()
    {
        $arr = $this->db->getPidList();
        foreach ($arr as $key => $value) {
            posix_kill($value['pid'], SIGHUP);
        }
        $this->db->clearPidList();
        // TODO: kill daemons and purge db
    }
}

class DaemonStart implements ConsoleCommand
{
    private WorkerInterface $worker;
    private DataBaseQueryDaemon $db;


    public function __construct(WorkerInterface $worker, DataBaseQueryDaemon $db)
    {
        $this->worker = $worker;
        $this->db = $db;
    }

    private function child($db, $worker)
    {
        $id = getmypid();
        $db->addToPidList($id);
        while (1) {
            $worker->run();
            sleep(10);
        }
    }

    public function run()
    {
        for ($i = 0; $i <= 1; $i++) {          // количество запускаемых демонов (2)
            sleep(1);
            $pid = pcntl_fork();    // форк процесса

            if ($pid == -1) {
                die("could not fork");   // ошибка форка
            } elseif ($pid) {

            } else {
                posix_setsid();           // отвязка чайлда от консоли
                $this->child($this->db, $this->worker);
            }
        }
    }
}
