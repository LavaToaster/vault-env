<?php

namespace Lavoaster\VaultEnv;

use Vault\Client as VaultClient;

class Client
{
    /**
     * @var VaultClient
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    public function __construct(VaultClient $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Loads vault data into the environment
     *
     * @return void
     */
    public function loadIntoEnvironment(): void
    {
        foreach ($this->getDataFromCache() as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            putenv($key . '=' . $value);
        }
    }

    /**
     * Loads vault data from the apcu cache
     *
     * @return array
     */
    public function getDataFromCache(): array
    {
        \apcu_delete('vault-env');

        return \apcu_entry('vault-env', [$this, 'getDataFromVault'], $this->config->getTtl());
    }

    /**
     * Loads vault data directly from vault
     *
     * @return array
     */
    protected function getDataFromVault(): array
    {
        $this->client->authenticate();

        return $this->client->read($this->config->getPath())->getData();
    }
}
