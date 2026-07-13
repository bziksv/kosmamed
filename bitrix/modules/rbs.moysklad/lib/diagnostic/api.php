<?php
namespace Rbs\Moysklad\Diagnostic;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Error;

use Rbs\Moysklad\Utils;
use Rbs\Moysklad\Config;
use Rbs\Moysklad\Debug;
use Rbs\Moysklad\Internals\ModuleWorkSwitcher;

class Api extends Controller
{

    private const ACTION_CONFIG = [
        'getLogMessages' => [
            'prefilters' => [],
            'params' => [
                'file' => 'string',
                'profileId' => 'int'
            ]
        ],
        'getLogFileList' => [
            'prefilters' => [],
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'refreshLogFile' => [
            'prefilters' => [],
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getLogDir' => [
            'prefilters' => [],
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getBitrixLog' => [
            'prefilters' => [],
            'params' => []
        ],
        'getAppState' => [
            'prefilters' => [],
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getModuleInfo' => [
            'prefilters' => [],
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'setModuleConfig' => [
            'prefilters' => [],
            'params' => [
                'name' => 'string',
                'value' => 'mixed',
                'profileId' => 'int'
            ]
        ],
        'getMethodMapping' => [
            'prefilters' => [],
            'params' => []
        ],
        'getFunctionList' => [
            'prefilters' => [],
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getCheckListPage' => [
            'prefilters' => [],
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getLicenseCheck' => [
            'prefilters' => [],
            'params' => []
        ]
    ];

    public function configureActions()
    {
        return array_map(function ($config) {
            return ['prefilters' => $config['prefilters']];
        }, self::ACTION_CONFIG);
    }

    public function getMethodMappingAction()
    {
        return array_map(function ($config) {
            return $config['params'];
        }, self::ACTION_CONFIG);
    }

    public function setModuleConfigAction($name, $value, $profileId = 0)
    {
        Config::setProfileId((int)$profileId);
        if($name === 'global_enabled'){
            ModuleWorkSwitcher::switchModuleWork($value === 'Y', ModuleWorkSwitcher::REASON_MONITORING_CHANGE);
        } else {
            Config::setOption($name, $value);
        }
        return $this->returnJson([
            'name' => $name,
            'value' => Config::getOption($name),
        ]);
    }

    public function getAppStateAction($profileId = 0)
    {
        Config::setProfileId((int)$profileId);
        return $this->returnJson(App::getAppState());
    }

    public function getBitrixLogAction()
    {        
        $result = [];

        try {
            $result = BitrixLog::getBitrixLog();
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }

        return $this->returnJson($result);
    }

    public function getLogMessagesAction($file = '', $profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = [];

       try {
            if (empty($file)) {
                $file = Debug\FileController::getInstance()->getDefaultLogFileName();
            }
            $result['log'] = Debug\Reader::parseLogFile($file);
            if(empty($result['log'][0]['DATE']) && empty($result['log'][0]['HEAD'])){
                $result['log'] = [];
            }
            $result['file'] = $file;

       } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
       }

        return $this->returnJson($result);
    }

    public function getLogFileListAction($profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = [];

        try {
            $result['files'] = Debug\Reader::getFileList();
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }

        return $this->returnJson($result);
    }

    public function refreshLogFileAction($profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = [];

        try {
            (new Debug\File())->refreshFile();
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }

        return $this->returnJson($result);
    }

    public function getLogDirAction($profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = [];

        try {
            $result['dir'] = Debug\FileController::getInstance()->getLogDirPath();
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }

        return $this->returnJson($result);
    }

    public function getCheckListPageAction($profileId = 0)
    {
        Config::setProfileId((int)$profileId);
 
        $result = [];

        try {
            $result = Dashboard\Page::getData();
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }

        return $this->returnJson($result);

    }

    public function getLicenseCheckAction()
    {
        return $this->returnJson(\CRbsMoyskladHelper::getLicenseData());
    }

    private function returnJson($result)
    {
        try {
            return Json::encode($result);
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }
    }

}