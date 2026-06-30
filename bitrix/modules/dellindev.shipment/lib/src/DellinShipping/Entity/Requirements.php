<?php
/**
 * Слой сущности дополнительных требований к погрузке и разгрузке.
 * Имеется маппинг UID кодов с условными названиями услуг, для быстрого формирования сущности для тела
 * запроса к API.
 * В дальнейшем при развитии проекта SDK, возможно приведения в комментариях конкретных кейсов использования.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */

namespace DellinShipping\Entity;

final class Requirements
{

   // public $isOrder;
    public $requirementsArrival = [];
    public $requirementsDerival = [];

    /**
     * Маппинг имеющихся услуг. Интерфейс для быстрого добавления в пул услуг.
     * @var array
     */
    public $mappingCode = [
      //  'loadingBack' => 'NULL',
        'sideLoading' => '0xb83b7589658a3851440a853325d1bf69',
        'upperLoading' => '0xabb9c63c596b08f94c3664c930e77778',
        'palletLift' => '0x92fce2284f000b0241dad7c2e88b1655',
        'manipulator' => '0x88f93a2c37f106d94ff9f7ada8efe886',
        'needOpenCar' => '0x9951e0ff97188f6b4b1b153dfde3cfec',
        'unCanopy' => '0x818e8ff1eda1abc349318a478659af08',
//        'needLoadingWork' => '0x88b6f23b70b15e51480587ec9fb77188',
//        'needUnloadingWork' => '0xb27f04c24889c7234e1325c584c515c0'
    ];

    /**
     * Маппинг ендпоинстов в запрос.
     * Сущность arival и derival внутри запроса.
     * @var array
     */
    public $mappingTypeReq = ['loading'   => 'requirementsDerival',
                              'unloading' => 'requirementsArrival'  ];


    public function __construct(?Config $config)
    {
        if(!($config instanceof Config)){

            throw new \Exception('Config is not DellinShipping\\Entity\\Config object');

        }

        if($this->validateInputDataLoading($config)){
            foreach ($config->getRequirementsLoading() as $key => $flag){
                if ($flag) $this->addUID($key, 'loading');
            }
        }
//        else {
//            $this->needOnlyLoadingWork($config);
//        }

        if($this->validateInputDataUnloading($config)){
            foreach ($config->getRequirementsUnloading() as $key => $flag){
                if ($flag) $this->addUID($key, 'unloading');
            }
        }
//        else {
//            $this->needOnlyUnloadingWork($config);
//        }

    }

// TODO При интеграции нужно передавать параметр handling в следующей итерации переделать

//    protected function needOnlyLoadingWork($config){
//        if($config->isGoodsLoading()){
//            if(isset($config->getRequirementsLoading()->{'0x88b6f23b70b15e51480587ec9fb77188'}) &&
//                    ($config->getRequirementsLoading()->{'0x88b6f23b70b15e51480587ec9fb77188'})){
//                $this->addUIDByName('needLoadingWork', 'loading');
//            }
//        }
//    }
//
//    protected function needOnlyUnloadingWork($config){
//
//        if($config->isGoodsUnloading()){
//            if(isset($config->getRequirementsUnloading()->{'0xb27f04c24889c7234e1325c584c515c0'}) &&
//                    ($config->getRequirementsUnloading()->{'0xb27f04c24889c7234e1325c584c515c0'})){
//                $this->addUIDByName('needUnloadingWork', 'loading');
//            }
//        }
//
//    }

    protected function validateInputDataLoading($config){

        if($config->isGoodsLoading()){ //Если от адреса
            //Если данные корректны и это не задняя загрузка.
            if(is_object($config->getRequirementsLoading()) && !($config->getRequirementsLoading()->BACK)){
                return true;
            }
            return false;
        }

    }

    protected function validateInputDataUnloading($config){
        if($config->isGoodsUnloading()){ //Если до адреса
            //Если данные корректны и это не задняя загрузка.
            if(is_object($config->getRequirementsUnloading()) && !($config->getRequirementsUnloading()->BACK)){
                return true;
            }
            return false;
        }
    }


    public function addUIDByName($name, $type)
    {

        if(array_key_exists($name, $this->mappingCode)){
            $this->{$this->mappingTypeReq[$type]}[] = $this->mappingCode[$name];
        } else {
            throw new \Exception('Requirements name is not found');
        }

    }

    public function addUID($uid, $type){

        if(array_key_exists($type, $this->mappingTypeReq)){
            $this->{$this->mappingTypeReq[$type]}[] = $uid;
        } else {
            throw new \Exception('Requirements type is not found');
        }
    }





}