<?php

namespace App\Console\Commands;

use App;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RoadRunnerCommand extends Command
{

    /**
     * The roadrunner debug flag
     *
     * @var string
     */
    const DEBUG_FLAG = "-d";

    /**
     * The roadrunner config flag
     *
     * @var string
     */
    const CONFIG_FLAG = "-c";

    /**
     * The roadrunner config filetypes
     *
     * @var string
     */
    const CONFIG_FILETYPES = ['yml', 'json'];

    /**
     * The roadrunner config prefix
     *
     * @var string
     */
    const CONFIG_PREFIX = ".rr";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rr 
                            {task=serve : The roadrunner command to run}
                            {--c|config= : Implicitly specificy a config}
                            {--d|debug : Debug mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs roadrunner commands with support for app environment';

    /**
     * Supported filetypes for roadrunner configuration
     *
     * @var array
     */
    protected $config_filetypes = ['yml', 'json'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $process = new Process([
            $this->roadRunnerBinary(),
            $this->roadRunnerCommand(),
            $this->inferDebugOption(),
            $this->inferConfigOption(),
        ]);

        $process->setTimeout(PHP_INT_MAX);

        $process->run(function ($type, $buffer) {
            $this->line(trim($buffer, "\n"));
        });
    }

    /**
     * Locate and return the absolute path to the roadrunner binary
     *
     * @return string
     */
    protected function roadRunnerBinary()
    {
        return base_path('rr');
    }

    /**
     * The roadrunner command which is to be run
     *
     * @return string
     */
    protected function roadRunnerCommand()
    {
        return $this->argument('task');
    }

    /**
     * Detect weather debuging is required and return the debug flag
     *
     * @return string
     */
    protected function inferDebugOption()
    {
        if (App::environment('local') || $this->option('debug')) {
            return static::DEBUG_FLAG;
        }
    }

    /**
     * Detect the correct config to use and return the config flag
     *
     * @return string
     */
    protected function inferConfigOption()
    {
        // If the user specifies a config, us it
        if ($config_file = $this->option('config')) {
            return $this->configOption($config_file);
        }

        // Try to find a config based on the app environment
        foreach (static::CONFIG_FILETYPES as $filetype) {
            // Implode looks nicer and is faster than concatenation
            $config_file = base_path(implode('.', [
                static::CONFIG_PREFIX,
                App::environment(),
                $filetype
            ]));

            if (file_exists($config_file)) {
                return $this->configOption($config_file);
            }
        }
    }

    /**
     * Create a config option string from a given file input
     *
     * @var string
     */
    protected function configOption($config_file)
    {
        return implode('', [
            static::CONFIG_FLAG,
            $config_file
        ]);
    }
}
