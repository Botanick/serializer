<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Serializer\DataSerializer\ScalarSerializer;

class ScalarSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $serializer = $this->getSerializer();

        $this->assertEquals('botanick.serializer.data-serializer.scalar', $serializer->getName());
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
     * @param string $expectedType
     * @param array $defaultOptions
     * @param array $serializeOptions
     * @dataProvider serializeProvider
     */
    public function testSerialize($value, $expectedValue, $expectedType, $defaultOptions, $serializeOptions = null)
    {
        $serializer = $this->getSerializer($defaultOptions);

        $serializedValue = $serializer->serialize($value, 'default', $serializeOptions);
        $this->assertSame($expectedValue, $serializedValue);
        $this->assertInternalType($expectedType, $serializedValue);
    }

    public function supportsProvider()
    {
        return array(
            array(null, false),
            array(1, true),
            array(1.0, true),
            array('a', true),
            array(true, true),
            array(array(), false),
            array(new \stdClass(), false),
            array(fopen(__FILE__, 'r'), false)
        );
    }

    public function serializeProvider()
    {
        return array(
            array(1, 1, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array()),
            array(1.0, 1.0, \PHPUnit_Framework_Constraint_IsType::TYPE_FLOAT, array()),
            array('a', 'a', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array()),
            array(true, true, \PHPUnit_Framework_Constraint_IsType::TYPE_BOOL, array()),
            array(1.1, true, \PHPUnit_Framework_Constraint_IsType::TYPE_BOOL, array('type' => 'bool')),
            array(1.1, 1, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array('type' => 'int')),
            array(1, 1.0, \PHPUnit_Framework_Constraint_IsType::TYPE_FLOAT, array('type' => 'float')),
            array(1.1, '1.1', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array('type' => 'string')),
            array('1a', 1, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array('type' => 'int')),
            array('man', 'Hello, man!', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array('format' => 'Hello, %s!')),
            array(1.2, '1.20', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array('format' => '%01.2f')),
            // serialize options w/o defaults
            array(123, '123', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array(), array('type' => 'string')),
            array(123, '~123~', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array(), array('format' => '~%s~')),
            // serialize options w/ defaults
            array(123, '123', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array('type' => 'float'), array('type' => 'string')),
            array(123, '~123~', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array('format' => '=%s='), array('format' => '~%s~')),
            // nonexistent type
            array(123, 123, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array('type' => 'blah')),
            array('123', '123', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array('type' => 'blah')),
            // non-string format
            array(123, 123, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array('format' => 42)),
            array('123', '123', \PHPUnit_Framework_Constraint_IsType::TYPE_STRING, array('format' => 42)),
            // type > format
            array('123', 123, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array('type' => 'int', 'format' => '-%s-')),
            array('123', 123, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array('type' => 'int'), array('format' => '-%s-')),
            array('123', 123, \PHPUnit_Framework_Constraint_IsType::TYPE_INT, array(), array('type' => 'int', 'format' => '-%s-')),
        );
    }

    /**
     * @param array $options
     * @return ScalarSerializer
     */
    protected function getSerializer(array $options = array())
    {
        $serializer = new ScalarSerializer();
        $serializer->setDefaultOptions($options);

        return $serializer;
    }
}
