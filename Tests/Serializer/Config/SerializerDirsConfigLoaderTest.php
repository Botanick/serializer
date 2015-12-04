<?php

namespace Botanick\Serializer\Tests\Serializer\Config;

use Botanick\Serializer\Serializer\Config\SerializerConfigCache;
use Botanick\Serializer\Serializer\Config\SerializerDirsConfigLoader;

class SerializerDirsConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    static $_configDir;
    static $_unreadablePerms;

    public static function setUpBeforeClass()
    {
        self::$_configDir = realpath(__DIR__ . '/../../Fixtures/config');
        self::$_unreadablePerms = fileperms(self::$_configDir . '/bad/unreadable') & 0777;
        chmod(self::$_configDir . '/bad/unreadable', 0000);
    }

    public static function tearDownAfterClass()
    {
        chmod(self::$_configDir . '/bad/unreadable', self::$_unreadablePerms);
    }

    public function testGetConfigForWithoutCache()
    {
        $configLoader = $this->getConfigLoader(array(self::$_configDir . '/good/1', self::$_configDir . '/good/2'));

        $this->assertSame(array('x' => 1), $configLoader->getConfigFor('Entity'));
    }

    /**
     * @param string $file
     * @param string $exceptionMessage
     * @dataProvider getConfigForWithoutCacheExceptionProvider
     */
    public function testGetConfigForWithoutCacheException($file, $exceptionMessage)
    {
        $configLoader = $this->getConfigLoader(array($file));

        $this->setExpectedException(
            'Botanick\\Serializer\\Exception\\ConfigLoadException',
            $exceptionMessage
        );

        $configLoader->getConfigFor('test');
    }

    public function testGetConfigForWithCache()
    {
        $config = array('test' => array('a' => 1));

        $configCache = $this->getMockBuilder('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigCache')
            ->disableOriginalConstructor()
            ->getMock();

        $configCache
            ->expects($this->once())
            ->method('getCachedConfig')
            ->willReturn($config);
        /** @var SerializerConfigCache $configCache */

        $configLoader = $this->getConfigLoader(
            array(self::$_configDir . '/good/1', self::$_configDir . '/good/2'),
            $configCache
        );

        $this->assertSame($config['test'], $configLoader->getConfigFor('test'));
    }

    public function testGetConfigForWithCacheMiss()
    {
        $configCache = $this->getMockBuilder('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigCache')
            ->disableOriginalConstructor()
            ->getMock();

        $that = $this;
        $configCache
            ->expects($this->once())
            ->method('getCachedConfig')
            ->willReturnCallback(
                function ($type, $sources, $createConfigCallback) use ($that) {
                    $that->assertInternalType('string', $type);
                    $that->assertNotEmpty($type);
                    $that->assertInternalType('array', $sources);
                    $that->assertEquals(2, sizeof($sources));

                    list($config, $filesAndDirs) = call_user_func($createConfigCallback);
                    $that->assertInternalType('array', $filesAndDirs);
                    $that->assertEquals(2, sizeof($filesAndDirs));

                    return $config;
                }
            );
        /** @var SerializerConfigCache $configCache */

        $configLoader = $this->getConfigLoader(
            array(self::$_configDir . '/good/1', self::$_configDir . '/good/2'),
            $configCache
        );

        $this->assertSame(array('x' => 1), $configLoader->getConfigFor('Entity'));
    }

    public function getConfigForWithoutCacheExceptionProvider()
    {
        $configDir = realpath(__DIR__ . '/../../Fixtures/config');

        return array(
            array($configDir . '/nonexistent', sprintf('Unable to load config from "%s". Directory not found.', $configDir . '/nonexistent')),
            array($configDir . '/bad/bad_format.yml', sprintf('Unable to load config from "%s". Not a directory.', $configDir . '/bad/bad_format.yml')),
            array($configDir . '/bad/unreadable', sprintf('Unable to load config from "%s". Directory is not readable.', $configDir . '/bad/unreadable'))
        );
    }

    /**
     * @param array $dirs
     * @param SerializerConfigCache $cache
     * @return SerializerDirsConfigLoader
     */
    protected function getConfigLoader(array $dirs = array(), SerializerConfigCache $cache = null)
    {
        $configLoader = new SerializerDirsConfigLoader($dirs, $cache);

        return $configLoader;
    }
}
