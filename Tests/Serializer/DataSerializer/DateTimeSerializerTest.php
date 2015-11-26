<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Serializer\DataSerializer\DateTimeSerializer;

class DateTimeSerializerTest extends \PHPUnit_Framework_TestCase
{
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
     * @param array $options
     * @dataProvider serializeProvider
     */
    public function testSerialize($value, $expectedValue, $options)
    {
        $serializer = $this->getSerializer($options);

        $serializedValue = $serializer->serialize($value);
        $this->assertSame($expectedValue, $serializedValue);
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
            array(fopen(__FILE__, 'r'), false),
            array(new \DateTime(), true),
            array(new \DateTime('999 years ago'), true)
        );
    }

    public function serializeProvider()
    {
        $now = new \DateTime();
        $once = \DateTime::createFromFormat('H:i:s d.m.Y', '17:47:45 26.11.2015', new \DateTimeZone('UTC'));

        return array(
            array($now, $now->getTimestamp(), array()),
            array($now, $now->getTimestamp(), array('format' => false)),
            array($now, $now->format('Y-m-d H:i:s'), array('format' => 'Y-m-d H:i:s')),
            array($once, 1448560065, array()),
            array($once, '2015-11-26 17:47', array('format' => 'Y-m-d H:i')),
        );
    }

    /**
     * @param array $options
     * @return DateTimeSerializer
     */
    protected function getSerializer(array $options = array())
    {
        $serializer = new DateTimeSerializer();
        $serializer->setDefaultOptions($options);

        return $serializer;
    }
}
