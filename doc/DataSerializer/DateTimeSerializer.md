# DateTimeSerializer

`DateTimeSerializer` converts instances of `\DateTime` built-in PHP class.

### Usage

```php
$dateTimeSerializer = new \Botanick\Serializer\Serializer\DataSerializer\DateTimeSerializer();
$dateTimeSerializer->setDefaultOptions($defaultOptions);
if ($dateTimeSerializer->supports($data)) {
    $serializedData = $dateTimeSerializer->serialize($data, null, $options);
}
```

### Options

```php
array(
    'format' => 'anything processable by \DateTime::format()'
)
```

### Supports

```php
$data instanceof \DateTime
```

### Serialize

If `format` option is provided and is string, then `$data->format($format)` is returned.

In other cases `$data->getTimestamp()` is returned.