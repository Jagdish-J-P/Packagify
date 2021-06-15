<?php

namespace JagdishJP\Packagify\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JagdishJP\Packagify\Exceptions\RuntimeException;

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
     * package directory path
     *
     * @var string
     */
    protected $packageDirectory = '';

    /**
     * stubs path
     *
     * @var string
     */
    protected $stubs = __DIR__ . "/../../../stubs";

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
            $vendorName = $this->argument('vendorName') ?? $this->ask('Enter Vendor Name: ', config('packagify.vendorName'));
            $vendorName = filter_var($vendorName, FILTER_SANITIZE_STRING);

            $packageName = $this->argument('packageName') ?? $this->ask('Enter Package Name: [e.g. Package1]', config('packagify.packageName'));
            $packageName = filter_var($packageName, FILTER_SANITIZE_STRING);

            $isValid = true;
            if (empty($vendorName) || empty($packageName)) {
                $this->error("Vendor Name and Package Name are required! Let's begin from start.");
                $isValid = false;
            }
        } while (!$isValid);

        $this->packageDirectory = base_path("packages/$vendorName/$packageName");
        $dir = new Filesystem;
        if (!$dir->exists($this->packageDirectory)) {
            $dir->makeDirectory($this->packageDirectory, 0755, true);
        }

        if ($this->createPackageStructure($this->packageDirectory))
            $this->info('Package Structure Created!');

        $this->info('Creating package composer.json');
        $this->createComposer($vendorName, $packageName);

        $this->info('Creating service provider');
        $this->createServiceProvider($vendorName, $packageName);

        $this->registerPackage($vendorName, $packageName);
        return 1;
    }

    /**
     * creates service provider for package
     *
     */
    protected function createServiceProvider(String $vendor, String $package)
    {
        $vendor = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $vendor);
        $vendor = Str::studly($vendor);

        $package = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $package);
        $package = Str::studly($package);

        $serviceProvider = File::get("$this->stubs/ServiceProvider.stub");
        $serviceProvider = Str::replace("_VendorName_\\_PackageName_", "$vendor\\$package", $serviceProvider);
        $serviceProvider = Str::replace("_ServiceProvider_", "{$package}ServiceProvider", $serviceProvider);
        File::put("$this->packageDirectory/src/providers/{$package}ServiceProvider.php", $serviceProvider);
    }

    /**
     * creates package composer
     *
     */
    protected function createComposer(String $vendorName, String $packageName)
    {
        $vendor = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $vendorName);
        $vendor = Str::studly($vendor);

        $package = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $packageName);
        $package = Str::studly($package);

        $emailId = $this->ask('Enter Package Author\'s email id:', config('packagify.vendorEmailId'));
        $emailId = filter_var($emailId, FILTER_SANITIZE_EMAIL);

        $description = $this->ask('Enter Package description (Optional):');
        $description = filter_var($description, FILTER_SANITIZE_STRING);

        $type = $this->anticipate(
            'Enter Package type (e.g. library, project, metapackage, composer-plugin):',
            ['library', 'project', 'metapackage', 'composer - plugin'],
            config('packagify.packageType')
        );

        $type = filter_var($type, FILTER_SANITIZE_STRING);

        $license = $this->ask('Enter License (Optional):', config('packagify.packageLicense'));
        $license = filter_var($license, FILTER_SANITIZE_STRING);

        $composerJson = $this->loadComposerJson($this->getComposerJsonStub());
        $composerJson['name'] = Str::lower("$vendorName/$packageName");
        $composerJson['description'] = $description;
        $composerJson['type'] = $type;
        $composerJson['license'] = $license;
        $composerJson['authors'] = [];
        $composerJson['authors'][] = ['name' => Str::studly($vendorName), 'email' => $emailId];

        $composerJson["autoload"] = ["psr-4" => ["$vendor\\$package\\" => "src"]];

        $providers = "$vendor\\$package\\Providers\\{$package}ServiceProvider";
        $composerJson['extra'] = ['laravel' => ['providers' => [$providers]]];

        $this->saveComposerJson($composerJson, $this->packageDirectory . "/composer.json");
    }

    /**
     * creates structure of package
     */
    protected function createPackageStructure(String $basePath)
    {
        $structure = config('packagify.packageStructure');
        foreach ($structure as $directory) {
            File::makeDirectory("$basePath\\$directory");
        }
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

        if (empty($composerJsonPath))
            $composerJsonPath = $this->getBaseComposerJsonPath();
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
    protected function registerPackage($vendor, $package)
    {
        $vendor = preg_replace("/[^a-zA-Z0-9\-]+/", "", $vendor);
        $relPackagePath = "./packages/$vendor/$package";

        $this->info('Registering package in composer.json.');

        $composerJson = $this->loadComposerJson();

        if (!isset($composerJson['repositories'])) {
            Arr::set($composerJson, 'repositories', []);
        }

        $filtered = array_filter($composerJson['repositories'], function ($repository) use ($relPackagePath) {
            return $repository['type'] === 'path'
                && $repository['url'] === $relPackagePath;
        });

        if (count($filtered) === 0) {
            $this->info('Registering composer repository for package.');

            $composerJson['repositories'][$package] = (object) [
                'type' => 'path',
                'url' => $relPackagePath,
            ];
        } else {
            $this->info('Composer repository for package is already registered.');
        }
        $repoName = Str::lower("$vendor/$package");

        $devBranch = $this->choice(
            'Choose development branch:',
            ['dev-master', 'dev-main'],
            config('packagify.devBranch')
        );

        Arr::set($composerJson, "require.$repoName", $devBranch);

        $this->saveComposerJson($composerJson);

        $this->info('Package successfully registered in composer.json.');
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
    protected function unregisterPackage($vendor, $package)
    {
        $vendor = preg_replace("/[^a-zA-Z0-9\-]+/", "", $vendor);
        $relPackagePath = "./packages/$vendor/$package";

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
    protected function getComposerJsonStub()
    {

        return __DIR__ . '/../../../stubs/composer.stub';
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
