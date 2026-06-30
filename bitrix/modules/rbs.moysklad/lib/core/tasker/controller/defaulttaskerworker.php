<?php
namespace Rbs\Moysklad\Core\Tasker\Controller;

use Bitrix\Main\Result;
use Bitrix\Main\Error;

use Rbs\Moysklad\Core\Tasker\Entity\TaskerCollection;
use Rbs\Moysklad\Core\Tasker\Entity\TaskerItem;

abstract class DefaultTaskerWorker
{
    /**
     * @var array
     */
    protected $params;
    /**
     * @var bool
     */
    protected $deleteFinalizedTasks = false;
    /**
     * @var TaskerCollection
     */
    protected $taskerCollection;

    public function __construct()
    {
        $this->initializeWorker();
    }

    public function execute(): Result
    {
        try {

            $this->validateProcess();
            $this->prepareParams();

            $result = $this->processTasksQueue();
            $this->finalizeProcess($result);

            return $result;

        } catch (\Throwable $e) {

            $result = new Result();
            $result->addError(new Error('Exception during executing tasks: ' . $e->getMessage()));
            $this->handleProcessException($e, $result);
            return $result;

        }
    }
    
    protected function processTasksQueue(): Result
    {
        $result = new Result();
        
        $this->taskerCollection = TaskerCollection::createForActiveItems(
            $this->getLineId(),
            $this->getAdditionalFilter()
        )->orderBy($this->getOrderBy())->limit($this->getLimit());

        $loadResult = $this->taskerCollection->load();
        if (!$loadResult->isSuccess()) {
            $result->addErrors($loadResult->getErrors());
            return $result;
        }

        if (count($this->taskerCollection) === 0) {
            $result->setData([
                'processed_count' => 0,
                'success_count' => 0,
                'error_count' => 0,
                'message' => 'Empty tasks for processing'
            ]);
            $this->handleEmptyTasks();
            return $result;
        }

        return $this->processTasks();
    }

    protected function processTasks(): Result
    {
        $result = new Result();
        
        foreach ($this->taskerCollection as $task) {

            /** @var TaskerItem $task */
            $startResult = $task->startAction();
            
            if (!$startResult->isSuccess()) {
                $task->stopAction();
                $result->addErrors($startResult->getErrors());
                continue;
            }

            $finishResult = new Result();

            try {
                
                $processingResult = $this->processTask($task);

                $data = $processingResult->getData() ?? null;

                if($processingResult->isSuccess()) {
                    $finishResult = $task->setSuccessTask($data);
                } else {
                    $errorMessage = isset($processingResult->getErrorMessages()[0]) ? $processingResult->getErrorMessages()[0] : 'Unknown error processing';
                    $finishResult = $task->setFailTask($errorMessage);
                }

                if($task->isFinalized() && $this->deleteFinalizedTasks) {
                    $finishResult = $task->delete();
                }
                
            } catch (\Throwable $e) {
                
                $finishResult = $task->setFailTask($e->getMessage());

            } finally {
                if (!$finishResult->isSuccess()) {
                    $result->addErrors($finishResult->getErrors());
                }
            }
            
        }

        return $result;
    }

    abstract protected function initializeWorker(): void;
    abstract protected function validateProcess(): void;
    abstract protected function prepareParams(): void;
    abstract protected function getLineId(): string;
    abstract protected function getLimit(): int;
    abstract protected function getAdditionalFilter(): array;
    abstract protected function getOrderBy(): array;
    abstract protected function processTask(TaskerItem $task): Result;
    abstract protected function finalizeProcess(Result $result): void;
    abstract protected function handleProcessException(\Throwable $e, Result $result): void;
    abstract protected function handleEmptyTasks(): void;
}