<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



class pinax_dataAccessDoctrine_DbField
{
    const NOT_INDEXED = 0;  // non indicizzato
    const INDEXED = 1;      // indicizzato
    const FULLTEXT = 2;     // indice fulltext
    const ONLY_INDEX = 16;    // indice fulltext

    public $name;
    public $type;
    public $size;
    public $key;
    public $validator; // viene settato dal compilatore del model
    public $defaultValue;
    public $readFormat;
    public $virtual;
    public $description; // con la label del campo si usa questo
    public $index; // tre costanti non indicizzato, indicizzato, fulltext
    public $option;
    public $isSystemField = false;

    function __construct($name, $type, $size, $key, $validator, $defaultValue, $readFormat=true, $virtual=false, $description='', $index=self::INDEXED, $option=null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->key = $key;
        $this->validator = $validator;
        $this->defaultValue = $defaultValue;
        $this->readFormat = $readFormat;
        $this->virtual = $virtual;
        $this->description = $description !== '' ? $description : $name;
        $this->index = $index;
        $this->option = pinax_maybeJsonDecode($option, true);

        // TODO c'� un problema con il valore di option
        // nelle schede ICCD � un stringa che punta d un altro model
        // il compilatore di Model usa una stringa
        // ma qui per ARRAY_ID vuole un oggetto, credo che sia usato anche in Content.xml come oggetto

        if (    $this->type == pinax_dataAccessDoctrine_types_Types::ARRAY_ID &&
                (is_null($this->option) || !isset($this->option[pinax_dataAccessDoctrine_types_Types::ARRAY_ID])) ) {
            // backward compatibility if the ARRAY_ID field don't have options
            // set the default values
            $this->option = array( pinax_dataAccessDoctrine_types_Types::ARRAY_ID => array(
                                        'type' => \Doctrine\DBAL\Types\Type::INTEGER,
                                        'field' => 'id'
                                    ));
        }
    }

    /**
     * @param  mixed $value
     * @param \Doctrine\DBAL\Connection $connection
     * @return mixed
     */
    public function format($value, $connection)
    {
        return $this->readFormat ? $connection->convertToPHPValue($value, $this->type) : $value;
    }

    /**
     * @param pinax_validators_ValidatorInterface $validator
     */
    public function addValidator($validator)
    {
        if (!$this->validator || (!$this->validator instanceof pinax_validators_CompositeValidator)) {
            $composite = new pinax_validators_CompositeValidator();
            if ($this->validator ) {
                $composite->add($this->validator);
            }
            $this->validator = $composite;
        }
        $this->validator->add($validator);
    }

    /**
     * @param  array $options
     * @return pinax_dataAccessDoctrine_DbField
     */
    static public function create($options)
    {
        if ($options['index'] == true) {
            $options['index'] = self::INDEXED;
        } else if ($options['index'] == false) {
            $options['index'] = self::NOT_INDEXED;
        } else if ($options['index'] == 'fulltext') {
            $options['index'] = self::FULLTEXT;
        }

        if ($options['onlyIndex']) {
            $options['index'] |= self::ONLY_INDEX;
        }

        if ($options['type'] == \Doctrine\DBAL\Types\Type::OBJECT && is_null($options['readFormat'])) {
            $options['readFormat'] = false;
        }

        return new pinax_dataAccessDoctrine_DbField(
            $options['name'],
            $options['type'],
            isset($options['size']) ? $options['size'] : 255,
            isset($options['key']) ? $options['key'] : false,
            $options['validator'],
            isset($options['defaultValue']) ? $options['defaultValue'] : '',
            isset($options['readFormat']) ? $options['readFormat'] : true,
            isset($options['virtual']) ? $options['virtual'] : false,
            isset($options['description']) ? $options['description'] : '',
            isset($options['index']) ? $options['index'] : self::NOT_INDEXED,
            $options['option']
        );
    }
}
