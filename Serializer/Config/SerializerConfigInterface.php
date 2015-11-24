<?php

namespace Botanick\Serializer\Serializer\Config;

interface SerializerConfigInterface
{
    /**
     * @param string $type
     * @return bool
     */
    public function isType($type);

    /**
     * @param mixed $sources
     * @return bool
     */
    public function isFromSources($sources);

    /**
     * @return mixed
     */
    public function getConfig();
}