# Laravel Package Maker

A basic Laravel Package skelton maker.

Package is currently in development.

This Package is created to scaffold laravel package.

# Installation

You can install the package via composer:

```
composer require jagdish-j-p/laravel-package-maker
```

Then run the publish command to publish the config file to edit packagify configuration.

```
php artisan vendor:publish --provider="JagdishJP\LaravelPackageMaker\Providers\LaravelPackageMakerServiceProvider"
```

to add/remove package directories edit config/packagify.php file

```
'packageStructure' => ["config", "database", "database\\factories", "database\\migrations", "database\\seeders", "public", "resources", "resources\\lang", "resources\\lang\\en", "resources\\views", "routes", "src", "src\\Console", "src\\Console\\Commands", "src\\Http", "src\\Http\\Controllers", "src\\Http\\Middleware", "src\\Models", "src\\Providers"]
```

**packageStructure** contains list of directories to create for your package. You can add or remove any directory.

# Usage

Set .env file for default configuration

`
PACKAGE_VENDOR="Vendor Name"
PACKAGE_NAME="Package Name"
VENDOR_EMAIL_ID="user@example.com"
`

**Create Package**
```
php artisan package:create
```

Follow the instructions and your package will be created.

**Create Command**
```
php artisan package:make command CommandName
```

**Create Controller**
```
php artisan package:make controller ControllerName
```

**Create Model**
```
php artisan package:make model ModelName
```

