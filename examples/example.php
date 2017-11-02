<?php

namespace Lavoaster\VaultEnv\Examples;

use Lavoaster\VaultEnv\Client;
use Lavoaster\VaultEnv\Config;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
use Vault\Client as VaultClient;
use VaultTransports\Guzzle6Transport;

require __DIR__ . '/../vendor/autoload.php';

//\apcu_delete('vault-env');

// Really crappy way of determining if you're running this from the local CLI
// or if you're in the docker container
$host = gethostbyname('vault');
$isInDocker = gethostbyname('vault') !== 'vault';
$host = $isInDocker ? $host : 'localhost';

$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

// I did try to the HTML Formatter but that somehow made this slow / hang... ¯\_(ツ)_/¯
$logger = (new Logger('vault-env'))
    ->pushHandler((new StreamHandler('php://output'))->setFormatter(new LineFormatter(LineFormatter::SIMPLE_FORMAT . ($isInDocker ? '<br />' : ''))));

$vaultClient = (new VaultClient(
    new Guzzle6Transport(['base_uri' => 'http://' . $host . ':8200']),
    $logger
))
    ->setAuthenticationStrategy(new AppRoleAuthenticationStrategy(
        $config['roleId'],
        $config['secretId']
    ));

$client = new Client($vaultClient, (new Config())->setPath('/secret/vault-env/env'));

$client->loadIntoEnvironment();

// Just be a bit careful here, since if you're calling this file from your machine, this thing will dump everything
// in your shell environment. (´･_･`)
$logger->debug('Environment Variables', ['env' => getenv()]);
