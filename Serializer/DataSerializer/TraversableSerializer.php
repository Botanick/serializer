<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

class TraversableSerializer extends DataSerializer
{
    /**
     * @param \Traversable $data
     * @param string $group
     * @param mixed $options
     * @return array
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        $result = [];

        foreach ($data as $k => $v) {
            $result[$k] = $this->getSerializer()->serialize($v, $group);
        }

        return $result;
    }

    public function supports($data)
    {
        return $data instanceof \Traversable;
    }
}