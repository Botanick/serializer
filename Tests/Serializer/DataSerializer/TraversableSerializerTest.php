<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Exception\ConfigNotFoundException;
use Botanick\Serializer\Exception\DataSerializerException;
use Botanick\Serializer\Serializer\DataSerializer\ObjectSerializer;
use Botanick\Serializer\Serializer\DataSerializer\TraversableSerializer;
use Botanick\Serializer\SerializerInterface;

class TraversableSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $serializer = $this->getSerializer();

        $this->assertEquals('botanick.serializer.data-serializer.traversable', $serializer->getName());
    }

    /**
     * @param mixed $value
     * @param bool $supports
     * @param bool $hasObjectSerializer
     * @param bool $hasConfig
     * @param bool $withCnfEx
     * @dataProvider supportsProvider
     */
    public function testSupports($value, $supports, $hasObjectSerializer = false, $hasConfig = false, $withCnfEx = false)
    {
        $serializer = $this->getSerializer($hasObjectSerializer, $hasConfig, $withCnfEx);

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
        $serializer = $this->getSerializer(false, false, false, $subcalls);

        $this->assertEquals($expectedValue, $serializer->serialize($value));
    }

    public function supportsProvider()
    {
        $traversable = $this->getMock('Traversable');

        return array(
            array(null, false),
            array(1, false),
            array(1.0, false),
            array('a', false),
            array(true, false),
            array(array(), false),
            array(fopen(__FILE__, 'r'), false),
            array(new \stdClass(), false, false, false),
            array(new \stdClass(), false, true, false, false),
            array(new \stdClass(), false, true, false, true),
            array(new \stdClass(), false, true, true),
            array($traversable, true, false, false),
            array($traversable, false, true, false, false),
            array($traversable, true, true, false, true),
            array($traversable, false, true, true)
        );
    }

    public function serializeProvider()
    {
        return array(
            array(new \ArrayIterator(array()), array(), 0),
            array(new \ArrayIterator(array(1, 2, 3)), array(1, 2, 3), 3),
            array(new \ArrayIterator(array('a' => 1, 'b' => 2)), array('a' => 1, 'b' => 2), 2)
        );
    }

    /**
     * @param bool $hasObjectSerializer
     * @param bool $hasConfig
     * @param bool $withCnfEx
     * @param int $subcalls
     * @return TraversableSerializer
     */
    protected function getSerializer($hasObjectSerializer = false, $hasConfig = false, $withCnfEx = false, $subcalls = 0)
    {
        $serializer = new TraversableSerializer();

        $s = $this->getMock('Botanick\\Serializer\\SerializerInterface');
        $s
            ->expects($this->exactly($subcalls))
            ->method('serialize')
            ->willReturnArgument(0);
        /** @var SerializerInterface $s */
        $serializer->setSerializer($s);

        if ($hasObjectSerializer) {
            $objectSerializer = $this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\ObjectSerializer');
            $objectSerializer
                ->expects($this->any())
                ->method('getConfig')
                ->willReturn(array());
            if (!$hasConfig) {
                $objectSerializer
                    ->expects($this->any())
                    ->method('getConfig')
                    ->willThrowException(new DataSerializerException('', 0, $withCnfEx ? new ConfigNotFoundException() : null));
            }

            /** @var ObjectSerializer $objectSerializer */
            $serializer->setObjectSerializer($objectSerializer);
        }

        return $serializer;
    }
}
