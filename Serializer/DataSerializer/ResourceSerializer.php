<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

class ResourceSerializer extends DataSerializer
{
    /**
     * @param resource $data
     * @param string $group
     * @param mixed $options
     * @return string
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        return (string)$data;
    }

    public function getName()
    {
        return 'botanick.serializer.data-serializer.resource';
    }

    public function supports($data)
    {
        return is_resource($data);
    }
}