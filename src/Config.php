<?php

namespace SamTaylor\MasterMind;


class Config
{
    public $config;

    public function __construct()
    {
        $this->config = include __DIR__.'/config/config.php';
    }

    public function get($key)
    {
        if(array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        return null;
    }
}