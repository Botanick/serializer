<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

class NullSerializer extends DataSerializer
{
    /**
     * @param null $data
     * @param string $group
     * @param mixed $options
     * @return null
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        return null;
    }

    public function getName()
    {
        return 'botanick.serializer.data-serializer.null';
    }

    public function supports($data)
    {
        return is_null($data);
    }
}