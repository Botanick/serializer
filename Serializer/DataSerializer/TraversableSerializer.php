<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

use Botanick\Serializer\Exception\ConfigNotFoundException;
use Botanick\Serializer\Exception\DataSerializerException;

class TraversableSerializer extends DataSerializer
{
    /**
     * @var ObjectSerializer
     */
    private $_objectSerializer;

    /**
     * @param ObjectSerializer $objectSerializer
     */
    public function setObjectSerializer(ObjectSerializer $objectSerializer)
    {
        $this->_objectSerializer = $objectSerializer;
    }

    /**
     * @return ObjectSerializer
     */
    protected function getObjectSerializer()
    {
        return $this->_objectSerializer;
    }

    /**
     * @param \Traversable $data
     * @param string $group
     * @param mixed $options
     * @return array
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        $result = array();

        foreach ($data as $k => $v) {
            $result[$k] = $this->getSerializer()->serialize($v, $group);
        }

        return $result;
    }

    public function supports($data)
    {
        if (!is_object($data)) {
            return false;
        }
        if ($data instanceof \Traversable) {
            if (!$this->getObjectSerializer()) {
                // if it's an object and is traversable but no object serializer found
                return true;
            }
            try {
                $this->getObjectSerializer()->getConfig($data, self::GROUP_DEFAULT);
            } catch (DataSerializerException $ex) {
                if ($ex->getPrevious() && $ex->getPrevious() instanceof ConfigNotFoundException) {
                    // if it's an object and is traversable but no config found
                    return true;
                }
            }
        }

        return false;
    }
}