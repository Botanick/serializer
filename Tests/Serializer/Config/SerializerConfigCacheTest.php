<?php

namespace Botanick\Serializer\Tests\Serializer\Config;

use Botanick\Serializer\Serializer\Config\SerializerConfigCache;
use Botanick\Serializer\Serializer\Config\SerializerConfigCacheDumper;
use Symfony\Component\Filesystem\Filesystem;

class SerializerConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    protected static $_umask;
    protected static $_cacheDir;
    protected static $_dummyFile;
    /**
     * @var Filesystem
     */
    protected static $_filesystem;
    protected static $_cacheClassPrefix;

    public static function setUpBeforeClass()
    {
        self::$_cacheDir = sys_get_temp_dir() . '/' . uniqid();
        self::$_umask = umask(0);
        mkdir(self::$_cacheDir, 0777, true);
        self::$_cacheDir = realpath(self::$_cacheDir);
        self::$_filesystem = new Filesystem();
        self::$_dummyFile = realpath(__DIR__ . '/../../Fixtures/dummy_file');
        self::$_cacheClassPrefix = uniqid('AppTest');
    }

    public static function tearDownAfterClass()
    {
        self::$_filesystem->remove(self::$_cacheDir);
        umask(self::$_umask);
    }

    private static function doSomeMagicWithCacheFile()
    {
        $newPrefix = uniqid('AppTest');
        $oldFile = sprintf('%s/%sBotanickSerializerConfig.php', self::$_cacheDir, self::$_cacheClassPrefix);
        $newFile = sprintf('%s/%sBotanickSerializerConfig.php', self::$_cacheDir, $newPrefix);

        file_put_contents(
            $newFile,
            str_replace(
                self::$_cacheClassPrefix . 'BotanickSerializerConfig',
                $newPrefix . 'BotanickSerializerConfig',
                file_get_contents($oldFile)
            )
        );

        self::$_cacheClassPrefix = $newPrefix;
    }

    /**
     * @param string $type
     * @param array $sources
     * @param bool $dumpCalled
     * @dataProvider getCachedConfigProvider
     */
    public function testGetCachedConfig($type, $sources, $dumpCalled)
    {
        $cache = $this->getCache($dumpCalled);

        $config = array('a', 1, true);
        $called = false;

        $this->assertEquals(
            $config,
            $cache->getCachedConfig(
                $type,
                $sources,
                function () use ($config, &$called) {
                    $called = true;

                    return array(
                        $config,
                        array(self::$_dummyFile)
                    );
                }
            )
        );
        $this->assertFileExists(sprintf('%s/%sBotanickSerializerConfig.php', self::$_cacheDir, self::$_cacheClassPrefix));
        $this->assertEquals($dumpCalled, $called);

        self::doSomeMagicWithCacheFile();
    }

    public function getCachedConfigProvider()
    {
        return array(
            array('type1', array('a', 'b'), true),
            array('type1', array('a', 'b'), false),
            array('type2', array('a', 'b'), true),
            array('type2', array('a', 'b'), false),
            array('type2', array('a', 'b', 'c'), true),
            array('type2', array('a', 'b', 'c'), false)
        );
    }

    /**
     * @param bool $dumpCalled
     * @return SerializerConfigCache
     */
    protected function getCache($dumpCalled = false)
    {
        $dumper = $this->getMockBuilder('Botanick\\Serializer\\Serializer\\Config\\SerializerConfigCacheDumper')
            ->enableProxyingToOriginalMethods()
            ->getMock();
        $dumper
            ->expects($dumpCalled ? $this->once() : $this->never())
            ->method('dump');
        /** @var SerializerConfigCacheDumper $dumper */

        $cache = new SerializerConfigCache(
            self::$_cacheClassPrefix,
            false,
            self::$_cacheDir,
            $dumper
        );

        return $cache;
    }
}
