<?php

namespace Lavoaster\VaultEnv\Tests;

use Lavoaster\VaultEnv\Client;
use Lavoaster\VaultEnv\Config;
use Mockery;
use PHPUnit\Framework\TestCase;
use Vault\Client as VaultClient;
use Vault\ResponseModels\Response;

class ClientTest extends TestCase
{
    public function testItCanLoadDataIntoTheEnvironment()
    {
        $dataInVault = [
            'APP_ENV' => 'production',
            'DB_NAME' => 'test',
            'DB_PASS' => 'test',
            'DB_HOST' => 'localhost',
            'DB_PORT' => 3306,
            'FEATURE_ENABLED' => true,
        ];

        $config = (new Config())
            ->setPath('secret/application/env')
            ->setTtl(60);

        $secret = Mockery::mock(Response::class)
            ->expects('getData')
            ->once()
            ->andReturn($dataInVault)
            ->getMock();

        $vaultClient = Mockery::mock(VaultClient::class);
        $vaultClient->expects('authenticate')->once();
        $vaultClient->expects('read')->withArgs([$config->getPath()])->once()->andReturn($secret);

        $client = new Client($vaultClient, $config);
        $client->loadIntoEnvironment();

        foreach ($dataInVault as $key => $value) {
            $valueFromEnvironment = getenv($key);

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $this->assertSame((string) $value, $valueFromEnvironment);
        }
    }
}
