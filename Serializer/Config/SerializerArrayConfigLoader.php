<?php

namespace Botanick\Serializer\Serializer\Config;

use Botanick\Serializer\Exception\ConfigNotFoundException;

class SerializerArrayConfigLoader implements SerializerConfigLoaderInterface
{
    /**
     * @var array
     */
    private $_config = null;

    /**
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        $this->setConfig($config);
    }

    public function getConfigFor($name)
    {
        if (is_null($this->_config)) {
            $this->loadConfig();
        }

        if (!array_key_exists($name, $this->_config)) {
            throw new ConfigNotFoundException(sprintf('Config for "%s" not found.', $name));
        }

        return $this->_config[$name];
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config = null)
    {
        $this->_config = $config;
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return $this->_config;
    }

    protected function loadConfig()
    {

    }
}