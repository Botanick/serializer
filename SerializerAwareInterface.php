<?php

namespace Botanick\Serializer;

interface SerializerAwareInterface
{
    /**
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer);
}