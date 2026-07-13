<?php
namespace Rbs\Moysklad\Core\Tasker\Entity;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;

use Rbs\Moysklad\Core\Types\Dictionary;
use Rbs\Moysklad\Core\Tasker\Core\TaskerTable;


class TaskerItem extends Dictionary
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_FAILED = 'FAILED';
    
    /** @var array */
    private const ALLOWED_FIELDS = [
        'LINE_ID', 'TAG', 'DATA', 'RESULT', 'CONFIG', 
        'IS_LOCKED', 'ATTEMPT', 'LAST_ERROR', 'STATUS'
    ];
    
    /** @var array */
    private const READONLY_FIELDS = ['ID', 'CREATED_AT', 'UPDATED_AT'];
    
    public function __construct(array $values = null)
    {
        parent::__construct($values);
    }
    
    public static function create(
        string $lineId,
        array $data,
        string $tag = '',
        array $config = []
    ): Result {
        $result = new Result();
        
        if (empty($lineId)) {
            $result->addError(new Error('LINE_ID cannot be empty'));
            return $result;
        }
        
        if (empty($data)) {
            $result->addError(new Error('DATA cannot be empty'));
            return $result;
        }
        
        $taskData = [
            'LINE_ID' => $lineId,
            'TAG' => $tag,
            'DATA' => $data,
            'CONFIG' => array_merge([
                'max_attempts' => 3,
                'timeout' => 300
            ], $config),
            'STATUS' => self::STATUS_PENDING,
            'IS_LOCKED' => 'N',
            'ATTEMPT' => 0,
            'LAST_ERROR' => '',
            'RESULT' => null,
            'CREATED_AT' => new DateTime(),
            'UPDATED_AT' => new DateTime()
        ];
        
        try {
            $task = new self($taskData);
            
            $saveResult = $task->save();
            if (!$saveResult->isSuccess()) {
                $result->addErrors($saveResult->getErrors());
                return $result;
            }
            
            $result->setData(['task' => $task]);
            
        } catch (\Exception $e) {
            $result->addError(Error::createFromThrowable($e));
        }
        
        return $result;
    }

    public static function createFromDatabase(int $taskId): Result
    {
        $result = new Result();
        
        if ($taskId <= 0) {
            $result->addError(new Error('Invalid task ID'));
            return $result;
        }
        
        try {
            $dbResult = TaskerTable::getByPrimary($taskId);
            $taskData = $dbResult->fetch();
            
            if (!$taskData) {
                $result->addError(new Error("Task with ID {$taskId} not found"));
                return $result;
            }
            
            $task = new self($taskData);
            $result->setData(['task' => $task]);
            
        } catch (\Exception $e) {
            $result->addError(Error::createFromThrowable($e));
        }
        
        return $result;
    }
    
    public function getField(string $fieldName): Result
    {
        $result = new Result();
        
        if (empty($fieldName)) {
            throw new ArgumentNullException('fieldName');
        }
        
        if (!$this->isValidField($fieldName)) {
            $result->addError(new Error("Unknown field: {$fieldName}"));
            return $result;
        }
        
        $value = $this->get($fieldName);
        $result->setData(['value' => $value]);
        
        return $result;
    }
    
   
    public function setField(string $fieldName, $value): Result
    {
        $result = new Result();
        
        if (empty($fieldName)) {
            throw new ArgumentNullException('fieldName');
        }
        
        if (!$this->isValidField($fieldName)) {
            $result->addError(new Error("Unknown field: {$fieldName}"));
            return $result;
        }
        
        if (in_array($fieldName, self::READONLY_FIELDS, true)) {
            $result->addError(new Error("Field {$fieldName} is readonly"));
            return $result;
        }
        
        $validationResult = $this->validateFieldValue($fieldName, $value);
        if (!$validationResult->isSuccess()) {
            $result->addErrors($validationResult->getErrors());
            return $result;
        }
        
        $this->set($fieldName, $value);
        
        return $result;
    }
    
   
    public function startAction(): Result
    {
        $result = new Result();
        
        $status = $this->get('STATUS');
        
        if ($status === self::STATUS_COMPLETED || $status === self::STATUS_FAILED) {
            $result->addError(new Error('Task ID ' . $this->getId() . ' is in final status and cannot be started'));
            return $result;
        }
        
        if ($this->get('IS_LOCKED') === 'Y') {
            $result->addError(new Error('Task ID ' . $this->getId() . ' is already locked'));
            return $result;
        }
        
        $this->set('IS_LOCKED', 'Y');
        $this->set('STATUS', self::STATUS_PROCESSING);
        $this->set('UPDATED_AT', new DateTime());
        
        $saveResult = $this->save();
        if (!$saveResult->isSuccess()) {
            $result->addErrors($saveResult->getErrors());
            return $result;
        }
        
        return $result;
    }

    public function resetAction(): Result
    {
        $result = new Result();

        $this->set('IS_LOCKED', 'N');
        $this->set('STATUS', self::STATUS_PENDING);
        $this->set('ATTEMPT', 0);
        $this->set('LAST_ERROR', '');
        $this->set('RESULT', null);
        $this->set('UPDATED_AT', new DateTime());
        
        $saveResult = $this->save();
        if (!$saveResult->isSuccess()) {
            $result->addErrors($saveResult->getErrors());
            return $result;
        }
        
        return $result;
    }
    
   
    public function stopAction(): Result
    {
        $result = new Result();
        
        if (!$this->isLocked()) {
            return $result;
        }
        
        $this->set('IS_LOCKED', 'N');
        $this->set('UPDATED_AT', new DateTime());
        
        $saveResult = $this->save();
        if (!$saveResult->isSuccess()) {
            $result->addErrors($saveResult->getErrors());
            return $result;
        }
        
        return $result;
    }
    
   
    public function setSuccessTask($resultData = null): Result
    {
        $result = new Result();
        
        $this->set('STATUS', self::STATUS_COMPLETED);
        
        if ($resultData !== null) {
            $this->set('RESULT', $resultData);
        }
        
        $this->set('UPDATED_AT', new DateTime());
        $this->set('IS_LOCKED', 'N');
        
        $saveResult = $this->save();
        if (!$saveResult->isSuccess()) {
            $result->addErrors($saveResult->getErrors());
            return $result;
        }
        
        return $result;
    }
    
   
    public function setFailTask(string $errorMessage, ?int $maxAttempts = null): Result
    {
        $result = new Result();
        
        if (empty($errorMessage)) {
            throw new ArgumentNullException('errorMessage');
        }
        
        if ($maxAttempts === null) {
            $config = $this->get('CONFIG');
            $maxAttempts = $config['max_attempts'] ?? 3;
        }
        
        if ($maxAttempts <= 0) {
            throw new ArgumentOutOfRangeException('maxAttempts', 1, null);
        }
        

        $currentAttempt = (int)$this->get('ATTEMPT') + 1;
        $this->set('ATTEMPT', $currentAttempt);
        $this->set('LAST_ERROR', $errorMessage);
        $this->set('UPDATED_AT', new DateTime());
        
        if ($currentAttempt >= $maxAttempts) {
            $this->set('STATUS', self::STATUS_FAILED);
        } else {
            $this->set('STATUS', self::STATUS_PENDING);
        }
        
        $this->set('IS_LOCKED', 'N');
        
        $saveResult = $this->save();
        if (!$saveResult->isSuccess()) {
            $result->addErrors($saveResult->getErrors());
            return $result;
        }
        
        return $result;
    }
    
   
    public function isFinalized(): bool
    {
        $status = $this->get('STATUS');
        return $status === self::STATUS_COMPLETED || $status === self::STATUS_FAILED;
    }
    
   
    public function isLocked(): bool
    {
        return $this->get('IS_LOCKED') === 'Y';
    }
    
   
    public function getId(): ?int
    {
        $id = $this->get('ID');
        return $id ? (int)$id : null;
    }

    public function delete(): Result
    {
        $result = new Result();
        $id = $this->getId();
        if ($id > 0) {
            $result = TaskerTable::delete($id);
            if (!$result->isSuccess()) {
                $result->addErrors($result->getErrors());
            }
        }
        return $result;
    }
    
   
    public function save(): Result
    {
        $result = new Result();
        
        try {
            $id = $this->getId();
            
            if ($id) {
                $dbResult = TaskerTable::update($id, $this->getValues());
            } else {
                $dbResult = TaskerTable::add($this->getValues());
                if ($dbResult->isSuccess()) {
                    $this->set('ID', $dbResult->getId());
                }
            }
            
            if (!$dbResult->isSuccess()) {
                $result->addErrors($dbResult->getErrors());
            }
            
        } catch (\Exception $e) {
            $result->addError(Error::createFromThrowable($e));
        }
        
        return $result;
    }
    
   
    private function isValidField(string $fieldName): bool
    {
        return in_array($fieldName, array_merge(self::ALLOWED_FIELDS, self::READONLY_FIELDS), true);
    }
    
    
    private function validateFieldValue(string $fieldName, $value): Result
    {
        $result = new Result();
        
        switch ($fieldName) {
            case 'STATUS':
                $allowedStatuses = [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_COMPLETED, self::STATUS_FAILED];
                if (!in_array($value, $allowedStatuses, true)) {
                    $result->addError(new Error("Invalid status value: {$value}"));
                }
                break;
                
            case 'IS_LOCKED':
                if (!is_bool($value) && $value !== 'Y' && $value !== 'N') {
                    $result->addError(new Error("IS_LOCKED must be boolean or Y/N"));
                }
                break;
                
            case 'ATTEMPT':
                if (!is_numeric($value) || (int)$value < 0) {
                    $result->addError(new Error("ATTEMPT must be non-negative integer"));
                }
                break;
                
            case 'LINE_ID':
                if (empty($value)) {
                    $result->addError(new Error("LINE_ID cannot be empty"));
                }
                break;
        }
        
        return $result;
    }
}
