<?php

namespace Deploy\Extensions\Base;

class ConfigWrapper
{
    protected $config;

    protected $sourcePath;

    public function __construct($config)
    {
        $this->config = $config;
        $this->sourcePath = $this->config['source']['path'].'/'.uniqid('deploy.');
    }

    /**
     * @return string
     */
    public function getSourceRepo(): string
    {
        return $this->config['source']['repo'];
    }

    /**
     * @return string
     */
    public function getSourceRevision(): string
    {
        return $this->config['source']['revision'];
    }

    /**
     * @return string
     */
    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }
}
