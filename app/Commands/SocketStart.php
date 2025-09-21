<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SocketStart extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'socket:start';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'socket:start [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write("Starting WebSocket server...", 'green');

        // Define the command to run ws.php with 'run' argument
        $command = 'php ' . ROOTPATH  . 'ws.php run';

        // Execute the command
        $output = shell_exec($command);

        // Display the output
        if ($output) {
            CLI::write($output, 'green');
        } else {
            CLI::error("Failed to run WebSocket server.");
        }
    }
}