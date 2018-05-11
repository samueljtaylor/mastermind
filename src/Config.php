<?php

namespace SamTaylor\MasterMind;


class Config
{
    /**
     * The config from config/config.php
     *
     * @var array
     */
    public $config;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->config = include __DIR__.'/config/config.php';
    }

    /**
     * Get config
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if(array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        return null;
    }
}