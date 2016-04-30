<?php

namespace Botanick\Serializer\Tests\Serializer\DataSerializer;

use Botanick\Serializer\Exception\ConfigNotFoundException;
use Botanick\Serializer\Exception\DataSerializerException;
use Botanick\Serializer\Serializer\Config\SerializerConfigLoaderInterface;
use Botanick\Serializer\Serializer\DataSerializer\ObjectSerializer;
use Botanick\Serializer\SerializerInterface;
use Botanick\Serializer\Tests\Fixtures\SimpleChild;
use Botanick\Serializer\Tests\Fixtures\SimpleClass;

class ObjectSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $serializer = $this->getSerializer();

        $this->assertEquals('botanick.serializer.data-serializer.object', $serializer->getName());
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
            ->with(get_class($value))
            ->willReturn($config);
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->assertEquals($expectedValue, $serializer->serialize($value, $group));
    }

    /**
     * @param array $config
     * @param string $prop
     * @throws \Exception
     * @dataProvider serializeExceptionIfPropertyNotResolvedProvider
     */
    public function testSerializeExceptionIfPropertyNotResolved($config, $prop)
    {
        $obj = new SimpleClass();
        $objClass = get_class($obj);

        $serializer = $this->getSerializer();

        $s = $this->getMock('Botanick\\Serializer\\SerializerInterface');
        $s
            ->expects($this->never())
            ->method('serialize');
        /** @var SerializerInterface $s */
        $serializer->setSerializer($s);

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->with($objClass)
            ->willReturn($config);
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->setExpectedExceptionRegExp(
            'Botanick\\Serializer\\Exception\\DataSerializerException',
            sprintf('~^Cannot access "%s" property in class "%s".~', preg_quote($prop, '~'), preg_quote($objClass, '~'))
        );

        try {
            $serializer->serialize($obj);
        } catch (\Exception $ex) {
            $this->assertInstanceOf('Symfony\\Component\\PropertyAccess\\Exception\\ExceptionInterface', $ex->getPrevious());

            throw $ex;
        }
    }

    public function testGetConfigIfConfigNotFound()
    {
        $obj = new SimpleClass();
        $objClass = get_class($obj);

        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->with($objClass)
            ->willThrowException($configNotFoundException = new ConfigNotFoundException('Config not found.'));
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        try {
            $serializer->getConfig($obj, 'default');
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
        $obj = new SimpleClass();
        $objClass = get_class($obj);

        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->with($objClass)
            ->willReturn($config);
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\DataSerializerException',
            'Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleClass". Neither "'.$group.'" nor "default" group was found.'
        );

        $serializer->getConfig($obj, $group);
    }

    public function testGetConfigIfEmptyGroupIsExtended()
    {
        $obj = new SimpleClass();
        $objClass = get_class($obj);

        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->with($objClass)
            ->willReturn(array('test' => false, 'test1' => array('$extends$' => 'test', 'a' => null), 'test2' => array('$extends$' => 'test1', 'b' => null)));
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\DataSerializerException',
            'Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleClass". Group cannot be extended from empty group "test", path: test2 -> test1.'
        );

        $serializer->getConfig($obj, 'test2');
    }

    public function testGetConfigIfCyclicExtensionOccured()
    {
        $obj = new SimpleClass();
        $objClass = get_class($obj);

        $serializer = $this->getSerializer();

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->with($objClass)
            ->willreturn(array('test' => array('$extends$' => 'test2'), 'test1' => array('$extends$' => 'test'), 'test2' => array('$extends$' => 'test1')));
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\DataSerializerException',
            'Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleClass". Cyclic groups extension found for group "test2", path: test2 -> test1 -> test.'
        );

        $serializer->getConfig($obj, 'test2');
    }

    public function testGetConfigIfParentsAreUsed()
    {
        $obj = new SimpleChild();
        $objClass = get_class($obj);
        $parent = new SimpleClass();
        $parentClass = get_class($parent);

        $serializer = $this->getSerializer();
        $serializer->setDefaultOptions(array('parents' => true));

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->at(0))
            ->method('getConfigFor')
            ->with($objClass)
            ->willThrowException(new ConfigNotFoundException('First fail'));
        $configLoader
            ->expects($this->at(1))
            ->method('getConfigFor')
            ->with($parentClass)
            ->willReturn(array('test' => array('a' => 1)));
        $configLoader
            ->expects($this->exactly(2))
            ->method('getConfigFor');
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        $this->assertEquals(array('a' => 1), $serializer->getConfig($obj, 'test'));
    }

    public function testGetConfigFailureIfParentsAreNotUsed()
    {
        $obj = new SimpleChild();
        $objClass = get_class($obj);

        $serializer = $this->getSerializer();
        $serializer->setDefaultOptions(array('parents' => false));

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->once())
            ->method('getConfigFor')
            ->with($objClass)
            ->willThrowException($configNotFoundException = new ConfigNotFoundException('Config not found.'));
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        try {
            $serializer->getConfig($obj, 'default');
        } catch (DataSerializerException $ex) {
            $this->assertEquals('Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleChild". Config not found.', $ex->getMessage());
            $this->assertSame($configNotFoundException, $ex->getPrevious());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testGetConfigFailureIfParentsAreUsed()
    {
        $obj = new SimpleChild();
        $objClass = get_class($obj);
        $parent = new SimpleClass();
        $parentClass = get_class($parent);

        $serializer = $this->getSerializer();
        $serializer->setDefaultOptions(array('parents' => true));

        $configLoader = $this->getMock('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigLoaderInterface');
        $configLoader
            ->expects($this->at(0))
            ->method('getConfigFor')
            ->with($objClass)
            ->willThrowException($configNotFoundException = new ConfigNotFoundException('First fail.'));
        $configLoader
            ->expects($this->at(1))
            ->method('getConfigFor')
            ->with($parentClass)
            ->willThrowException(new ConfigNotFoundException('Second fail.'));
        $configLoader
            ->expects($this->exactly(2))
            ->method('getConfigFor');
        /** @var SerializerConfigLoaderInterface $configLoader */
        $serializer->setConfigLoader($configLoader);

        try {
            $serializer->getConfig($obj, 'default');
        } catch (DataSerializerException $ex) {
            $this->assertEquals('Cannot serialize class "Botanick\\Serializer\\Tests\\Fixtures\\SimpleChild". First fail.', $ex->getMessage());
            $this->assertSame($configNotFoundException, $ex->getPrevious());

            return;
        }

        $this->fail('An expected exception has not been raised.');
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
            array(fopen(__FILE__, 'r'), false),
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
            // test of $value$ keyword
            array($obj, 'now U see me', 0, 'default', array('default' => array('$value$' => 'now U see me'))),
            array($obj, null, 0, 'default', array('default' => array('$value$' => null))),
            // extending with skipping
            array($obj, array('a' => 1, 'c' => 'c'), 2, 'test2', array('test' => array('a' => null), 'test1' => array('$extends$' => 'test', 'b' => null), 'test2' => array('$extends$' => 'test1', 'b' => false, 'c' => 'c'))),
            // test of $value$ property keyword
            array($obj, array('d' => 'now U see me'), 1, 'default', array('default' => array('d' => array('$value$' => 'now U see me')))),
            array($obj, array('d' => null), 1, 'default', array('default' => array('d' => array('$value$' => null)))),
            // test of $getter$ keyword
            array($obj, array('d' => 'd'), 1, 'default', array('default' => array('d' => array('$getter$' => 'propD')))),
            // test of $default$ keyword
            array($obj, array('nonexistent' => 'hi there!'), 1, 'default', array('default' => array('nonexistent' => array('$default$' => 'hi there!')))),
        );
    }

    public function serializeExceptionIfPropertyNotResolvedProvider()
    {
        return array(
            array(array('default' => array('nonexistent' => null)), 'nonexistent'),
            array(array('default' => array('nonexistent' => array('$getter$' => 'anothernonexistent'))), 'nonexistent'),
        );
    }

    public function getConfigIfGroupNotFoundProvider()
    {
        return array(
            array('default', array()),
            array('test', array()),
            array('default', array('default1' => array(), 'test1' => array())),
            array('test', array('default1' => array(), 'test1' => array())),
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
