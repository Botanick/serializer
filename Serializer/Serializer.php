<?php

namespace Botanick\Serializer\Serializer;

use Botanick\Serializer\Exception\SerializerNotFoundException;
use Botanick\Serializer\Serializer\DataSerializer\DataSerializerInterface;
use Botanick\Serializer\Serializer\DataSerializer\DataSerializersAwareInterface;
use Botanick\Serializer\SerializerAwareInterface;
use Botanick\Serializer\SerializerInterface;

class Serializer implements SerializerInterface, DataSerializersAwareInterface
{
    const OPTION_DATA_SERIALIZER = '$dataSerializer$';

    /**
     * @var DataSerializerInterface[][]
     */
    protected $_dataSerializers = array();
    /**
     * @var bool
     */
    protected $_dataSerializersNeedSort = false;

    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        $dsName = null;
        if (is_array($options)) {
            if (isset($options[self::OPTION_DATA_SERIALIZER])) {
                $dsName = $options[self::OPTION_DATA_SERIALIZER];
                unset($options[self::OPTION_DATA_SERIALIZER]);
            }
        }

        foreach ($this->getDataSerializers() as $dataSerializers) {
            foreach ($dataSerializers as $dataSerializer) {
                if (
                    !is_null($dsName) && $dsName === $dataSerializer->getName()
                    || is_null($dsName) && $dataSerializer->supports($data)
                ) {
                    return $dataSerializer->serialize($data, $group, $options);
                }
            }
        }

        throw new SerializerNotFoundException(
            sprintf(
                'No serializers found for "%s"',
                is_object($data) ? get_class($data) : gettype($data)
            )
        );
    }

    public function addDataSerializer(DataSerializerInterface $dataSerializer, $priority)
    {
        if ($dataSerializer instanceof SerializerAwareInterface) {
            $dataSerializer->setSerializer($this);
        }

        $this->_dataSerializers[$priority][] = $dataSerializer;
        $this->_dataSerializersNeedSort = true;
    }

    protected function getDataSerializers()
    {
        if ($this->_dataSerializersNeedSort) {
            $this->sortDataSerializers();
            $this->_dataSerializersNeedSort = false;
        }

        return $this->_dataSerializers;
    }

    protected function sortDataSerializers()
    {
        krsort($this->_dataSerializers);
    }
}