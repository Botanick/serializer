# ArraySerializer

`ArraySerializer` serializes PHP's arrays recursively.

### Usage

```php
$arraySerializer = new \Botanick\Serializer\Serializer\DataSerializer\ArraySerializer();
$arraySerializer->setSerializer($serializer);
if ($arraySerializer->supports($data)) {
    $serializedData = $arraySerializer->serialize($data);
}
```

### Options

`ArraySerializer` has no options.

### Supports

```php
is_array($data)
```

### Serialize

Returns array with keys left untoched and values serialized using `Serializer`.