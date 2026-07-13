<?php


namespace Ipolh\SDEK\Core\Entity;


/**
 * Trait FieldsContainer
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 */
class FieldsContainer
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @return array
     */
    public function getContainer()
    {
        if(!is_array($this->fields))
            $this->fields = array();

        return $this->fields;
    }

    /**
     * @param string $code
     * @param mixed $val
     * @return $this
     */
    public function setField($code, $val)
    {
        $this->fields[$code] = $val;

        return $this;
    }

    /**
     * @param array $arFields
     * @return $this
     */
    public function setFields($arFields)
    {
        foreach ($arFields as $field => $val)
        {
            $this->setField($field, $val);
        }
        return $this;
    }

    /**
     * @param string $code
     * @return mixed|null
     */
    public function getField($code)
    {
        $container = $this->getContainer();
        return (array_key_exists($code, $container)) ? $container[$code] : null;
    }

}