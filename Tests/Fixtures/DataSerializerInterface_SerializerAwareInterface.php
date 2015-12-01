<?php

namespace Botanick\Serializer\Tests\Fixtures;

use Botanick\Serializer\Serializer\DataSerializer\DataSerializerInterface;
use Botanick\Serializer\SerializerAwareInterface;

/**
 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/280
 */
interface DataSerializerInterface_SerializerAwareInterface extends DataSerializerInterface, SerializerAwareInterface
{

}