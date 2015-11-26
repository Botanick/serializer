<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Serializer\DataSerializer\NullSerializer;

class NullSerializerTest extends \PHPUnit_Framework_TestCase
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

    public function testSerialize()
    {
        $serializer = $this->getSerializer();

        $this->assertNull($serializer->serialize(null));
        $this->assertNull($serializer->serialize('lol this is not null!'), 'NullSerializer::serialize() should always return `null`!');
    }

    public function supportProvider()
    {
        return array(
            array(null, true),
            array(1, false),
            array(1.0, false),
            array('a', false),
            array(true, false),
            array(array(), false),
            array(new \stdClass(), false),
            array(fopen(__FILE__, 'r'), false)
        );
    }

    /**
     * @return NullSerializer
     */
    protected function getSerializer()
    {
        return new NullSerializer();
    }
}
