<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Exception\ConfigNotFoundException;
use Botanick\Serializer\Exception\DataSerializerException;
use Botanick\Serializer\Serializer\Config\SerializerConfigLoaderInterface;
use Botanick\Serializer\Serializer\DataSerializer\ObjectSerializer;
use Botanick\Serializer\SerializerInterface;
use Botanick\Serializer\Tests\Fixtures\SimpleClass;

class ObjectSerializerTest extends \PHPUnit_Framework_TestCase
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
     * @param object $value
     * @param array|null $expectedValue
     * @param int $subcalls
     * @param string $group
     * @param mixed $config
     * @dataProvider serializeProvider
     */
    public function testSerialize($value, $expectedValue, $subcalls, $group, $config)
    {
        $serializer = $this->getSerializer();

        $s = $this->getMock('Botanick\\Serializer\\SerializerInterface');
        $s
            ->expects($this->exactly($subcalls))
            ->method('serialize')
            ->willReturnArgument(0);
        /** @var SerializerInterface $s */
        $serializer->setSerializer($s);

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->willReturn($config);
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->assertEquals($expectedValue, $serializer->serialize($value, $group));
    }

    public function testGetConfigIfConfigNotFound()
    {
        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->willThrowException($configNotFoundException = new ConfigNotFoundException('Config not found.'));
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        try {
            $serializer->getConfig(new SimpleClass(), 'default');
        } catch (DataSerializerException $ex) {
            $this->assertEquals('Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleClass". Config not found.', $ex->getMessage());
            $this->assertSame($configNotFoundException, $ex->getPrevious());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @param string $group
     * @param array $config
     * @dataProvider getConfigIfGroupNotFoundProvider
     */
    public function testGetConfigIfGroupNotFound($group, $config)
    {
        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->willreturn($config);
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\DataSerializerException',
            'Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleClass". Neither "' . $group . '" nor "default" group was found.'
        );

        $serializer->getConfig(new SimpleClass(), $group);
    }

    public function testGetConfigIfEmptyGroupIsExtended()
    {
        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->willreturn(array('test' => false, 'test1' => array('$extends$' => 'test', 'a' => null), 'test2' => array('$extends$' => 'test1', 'b' => null)));
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\DataSerializerException',
            'Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleClass". Group cannot be extended from empty group "test", path: test2 -> test1.'
        );

        $serializer->getConfig(new SimpleClass(), 'test2');
    }

    public function testGetConfigIfCyclicExtensionOccured()
    {
        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->willreturn(array('test' => array('$extends$' => 'test2'), 'test1' => array('$extends$' => 'test'), 'test2' => array('$extends$' => 'test1')));
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\DataSerializerException',
            'Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleClass". Cyclic groups extension found for group "test2", path: test2 -> test1 -> test.'
        );

        $serializer->getConfig(new SimpleClass(), 'test2');
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
            array(new \stdClass(), true),
            array($this->getMock('Traversable'), true),
            array($this->getMock('Botanick\\Serializer\\Serializer\\DataSerializer\\ObjectSerializer'), true),
            array(fopen(__FILE__, 'r'), false)
        );
    }

    public function serializeProvider()
    {
        $obj = new SimpleClass();

        return array(
            // simple resolve with "don't serialize me" config, default group
            array($obj, null, 0, 'default', array('default' => false)),
            // same as above, but with named group
            array($obj, null, 0, 'test', array('test' => false)),
            // empty serialization
            array($obj, array(), 0, 'default', array('default' => array())),
            // fallback to default group
            array($obj, array('a' => 1), 1, 'test', array('default' => array('a' => null))),
            // simple serialization
            array($obj, array('a' => 1, 'b' => 'b', 'null' => null, 'c' => 'c'), 4, 'default', array('default' => array('a' => null, 'b' => null, 'null' => null, 'c' => 'c'))),
            // test of $extend$ keyword
            array($obj, array('a' => 1, 'b' => 'b', 'c' => 'c'), 3, 'test2', array('test' => array('a' => null), 'test1' => array('$extends$' => 'test', 'b' => null), 'test2' => array('$extends$' => 'test1', 'c' => null))),
            // extending with skipping
            array($obj, array('a' => 1, 'c' => 'c'), 2, 'test2', array('test' => array('a' => null), 'test1' => array('$extends$' => 'test', 'b' => null), 'test2' => array('$extends$' => 'test1', 'b' => false, 'c' => 'c'))),
            // test of $getter$ keyword
            array($obj, array('d' => 'd'), 1, 'default', array('default' => array('d' => array('$getter$' => 'propD')))),
            // test of $default$ keyword
            array($obj, array('nonexistent' => 'hi there!'), 1, 'default', array('default' => array('nonexistent' => array('$default$' => 'hi there!')))),
        );
    }

    public function getConfigIfGroupNotFoundProvider()
    {
        return array(
            array('default', array()),
            array('test', array()),
            array('default', array('default1' => array(), 'test1' => array())),
            array('test', array('default1' => array(), 'test1' => array()))
        );
    }

    /**
     * @return ObjectSerializer
     */
    protected function getSerializer()
    {
        $serializer = new ObjectSerializer();

        return $serializer;
    }
}
