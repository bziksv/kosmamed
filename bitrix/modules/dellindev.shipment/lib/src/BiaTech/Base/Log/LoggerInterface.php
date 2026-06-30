<?php

/**
 * Логгер интерфейс по PSR-3. Подробнее рекомендую ознакомиться со спецификацией RFC 2119.
 * Комментарии к методам указаны приблительно, согласно правилам разработки первой итерации обновлённой версии.
 * В дальнейшем при развитии проекта SDK, возможно приведения в комментариях конкретных кейсов.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */


namespace BiaTech\Base\Log;


interface LoggerInterface
{



    /**
     * Требуется немедленные действия
     *
     * Например: вебсайт отключен, БД недоступна, и т.д.
     * Такой лог должен вызывать СМС оповещение и побуждать Вас к немедленным действиям.
     *
     * @param string $message
     * @param array $context
     * @return void
     */

    public function alert($message, array $context = array());

    /**
     * Критическая ситуация.
     *
     * Пример: недоступен компонент приложения, неожиданный Exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */

    public function critical($message, array $context = array());

    /**
     * Ошибка на стадии выполнения, не требующая немедленных действий,
     * но требующая быть залогированной и дальнейшего изучения.
     *
     * @param string $message
     * @param array $context
     * @return void
     */


    public function error($message, array $context = array());


    /**
     * Полезные значимые события.
     *
     * Пример: логи пользователей, логи SQL.
     *
     * @param string $message
     * @param array $context
     * @return void
     */

    public function info($message, array $context = array());

    /**
     * Подробная отладочная информация.
     *
     * @param string $message
     * @param array $context
     * @return void
     */

    public function debug($message, array $context = array());

    /**
     * Логи с произвольным уровнем.
     * В переменную $log передается нужный уровень логирования.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    // TODO определиться с его необходимостью
    public function log($level, $message, array $context = array());


}