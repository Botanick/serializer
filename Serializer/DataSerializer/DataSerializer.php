<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

use Botanick\Serializer\SerializerAwareInterface;
use Botanick\Serializer\SerializerInterface;

abstract class DataSerializer implements DataSerializerInterface, SerializerAwareInterface
{
    /**
     * @var SerializerInterface
     */
    private $_serializer;
    /**
     * @var array
     */
    private $_defaultOptions = array();

    public function __construct()
    {
        $this->setDefaultOptions($this->getBaseDefaultOptions());
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->_serializer = $serializer;
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->_serializer;
    }

    /**
     * @param array $options
     */
    public function setDefaultOptions(array $options)
    {
        $this->_defaultOptions = array_merge($this->_defaultOptions, $options);
    }

    /**
     * @return array
     */
    protected function getBaseDefaultOptions()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return $this->_defaultOptions;
    }

    /**
     * @param mixed $options
     * @return array
     */
    protected function mergeOptions($options)
    {
        if (!is_array($options)) {
            return $this->getDefaultOptions();
        }

        return array_merge($this->getDefaultOptions(), $options);
    }
}