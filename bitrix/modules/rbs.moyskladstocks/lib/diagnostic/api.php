<?php
namespace Rbs\MoyskladStocks\Diagnostic;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\Internals\ModuleWorkSwitcher;

class Api extends Controller
{
    private const ACTION_CONFIG = [
        'getLogMessages' => [
            'params' => [
                'file' => 'string',
                'profileId' => 'int'
            ]
        ],
        'getLogFileList' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'refreshLogFile' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getLogDir' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getBitrixLog' => [
            'params' => []
        ],
        'getAppState' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getModuleInfo' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'setModuleConfig' => [
            'params' => [
                'name' => 'string',
                'value' => 'mixed',
                'profileId' => 'int'
            ]
        ],
        'getMethodMapping' => [
            'params' => []
        ],
        'getFunctionList' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getCheckListPage' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getLicenseCheck' => [
            'params' => []
        ],
        'getEntityIblockList' => [
            'params' => [
                'profileId' => 'int'
            ]
        ],
        'getIblockElementsMetaInfo' => [
            'params' => [
                'iblockId' => 'int'
            ]
        ],
        'getDuplicateItemsList' => [
            'params' => [
                'type' => 'string',
                'iblockId' => 'int',
                'limit' => 'int',
                'offset' => 'int'
            ]
        ],
        'getMoyskladExternalCodeList' => [
            'params' => [
                'entity' => 'string',
                'limit' => 'int',
                'offset' => 'int',
                'profileId' => 'int'
            ]
        ],
        'getElementXmlIdsList' => [
            'params' => [
                'iblock_id' => 'int',
                'xmlIds' => 'array'
            ]
        ],
    ];

    public function configureActions()
    {
        return array_map(function ($config) {
            return ['prefilters' => [
                new Authentication(),
                new Csrf()
            ]];
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

    public function getFunctionListAction($profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = [];
        try {
            $result = Monitoring\Agents\Monitoring::getData();
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }

        return $this->returnJson($result);
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
        return $this->returnJson(\CRbsMoyskladStocks::getLicenseData());
    }

    public function getEntityIblockListAction(int $profileId = 0)
    {
        Config::setProfileId((int)$profileId);
        $result = [];
        try {
            $result = Tools\IblockElementAnalyzer::getEntityIblockList();
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }
        return $this->returnJson($result);
    }

    public function getIblockElementsMetaInfoAction(int $iblockId = 0)
    {
        $result = [];
        try {
            $result = Tools\IblockElementAnalyzer::getIblockElementsMetaInfo($iblockId);
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }
        return $this->returnJson($result);
    }

    public function getDuplicateItemsListAction(string $type = 'element', int $iblockId = 0, int $limit = 100, int $offset = 0)
    {
        $result = [];
        try {
            $result = Tools\IblockElementAnalyzer::buildDuplicatesList($type, $iblockId, $limit, $offset);
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }
        return $this->returnJson($result);
    }

    public function getMoyskladExternalCodeListAction(string $entity = 'product', int $limit = 100, int $offset = 0, int $profileId = 0)
    {
        Config::setProfileId((int)$profileId);
        $result = [];
        try {

            $result = Tools\MoyskladClient::getExternalCodeList($entity, $limit, $offset);

        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }
        return $this->returnJson($result);
    }

    public function getElementXmlIdsListAction(int $iblock_id = 0, array $xmlIds = [])
    {
        $result = [];
        try {
            $result = Tools\IblockElementAnalyzer::getElementXmlIdsList($iblock_id, $xmlIds);
        } catch (\Throwable $e) {
            $this->addError(new Error(Utils::build_exception_message($e)));
        }
        return $this->returnJson($result);
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