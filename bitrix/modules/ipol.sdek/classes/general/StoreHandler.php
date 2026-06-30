<?php
namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Adapter\Store;
use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\StoresTable;

IncludeModuleLangFile(__FILE__);

class StoreHandler extends abstractGeneral
{
    /**
     * @var Store
     */
    protected static $store;

    /**
     * @return Store
     */
    public static function getStore()
    {
        return self::$store;
    }

    /**
     * Init da Store adapter
     * @return void
     */
    protected static function initStore()
    {
        $options = new Options();
        self::$store = new Store($options);
    }

    /**
     * Makes new Store using default preset
     * @return Store
     */
    public static function loadNewStore()
    {
        self::initStore();
        return self::getStore()->newStore();
    }

    /**
     * Load existing Store from DB
     * @param int $id
     * @return Store
     */
    public static function loadUploadedStore($id)
    {
        self::initStore();
        return self::getStore()->uploadedStore($id);
    }

    /**
     * Returns Store data if given id exists or new object if not
     * @param int $id
     * @return Store
     */
    public static function getStoreData($id)
    {
        if (!empty($id) && StoresTable::getByPrimaryId($id)) {
            return self::loadUploadedStore($id);
        }

        return self::loadNewStore();
    }

    /**
     * Upload Store using data from AJAX call
     * @return Result
     */
    public static function uploadStore()
    {
        $result = new Result();

        if (\sdekHelper::isAdmin('W')) {
            self::initStore();
            $store = self::getStore()->requestStore();

            $saveResult = self::saveStore($store);
            if ($saveResult->isSuccess()) {
                $result->setData($saveResult->getData());
            } else {
                $result->addErrors($saveResult->getErrors());
            }
        } else {
            $result->addError(new Error(Tools::getMessage('MESS_STORE_NO_RIGHTS_UPLOAD')));
        }

        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode([
                'success' => $result->isSuccess(),
                'errors'  => $result->getErrors()->isEmpty() ? '' : $result->getErrorsString(Result::SEPARATOR_NEW_LINE),
                'id'      => $result->isSuccess() ? $result->getData()['ID'] : false,
            ]);
        }

        return $result;
    }

    /**
     * Save Store data to DB
     * @param Store $store
     * @return Result
     */
    public static function saveStore($store)
    {
        $result = new Result();

        $data = array(
            'IS_ACTIVE'               => ($store->isActive() ? 'Y' : 'N'),
            'NAME'                    => $store->getCoreAddress()->getField('name'),

            'IS_SENDER_DATA_SENT'     => ($store->getCoreSender()->getField('isSenderDataSent') ? 'Y' : 'N'),
            'SENDER_COMPANY'          => $store->getCoreSender()->getCompany(),
            'SENDER_NAME'             => $store->getCoreSender()->getFullName(),
            'SENDER_PHONE_NUMBER'     => $store->getCoreSender()->getPhone(),
            'SENDER_PHONE_ADDITIONAL' => $store->getCoreSender()->getField('phoneAdditional'),
            'NEED_CALL'               => ($store->getCoreSender()->getField('needCall') ? 'Y' : 'N'),
            'POWER_OF_ATTORNEY'       => ($store->getCoreSender()->getField('powerOfAttorney') ? 'Y' : 'N'),
            'IDENTITY_CARD'           => ($store->getCoreSender()->getField('identityCard') ? 'Y' : 'N'),

            'IS_SELLER_DATA_SENT'     => ($store->getCoreSeller()->getField('isSellerDataSent') ? 'Y' : 'N'),
            'SELLER_NAME'             => $store->getCoreSeller()->getCompany(),
            'SELLER_PHONE'            => $store->getCoreSeller()->getPhone(),
            'SELLER_ADDRESS'          => $store->getCoreSeller()->getField('address'),

            'IS_DEFAULT_FOR_LOCATION' => ($store->getCoreAddress()->getField('isDefaultForLocation') ? 'Y' : 'N'),
            'IS_ADDRESS_DATA_SENT'    => ($store->getCoreAddress()->getField('isAddressDataSent') ? 'Y' : 'N'),
            'FROM_LOCATION_CODE'      => $store->getCoreAddress()->getCode(),
            'FROM_LOCATION_STREET'    => $store->getCoreAddress()->getStreet(),
            'FROM_LOCATION_HOUSE'     => $store->getCoreAddress()->getHouse(),
            'FROM_LOCATION_FLAT'      => $store->getCoreAddress()->getFlat(),
            'COMMENT'                 => $store->getCoreAddress()->getComment(),

            'INTAKE_TIME_FROM'        => $store->getCoreAddress()->getField('intakeTimeFrom'),
            'INTAKE_TIME_TO'          => $store->getCoreAddress()->getField('intakeTimeTo'),
            'LUNCH_TIME_FROM'         => $store->getCoreAddress()->getField('lunchTimeFrom'),
            'LUNCH_TIME_TO'           => $store->getCoreAddress()->getField('lunchTimeTo'),
        );

        $dbData = StoresTable::getByPrimaryId($store->getId());
        if (!empty($dbData)) {
            $dbResult = StoresTable::update($store->getId(), $data);
        } else {
            $dbResult = StoresTable::add($data);
        }

        if ($dbResult->isSuccess()) {
            // ID of updated or added record
            $result->setData(['ID' => (!empty($dbData) ? $store->getId() : $dbResult->getId())]);
        } else {
            foreach ($dbResult->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), $error->getCode()));
            }
        }

        return $result;
    }

    /**
     * Ajax wrapper for loading existed Store data
     * @param array $request
     * @return void
     */
    public static function loadStoreRequest($request)
    {
        $result = array('success' => false, 'errors' => 'Unknown error.', 'data' => false);

        if (Tools::isModuleAjaxRequest()) {
            if (isset($request['fromLocationCode'])) {
                $fromLocationCode = (int)$request['fromLocationCode'];
                if ($fromLocationCode > 0) {
                    $store = StoresTable::getList([
                        'select' => ['ID'],
                        'filter' => ['=IS_ACTIVE' => 'Y', '=FROM_LOCATION_CODE' => $fromLocationCode, '=IS_DEFAULT_FOR_LOCATION' => 'Y'],
                        'order'  => ['ID' => 'ASC'],
                        'limit'  => 1,
                    ])->fetch();

                    $result['success'] = true;
                    $result['errors']  = '';
                    $result['data']    = ($store['ID']) ? self::loadUploadedStore($store['ID']) : self::loadNewStore();
                } else {
                    $result['errors'] = 'Loading Store data failed cause no From Location Code given.';
                }
            } else if (isset($request['storeId'])) {
                $id = (int)$request['storeId'];
                if ($id > 0 && StoresTable::getByPrimaryId($id)) {
                    $result['success'] = true;
                    $result['errors']  = '';
                    $result['data']    = self::loadUploadedStore($id); // Da JsonSerializable magic hides in adapter
                } else if ($id === 0) {
                    $result['success'] = true;
                    $result['errors']  = '';
                    $result['data']    = self::loadNewStore();
                } else {
                    $result['errors'] = 'Loading Store data failed cause no Store found by given ID.';
                }
            } else {
                $result['errors'] = 'Loading Store data failed cause no required params given.';
            }
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Ajax wrapper for loading new Store data
     * @param array $request
     * @return void
     */
    public static function newStoreRequest($request)
    {
        $result = ['success' => false, 'errors' => 'Unknown error.', 'data' => false];

        if (Tools::isModuleAjaxRequest()) {
            $result['success'] = true;
            $result['errors']  = '';
            $result['data']    = self::loadNewStore();
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Delete row from StoresTable for given primary ID
     * @param int $id
     * @return Result
     */
    public static function deleteStore($id)
    {
        $result = new Result();

        if (\sdekHelper::isAdmin('W')) {
            if (!empty($id)) {
                $store = StoresTable::getByPrimaryId($id);
                if (!empty($store['ID'])) {
                    $deleteResult = StoresTable::delete($store['ID']);
                    if ($deleteResult->isSuccess()) {
                        $result->setData(['DELETED_ID' => $store['ID']]);
                    } else {
                        foreach ($deleteResult->getErrors() as $error) {
                            $result->addError(new Error($error->getMessage(), $error->getCode()));
                        }
                    }
                } else {
                    $result->addError(new Error('Delete failed cause no Store found by given ID.'));
                }
            } else {
                $result->addError(new Error('Delete failed cause no Store ID given.'));
            }
        } else {
            $result->addError(new Error(Tools::getMessage('MESS_STORE_NO_RIGHTS_DELETE')));
        }

        return $result;
    }

    /**
     * Ajax wrapper for deleteStore
     * @param array $request
     * @return void
     */
    public static function deleteStoreRequest($request)
    {
        $result = array('success' => false, 'errors' => 'Unknown error.');

        if (Tools::isModuleAjaxRequest()) {
            $deleteResult = self::deleteStore($request['storeId']);
            $result['success'] = $deleteResult->isSuccess();
            $result['errors']  = $deleteResult->getErrors()->isEmpty() ? '' : $deleteResult->getErrorsString(Result::SEPARATOR_NEW_LINE);
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Returns sender cities as string for JS autocomplete
     * @return string
     */
    public static function getSenderCitiesJS()
    {
        $result = '';

        $dbRes = \sqlSdekCity::select(['REGION', 'ASC']);
        while ($city = $dbRes->Fetch()) {
            $result .= "{label:'{$city['REGION']}, {$city['NAME']}',value:'{$city['SDEK_ID']}'},";
        }

        return $result;
    }

    /**
     * Returns list of active stores
     * @return array
     */
    public static function getActiveStores()
    {
        $result = [];

        $dbRes = StoresTable::getList(['select' => ['ID', 'NAME'], 'filter' => ['IS_ACTIVE' => 'Y']]);
        while ($tmp = $dbRes->fetch()) {
            $result[$tmp['ID']] = $tmp['NAME'];
        }

        return $result;
    }
}