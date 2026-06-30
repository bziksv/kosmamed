<?php
namespace Rbs\Moysklad;

class Notification
{
    private $mess = '';
    private $mail = '';
    private $mailFrom = '';
    private $isMailCorrect = false;
    private $errorCode = -1;

    function __construct($message = '', $errorCode = -1)
    {
        $this->mess = $message;
        $this->mail = Config::getEmailNotification();
        $this->mailFrom = Config::getEmailNotificationFrom();
        $this->isMailCorrect = filter_var($this->mail, FILTER_VALIDATE_EMAIL) && filter_var($this->mailFrom, FILTER_VALIDATE_EMAIL);
        $this->errorCode = (int)$errorCode;
    }

    function checkNotifications()
    {
        if(!$this->isDelayNotificationByErrorCode()){
            $this->checkEmailNotification();
            $this->checkAdminNotification();
        }
    }

    private function isDelayNotificationByErrorCode()
    {
        $needDelay = false;

        if ($this->errorCode >= 0) {
            
            $errorsForTimeNotify = Config::getErrorsCodeForTimeNotify();
            if(count($errorsForTimeNotify) > 0){
                if(in_array($this->errorCode, $errorsForTimeNotify)){

                    $currentTime = time();
                    $timeOption = 'last_error_time_for_' . $this->errorCode;
                    $attemptOption = 'last_error_attempt_for_' . $this->errorCode;

                    $maxTime = 60 * 30;
                    $maxAttempt = 5;

                    $lastErrorTime = (int)Config::getOption($timeOption, -1);
                    if($lastErrorTime < 0){
                        $lastErrorTime = $currentTime;
                        Config::setOption($timeOption, $currentTime);
                    }
                    $timeDiff = $currentTime - $lastErrorTime;

                    $lastErrorAttempt = (int)Config::getOption($attemptOption, 1);

                    if($timeDiff <= $maxTime){
                        if($lastErrorAttempt <= $maxAttempt){
                            Config::setOption($attemptOption, $lastErrorAttempt + 1);
                            $needDelay = true;
                        } else {
                            $this->refreshDelayNotificationParams();
                        }
                    } else {
                        $needDelay = true;
                        $this->refreshDelayNotificationParams();
                    }

                }
            }
        }

        return $needDelay;
    }

        private function refreshDelayNotificationParams()
        {
            if($this->errorCode > 0){
                $timeOption = 'last_error_time_for_' . $this->errorCode;
                $attemptOption = 'last_error_attempt_for_' . $this->errorCode;
                Config::setOption($timeOption, -1);
                Config::setOption($attemptOption, 1);
            }            
        }

    private function checkEmailNotification()
    {
        if(!Config::checkFeature('loggerexchangeemail') || !$this->isMailCorrect) return;

        $to      = $this->mail;
        $subject = $this->mess;
        $message = LangMsg::get('ERROR_EXCHANGE', ['#MSG#' => $this->mess]);
        $headers = 'From: ' . $this->mailFrom . "\r\n" .
            'Reply-To: ' . $this->mailFrom . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers); 
    }

    private function checkAdminNotification()
    {
        if(!Config::checkFeature('loggerexchangenotify')) return;

        if(Config::getProfileId() > 0) {
            \CAdminNotify::Add([
                "MESSAGE" => LangMsg::get('ERROR_EXCHANGE_BY_PROFILE', [
                    '#MSG#' => $this->mess,
                    '#PROFILE_ID#' => Config::getProfileId()
                ]),
                "TAG" => "ORDER_FAIL",
                "MODULE_ID" => Config::getModuleId(),
                "ENABLE_CLOSE" => "Y",
                "NOTIFY_TYPE" => \CAdminNotify::TYPE_ERROR
            ]);
        } else {
            \CAdminNotify::Add([
                "MESSAGE" => LangMsg::get('ERROR_EXCHANGE', ['#MSG#' => $this->mess]),
                "TAG" => "ORDER_FAIL",
                "MODULE_ID" => Config::getModuleId(),
                "ENABLE_CLOSE" => "Y",
                "NOTIFY_TYPE" => \CAdminNotify::TYPE_ERROR
            ]);
        }
        
    }
}