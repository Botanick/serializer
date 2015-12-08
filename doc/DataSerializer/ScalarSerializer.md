# ScalarSerializer

`ScalarSerializer` can convert only PHP scalars.

### Usage

```php
$scalarSerializer = new \Botanick\Serializer\Serializer\DataSerializer\ScalarSerializer();
$scalarSerializer->setDefaultOptions($defaultOptions);
if ($scalarSerializer->supports($data)) {
    $serializedData = $scalarSerializer->serialize($data, null, $options);
}
```

### Options

```php
array(
    'type' => 'bool', // supports: 'bool', 'boolean', 'int', 'integer', 'number', 'float', 'double', 'real', 'string'
    'format' => 'anything processable by sprintf() containing one and only one placeholder (e.g. %s)'
)
```

### Supports

```php
is_scalar($data)
```

### Serialize

When `type` is provided and is one of supported ones, then an argument will be converted using `(casting)` (e.g. `return (bool)$data;`).

If `format` option is provided, then `sprintf($format, $data)` is used.

If neither supported `type` nor `format` is provided, then serializer returns an argument itself.