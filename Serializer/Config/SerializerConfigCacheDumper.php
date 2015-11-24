<?php

namespace Botanick\Serializer\Serializer\Config;

class SerializerConfigCacheDumper
{
    /**
     * @param string $class
     * @param string $type
     * @param array $sources
     * @param array $config
     * @return string
     */
    public function dump($class, $type, $sources, $config)
    {
        $sources = sha1(serialize($sources));
        $config = var_export($config, true);

        return <<<EOF
<?php

class {$class} implements Botanick\\Serializer\\Serializer\\Config\\SerializerConfigInterface
{
    public function isType(\$type)
    {
        return '{$type}' === \$type;
    }

    public function isFromSources(\$sources)
    {
        return '{$sources}' === sha1(serialize(\$sources));
    }

    public function getConfig()
    {
        return {$config};
    }
}
EOF;
    }
}