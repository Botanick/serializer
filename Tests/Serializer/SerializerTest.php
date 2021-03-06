<?php

namespace Botanick\Serializer\Tests\Serializer;

use Botanick\Serializer\Serializer\DataSerializer\DataSerializerInterface;
use Botanick\Serializer\Serializer\Serializer;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeWithNoSerializers()
    {
        $serializer = $this->getSerializer();

        $this->setExpectedException('Botanick\\Serializer\\Exception\\SerializerNotFoundException', 'No serializers found for "NULL"');

        $serializer->serialize(null);
    }

    public function testSerializeWithNoSupportedSerializers()
    {
        $serializer = $this->getSerializer();

        for ($i = 0; $i < 10; $i++) {
            $s = $this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\DataSerializerInterface');
            $s
                ->expects($this->once())
                ->method('supports')
                ->willReturn(false);
            $s
                ->expects($this->never())
                ->method('serialize');
            /** @var DataSerializerInterface $s */
            $serializer->addDataSerializer($s, 0);
        }

        $this->setExpectedException('Botanick\\Serializer\\Exception\\SerializerNotFoundException', 'No serializers found for "NULL"');

        $serializer->serialize(null);
    }

    public function testSerializeWithSupportedSerializer()
    {
        $serializer = $this->getSerializer();

        $s = $this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\DataSerializerInterface');
        $s
            ->expects($this->once())
            ->method('supports')
            ->willReturn(true);
        $s
            ->expects($this->once())
            ->method('serialize')
            ->willReturnArgument(0);
        /** @var DataSerializerInterface $s */
        $serializer->addDataSerializer($s, 0);

        $this->assertSame(null, $serializer->serialize(null));
    }

    public function testSerializeWithPrioritizedSerializers()
    {
        $serializer = $this->getSerializer();

        $matrix = array(
            array(100, $this->once(), false, $this->never()),
            array(0, $this->once(), true, $this->once()),
            array(-100, $this->never(), true, $this->never()),
        );
        foreach ($matrix as $row) {
            list($priority, $expectsSupports, $supports, $expectsSerialize) = $row;
            $s = $this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\DataSerializerInterface');
            $s
                ->expects($expectsSupports)
                ->method('supports')
                ->willReturn($supports);
            $s
                ->expects($expectsSerialize)
                ->method('serialize')
                ->willReturnArgument(0);
            /** @var DataSerializerInterface $s */
            $serializer->addDataSerializer($s, $priority);
        }

        $this->assertSame(null, $serializer->serialize(null));
    }

    public function testSerializerWithDataSerializerOption()
    {
        $serializer = $this->getSerializer();

        $s = $this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\DataSerializerInterface');
        $s
            ->expects($this->once())
            ->method('getName')
            ->willReturn('super-serializer');
        $s
            ->expects($this->once())
            ->method('serialize')
            ->willReturnArgument(0);
        /** @var DataSerializerInterface $s */
        $serializer->addDataSerializer($s, 0);

        $this->assertSame(null, $serializer->serialize(null, 'default', array('$dataSerializer$' => 'super-serializer')));
    }

    public function testSerializerWithDataSerializerOptionLowPriority()
    {
        $serializer = $this->getSerializer();

        $s1 = $this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\DataSerializerInterface');
        $s1
            ->expects($this->any())
            ->method('getName')
            ->willReturn('super-serializer');
        $s1
            ->expects($this->any())
            ->method('serialize')
            ->willReturnArgument(0);
        /** @var DataSerializerInterface $s1 */
        $serializer->addDataSerializer($s1, -100);

        $s2 = $this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\DataSerializerInterface');
        $s2
            ->expects($this->any())
            ->method('supports')
            ->willReturn(true);
        $s2
            ->expects($this->never())
            ->method('serialize');
        /** @var DataSerializerInterface $s2 */
        $serializer->addDataSerializer($s2, 100);

        $this->assertSame(null, $serializer->serialize(null, 'default', array('$dataSerializer$' => 'super-serializer')));
    }

    public function testSerializerInjectionIntoDataSerializer()
    {
        $serializer = $this->getSerializer();

        $s = $this->getMock('Botanick\\Serializer\\Tests\\Fixtures\\DataSerializerInterface_SerializerAwareInterface');
        $s
            ->expects($this->once())
            ->method('setSerializer')
            ->with($this->identicalTo($serializer));
        /** @var DataSerializerInterface $s */
        $serializer->addDataSerializer($s, 0);
    }

    protected function getSerializer()
    {
        return new Serializer();
    }
}
