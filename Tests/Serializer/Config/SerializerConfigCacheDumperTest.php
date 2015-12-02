<?php

namespace Botanick\Serializer\Tests\Serializer\Config;

use Botanick\Serializer\Serializer\Config\SerializerConfigCacheDumper;
use Botanick\Serializer\Serializer\Config\SerializerConfigInterface;

class SerializerConfigCacheDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $dumper = new SerializerConfigCacheDumper();

        $class = uniqid('DumpedCacheClass_');
        $type = 'myType';
        $sources = array('a', 1, true);
        $config = array('a' => 1, 'x' => array('xx' => 1));

        eval('?>' . $dumper->dump($class, $type, $sources, $config));

        /** @var SerializerConfigInterface $cache */
        $cache = new $class();

        $this->assertTrue($cache->isType($type));
        $this->assertTrue($cache->isFromSources($sources));
        $this->assertEquals($config, $cache->getConfig());
    }
}
