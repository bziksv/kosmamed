<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Package
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Package extends AbstractEntity
{
    /**
     * @var string
     */
    protected $number;

    /**
     * @var integer
     */
    protected $weight;

    /**
     * @var integer|null
     */
    protected $length;

    /**
     * @var integer|null
     */
    protected $width;

    /**
     * @var integer|null
     */
    protected $height;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var ItemList|null
     */
    protected $items;

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return Package
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return Package
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int|null $length
     * @return Package
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     * @return Package
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     * @return Package
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return Package
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return ItemList|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return Package
     * @throws BadResponseException
     */
    public function setItems($items)
    {
        $collection = new ItemList();
        $this->items = $collection->fillFromArray($items);
        return $this;
    }

    /**
     * @param ItemList|null $items
     * @return Package
     */
    public function setItemsFromObject($items)
    {
        $this->items = $items;
        return $this;
    }
}