# Serializer

[![Build Status](https://travis-ci.org/Botanick/serializer.svg?branch=master)](https://travis-ci.org/Botanick/serializer)

This library allows you to serialize PHP structures of any complexity into PHP primitives: scalars, arrays, arrays of scalars, arrays of arrays, etc. The result can be then safely stored somewhere or passed anywhere using `serialize()`, `json_encode()` or any other way to convert plain arrays into string.

This library contains built-in serializers for:

- `null`
- scalars (bool, int, float, string)
- PHP `resource` type
- arrays and `\Traversable`
- `\DateTime`
- objects (which are flexibly configurable via custom configurations)

### Installation

This library is managed via composer. You can install it by calling:

```sh
$ composer require botanick/serializer
```

or manually adding it to your `composer.json`:

```json
    "require": {
        "botanick/serializer": "dev-master"
    }
```

### Usage

Serializer usage is split into two general steps.

First of all, you should construct data serializers you are going to use. For example, if you are going to serialize only arrays of DateTimes, then your code will look like:

```php
use Botanick\Serializer\Serializer;

$arraySerializer = new Serializer\DataSerializer\ArraySerializer();
$dateTimeSerializer = new Serializer\DataSerializer\DateTimeSerializer();
// for detailed info on DateTimeSerializer options see an according section
$dateTimeSerializer->setDefaultOptions(array('format' => 'Y-m-d H:i:s'));
```

Then you need serializer to know about data serializers it should use:

```php
use Botanick\Serializer\Serializer;

$serializer = new Serializer\Serializer();
$serializer->addDataSerializer($arraySerializer, -9999);
$serializer->addDataSerializer($dateTimeSerializer, -8888);
```

That's all! Now you are able to serialize your data:

```php
$data = array(
    new \DateTime(),
    'yesterday' => new \DateTime('1 day ago'),
    'dates' => array(
        'some-date' => \DateTime::createFromFormat('H:i:s d.m.Y', '11:22:33 01.02.2003'),
        'another-date' => \DateTime::createFromFormat('d.m.Y', '01.02.2003')
    )
);
$serializedData = $serializer->serialize($data);
var_export($serializedData);

// you will see something like this:
// array (
//   0 => '2015-12-08 16:42:54',
//   'yesterday' => '2015-12-07 16:42:54',
//   'dates' => 
//   array (
//     'some-date' => '2003-02-01 11:22:33',
//     'another-date' => '2003-02-01 16:42:54',
//   ),
// )
```

### Documentation

- Data serializers:
    - [NullSerializer](doc/Serializer/DataSerializer/NullSerializer.md)
    - [ScalarSerializer](doc/Serializer/DataSerializer/ScalarSerializer.md)
    - [ResourceSerializer](doc/Serializer/DataSerializer/ResourceSerializer.md)
    - [ArraySerializer](doc/Serializer/DataSerializer/ArraySerializer.md)
    - [DateTimeSerializer](doc/Serializer/DataSerializer/DateTimeSerializer.md)
    - [ObjectSerializer](doc/Serializer/DataSerializer/ObjectSerializer.md)
    - [TraversableSerializer](doc/Serializer/DataSerializer/TraversableSerializer.md)
    - [Implementing your own data serializer](doc/Serializer/DataSerializer/YourOwn.md)
- Config loaders (used in `ObjectSerializer`):
    - [SerializerArrayConfigLoader](doc/Config/SerializerArrayConfigLoader.md)
    - [SerializerFilesConfigLoader](doc/Config/SerializerFilesConfigLoader.md)
    - [SerializerDirsConfigLoader](doc/Config/SerializerDirsConfigLoader.md)
    - [Config caching](doc/Config/YourOwn.md)
    - [Implementing your own config loader](doc/Config/YourOwn.md)
- Serializer itself:
    - [Built-in serializer](doc/Serializer/Serializer.md)
    - [Implementing your own serializer](doc/Serializer/YourOwn.md)
