<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

class DateTimeSerializer extends DataSerializer
{
    /**
     * @param \DateTime $data
     * @param string $group
     * @param mixed $options
     * @return int|string
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        $options = $this->mergeOptions($options);

        if (is_string($options['format'])) {
            return $data->format($options['format']);
        }

        return $data->getTimestamp();
    }

    public function supports($data)
    {
        return $data instanceof \DateTime;
    }

    protected function getBaseDefaultOptions()
    {
        return [
            'format' => false
        ];
    }
}