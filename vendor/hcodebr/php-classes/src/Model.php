<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 10/01/2019
 * Time: 14:04
 */

namespace Hcode;


class Model
{

    private $values = [];

    public function __call($name, $args)
    {
        $method = substr($name, 0, 3);
        $fieldName = strtolower(substr($name, 3, strlen($name)));
        switch ($method){

            case "get":
                //Quando a variável não existir será atribuido o valor NULL
                return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
                break;


            case "set":
                    $this->values[$fieldName] = $args[0];
                break;

        }
    }

    public function setData($data = array())
    {
        foreach ( $data as $ket => $value) {
                $this->{"set".$ket}($value);

        }
    }

    public function getValues(){
        return $this->values;
    }

}