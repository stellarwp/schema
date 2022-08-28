<?php
/**
 * The representation of a builder parameter.
 *
 * @package StellarWP\Schema\lucatume\DI52\Builders
 *
 * @license GPL-3.0
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\lucatume\DI52\Builders;

use StellarWP\Schema\lucatume\DI52\ContainerException;
use ReflectionParameter;

/**
 * Class Parameter
 *
 * @package StellarWP\Schema\lucatume\DI52\Builders
 */
class Parameter
{
    /**
     * The parameter type or `null` if the type cannot be parsed.
     *
     * @var string|null
     */
    protected $type;
    /**
     * Whether the parameter is an optional one or not.
     *
     * @var bool
     */
    protected $isOptional;
    /**
     * The parameter default value, or `null` if not available.
     *
     * @var mixed|null
     */
    protected $defaultValue;
    /**
     * Whether the parameter is a class or not.
     *
     * @var bool
     */
    protected $isClass;

    /**
     * A list of the types that are NOT classes.
     *
     * @var array<string>
     */
    protected static $nonClassTypes = [
        'string',
        'int',
        'bool',
        'float',
        'double',
        'array',
        'resource',
        'callable',
        'iterable',
    ];
    /**
     * A map relating the string output type to the internal, type-hintable, type.
     *
     * @var array<string>
     */
    protected static $conversionMap = [
        'integer' => 'int',
        'boolean' => 'bool',
        'double' => 'float'
    ];

    /**
     * The parameter name.
     *
     * @var string
     */
    protected $name;

    /**
     * Parameter constructor.
     *
     * @param int                 $index               The parameter position in the list of parameters.
     * @param ReflectionParameter $reflectionParameter The parameter reflection to extract the information from.
     */
    public function __construct($index, ReflectionParameter $reflectionParameter)
    {
        $string = $reflectionParameter->__toString();
        $s = trim(str_replace('Parameter #' . $index, '', $string), '[ ]');
        $frags = explode(' ', $s);

        $this->name = $reflectionParameter->name;
        $this->type = strpos($frags[1], '$') === 0 ? null : $frags[1];
        // PHP 8.0 nullables.
        $this->type = str_replace('?', '', (string)$this->type);
        if (isset(static::$conversionMap[$this->type])) {
            $this->type = static::$conversionMap[$this->type]; // @codeCoverageIgnore
        }
        $this->isClass = $this->type && !in_array($this->type, static::$nonClassTypes, true);
        $this->isOptional = $frags[0] === '<optional>';
        $this->defaultValue = $this->isOptional ? $reflectionParameter->getDefaultValue() : null;
    }

    /**
     * Returns the parameter extracted data.
     *
     * @return array<string,string|bool|mixed> A map of the parameter data.
     */
    public function getData()
    {
        return [
            'type' => $this->type,
            'isOptional' => $this->isOptional,
            'defaultValue' => $this->defaultValue
        ];
    }

    /**
     * Returns the parameter default value, if any.
     *
     * @return mixed|null The parameter default value, if any.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns the parameter class name, if any.
     *
     * @return string|null The parameter class name, if any.
     */
    public function getClass()
    {
        return $this->isClass ? $this->type : null;
    }

    /**
     * Returns the parameter name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the parameter type, if any.
     *
     * @return string|null The parameter type, if any.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Either return the parameter default value, or die trying.
     *
     * @return mixed|null The parameter default value.
     * @throws ContainerException If the parameter does not have a default value.
     */
    public function getDefaultValueOrFail()
    {
        if (!$this->isOptional) {
            throw new ContainerException(
                sprintf(
                    'Parameter $%s is not optional and is not type-hinted: auto-wiring is not magic.',
                    $this->name
                )
            );
        }
        return $this->defaultValue;
    }
}
