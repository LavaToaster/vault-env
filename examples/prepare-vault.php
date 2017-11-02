<?php

/**
 * This file exists to bootstrap the vault server in docker-compose.yml
 *
 * Please don't use this as a way of getting tokens in production. You'll make me a sad panda.
 *
 * See `examples/example.php` for the better example.
 */

use VaultTransports\Guzzle6Transport;

require __DIR__ . '/../vendor/autoload.php';

// From ../docker-compose.yml
$rootToken = '6bc61108-a72c-49cf-92ef-719b3789360e';

$authHeader = ['X-Vault-Token' => $rootToken];
$transport = new Guzzle6Transport(['base_uri' => 'http://localhost:8200/v1/', 'headers' => $authHeader]);

$transport->request('POST', 'sys/policy/vault-env', [
    'json' => [
        'rules' => 'path "secret/vault-env/env" {
  capabilities = ["read"]
}'
    ],
]);

$transport->request('POST', 'sys/auth/approle', [
    'json' => [
        'type' => 'approle',
    ],
]);

$transport->request('POST', 'auth/approle/role/vault-env', [
    'json' => [
        'policies' => ['vault-env']
    ],
]);

$roleIdResponse = $transport->request('GET', 'auth/approle/role/vault-env/role-id');
$roleId = json_decode($roleIdResponse->getBody()->getContents(), true)['data']['role_id'];

$secretIdResponse = $transport->request('POST', 'auth/approle/role/vault-env/secret-id');
$secretId = json_decode($secretIdResponse->getBody()->getContents(), true)['data']['secret_id'];

file_put_contents(__DIR__ . '/config.json', json_encode(compact('roleId', 'secretId')));

$transport->request('PUT', 'secret/vault-env/env', [
    'json' => [
        'APP_ENV' => 'production',
        'DB_NAME' => 'test',
        'DB_PASS' => 'test',
        'DB_HOST' => 'localhost',
        'DB_PORT' => 3306,
        'FEATURE_ENABLED' => true,
    ]
]);
