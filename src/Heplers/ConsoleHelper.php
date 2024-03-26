<?php


class ConsoleHelper
{
    private array $commands;


    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    public function consoleStart()
    {

        echo "Command List" . PHP_EOL;
        foreach ($this->commands as $command => $action) {
            echo $command . PHP_EOL;
        }
        while (1) {
            $userCommand = readline("Enter Command:");

            if (array_key_exists($userCommand, $this->commands)) {
                $this->commands[$userCommand]->run();
            } else {
                echo "This Command Doesn't exists" . PHP_EOL;
            }
            sleep(2);
        }
    }
}

