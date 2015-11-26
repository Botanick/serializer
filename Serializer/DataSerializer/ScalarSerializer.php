<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

class ScalarSerializer extends DataSerializer
{
    const TYPE_BOOL = 'bool';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INT = 'int';
    const TYPE_INTEGER = 'integer';
    const TYPE_NUMBER = 'number';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_REAL = 'real';
    const TYPE_STRING = 'string';

    /**
     * @param mixed $data
     * @param string $group
     * @param mixed $options
     * @return mixed
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        $options = $this->mergeOptions($options);

        switch ($options['type']) {
            case static::TYPE_BOOL:
            case static::TYPE_BOOLEAN:
                return (bool)$data;
            case static::TYPE_INT:
            case static::TYPE_INTEGER:
            case static::TYPE_NUMBER:
                return (int)$data;
            case static::TYPE_FLOAT:
            case static::TYPE_DOUBLE:
            case static::TYPE_REAL:
                return (float)$data;
            case static::TYPE_STRING:
                return (string)$data;
        }

        if (is_string($options['format'])) {
            return sprintf($options['format'], $data);
        }

        return $data;
    }

    public function supports($data)
    {
        return is_scalar($data);
    }

    protected function getBaseDefaultOptions()
    {
        return array(
            'type' => false,
            'format' => false
        );
    }
}