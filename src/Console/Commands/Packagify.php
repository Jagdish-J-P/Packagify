<?php

namespace Jagdish_J_P\Packagify\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Packagify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:create {vendorName? : package vendor name} {packageName? : package name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create package scaffolding';

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

        do {
            $vendorName = $this->argument('vendorName');
            if (empty($vendorName))
                $vendorName = $this->ask('Enter Vendor Name: [e.g. Jagdish-J-P]');
            $vendorName = filter_var($vendorName, FILTER_SANITIZE_STRING);

            $packageName = filter_var($this->argument('packageName'), FILTER_SANITIZE_STRING);
            if (empty($packageName))
                $packageName = $this->ask('Enter Package Name: [e.g. Package1]');
            $packageName = filter_var($packageName, FILTER_SANITIZE_STRING);

            $emailId = $this->ask('Enter Package Maintainer\'s email id:');
            $emailId = filter_var($emailId, FILTER_SANITIZE_EMAIL);

            $description = $this->ask('Enter Package description (Optional):');
            $description = filter_var($description, FILTER_SANITIZE_STRING);

            $type = $this->anticipate('Enter Package type (e.g. library, project, metapackage, composer-plugin):', ['library', 'project', 'metapackage', 'composer - plugin']);
            $type = filter_var($type, FILTER_SANITIZE_STRING);

            $license = $this->ask('Enter License (Optional):');
            $license = filter_var($license, FILTER_SANITIZE_STRING);

            $isValid = true;
            if (empty($vendorName) || empty($packageName)) {
                $this->error("Vendor Name and Package Name are required! Let's begin from start.");
                $isValid = false;
            }
        } while (!$isValid);

        $composerJson = $this->loadComposerJson($this->getComposerJsonPath());
        $composerJson['name'] = Str::lower("$vendorName/$packageName");
        $composerJson['description'] = $description;
        $composerJson['type'] = $type;
        $composerJson['license'] = $license;
        $composerJson['authors'][] = ['name' => Str::studly($vendorName), 'email' => $emailId];

        $this->saveComposerJson($composerJson, $this->getComposerJsonPath());
        $dir = new Filesystem;
        $ds = DIRECTORY_SEPARATOR;
        if (!$dir->exists($directory = base_path("packages$ds$vendorName$ds$packageName"))) {
            $dir->makeDirectory($directory, 0755, true);
        }

        if (!$dir->exists(__DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}structure")) {
            echo "Source Package Not exisist!";
            return 0;
        }

        if ($dir->copyDirectory(__DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}structure", $directory))
            echo 'Package Created!';

        return 1;
    }
    /**
     * Load and parse content of composer.json.
     *
     * @return array
     *
     * @throws FileNotFoundException
     * @throws RuntimeException
     */
    protected function loadComposerJson($composerJsonPath = null)
    {
        if (empty($composerJsonPath))
            $composerJsonPath = $this->getBaseComposerJsonPath();

        if (!File::exists($composerJsonPath)) {
            throw new FileNotFoundException('composer.json does not exist');
        }

        $composerJsonContent = File::get($composerJsonPath);
        $composerJson = json_decode($composerJsonContent, true);

        if (!is_array($composerJson)) {
            throw new RuntimeException("Invalid composer.json file [$composerJsonPath]");
        }

        return $composerJson;
    }

    /**
     * @param array $composerJson
     *
     * @throws RuntimeException
     */
    protected function saveComposerJson($composerJson, $composerJsonPath = null)
    {
        $newComposerJson = json_encode(
            $composerJson,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if (empty($$composerJsonPath))
            $composerJsonPath = $this->getComposerJsonPath();
        if (File::put($composerJsonPath, $newComposerJson) === false) {
            throw new RuntimeException("Cannot write to composer.json [$composerJsonPath]");
        }
    }
    /**
     * Register package in composer.json.
     *
     * @param $vendor
     * @param $package
     * @param $relPackagePath
     *
     * @throws RuntimeException
     */
    protected function registerPackage($vendor, $package, $relPackagePath)
    {
        $this->info('Register package in composer.json.');

        $composerJson = $this->loadComposerJson();

        if (!isset($composerJson['repositories'])) {
            Arr::set($composerJson, 'repositories', []);
        }

        $filtered = array_filter($composerJson['repositories'], function ($repository) use ($relPackagePath) {
            return $repository['type'] === 'path'
                && $repository['url'] === $relPackagePath;
        });

        if (count($filtered) === 0) {
            $this->info('Register composer repository for package.');

            $composerJson['repositories'][] = (object) [
                'type' => 'path',
                'url' => $relPackagePath,
            ];
        } else {
            $this->info('Composer repository for package is already registered.');
        }

        Arr::set($composerJson, "require.$vendor/$package", 'dev-master');

        $this->saveComposerJson($composerJson);

        $this->info('Package was successfully registered in composer.json.');
    }

    /**
     * Unregister package from composer.json.
     *
     * @param $vendor
     * @param $package
     *
     * @throws FileNotFoundException
     * @throws RuntimeException
     */
    protected function unregisterPackage($vendor, $package, $relPackagePath)
    {
        $this->info('Unregister package from composer.json.');

        $composerJson = $this->loadComposerJson();

        unset($composerJson['require']["$vendor\\$package\\"]);

        $repositories = array_filter($composerJson['repositories'], function ($repository) use ($relPackagePath) {
            return $repository['type'] !== 'path'
                || $repository['url'] !== $relPackagePath;
        });

        $composerJson['repositories'] = $repositories;

        if (count($composerJson['repositories']) === 0) {
            unset($composerJson['repositories']);
        }

        $this->saveComposerJson($composerJson);

        $this->info('Package was successfully unregistered from composer.json.');
    }

    /**
     * Get composer.json path.
     *
     * @return string
     */
    protected function getComposerJsonPath()
    {

        return __DIR__ . '/../../../structure/composer.json';
    }

    /**
     * Get project composer.json path.
     *
     * @return string
     */
    protected function getBaseComposerJsonPath()
    {

        return base_path('composer.json');
    }
}
