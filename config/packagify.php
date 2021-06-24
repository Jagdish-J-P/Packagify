<?php

/**
 * Default values for package. You can change this at run-time
 */
return [
    'vendorName' => env('PACKAGE_VENDOR', 'Jagdish-J-P'), // Replace with your vendor name
    'packageName' => env('PACKAGE_NAME', 'MyPackage'), // Replace with your package name
    'vendorEmailId' => env('VENDOR_EMAIL_ID', 'jagdish1230@gmail.com'), // Replace with vendor email id
    'packageType' => 'library',
    'packageLicense' => 'MIT',
    'devBranch' => 'dev-main',
    'packageStructure' => ["config", "database", "database\\factories", "database\\migrations", "database\\seeders", "public", "resources", "resources\\lang", "resources\\views", "resources\\lang\\en", "routes", "src", "src\\Console", "src\\Console\\Commands", "src\\Http", "src\\Http\\Controllers", "src\\Http\\Middleware", "src\\Models", "src\\Providers"]
];
