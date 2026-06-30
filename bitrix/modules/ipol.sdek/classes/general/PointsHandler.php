<?php

namespace Ipolh\SDEK;

use \Ipolh\SDEK\Bitrix\Controller\pvzController;
use \Ipolh\SDEK\Legacy\transitApplication;

class PointsHandler extends abstractGeneral
{
    /**
     * Pvz request types
     */
    const REQUEST_TYPE_SDEK = 'sdek';
    const REQUEST_TYPE_BACKUP = 'backup';

    public static function updatePoints($requestType = self::REQUEST_TYPE_SDEK, $forced = false)
    {
        $result = array('SUCCESS' => false, 'ERROR' => false);

        if ($accountId = \Ipolh\SDEK\option::get('logged')) {
            $account = \sqlSdekLogs::getById($accountId);
            if ($requestType == self::REQUEST_TYPE_BACKUP) {
                // @TODO: check / refactor
                // - This is madness!
                // - This is REQUEST_TYPE_BACKUP CALL!
                $application = new transitApplication($account['ACCOUNT'], $account['SECURE']);
                $application->requestType = self::REQUEST_TYPE_BACKUP;
            } else {
                $application = self::makeApplication($account['ACCOUNT'], $account['SECURE']);

                if ($application instanceof \Ipolh\SDEK\SDEK\SdekApplication) {
                    $application
                        ->setTestMode(false)
                        ->setTimeout(30)
                        ->setCache(null)
                        ->setLogger(null);
                }
            }

            $controller = new pvzController(false);
            $controller->setApplication($application);
            $refreshResult = $controller->refreshPoints($forced);

            if ($refreshResult->isSuccess()) {
                $result['SUCCESS'] = true;
            } else {
                $result['ERROR'] = implode(', ', $refreshResult->getErrorMessages());
            }
        } else {
            $result['ERROR'] = 'Successful module authorization required before call updatePoints method.';
        }

        return $result;
    }

    public static function getPoints($cityId, $mode = 'PVZ', $weight = null, $dimensions = null)
    {
        $pvzController = new pvzController(true);
        $arList = $pvzController->getList();

        $points = $arList[$mode][$cityId];

        rsort($dimensions);

        foreach ($points as $code => $point) {
            if (!is_null($weight) && array_key_exists('WeightLim', $point) && ($point['WeightLim']['MIN'] > $weight || $point['WeightLim']['MAX'] < $weight)) {
                unset($points[$code]);
            }

            if (!is_null($dimensions) && array_key_exists('Dimensions', $point)) {
                rsort($point['Dimensions']);
                foreach ($point['Dimensions'] as $key => $dimension) {
                    if ($point['Dimensions'][$key] > $dimensions[$key]) {
                        unset($points[$code]);
                    }
                }
            }
        }

        return $points;
    }

    /**
     * @param int $citySdekId
     * @param array $goods - @see CDeliverySDEK::$goods
     * @return array
     */
    public static function checkAvailableProfiles($citySdekId, $goods)
    {
        $modes = ['PVZ' => false, 'POSTAMAT' => false];

        // Because of Packs
        if (array_keys($goods) !== range(0, count($goods) - 1)) {
            $goods = [$goods];
        }

        $pvzController = new pvzController(true);
        $pointsList = $pvzController->getList();

        foreach (['PVZ', 'POSTAMAT'] as $mode) {
            if (!empty($pointsList[$mode]) && is_array($pointsList[$mode][$citySdekId]) && !empty($pointsList[$mode][$citySdekId])) {
                $points = $pointsList[$mode][$citySdekId];

                // Check against Packs
                foreach ($goods as $good) {
                    $weightCheck = $good['W']; // kg
                    $gabsCheck = []; // cm

                    if (!(empty($good['D_L']) || empty($good['D_W']) || empty($good['D_H']))) {
                        $gabsCheck = [$good['D_L'], $good['D_W'], $good['D_H']];
                        rsort($gabsCheck);

                        if (\Ipolh\SDEK\option::get('mindVWeight') === 'Y') {
                            $weightCheck = max($weightCheck, \sdekHelper::getVolumeWeight($good['D_L'] * 10, $good['D_W'] * 10, $good['D_H'] * 10));
                        }
                    }

                    foreach ($points as $code => $point) {
                        if (array_key_exists('WeightLim', $point) && ($point['WeightLim']['MIN'] > $weightCheck || $point['WeightLim']['MAX'] < $weightCheck)) {
                            unset($points[$code]);
                            continue;
                        }

                        if (!empty($gabsCheck) && array_key_exists('Dimensions', $point)) {
                            rsort($point['Dimensions']);
                            foreach ($gabsCheck as $key => $gab) {
                                if ($point['Dimensions'][$key] < $gab) {
                                    unset($points[$code]);
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!empty($points)) {
                    $modes[$mode] = true;
                }
            }
        }

        return ['pickup' => $modes['PVZ'], 'postamat' => $modes['POSTAMAT']];
    }
}