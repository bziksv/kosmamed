<?php
namespace Rbs\MoyskladStocks\Diagnostic\Dashboard\Core;

use Rbs\MoyskladStocks\Diagnostic\Dashboard\Page;

class ItemsFactory
{
    public static function createItem(string $group, string $key): ItemInterface
    {
        $className = "\\Rbs\\MoyskladStocks\\Diagnostic\\Dashboard\\Items\\{$group}\\{$key}";
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