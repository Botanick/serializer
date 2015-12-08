# NullSerializer

The only goal of `NullSerializer` is to serialize PHP's `null`.

### Usage

```php
$nullSerializer = new \Botanick\Serializer\Serializer\DataSerializer\NullSerializer();
if ($nullSerializer->supports($data)) {
    $serializedData = $nullSerializer->serialize($data);
}
```

### Options

`NullSerializer` has no options.

### Supports

```php
is_null($data)
```

### Serialize

This serializer returns `null`. Literally.