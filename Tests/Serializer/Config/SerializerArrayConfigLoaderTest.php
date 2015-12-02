<?php

namespace Botanick\Serializer\Tests\Serializer\Config;

use Botanick\Serializer\Serializer\Config\SerializerArrayConfigLoader;

class SerializerArrayConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $config
     * @param string $name
     * @param mixed $expectedResult
     * @dataProvider getConfigForProvider
     */
    public function testGetConfigFor($config, $name, $expectedResult)
    {
        $configLoader = $this->getConfigLoader($config);

        $this->assertEquals($expectedResult, $configLoader->getConfigFor($name));
    }

    /**
     * @param array $config
     * @param string $name
     * @param mixed $expectedResult
     * @dataProvider getConfigForProvider
     */
    public function testGetConfigForWithSetConfig($config, $name, $expectedResult)
    {
        $configLoader = $this->getConfigLoader();
        $configLoader->setConfig($config);

        $this->assertEquals($expectedResult, $configLoader->getConfigFor($name));
    }

    /**
     * @param array $config
     * @param string $name
     * @dataProvider getConfigForExceptionProvider
     */
    public function testGetConfigForException($config, $name)
    {
        $configLoader = $this->getConfigLoader($config);

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\ConfigNotFoundException',
            sprintf('Config for "%s" not found.', $name)
        );

        $configLoader->getConfigFor($name);
    }

    public function getConfigForProvider()
    {
        return array(
            array(array('a' => false), 'a', false),
            array(array('a' => array(1, 2), 'b' => array(3, 4)), 'a', array(1, 2)),
            array(array('a' => array(1, 2), 'b' => array(3, 4)), 'b', array(3, 4))
        );
    }

    public function getConfigForExceptionProvider()
    {
        return array(
            array(null, 'a'),
            array(array(), 'a'),
            array(array('a' => 1, 'b' => 2), 'c')
        );
    }

    /**
     * @param array $config
     * @return SerializerArrayConfigLoader
     */
    protected function getConfigLoader(array $config = null)
    {
        $configLoader = new SerializerArrayConfigLoader($config);

        return $configLoader;
    }
}
