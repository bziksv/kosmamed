<?php
namespace Rbs\Moysklad\Diagnostic\Dashboard\Core;

abstract class BaseItem implements ItemInterface
{
    protected const STATUS_ERROR = 'error';
    protected const STATUS_WARNING = 'warning';
    protected const STATUS_SUCCESS = 'success';

    protected const CARD_TYPE_DEFAULT = 'default';
    protected const CARD_TYPE_LIST = 'list';

    protected $key;
    protected $group;
    protected $title;
    protected $description;

    protected $value = false;
    protected $status = self::STATUS_ERROR;
    protected $valueDescription = '';
    protected $recommendations = '';

    public function __construct()
    {
        $classPath = static::class;
        $parts = explode('\\', $classPath);
        $this->key = mb_strtoupper(end($parts));
        
        $itemsIndex = array_search('Items', $parts);
        if ($itemsIndex !== false && isset($parts[$itemsIndex + 1])) {
            $this->group = mb_strtoupper($parts[$itemsIndex + 1]);
        } else {
            $this->group = '';
        }

        $this->setTitle();
        $this->setDescription();

        $this->executeItemCheck();
    }

    protected abstract function setTitle(): void;
    protected abstract function setDescription(): void;
    protected abstract function executeItemCheck(): void;
    
    public function getCardType(): string
    {
        return self::CARD_TYPE_DEFAULT;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getGroup(): string
    {
        return $this->group;
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function getValue()
    {
        return $this->value;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getValueDescription(): string
    {
        return $this->valueDescription;
    }

    public function getRecommendations(): string
    {
        return $this->recommendations;
    }
    
} 
