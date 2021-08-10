<?php

namespace JagdishJP\LaravelPackageMaker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use File;

class PackageMake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:make {makeCommand : command name} {name : name for the command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to scaffold artisan make command.';

    /**
     *  valid command lists
     *
     * @var array
     */
    protected $validCommands = ['command', 'controller', 'model'];

    /**
     * default vendor name
     */
    protected $vendorName;

    /**
     * default package name
     */
    protected $packageName;

    /**
     * Package directory path
     *
     * @var string
     */
    protected $packageDirectory;

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
     * @return int
     */
    public function handle()
    {
        $this->vendorName = config('packagify.vendorName');
        $this->info("Default Vendor: $this->vendorName");

        $this->packageName = $this->ask("Enter Package Name: ", config('packagify.packageName'));
        Config::set('packagify.packageName', $this->packageName);

        $this->packageDirectory = base_path("packages/$this->vendorName/$this->packageName/src");

        $command = $this->argument('makeCommand');
        if (!in_array($command, $this->validCommands))
            return $this->error('Invalid Command!');

        $name = $this->argument('name');

        $this->action($command, $name);
        return 0;
    }

    public function action(string $command, string $name)
    {
        switch ($command) {
            case 'command':
                $this->make('command', $name, "/Console/Commands");
                break;
            case 'controller':
                $this->make('controller', $name, "/Http/Controllers");
                break;
            case 'model':
                $this->make('model', $name, "/Models");
                break;
        }
    }

    /**
     * creates files based on commands
     *
     * @param String $command           command name
     * @param String $name              file name
     * @param String $dummyName         dummy name
     * @param String $dummyFilePath     dummy file path
     * @param String $filePath          actual file path
     *
     */
    public function make(String $command, String $name, String $actualFilePath)
    {
        $dummyName = 'Packagify_Dummy_' . Str::title($command);
        $dummyFilePath = "$actualFilePath/$dummyName.php";
        $actualFilePath .= "/$name.php";

        $overWrite = true;
        if (File::exists($this->packageDirectory . $actualFilePath))
            $overWrite = $this->confirm($this->packageDirectory . $actualFilePath . " already exists. Do you want to overwrite?");

        if ($overWrite) {
            Artisan::call("make:$command $dummyName");

            if (!File::exists(app_path() . $dummyFilePath))
                return $this->error(app_path() . $dummyFilePath . " not exists");

            if (!File::exists(dirname($this->packageDirectory . $dummyFilePath)))
                File::makeDirectory(dirname($this->packageDirectory . $dummyFilePath), 0755, true);

            $import = '';
            if ($command == 'controller')
                $import = "\nuse App\Http\Controllers\Controller;";
            File::move(app_path() . $dummyFilePath, $this->packageDirectory . $actualFilePath);

            $content = File::get($this->packageDirectory . $actualFilePath);
            File::put($this->packageDirectory . $actualFilePath, Str::replace(
                [$dummyName, 'App\\', 'use Illuminate\Http\Request;'],
                [$name, Str::studly("$this->vendorName\\$this->packageName\\"), 'use Illuminate\Http\Request;' . $import],
                $content
            ));
        }
    }
}
