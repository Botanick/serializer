<?php

namespace Botanick\Serializer\Serializer\Config;

use Botanick\Serializer\Exception\ConfigLoadException;
use Symfony\Component\Finder\Finder;

class SerializerDirsConfigLoader extends SerializerFilesConfigLoader
{
    /**
     * @var array
     */
    private $_dirs = [];

    /**
     * @var SerializerConfigCache
     */
    private $_cache;

    /**
     * @param array $dirs
     * @param SerializerConfigCache $cache
     */
    public function __construct(array $dirs = [], SerializerConfigCache $cache = null)
    {
        parent::__construct();

        $this->setDirs($dirs);
        $this->_cache = $cache;
    }

    /**
     * @param array $dirs
     */
    public function setDirs(array $dirs)
    {
        $this->_dirs = $dirs;
    }

    /**
     * @return array
     */
    protected function getDirs()
    {
        return $this->_dirs;
    }

    /**
     * @return SerializerConfigCache
     */
    private function getCache()
    {
        return $this->_cache;
    }

    /**
     * @return string
     */
    private function getCacheType()
    {
        return 'dirs';
    }

    /**
     * @throws ConfigLoadException
     */
    protected function loadConfig()
    {
        if (!$this->getCache()) {
            $this->loadConfigInternal();

            return;
        }

        $config = $this->getCache()->getCachedConfig(
            $this->getCacheType(),
            $this->getDirs(),
            function () {
                return $this->loadConfigInternal();
            }
        );
        $this->setConfig($config);
    }

    /**
     * @return array
     * @throws ConfigLoadException
     */
    private function loadConfigInternal()
    {
        $files = [];

        $finder = new Finder();
        foreach ($this->getDirs() as $dir) {
            if (false === $dirPath = realpath($dir)) {
                throw new ConfigLoadException(
                    sprintf(
                        'Unable to load config from "%s". Directory not found.',
                        $dir
                    )
                );
            }
            if (!is_dir($dirPath)) {
                throw new ConfigLoadException(
                    sprintf(
                        'Unable to load config from "%s". Not a directory.',
                        $dir
                    )
                );
            }
            if (!is_readable($dirPath)) {
                throw new ConfigLoadException(
                    sprintf(
                        'Unable to load config from "%s". Directory is not readable.',
                        $dir
                    )
                );
            }

            $finder->files()->in($dir);
            foreach ($finder as $file) {
                $files[] = $file;
            }
        }

        parent::setFiles($files);
        parent::loadConfig();

        return [
            $this->getConfig(),
            $this->getDirs()
        ];
    }
}