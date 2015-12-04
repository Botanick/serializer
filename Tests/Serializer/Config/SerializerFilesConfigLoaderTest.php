<?php

namespace Botanick\Serializer\Tests\Serializer\Config;

use Botanick\Serializer\Serializer\Config\SerializerConfigCache;
use Botanick\Serializer\Serializer\Config\SerializerFilesConfigLoader;

class SerializerFilesConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    static $_configDir;
    static $_unreadablePerms;

    public static function setUpBeforeClass()
    {
        self::$_configDir = realpath(__DIR__ . '/../../Fixtures/config');
        self::$_unreadablePerms = fileperms(self::$_configDir . '/bad/unreadable/unreadable.yml') & 0777;
        chmod(self::$_configDir . '/bad/unreadable/unreadable.yml', 0000);
    }

    public static function tearDownAfterClass()
    {
        chmod(self::$_configDir . '/bad/unreadable/unreadable.yml', self::$_unreadablePerms);
    }

    public function testGetConfigForWithoutCache()
    {
        $configLoader = $this->getConfigLoader(array(self::$_configDir . '/good/1/Entity.yml', self::$_configDir . '/good/2/AnotherEntity.yml'));

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

    public function testGetConfigForWithoutCacheException2()
    {
        $configLoader = $this->getConfigLoader(array(self::$_configDir . '/bad/bad_format.yml'));

        $this->setExpectedExceptionRegExp(
            'Botanick\\Serializer\\Exception\\ConfigLoadException',
            sprintf('~^Unable to load config from "%s"\.~', preg_quote(self::$_configDir . '/bad/bad_format.yml', '~'))
        );

        try {
            $configLoader->getConfigFor('test');
        } catch (\Exception $ex) {
            $this->assertInstanceOf('Symfony\\Component\\Yaml\\Exception\\ParseException', $ex->getPrevious());

            throw $ex;
        }
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
            array(self::$_configDir . '/good/1/Entity.yml', self::$_configDir . '/good/2/AnotherEntity.yml'),
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
            array(self::$_configDir . '/good/1/Entity.yml', self::$_configDir . '/good/2/AnotherEntity.yml'),
            $configCache
        );

        $this->assertSame(array('x' => 1), $configLoader->getConfigFor('Entity'));
    }

    public function getConfigForWithoutCacheExceptionProvider()
    {
        $configDir = realpath(__DIR__ . '/../../Fixtures/config');

        return array(
            array($configDir . '/bad/nonexistent.yml', sprintf('Unable to load config from "%s". File not found.', $configDir . '/bad/nonexistent.yml')),
            array($configDir . '/bad', sprintf('Unable to load config from "%s". Not a file.', $configDir . '/bad')),
            array($configDir . '/bad/unreadable/unreadable.yml', sprintf('Unable to load config from "%s". File is not readable.', $configDir . '/bad/unreadable/unreadable.yml')),
            array($configDir . '/bad/not_array.yml', sprintf('Unable to load config from "%s". Bad content format.', $configDir . '/bad/not_array.yml'))
        );
    }

    /**
     * @param array $files
     * @param SerializerConfigCache $cache
     * @return SerializerFilesConfigLoader
     */
    protected function getConfigLoader(array $files = array(), SerializerConfigCache $cache = null)
    {
        $configLoader = new SerializerFilesConfigLoader($files, $cache);

        return $configLoader;
    }
}
