# ConfigManager

**ConfigManager** is a lightweight library for managing PHP configuration files with support for Dot-Notation and Lazy Loading.

## Installation

The package requires **PHP 8.3** or newer.

```bash
composer require ctorh23/configmanager
```

## Usage

### Configuration File Structure

Create a PHP file, for example `app.php`, that looks like this:

```php
return [
	'name' => 'My App',
	'env' => $this->env('APP_ENV', 'production'),
	'encryption' => [
		'cipher' => 'AES-256-GCM',
		'key' => $this->env('APP_KEY'),
	],
];
```

The file must return an array. As you can see, you can use environment variables as configuration settings. The second parameter of the `env()` method is a default value.

### Accessing Configuration Values

Assuming your configuration files are saved in a `config/` directory, you must pass the path to this directory to instantiate a `ConfigManager` object:

```php
use Ctorh23\ConfigManager\ConfigManager;

$confMan = new ConfigManager(__DIR__ . '/config');

echo $confMan->get('app.name'); //Output: My App
echo $confMan->get('app.env'); //Output: production
echo $confMan->get('app.encryption.cipher'); //Output: AES-256-GCM

// Passing a default value as a second parameter
echo $confMan->get('app.logs', 'logs/'); //Output: logs/
```

You can also dynamically set configuration settings, but you should keep in mind that they are not persisted on the filesystem:

```php
$confMan->set('timezone', 'UTC');
$confMan->set('app.encryption.cipher', 'AES-XTS');

echo $confMan->get('timezone'); //Output: UTC
echo $confMan->get('app.encryption.cipher'); //Output: AES-XTS
```
