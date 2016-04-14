<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Serializer\DataSerializer\ArraySerializer;
use Botanick\Serializer\SerializerInterface;

class ArraySerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $serializer = $this->getSerializer();

        $this->assertEquals('botanick.serializer.data-serializer.array', $serializer->getName());
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

    /**
     * @param mixed $value
     * @param mixed $expectedValue
     * @param int $subcalls
     * @dataProvider serializeProvider
     */
    public function testSerialize($value, $expectedValue, $subcalls)
    {
        $serializer = $this->getSerializer($subcalls);

        $this->assertEquals($expectedValue, $serializer->serialize($value));
    }

    public function supportsProvider()
    {
        return array(
            array(null, false),
            array(1, false),
            array(1.0, false),
            array('a', false),
            array(true, false),
            array(array(), true),
            array(array(1, 2, 3), true),
            array(array('a' => 1, 'b' => 2), true),
            array(new \stdClass(), false),
            array(fopen(__FILE__, 'r'), false)
        );
    }

    public function serializeProvider()
    {
        return array(
            array(array(), array(), 0),
            array(array(1, 2, 3), array(1, 2, 3), 3),
            array(array('a' => 1, 'b' => 2), array('a' => 1, 'b' => 2), 2)
        );
    }

    /**
     * @param int $subcalls
     * @return ArraySerializer
     */
    protected function getSerializer($subcalls = 0)
    {
        $serializer = new ArraySerializer();

        $s = $this->getMock('Botanick\\Serializer\\SerializerInterface');
        $s
            ->expects($this->exactly($subcalls))
            ->method('serialize')
            ->willReturnArgument(0);
        /** @var SerializerInterface $s */
        $serializer->setSerializer($s);

        return $serializer;
    }
}
