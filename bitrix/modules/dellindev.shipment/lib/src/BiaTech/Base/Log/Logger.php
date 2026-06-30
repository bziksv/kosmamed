<?php

/**
 * Кастомная реализация логгирования по PSR-3 .
 * Методы специально сделаны примитивно и вводятся в ядро через интерфейс, для возможности переопределения.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */

namespace BiaTech\Base\Log;


use BiaTech\Base\Log\LoggerInterface;
use DellinShipping\Entity\Config;


class Logger implements LoggerInterface
{

    private bool $isDebug;
    private bool $isWarning;
    private $pathForLogs = '/bitrix/modules/dellindev.shipment/logs/';

    /**
     * Logger constructor.
     */
    public function __construct(Config $config)
    {
        $this->setIsDebug($config->isDebug());
        $this->setIsWarning($config->isWarning());
    }

    /**
     * @return mixed
     */
    public function isDebug()
    {
        return $this->isDebug;
    }

    /**
     * @param mixed $isDebug
     */
    public function setIsDebug($isDebug): void
    {
        $this->isDebug = $isDebug;
    }

    /**
     * @return mixed
     */
    public function isWarning()
    {
        return $this->isWarning;
    }

    /**
     * @param mixed $isWarning
     */
    public function setIsWarning($isWarning): void
    {
        $this->isWarning = $isWarning;
    }

    /**
     * @return string
     */
    public function getPathForLogs(): string
    {
        return $this->pathForLogs;
    }

    /**
     * @param string $pathForLogs
     */
    public function setPathForLogs(string $pathForLogs): void
    {
        $this->pathForLogs = $pathForLogs;
    }


    /**
     * Метод для преобразования контекста в строку для записи в файл.
     * @param $message
     * @param array $context
     * @return string
     */
   private function interpolate(string $message, array $context = array())
    {
        // Построение массива подстановки с фигурными скобками
        // вокруг значений ключей массива context.
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // Подстановка значений в сообщение и возврат результата.
        return strtr($message, $replace);
    }


    /**
     * Метод записи алерта в логи. Используется в следующих случаях:
     * - Не корректная версия интерпритатора PHP;
     * - Отсутствие зависимости  curl у php;
     * - данные не прошли валидацию.
     * @inheritDoc
     */
    public function alert($message, array $context = array())
    {
        $typeWriteLog = 'ALERT';
        $fileName = 'ALERT_log_'.date("Y-m-d").'.log';
        $data = $this->builderDataForLog($typeWriteLog, $message, $context);
        $this->writeMessage($fileName, $data);
    }

    /**
     * Метод записи критических ошибок:
     * - Неизвестное промежуточное значение в сущности;
     * - Отсутствие возможности получить параметры вовсе;
     * @inheritDoc
     */
    public function critical($message, array $context = array())
    {
        $typeWriteLog = 'CRITICAL';
        $fileName = 'critical_log_'.date("Y-m-d").'.log';
        $data = $this->builderDataForLog($typeWriteLog, $message, $context);
        $this->writeMessage($fileName, $data);
    }

    /**
     * Метод логирования обычных ошибок:
     * - Параметр передаваемый в сущность не прошёл валидацию;
     * - Параметры от ответа от апи содержат ошибки;
     * @inheritDoc
     */
    public function error($message, array $context = array())
    {

        $typeWriteLog = 'ERROR';
        $fileName = 'error_log_'.date("Y-m-d").'.log';
        $data = $this->builderDataForLog($typeWriteLog, $message, $context);
        $this->writeMessage($fileName, $data);

    }

    private function writeMessage($fileName, $data)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].$this->pathForLogs.$fileName,
            $data, FILE_APPEND);
    }

    /**
     * Метод работы с инфовставками:
     * - проведённый успешно расчёт стоимости;
     * - проведённая успешно заявка;
     * @inheritDoc
     */
    public function info($message, array $context = array())
    {
        $typeWriteLog = 'INFO';
        $fileName = 'info_log_'.date("Y-m-d").'.log';
        $data = $this->builderDataForLog($typeWriteLog, $message, $context);
        $this->writeMessage($fileName, $data);
    }

    /**
     * Метод описывающий отладочную подробную информацию на всех этапах.
     * @inheritDoc
     */
    public function debug($message, array $context = array())
    {

        $typeWriteLog = 'DEBUG';
        $fileName = 'debug_log_'.date("Y-m-d").'.log';
        $data = $this->builderDataForLog($typeWriteLog, $message, $context);

        if($this->isDebug()){

           $this->writeMessage($fileName, $data);

        }
    }

    private function builderDataForLog($typeWriteLog, $message, $context)
    {

        $dateNow = date("Y-m-d H:i:s");
        $data = '=================================|START|================================'.PHP_EOL;
        $data.= '==Type write:|'.$typeWriteLog.'|==Date :|'.$dateNow.'|==Body write:'.PHP_EOL.
            $this->interpolate($message, $context);
        $data.=  '=================================|END|================================'.PHP_EOL;

        return $data;
    }

    /**
     * Логи с кастомным уровнем. Врядли пригодится, сделаем на будущее.
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {

        $fileName = $level.'_log_'.date("Y-m-d").'.log';
        $data = $this->builderDataForLog($level, $message, $context);
        $this->writeMessage($fileName, $data);

    }
}