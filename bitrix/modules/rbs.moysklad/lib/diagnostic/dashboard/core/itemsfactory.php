<?php
namespace Rbs\Moysklad\Diagnostic\Dashboard\Core;

class ItemsFactory
{
    public static function createItem(string $group, string $key): ItemInterface
    {
        $className = "\\Rbs\\Moysklad\\Diagnostic\\Dashboard\\Items\\{$group}\\{$key}";
        if (!class_exists($className)) {
            throw new \Exception("Class {$className} not found");
        }
        
        $instance = new $className();
        
        if (!($instance instanceof ItemInterface)) {
            throw new \Exception("Class {$className} not implements ItemInterface");
        }
        
        return $instance;
    }
}