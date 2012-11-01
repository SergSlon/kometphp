<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet;

/**
 * 
 * Array schema validator
 * 
 * Usage example:
 * <pre>
 * $schema = array(
 *      "username" => array('@filter' => '/^[a-z0-9_]{5,24}$/'),
 *      "age?" => array('@filter' => FILTER_VALIDATE_INT, '@default' => 18),
 *      "products?" => array(
 *          array(
 *              "name" => "!@",
 *              "description" => "!@",
 *              "sort?" => array('@filter' => FILTER_VALIDATE_BOOLEAN, '@default' => false)
 *          )
 *      ),
 *      "mustbetrue" => array('@filter' => function($v) {
 *              return $v === true;
 *          }, '@default' => true),
 *  );
 *
 *  $data = array(
 *      "username" => "xaguilars",
 *      "mustbetrue" => "xx",
 *      "products" => array(
 *          array(
 *              "name" => "prod1",
 *              "description" => "desc1",
 *              "sortnn" => true
 *          ),
 *          array(
 *              "name" => "prod2",
 *              "description" => "desc2",
 *              "sort" => true
 *          )
 *      )
 *  );
 *
 *  $v = new Validator($schema);
 *  print_r($v->parse($data));
 *
 *  print_r($v);
 * </pre>
 * @package Komet
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 */
class Validator
{

    const ERROR_NOT_SET = 1; // cuando una propiedad es requerida y no está asignada
    const ERROR_NOT_MATCH = 2; // no ha pasado el filtro de validación
    const ERROR_IS_EMPTY = 3; // está vacío
    const ERROR_NOT_EQUAL = 4; // los valores no coinciden
    const ERROR_NOT_FOUND = 5; // propiedad no encontrada en el schema

    protected $schema = array();
    protected $level = array();
    protected $errors = array();
    protected $validData = array();

    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Valida el schema contra los datos.
     * 
     * Genera errores para propiedades requeridas que falten y
     * rellena las propiedades que no existan y tengan un default value.
     * 
     * No hace validación.
     */
    protected function parseSchema($data, $schema = null, $reset = true)
    {
        $schema = ($schema === null) ? $this->schema : $schema;

        if (!is_array($data) or !is_array($schema))
            return false;

        if ($reset) {
            $this->reset();
        }

        foreach ($schema as $property => $value) { // $value es el sub schema
            $this->level[] = $property; // bajar un nivel

            if (is_string($property)) {
                $isRequired = ($this->isOptional($property) == false);
                if ($isRequired == false) {
                    $property = preg_replace('/\?$/', "", $property);
                }

                if (isset($data[$property])) { // campo asignado
                    if (!$this->hasFilter($value)) { // es array, not empty o valor exacto
                        if (is_array($value)) { // recursividad
                            $data[$property] = $this->parseSchema($data[$property], $value, false);
                        }
                    }
                } else { // campo no asignado
                    if ($isRequired) { //campo requerido
                        $this->addError(self::ERROR_NOT_SET, null);
                    }
                    // campo no requerido, mirar si hay default
                    if ($this->hasDefault($value))
                        $data[$property] = $value["@default"];
                }
            }else {
                //numeric index, validar cada item
                if (is_array($data)) {
                    foreach ($data as $k => $v) {
                        $data[$k] = $this->parseSchema($v, $value, false);
                    }
                } else {
                    $data = $this->parseSchema($data, $value, false);
                }
            }

            if (!empty($this->level))
                array_pop($this->level); //subir un nivel
        }

        return $data;
    }

    /**
     * Valida los datos contra el schema, sin tener en cuenta default
     * values o propiedades que falten.
     * 
     * 
     */
    protected function parseData($data, $schema = null, $reset = true, $unsetInvalid = true)
    {
        $schema = ($schema === null) ? $this->schema : $schema;

        if (!is_array($schema) or $this->hasFilter($schema)) {
            return $this->parseField(null, $data, $schema);
        }

        if (!is_array($data) or !is_array($schema))
            return false;

        if ($reset) {
            $this->reset();
        }

        foreach ($data as $property => $value) {
            $this->level[] = $property; // bajar un nivel

            if (!is_numeric($property)) { // index no numerico
                if (isset($schema[$property]) or isset($schema[$property . "?"])) { //existe en el schema
                    $propSchema = isset($schema[$property]) ? $schema[$property] : $schema[$property . "?"];

                    if (is_array($value)) {
                        //recursividad
                        $data[$property] = $this->parseData($value, $propSchema, false);
                    } else {
                        $this->parseField($property, $value, $propSchema, $data, $unsetInvalid);
                    }
                } else { // no existe en el schema
                    if ($unsetInvalid)
                        unset($data[$property]);
                    $this->addError(self::ERROR_NOT_FOUND, $value);
                }
            } else { //index numerico
                if (isset($schema[0])) {
                    $propSchema = $schema[0];
                    //validar value contra $propSchema

                    if (is_array($value)) {
                        $data[$property] = $this->parseData($value, $propSchema, false);
                    } else {
                        $this->parseField($property, $value, $propSchema, $data, $unsetInvalid);
                    }
                } else { // no hay un schema
                    if ($unsetInvalid)
                        unset($data[$property]);
                    $this->addError(self::ERROR_NOT_FOUND, $value);
                }
            }

            if (!empty($this->level))
                array_pop($this->level); //subir un nivel
        }
        return $data;
    }

    protected function parseField($property, $value, $propSchema, &$data = null, $unsetInvalid = true) //, &$data = null)
    {
        $isInvalid = false;
        
//        if ($propSchema instanceof \Closure) { // es función anónima
//            if ($propSchema($value) !== true) {
//                $isInvalid = true;
//                $this->addError(self::ERROR_NOT_MATCH, $value);
//            }
//        } else
            
        if ($this->hasFilter($propSchema)) { // contiene filtro
            if ($this->validateValue($value, $propSchema['@filter']) !== true) {
                $isInvalid = true;
                $this->addError(self::ERROR_NOT_MATCH, $value);
            }
        } elseif ($propSchema == "!@") { // not empty
            if (empty($value)) {
                $isInvalid = true;
                $this->addError(self::ERROR_IS_EMPTY, $value);
            }
        } else { // exacto a $propSchema
            if ($value != $propSchema) {
// $data[$property]=$propSchema;
                $isInvalid = true;
                $this->addError(self::ERROR_NOT_EQUAL, $value);
            }
        }
        if ($unsetInvalid && is_array($data) && ($isInvalid == true)) {
            unset($data[$property]);
            return null;
        } else {
            return $value;
        }
    }

    /**
     * Checks if a variable exists inside an array and matches the given php filter or regular expression.
     * If it matches returns the variable value, otherwise returns $default
     * 
     * @param array $arr Associated array of values
     * @param string $key Array key name
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $filter FILTER_* constant value or regular expression
     * @return boolean
     */
    protected function validateValue($value, $filter = null)
    {
        if ($filter != null) {
            if (is_string($filter) && ($filter{0} == "/")) {
                return (preg_match($filter, $value) > 0);
            } elseif (is_int($filter)) {
                return filter_var($value, $filter) ? true : false;
            } elseif (is_callable($filter)) {
                return $filter($value) ? true : false;
            }
        }else
            return true;
    }

    /**
     * 
     * combina parseData y parseSchema
     */
    public function parse($data, $schema = null, $unsetInvalid = true)
    {
        $this->validData = $this->parseSchema($this->parseData($data, $schema, true, $unsetInvalid), $schema, false);
        return $this->validData;
    }

    public function isValid()
    {
        return (count($this->errors) == 0) and (!empty($this->validData));
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function reset()
    {
        $this->level = array();
        $this->errors = array();
        $this->validData = array();
    }

    protected function addError($errorCode, $value)
    {
        $lv = implode(".", $this->level);
        $this->errors[$lv][] = array("value" => $value, "errorCode" => $errorCode);
    }

    protected function hasDefault($schema)
    {
        return (is_array($schema) and isset($schema['@default']));
    }

    protected function hasFilter($schema)
    {
        return (is_array($schema) and isset($schema['@filter']));
    }

    protected function isOptional($property)
    {
        return preg_match('/.+\?$/', $property) > 0;
    }

}