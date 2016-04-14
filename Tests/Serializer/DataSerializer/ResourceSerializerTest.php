<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Serializer\DataSerializer\ResourceSerializer;

class ResourceSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $serializer = $this->getSerializer();

        $this->assertEquals('botanick.serializer.data-serializer.resource', $serializer->getName());
    }

    /**
     * @param mixed $value
     * @param bool $supports
     * @dataProvider supportsProvider
     */
    public function testSupports($value, $supports)
    {
        $serializer = $this->getSerializer();

        $this->assertEquals($supports, $serializer->supports($value));
    }

    public function testSerialize()
    {
        $serializer = $this->getSerializer();

        $this->assertRegExp('~^Resource id #\d+$~', $serializer->serialize(fopen(__FILE__, 'r')));
    }

    public function supportsProvider()
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
