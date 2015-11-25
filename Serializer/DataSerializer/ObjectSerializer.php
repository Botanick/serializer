<?php

namespace Botanick\Serializer\Serializer\DataSerializer;

use Botanick\Serializer\Exception\ConfigNotFoundException;
use Botanick\Serializer\Exception\DataSerializerException;
use Botanick\Serializer\Serializer\Config\SerializerConfigLoaderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ObjectSerializer extends DataSerializer
{
    const PROP_EXTENDS = '$extends$';
    const PROP_GETTER = '$getter$';
    const PROP_DEFAULT = '$default$';

    /**
     * @var SerializerConfigLoaderInterface
     */
    private $_configLoader;

    /**
     * @var PropertyAccessorInterface
     */
    private $_propertyAccessor = null;

    /**
     * @param SerializerConfigLoaderInterface $configLoader
     */
    public function setConfigLoader(SerializerConfigLoaderInterface $configLoader)
    {
        $this->_configLoader = $configLoader;
    }

    /**
     * @return SerializerConfigLoaderInterface
     */
    protected function getConfigLoader()
    {
        return $this->_configLoader;
    }

    /**
     * @return PropertyAccessorInterface
     */
    protected function getPropertyAccessor()
    {
        if (is_null($this->_propertyAccessor)) {
            $this->_propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableMagicCall()
                ->getPropertyAccessor();
        }

        return $this->_propertyAccessor;
    }

    /**
     * @param object $data
     * @param string $group
     * @param mixed $options
     * @return array|null
     * @throws DataSerializerException
     */
    public function serialize($data, $group = self::GROUP_DEFAULT, $options = null)
    {
        $config = $this->getConfig($data, $group);

        if ($config === false) {
            return null;
        }

        $result = [];

        foreach ($config as $prop => $propOptions) {
            if ($propOptions === false) {
                continue;
            }

            $value = $this->getValue($data, $prop, $propOptions);

            $result[$prop] = $this->getSerializer()->serialize($value, $group, $propOptions);
        }

        return $result;
    }

    /**
     * @param object $data
     * @param string $group
     * @return mixed
     * @throws DataSerializerException
     */
    protected function getConfig($data, $group)
    {
        $className = get_class($data);

        try {
            $configGroups = $this->getConfigLoader()->getConfigFor($className);
        } catch (ConfigNotFoundException $ex) {
            throw new DataSerializerException(
                sprintf(
                    'Cannot serialize class "%s". %s',
                    $className,
                    $ex->getMessage()
                ),
                0,
                $ex
            );
        }

        $config = [];
        $visitedGroups = [];
        while (true) {
            if (isset($configGroups[$group])) {

            } elseif (isset($configGroups[self::GROUP_DEFAULT])) {
                $group = self::GROUP_DEFAULT;
            } else {
                throw new DataSerializerException(
                    sprintf(
                        'Cannot serialize class "%s". Neither "%s" nor "%s" group was found.',
                        $className,
                        $group,
                        self::GROUP_DEFAULT
                    )
                );
            }

            if ($configGroups[$group] === false) {
                if (!empty($config)) {
                    throw new DataSerializerException(
                        sprintf(
                            'Cannot serialize class "%s". Group cannot be extended from empty group "%s", path: %s.',
                            $className,
                            $group,
                            implode(' -> ', $visitedGroups)
                        )
                    );
                }

                return false;
            } else {
                $config = array_merge($configGroups[$group], $config);
            }
            $visitedGroups[] = $group;

            if (isset($config[self::PROP_EXTENDS])) {
                $extendedGroup = $config[self::PROP_EXTENDS];

                if (in_array($extendedGroup, $visitedGroups, true)) {
                    throw new DataSerializerException(
                        sprintf(
                            'Cannot serialize class "%s". Cyclic groups extension found for group "%s", path: %s.',
                            $className,
                            $extendedGroup,
                            implode(' -> ', $visitedGroups)
                        )
                    );
                }

                unset($config[self::PROP_EXTENDS]);

                $group = $extendedGroup;
                continue;
            }

            break;
        }

        return $config;
    }

    /**
     * @param object $object
     * @param string $prop
     * @param array $propOptions
     * @return mixed
     * @throws DataSerializerException
     */
    protected function getValue($object, $prop, $propOptions)
    {
        $getter = $prop;
        if (is_array($propOptions)) {
            if (isset($propOptions[self::PROP_GETTER])) {
                $getter = $propOptions[self::PROP_GETTER];
                unset($propOptions[self::PROP_GETTER]);
            }
        }

        try {
            return $this->getPropertyAccessor()->getValue($object, $getter);
        } catch (\Exception $ex) {
            if (is_array($propOptions) && array_key_exists(self::PROP_DEFAULT, $propOptions)) {
                return $propOptions[self::PROP_DEFAULT];
            }

            throw new DataSerializerException(
                sprintf(
                    'Cannot access "%s" property in class "%s". %s',
                    $prop,
                    get_class($object),
                    $ex->getMessage()
                ),
                0,
                $ex
            );
        }
    }

    public function supports($data)
    {
        return is_object($data);
    }
}