<?php
namespace Ipolh\SDEK\Bitrix\Entity;

use Ipolh\SDEK\SDEK\Entity\OptionsInterface;


/**
 * Class options
 * @package Ipolh\SDEK
 * Объект для передачи значений опций куда-нибудь в классы-контроллеры.
 * Позволяет получить значение опции по коду, а также делает перегрузку.
 * Так, опцию key можно получить через options->getOption(key) или же options->getKey()
 * Сделано, так как использовать option.php не совсем корректно с точки зрения столпов ООП и вселенской мудрости
 */
class Options implements OptionsInterface
{
    public static function fetchOption($code)
    {
        return \Ipolh\SDEK\option::get($code);
    }

    public function pushOption($option,$handle)
    {
        $this->$option = $handle;
    }

    public function __call($name, $arguments)
    {
        if(strpos($name,'fetch') !== false)
        {
            $option = lcfirst(substr($name,5));

            if(property_exists($this,$option))
                return $this->$option;
            else {
                $this->$option = self::fetchOption($option);
                return $this->$option;
            }
        }
        elseif(strpos($name,'push') !== false)
        {
            $option = lcfirst(substr($name,4));

            $this->pushOption($option,$arguments[0]);

            return $this;
        }
        else
            throw new \Exception('Call to unknown method '.$name);
    }
}