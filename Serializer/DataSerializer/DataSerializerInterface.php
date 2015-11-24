<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

use Botanick\Serializer\SerializerInterface;

interface DataSerializerInterface extends SerializerInterface
{
    /**
     * @param $data
     * @return bool
     */
    public function supports($data);
}