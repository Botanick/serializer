<?php

namespace Botanick\Serializer;

use Botanick\Serializer\Exception\DataSerializerException;
use Botanick\Serializer\Exception\SerializerNotFoundException;

interface SerializerInterface
{
    const GROUP_DEFAULT = 'default';

    /**
     * @param mixed $data
     * @param string $group
     * @param mixed $options
     * @return mixed
     * @throws SerializerNotFoundException
     * @throws DataSerializerException
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null);
}