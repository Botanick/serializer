<?php

namespace Botanick\Serializer\Serializer\Config;

use Botanick\Serializer\Exception\ConfigNotFoundException;

interface SerializerConfigLoaderInterface
{
    /**
     * @param string $name
     * @return mixed
     * @throws ConfigNotFoundException
     */
    public function getConfigFor($name);
}