<?php
namespace Rbs\Moysklad\Controller\Tasker;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

use Rbs\Moysklad\Core\Tasker\Controller\DefaultTaskerWorker;
use Rbs\Moysklad\Core\Tasker\Entity\TaskerItem;
use Rbs\Moysklad\Core\Tasker\Core\TaskerTable;

use Rbs\Moysklad\Config;
use Rbs\Moysklad\Debug\Loger;
use Rbs\Moysklad\Webhook;
use Rbs\Moysklad\LangMsg;

class WebhookTaskerWorker extends DefaultTaskerWorker
{
    /**
     * @var int
     */
    protected $profileId;
    /**
     * @var Loger
     */
    protected $logger;
    /**
     * @var string
     */
    protected $lineId;
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var bool
     */
    protected $isEmptyTasks = false;

    public function __construct(int $profileId = 0)
    {
        $this->profileId = $profileId;
        parent::__construct();
    }

    protected function initializeWorker(): void
    {
        $this->logger = new Loger();
        $this->lineId = 'webhook_line_' . $this->profileId;
        $this->limit = Config::getOption('webhook_limit_tasker', 20);
        //$this->limit = 5;
        $this->deleteFinalizedTasks = true;
    }

    protected function validateProcess(): void
    {
        if ($this->profileId < 0) {
            throw new \Bitrix\Main\SystemException(LangMsg::get('WEBHOOK_TASKER_WORKER_INVALID_PROFILE_ID'));
        }        
        if (empty($this->lineId)) {
            throw new \Bitrix\Main\SystemException(LangMsg::get('WEBHOOK_TASKER_WORKER_LINE_ID_EMPTY'));
        }
    }

    protected function prepareParams(): void
    {
        $this->params = [
            'line_id' => $this->lineId,
            'limit' => $this->limit
        ];
    }

    protected function getLineId(): string
    {
        return $this->lineId;
    }

    protected function getLimit(): int
    {
        return $this->limit;
    }

    protected function getAdditionalFilter(): array
    {
        return [];
    }

    protected function getOrderBy(): array
    {
        return ['CREATED_AT' => 'ASC'];
    }

    /**
     * @var array
     */
    private $executedEntities = [];

    protected function processTask(TaskerItem $task): Result
    {
        $result = new Result();

        $taskData = $task->get('DATA');
        
        if (!isset($taskData['event_hook']) || !isset($taskData['event_hook']->meta->href)) {
            $result->setData([
                'message' => LangMsg::get('WEBHOOK_TASKER_WORKER_EMPTY_EVENT_HOOK')
            ]);
            $this->logger->addWarningMessage(LangMsg::get('WEBHOOK_TASKER_WORKER_EMPTY_EVENT_HOOK'));
            return $result;
        }

        if(isset($this->executedEntities[$taskData['event_hook']->meta->href])){
            $result->setData([
                'message' => LangMsg::get('WEBHOOK_TASKER_WORKER_ENTITY_ALREADY_EXECUTED', ['#HREF#' => $taskData['event_hook']->meta->href])
            ]);
            $this->logger->addWarningMessage(LangMsg::get('WEBHOOK_TASKER_WORKER_ENTITY_ALREADY_EXECUTED', ['#HREF#' => $taskData['event_hook']->meta->href]));
            return $result;
        }

        $this->executedEntities[$taskData['event_hook']->meta->href] = true;
        
        try {
            Webhook::processHookItemArray([$taskData['event_hook']], true);
            $result->setData([
                'message' => LangMsg::get('WEBHOOK_TASKER_WORKER_ENTITY_PROCESSED', ['#HREF#' => $taskData['event_hook']->meta->href])
            ]);
            $this->logger->addSuccessMessage(LangMsg::get('WEBHOOK_TASKER_WORKER_ENTITY_PROCESSED', ['#HREF#' => $taskData['event_hook']->meta->href]));
        } catch (\Throwable $e) {
            $result->addError(new Error($e->getMessage()));
            $this->logger->addErrorMessage(LangMsg::get('WEBHOOK_TASKER_WORKER_ENTITY_PROCESSED_WITH_ERROR', ['#HREF#' => $taskData['event_hook']->meta->href, '#ERROR#' => $e->getMessage()]));
        }
        
        return $result;
    }

    protected function finalizeProcess(Result $processResult): void
    {
        if (!$processResult->isSuccess()) {
            $this->logger->addErrorMessage(LangMsg::get('WEBHOOK_TASKER_WORKER_QUEUE_ERRORS'));
            foreach ($processResult->getErrors() as $error) {
                $this->logger->addErrorMessage($error->getMessage());
            }
        }

        if (!$this->isEmptyTasks) {
            $this->finishLoggerProcess();
        }        
    }

    protected function handleEmptyTasks(): void
    {
        $this->isEmptyTasks = true;
    }

    protected function handleProcessException(\Throwable $e, Result $result): void
    {
        $this->logger->addErrorMessage(LangMsg::get('WEBHOOK_TASKER_WORKER_CRITICAL_ERROR', ['#ERROR#' => $e->getMessage()]));
        $this->finishLoggerProcess();
    }

    public function getLogger(): Loger
    {
        return $this->logger;
    }

    private function finishLoggerProcess(): void
    {
        $this->logger->addFinishMessage(LangMsg::get('WEBHOOK_TASKER_WORKER_PROCESSING_TIME', ['#TIME#' => $this->logger->getLogTime()]));
        $this->logger->exportLog(LangMsg::get('WEBHOOK_TASKER_WORKER_PROCESSING_TITLE'));
    }

    /**Utility method to reset the tasker index */
    public static function resetTaskerIndex(): void
    {
        $currentDate = new \DateTime();
        $lastDateUpdate = Config::getOption('reset_tasker_index_date_update', '');
        if(!empty($lastDateUpdate)) {
            $lastDateUpdate = new \DateTime($lastDateUpdate);
        }
        $canExecuteByDate = empty($lastDateUpdate) || ($currentDate->getTimestamp() - $lastDateUpdate->getTimestamp()) > 86400 * 3;

        if(TaskerTable::getCount() == 0 && !empty(TaskerTable::getTableName()) && $canExecuteByDate) {
            $logger = new Loger();
            $logger->addInfoMessage(LangMsg::get('AGENT_RESET_TASKER_INDEX_START'));
            try {
                $connection = \Bitrix\Main\Application::getConnection();
                $connection->truncateTable(TaskerTable::getTableName());
                $logger->addSuccessMessage(LangMsg::get('AGENT_RESET_TASKER_INDEX_SUCCESS'));
                Config::setOption('reset_tasker_index_date_update', $currentDate->format('Y-m-d H:i:s'));
            } catch (\Throwable $e) {
                $logger->addErrorMessage(LangMsg::get('AGENT_RESET_TASKER_INDEX_ERROR', ['#ERROR#' => $e->getMessage()]));
            } finally {
                $logger->exportLog(LangMsg::get('AGENT_RESET_TASKER_INDEX_END'));
            }
        }
    }
}