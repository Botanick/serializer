<?php

namespace Botanick\Serializer\Serializer\Config;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileResource;

class SerializerConfigCache
{
    /**
     * @var string
     */
    private $_cacheClassPrefix;
    /**
     * @var bool
     */
    private $_debug;
    /**
     * @var string
     */
    private $_cacheDir;
    /**
     * @var SerializerConfigCacheDumper
     */
    private $_dumper;

    /**
     * @param string $cacheClassPrefix
     * @param bool $debug
     * @param string $cacheDir
     * @param SerializerConfigCacheDumper $dumper
     */
    public function __construct($cacheClassPrefix, $debug, $cacheDir, SerializerConfigCacheDumper $dumper)
    {
        $this->_cacheClassPrefix = $cacheClassPrefix;
        $this->_debug = $debug;
        $this->_cacheDir = $cacheDir;
        $this->_dumper = $dumper;
    }

    /**
     * @return string
     */
    protected function getCacheClassPrefix()
    {
        return $this->_cacheClassPrefix;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->_debug;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->_cacheDir;
    }

    /**
     * @return SerializerConfigCacheDumper
     */
    protected function getDumper()
    {
        return $this->_dumper;
    }

    /**
     * @param string $type
     * @param mixed $sources
     * @param callable $createConfigCallback
     * @return mixed
     */
    public function getCachedConfig($type, $sources, $createConfigCallback)
    {
        $class = sprintf(
            '%sBotanickSerializerConfig',
            $this->getCacheClassPrefix()
        );
        $cache = new ConfigCache(
            sprintf(
                '%s/%s.php',
                $this->getCacheDir(),
                $class
            ),
            $this->isDebug()
        );

        if ($cache->isFresh()) {
            require_once $cache->getPath();
            /** @var SerializerConfigInterface $serializerConfig */
            $serializerConfig = new $class();
            if (
                $serializerConfig->isType($type)
                && $serializerConfig->isFromSources($sources)
            ) {
                return $serializerConfig->getConfig();
            }
        }

        list($config, $filesAndDirs) = call_user_func($createConfigCallback);

        $resources = array();
        foreach ($filesAndDirs as $fileOrDir) {
            if (is_file($fileOrDir)) {
                $resources[] = new FileResource($fileOrDir);
            } elseif (is_dir($fileOrDir)) {
                $resources[] = new DirectoryResource($fileOrDir);
            }
        }

        $cache->write($this->getDumper()->dump($class, $type, $sources, $config), $resources);

        return $config;
    }
}