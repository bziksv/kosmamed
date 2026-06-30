<?php
namespace Ipolh\SDEK\Core\Entity\Result;

use Ipolh\SDEK\Core\Entity\Collection;

/**
 * Class InfoCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 */
class InfoCollection extends Collection implements \Countable
{
    /**
     * Returns true if collection is empty
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->{$this->field});
    }

    /**
     * Returns count of collection elements
     * @return int
     */
    public function count()
    {
        return count($this->{$this->field});
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->{$this->field};
    }

    /**
     * Adds elements from given collection to this one
     * @param InfoCollection $collection
     * @return $this
     */
    public function append($collection)
    {
        // TODO: Implement collection element class check

        $collection->reset();
        while ($item = $collection->getNext()) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * Returns array of strings with Info messages
     * @return string[]
     */
    public function getMessages()
    {
        $messages = [];

        $this->reset();
        while ($info = $this->getNext()) {
            /** @var $info Info */
            $messages[] = strval($info);
        }

        return $messages;
    }
}