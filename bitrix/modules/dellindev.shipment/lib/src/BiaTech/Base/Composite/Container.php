<?php
declare(strict_types=1);
/**
 * Компоновщик массивов.
 * Объектно-ориентированное представление работы с массивами.
 * Этот объект собирает/добавляет в стек поля объявленные в сущности Field или другие
 * сущности имплементированные от интерфейса CompositeInterface.
 * Текущая сущность может быть использованна как значение передаваемое в сущность Field.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */

namespace BiaTech\Base\Composite;


class Container implements CompositeInterface
{
    /**
     * Стек полей.
     * @var array
     */
    private array $fields = [];


    /**
     * Метод добавляющий в тело контейнера объекты имплементированные от интерфейса
     * CompositeInterface.
     * @param CompositeInterface $field
     */
    public function add(CompositeInterface $field ){
        $init = $this->fields;
        $this->fields = array_merge($init, $field->toArray());
      //  $this->fields[] = $field->toArray();

    }


    /**
     * Обязательный метод приводящий значения из объекта к массиву.
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this->fields;
    }
}


