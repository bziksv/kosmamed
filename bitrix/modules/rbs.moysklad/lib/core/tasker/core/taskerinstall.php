<?php
namespace Rbs\Moysklad\Core\Tasker\Core;

use Bitrix\Main\ORM\Entity;

class TaskerInstall
{
    private const TASKER_TABLE_PATH = __DIR__ . '/taskertable.php';
    private const TASKER_TABLE_CLASS = 'Rbs\Moysklad\Core\Tasker\Core\TaskerTable';

    public static function install(): bool
    {
        try {
            if (!file_exists(self::TASKER_TABLE_PATH)) {
                throw new \Exception('File TaskerTable not found: ' . self::TASKER_TABLE_PATH);
            }

            require_once self::TASKER_TABLE_PATH;

            if (!class_exists(self::TASKER_TABLE_CLASS)) {
                throw new \Exception('Class TaskerTable not found: ' . self::TASKER_TABLE_CLASS);
            }

            $entity = Entity::getInstance(self::TASKER_TABLE_CLASS);
            $entity->createDbTable();

            return true;

        } catch (\Exception $e) {
            throw new \Exception('Error installing TaskerTable: ' . $e->getMessage());
        }
    }

    public static function uninstall(): bool
    {
        try {
            if (!file_exists(self::TASKER_TABLE_PATH)) {
                throw new \Exception('File TaskerTable not found: ' . self::TASKER_TABLE_PATH);
            }

            require_once self::TASKER_TABLE_PATH;

            if (!class_exists(self::TASKER_TABLE_CLASS)) {
                throw new \Exception('Class TaskerTable not found: ' . self::TASKER_TABLE_CLASS);
            }

            $connection = \Bitrix\Main\Application::getConnection();
            if($connection->isTableExists(TaskerTable::getTableName())) {
                $connection->dropTable(TaskerTable::getTableName());
            }

            return true;

        } catch (\Exception $e) {
            throw new \Exception('Error deleting TaskerTable: ' . $e->getMessage());
        }
    }

    public static function reinstall(): bool
    {
        try {
            self::uninstall();
            
            self::install();

            return true;

        } catch (\Exception $e) {
            throw new \Exception('Error reinstalling TaskerTable: ' . $e->getMessage());
        }
    }

    public static function isInstalled(): bool
    {
        try {
            if (!file_exists(self::TASKER_TABLE_PATH)) {
                return false;
            }

            require_once self::TASKER_TABLE_PATH;

            if (!class_exists(self::TASKER_TABLE_CLASS)) {
                return false;
            }

            $tableName = TaskerTable::getTableName();
            $connection = \Bitrix\Main\Application::getConnection();
            return $connection->isTableExists($tableName);

        } catch (\Exception $e) {
            return false;
        }
    }
}