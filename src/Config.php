<?php

namespace Lavoaster\VaultEnv;

class Config
{
    /***
     * @var string
     */
    protected $path;

    /**
     * @var int
     */
    private $ttl = 600;

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the path to the vault secret that contains the variables to put into the environment
     *
     * E.G "secret/application/env"
     *
     * @param string $path Path to vault secret
     * @return Config
     */
    public function setPath(string $path): Config
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Set cache TTL in seconds.
     *
     * @param int $ttl Seconds until cache refreshes
     * @return Config
     */
    public function setTtl(int $ttl): Config
    {
        $this->ttl = $ttl;

        return $this;
    }
}
