<?php
namespace Rbs\Moysklad\Core\Tasker\Core;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;

class TaskerRepository
{
    private static $instances = [];
    private $lineId;
    
    private function __construct(string $lineId)
    {
        $this->lineId = $lineId;
    }
    
    public static function getInstance(string $lineId = 'DEFAULT'): self
    {
        if (!isset(self::$instances[$lineId])) {
            self::$instances[$lineId] = new self($lineId);
        }
        
        return self::$instances[$lineId];
    }
    private function __clone() {}
    public function __wakeup() {throw new NotSupportedException("Cannot unserialize singleton");}
    
    public function addTask(array $data, array $config = [], string $tag = null): int
    {
        if (empty($data)) {
            throw new ArgumentNullException('data');
        }
        
        if (empty($config)) {
            throw new ArgumentNullException('config');
        }
        
        $fields = [
            'LINE_ID' => $this->lineId,
            'DATA' => $data,
            'CONFIG' => $config,
            'STATUS' => 'PENDING',
            'IS_LOCKED' => false,
            'ATTEMPT' => 0,
            'CREATED_AT' => new DateTime(),
            'UPDATED_AT' => new DateTime(),
        ];
        
        if ($tag !== null) {
            $fields['TAG'] = $tag;
        }
        
        $result = TaskerTable::add($fields);
        
        if (!$result->isSuccess()) {
            throw new ObjectException('Failed to add task: ' . implode(', ', $result->getErrorMessages()));
        }
        
        return $result->getId();
    }
    
    public function updateTask(int $taskId, array $fields): bool
    {
        $task = TaskerTable::getByPrimary($taskId)->fetchObject();
        if (!$task) {
            throw new ObjectNotFoundException("Task with ID {$taskId} not found");
        }
        
        if ($task->getLineId() !== $this->lineId) {
            throw new InvalidOperationException('Task does not belong to this line');
        }
        
        $fields['UPDATED_AT'] = new DateTime();
        
        $result = TaskerTable::update($taskId, $fields);
        
        if (!$result->isSuccess()) {
            throw new ObjectException('Failed to update task: ' . implode(', ', $result->getErrorMessages()));
        }
        
        return true;
    }
    
    public function deleteTask(int $taskId): bool
    {
        $task = TaskerTable::getByPrimary($taskId)->fetchObject();
        if (!$task) {
            throw new ObjectNotFoundException("Task with ID {$taskId} not found");
        }
        
        if ($task->getLineId() !== $this->lineId) {
            throw new InvalidOperationException('Task does not belong to this line');
        }
        
        if ($task->getIsLocked()) {
            throw new InvalidOperationException('Cannot delete locked task');
        }
        
        $result = TaskerTable::delete($taskId);
        
        if (!$result->isSuccess()) {
            throw new ObjectException('Failed to delete task: ' . implode(', ', $result->getErrorMessages()));
        }
        
        return true;
    }
    
    public function getList(array $filter = [], array $order = [], int $limit = null, int $offset = null): array
    {
        $filter['LINE_ID'] = $this->lineId;
        
        $parameters = [
            'filter' => $filter,
            'order' => $order ?: ['ID' => 'ASC'],
        ];
        
        if ($limit !== null) {
            $parameters['limit'] = $limit;
        }
        
        if ($offset !== null) {
            $parameters['offset'] = $offset;
        }
        
        $result = TaskerTable::getList($parameters);
        
        $tasks = [];
        while ($task = $result->fetch()) {
            $tasks[] = $task;
        }
        
        return $tasks;
    }
    
    public function getCount(array $filter = []): int
    {
        $filter['LINE_ID'] = $this->lineId;
        
        $result = TaskerTable::getList([
            'filter' => $filter,
            'select' => ['CNT'],
            'runtime' => [
                new ExpressionField('CNT', 'COUNT(*)')
            ]
        ]);
        
        $row = $result->fetch();
        
        return (int)($row['CNT'] ?? 0);
    }
    
    public function getLineId(): string
    {
        return $this->lineId;
    }
}