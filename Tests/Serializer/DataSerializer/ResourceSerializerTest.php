<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Serializer\DataSerializer\ResourceSerializer;

class ResourceSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $value
     * @param bool $supports
     * @dataProvider supportProvider
     */
    public function testSupport($value, $supports)
    {
        $serializer = $this->getSerializer();

        $this->assertEquals($supports, $serializer->supports($value));
    }

    public function supportProvider()
    {
        return array(
            array(null, false),
            array(1, false),
            array(1.0, false),
            array('a', false),
            array(true, false),
            array(array(), false),
            array(new \stdClass(), false),
            array(fopen(__FILE__, 'r'), true)
        );
    }

    /**
     * @return ResourceSerializer
     */
    protected function getSerializer()
    {
        return new ResourceSerializer();
    }
}
