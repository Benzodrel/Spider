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
            posix_kill($value['pid'], SIGTERM);
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
            posix_kill($value['pid'], SIGTERM);
        }
        $this->db->clearPidList();
    }
}

class DaemonStart implements ConsoleCommand
{
    private WorkerInterface $workerHelper;
    private DataBaseQueryDaemon $db;
    private array $childProcesses = array();
    private int $max_processes = 3;

    public function __construct(WorkerInterface $workerHelper, DataBaseQueryDaemon $db)
    {
        $this->workerHelper = $workerHelper;
        $this->db = $db;
    }

    private function worker($db, $workerHelper)
    {

        $pid = pcntl_fork();    // форк процесса

        if ($pid == -1) {
            // TODO: exception handling
            die('Could not launch new job, exiting');   // ошибка форка
        } elseif ($pid) {
            $this->childProcesses[] = $pid;
        } else {

            file_put_contents('/srv/website/test/pid.txt', getmypid() . PHP_EOL, FILE_APPEND);

            $test = $workerHelper->run();
            if (is_array($test)) {
                file_put_contents('/srv/website/test/db.txt', implode("", $test) . PHP_EOL, FILE_APPEND);
            }
            posix_kill(getmypid(), SIGTERM);
        }

    }

    public function run()
    {

        $pid = pcntl_fork();
        if ($pid == -1) {
            // TODO: exception handling
            exit('Could not fork daemon');
        } elseif ($pid) {

        } else {
            posix_setsid();
            file_put_contents('/srv/website/test/pid.txt', getmypid() . PHP_EOL, FILE_APPEND);
            $pid = getmypid();
            $this->db->getConnect();
            $this->db->addToPidList($pid);

            while (1) {

                while (count($this->childProcesses) <= $this->max_processes) {
                    $this->worker($this->db, $this->workerHelper);
//                    sleep(1);
                }
                while (count($this->childProcesses) > 0) {
                    foreach ($this->childProcesses as $key => $pid) {
                        $res = pcntl_waitpid($pid, $status, WNOHANG);

                        // If the process has already exited
                        if ($res == -1 || $res > 0)
                            unset($this->childProcesses[$key]);
                    }
                }
            }
        }
    }
}
