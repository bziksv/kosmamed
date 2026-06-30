<?php
namespace Rbs\MoyskladStocks\Internals;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

class HlBlockValues
{    
    private static $instances = [];
    private $hlBlockId;    
    private $dataClass;    
    private $values = [];    
    private $nameField = 'UF_NAME';    
    private $isNumericXmlId = false;
    private $isLoaded = false;

    private function __construct(int $hlBlockId, string $nameField = 'UF_NAME', bool $isNumericXmlId = false)
    {
        $this->hlBlockId = $hlBlockId;
        $this->nameField = $nameField;
        $this->isNumericXmlId = $isNumericXmlId;
        $this->initDataClass();
    }

    
    public static function getInstance(int $hlBlockId, string $nameField = 'UF_NAME', bool $isNumericXmlId = false): self
    {
        $key = $hlBlockId . '_' . $nameField;
        
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($hlBlockId, $nameField, $isNumericXmlId);
        }
        
        return self::$instances[$key];
    }

    private function initDataClass(): void
    {
        \Bitrix\Main\Loader::includeModule('highloadblock');
        
        $hlBlock = HighloadBlockTable::getList([
            'filter' => ['=ID' => $this->hlBlockId]
        ])->fetch();

        if (!$hlBlock) {
            throw new ArgumentException("HL-block with ID {$this->hlBlockId} not found");
        }

        $entity = HighloadBlockTable::compileEntity($hlBlock);
        $this->dataClass = $entity->getDataClass();
    }

    private function loadValues(): void
    {
        if ($this->isLoaded) {
            return;
        }

        $this->values = [];
        
        $result = $this->dataClass::getList([
            'select' => ['ID', $this->nameField],
            'filter' => []
        ]);

        while ($row = $result->fetch()) {
            $name = trim((string)$row[$this->nameField]);
            if (!empty($name)) {
                $this->values[mb_strtolower($name)] = (int)$row['ID'];
            }
        }
        
        $this->isLoaded = true;
    }

    public function getValueId(string $name): int
    {
        $this->loadValues();
        
        $name = trim($name);
        if (empty($name)) {
            throw new ArgumentException('Name cannot be empty');
        }
        
        $nameKey = mb_strtolower($name);
        
        if (isset($this->values[$nameKey])) {
            return $this->values[$nameKey];
        }
        
        return $this->createValue($name);
    }

    private function createValue(string $name): int
    {
        $data = [
            $this->nameField => $name,
            'UF_XML_ID' => $this->isNumericXmlId ? '111' . rand(100, 999) . '999' : md5($name)
        ];
        
        $result = $this->dataClass::add($data);
        
        if (!$result->isSuccess()) {
            $errors = implode(', ', $result->getErrorMessages());
            throw new SystemException("Error create value in hlblock: {$errors}");
        }
        
        $id = (int)$result->getId();
        
        $nameKey = mb_strtolower($name);
        $this->values[$nameKey] = $id;
        
        return $id;
    }

    public function reloadValues(): void
    {
        $this->isLoaded = false;
        $this->values = [];
        $this->loadValues();
    }

    public function getAllValues(): array
    {
        $this->loadValues();
        return $this->values;
    }

    public function hasValue(string $name): bool
    {
        $this->loadValues();
        $nameKey = mb_strtolower(trim($name));
        return isset($this->values[$nameKey]);
    }

    public function getHlBlockId(): int
    {
        return $this->hlBlockId;
    }

    public function getNameField(): string
    {
        return $this->nameField;
    }

    private function __clone() {}
    private function __wakeup() {}
}