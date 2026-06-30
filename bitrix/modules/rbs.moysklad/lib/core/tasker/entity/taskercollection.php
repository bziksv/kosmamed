<?php
namespace Rbs\Moysklad\Core\Tasker\Entity;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;

use Bitrix\Main\Result;
use Bitrix\Main\Error;

use Rbs\Moysklad\Core\Tasker\Core\TaskerRepository;

class TaskerCollection implements \Iterator, \Countable
{
    /** @var string */
    private $lineId;
    
    /** @var array */
    private $filter;
    
    /** @var array */
    private $order;
    
    /** @var int|null */
    private $limit;
    
    /** @var int|null */
    private $offset;
    
    /** @var TaskerItem[] */
    private $tasks = [];
    
    /** @var bool */
    private $isLoaded = false;
    
    /** @var int */
    private $position = 0;
    
    /** @var TaskerRepository */
    private $repository;
    
    public function __construct(
        string $lineId = 'DEFAULT', 
        array $filter = [], 
        array $order = ['ID' => 'ASC'], 
        ?int $limit = null, 
        ?int $offset = null
    ) {
        if (empty($lineId)) {
            throw new ArgumentNullException('lineId');
        }
        
        $this->lineId = $lineId;
        $this->filter = $filter;
        $this->order = $order;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->repository = TaskerRepository::getInstance($lineId);
    }
    
    public static function createForActiveItems(string $lineId = 'DEFAULT', array $additionalFilter = []): self
    {
        $filter = array_merge([
            '!STATUS' => [TaskerItem::STATUS_COMPLETED, TaskerItem::STATUS_FAILED],
            'IS_LOCKED' => false
        ], $additionalFilter);
        
        return new self($lineId, $filter);
    }

    public static function createByTag(string $lineId = 'DEFAULT', string $tag = '', array $additionalFilter = []): self
    {
        $filter = array_merge(['TAG' => $tag], $additionalFilter);
        
        return new self($lineId, $filter);
    }
   
    public static function createWithTask(
        string $lineId = 'DEFAULT',
        array $data = [],
        string $tag = '',
        array $config = []
    ): Result {
        $result = new Result();
        
        try {
            $collection = new self($lineId);
            
            $addResult = $collection->addTask($data, $tag, $config);
            
            if (!$addResult->isSuccess()) {
                $result->addErrors($addResult->getErrors());
                return $result;
            }
            
            $result->setData([
                'collection' => $collection,
                'task' => $addResult->getData()['task']
            ]);
            
        } catch (\Exception $e) {
            $result->addError(Error::createFromThrowable($e));
        }
        
        return $result;
    }

    public function count(): int
    {
        return $this->repository->getCount($this->filter);
    }

    public function load(): Result
    {
        $result = new Result();
        
        try {
            $tasks = $this->repository->getList(
                $this->filter, 
                $this->order, 
                $this->limit, 
                $this->offset
            );
            
            $this->tasks = [];
            foreach ($tasks as $taskData) {
                $this->tasks[] = new TaskerItem($taskData);
            }
            
            $this->isLoaded = true;
            $this->position = 0;
            
        } catch (\Exception $e) {
            $result->addError(Error::createFromThrowable($e));
        }
        
        return $result;
    }

    public function reload(): Result
    {
        $this->isLoaded = false;
        return $this->load();
    }

    public function filter(array $additionalFilter): self
    {
        $newFilter = array_merge($this->filter, $additionalFilter);
        
        return new self(
            $this->lineId,
            $newFilter,
            $this->order,
            $this->limit,
            $this->offset
        );
    }

    public function limit(int $limit, ?int $offset = null): self
    {
        if ($limit <= 0) {
            throw new ArgumentOutOfRangeException('limit', 1, null);
        }
        
        return new self(
            $this->lineId,
            $this->filter,
            $this->order,
            $limit,
            $offset ?? $this->offset
        );
    }

    public function orderBy(array $order): self
    {
        return new self(
            $this->lineId,
            $this->filter,
            $order,
            $this->limit,
            $this->offset
        );
    }

    public function getLineId(): string
    {
        return $this->lineId;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }
    
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        
        return $this->tasks[$this->position] ?? null;
    }
    
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }
    
    public function next(): void
    {
        $this->position++;
    }
    
    public function rewind(): void
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        
        $this->position = 0;
    }
    
    public function valid(): bool
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        
        return isset($this->tasks[$this->position]);
    }

    public function addTask(array $data, string $tag = '', array $config = []): Result
    {
        $result = new Result();
        
        try {
            $createResult = TaskerItem::create($this->lineId, $data, $tag, $config);
            
            if (!$createResult->isSuccess()) {
                $result->addErrors($createResult->getErrors());
                return $result;
            }
            
            /** @var TaskerItem $task */
            $task = $createResult->getData()['task'];
            
            if ($this->isLoaded) {
                $this->tasks[] = $task;
            }
            
            $result->setData(['task' => $task]);
            
        } catch (\Exception $e) {
            $result->addError(Error::createFromThrowable($e));
        }
        
        return $result;
    }

    public function addTasks(array $tasksData): Result
    {
        $result = new Result();
        
        if (empty($tasksData)) {
            $result->addError(new Error('Tasks data cannot be empty'));
            return $result;
        }
        
        $addedTasks = [];
        $errors = [];
        
        foreach ($tasksData as $index => $taskData) {
            if (!isset($taskData['data']) || !is_array($taskData['data'])) {
                $errors[] = new Error("Task #{$index}: DATA is required and must be array");
                continue;
            }
            
            $tag = $taskData['tag'] ?? '';
            $config = $taskData['config'] ?? [];
            
            try {
                $addResult = $this->addTask($taskData['data'], $tag, $config);
                
                if ($addResult->isSuccess()) {
                    $addedTasks[] = $addResult->getData()['task'];
                } else {
                    $addResult->addErrors($addResult->getErrors());
                }
                
            } catch (\Exception $e) {
                $errors[] = new Error("Task #{$index}: " . $e->getMessage());
            }
        }
        
        if (!empty($errors)) {
            $result->addErrors($errors);
        }
        
        $result->setData([
            'added_tasks' => $addedTasks,
            'added_count' => count($addedTasks),
            'total_count' => count($tasksData)
        ]);
        
        return $result;
    }
}
