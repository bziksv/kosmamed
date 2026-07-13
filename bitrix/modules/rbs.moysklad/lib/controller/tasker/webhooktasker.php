<?php
namespace Rbs\MoySklad\Controller\Tasker;

use Rbs\Moysklad\Core\Tasker\Entity\TaskerCollection;

class WebhookTasker
{
    private static $instances = [];
    private $line_id = '';
    
    private function __construct(int $profileId = 0)
    {
        $this->line_id = 'webhook_line_' . $profileId;
    }

    public static function getInstance(int $profileId = 0): self
    {
        if(!isset(self::$instances[$profileId])) {
            self::$instances[$profileId] = new self($profileId);
        }
        return self::$instances[$profileId];
    }

    public function addTask($eventHook = false): bool
    {
        if (!$eventHook || !is_object($eventHook)) {
            return false;
        }

        $collection = new TaskerCollection($this->line_id);
            
        $taskData = [
            'event_hook' => $eventHook
        ];

        $tag = sprintf('%s_%s', $eventHook->meta->type ?? 'unknown', $eventHook->action ?? 'unknown');
        
        $config = [
            'max_attempts' => 2
        ];

        $result = $collection->addTask($taskData, $tag, $config);
        
        return $result->isSuccess();
        
    }
}