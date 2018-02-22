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

    public function getBackendSourcePath()
    {
        if ($this->config['source']['backend'] == null) {
            return null;
        }
        return "{$this->getSourcePath()}/{$this->config['source']['backend']}";
    }

    public function getAdminAPIBase()
    {
        $protocol = 'http'.($this->config['target']['service']['https']?'s':'');
        return "$protocol://{$this->config['target']['service']['host']}/admin/api";
    }

    public function getAdminSourcePath()
    {
        return "{$this->getBackendSourcePath()}/vue";
    }

    public function getAdminPath()
    {
        return $this->config['target']['admin']['path'];
    }

    public function getAdminServers()
    {
        return $this->config['target']['admin']['server'];
    }

    public function getRemoteUser()
    {
        return $this->config['remote']['user'];
    }

    public function getRemoteKey()
    {
        return $this->config['remote']['key'];
    }

    public function getServicePath()
    {
        return $this->config['target']['service']['path'];
    }

    public function getServiceServers()
    {
        return $this->config['target']['service']['server'];
    }

    public function getFrontendSourcePath()
    {
        if ($this->config['source']['frontend'] == null) {
            return null;
        }
        return "{$this->getSourcePath()}/{$this->config['source']['frontend']}";
    }

    public function getServiceAPIBase()
    {
        $protocol = 'http'.($this->config['target']['service']['https']?'s':'');
        return "$protocol://{$this->config['target']['service']['host']}/api";
    }
}
