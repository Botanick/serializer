# ResourceSerializer

The only goal of `ResourceSerializer` is to serialize PHP's variables of `resource` type (e.g. after `fopen()`, `mysql_query()`, etc.).

### Usage

```php
$resourceSerializer = new \Botanick\Serializer\Serializer\DataSerializer\ResourceSerializer();
if ($resourceSerializer->supports($data)) {
    $serializedData = $resourceSerializer->serialize($data);
}
```

### Options

`ResourceSerializer` has no options.

### Supports

```php
is_resource($data)
```

### Serialize

This serializer returns `resource` converted to `string` type (`return (string)$data`).