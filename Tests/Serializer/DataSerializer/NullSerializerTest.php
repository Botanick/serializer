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

    public function supportProvider()
    {
        return array(
            array(null, true),
            array(1, false),
            array(1.0, false),
            array('a', false),
            array(true, false),
            array(array(), false),
            array(new \stdClass(), false)
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
