<?php
namespace Ipolh\SDEK\Bitrix\Controller;

use Ipolh\SDEK\Core\Delivery\Cargo;
use Ipolh\SDEK\Core\Delivery\CargoItem;
use Ipolh\SDEK\Core\Delivery\Location;
use Ipolh\SDEK\Core\Delivery\Shipment;
use Ipolh\SDEK\SDEK\Entity\CalculateListResult;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

class Calculator extends abstractController
{
    protected $shipment;
    protected $tarif;

    protected static $modeMilti        = 'IPOLSDEK_CALCMODEMULTI';
    protected static $tariffsSortOrder = 'PRICE';

    public function __construct($application = false)
    {
        parent::__construct($application);
    }

    public function makeShipments($sender,$city,$goods,$tarif,$calcMode)
    {
        /*if(!is_numeric($tarif)){
            $arPriority = \CDeliverySDEK::getListOfTarifs($tarif,$calcMode);
        }*/
        $this->application->tarif  = $tarif;
        $this->application->calcMode = $calcMode;

        $this->shipment = new Shipment();

        if (array_key_exists('W', $goods)) {
            $goods = array($goods);
        }

        try {
            foreach ($goods as $arGood) {
                $cargo = new Cargo();
                $item  = new CargoItem();
                $item->setGabs($arGood['D_L']*10, $arGood['D_W']*10, $arGood['D_H']*10)->setWeight($arGood['W']*1000);
                $cargo->add($item);
                $this->shipment->addCargo($cargo);
            }
        } catch (\Exception $e) {

        }

        $locationFrom = new Location('api');
        $locationFrom->setId($sender);

        $locationTo = new Location('api');
        $locationTo->setId($city);

        $this->shipment->setFrom($locationFrom)->setTo($locationTo);

        $this->shipment->setFields(array('tarif' => $tarif, 'mode' => $calcMode));
    }

    /**
     * Delivery calculation wrapper
     * @return array
     */
    public function calculate()
    {
        if (\Ipolh\SDEK\abstractGeneral::isNewApp()) {
            $modeMilti = (defined(self::$modeMilti) && constant(self::$modeMilti) === true);

            // Logic only for 2.0
            // - if tariff number given, calculate it
            // - if pack of tariffs given, call calculate list, define optimal tariff by priority and calculate it (yes, second API call)
            if (is_numeric($this->application->tarif)) {
                $requestedTariff = $this->application->tarif;
            } else {
                if ($modeMilti) {
                    // EXPERIMENTAL mode: NO warranties, NO support
                    $tariffCodes = \CDeliverySDEK::getListOfTarifs($this->application->tarif, $this->application->calcMode);
                    if (empty($tariffCodes)) {
                        return ['No active tariffs for profile ' . $this->application->tarif];
                    } else {
                        $multiResult = $this->calculateMulti($tariffCodes);
                        if ($multiResult->isSuccess()) {
                            $calculatedTariffs = $multiResult->getData()['CALCULATED_TARIFFS'];
                            $recalcRequired    = $multiResult->getData()['RECALC_REQUIRED'];

                            /** @var ErrorCollection $errors */
                            $errors            = $multiResult->getData()['ERRORS'];

                            if (!empty($recalcRequired)) {
                                // We got 'v2_internal_error' for some tariffs, so try to recalculate it
                                $multiResultSecond = $this->calculateMulti($recalcRequired);
                                if ($multiResultSecond->isSuccess()) {
                                    $calculatedTariffs = array_merge($calculatedTariffs, $multiResultSecond->getData()['CALCULATED_TARIFFS']);

                                    $errors->add($multiResultSecond->getData()['ERRORS']->toArray());
                                } else {
                                    $errors->add($multiResultSecond->getErrors());
                                }
                            }

                            if (!empty($calculatedTariffs)) {
                                // Got something to choose
                                usort($calculatedTariffs, [Calculator::class, 'sortTariffs']);
                                return $calculatedTariffs[0];
                            } else {
                                foreach ($errors as $error)
                                    $result[] = (($error->getCode()) ? '['.$error->getCode().'] ' : '').$error->getMessage();

                                return $result;
                            }
                        } else {
                            foreach ($multiResult->getErrors() as $error)
                                $result[] = (($error->getCode()) ? '['.$error->getCode().'] ' : '').$error->getMessage();

                            return $result;
                        }
                    }
                } else {
                    $listResult = $this->calculateTariffList();
                    if ($listResult['success']) {
                        $requestedTariff = $listResult['tarif'];
                    } else {
                        return $listResult;
                    }
                }
            }

            return $this->calculateTariff($requestedTariff);
        }

        return $this->calculateTariffList();
    }

    /**
     * @param array $tariffCodes
     * @return Result
     */
    public function calculateMulti($tariffCodes)
    {
        $result = new Result();

        $answer = $this->application->calculateMulti($this->shipment, $tariffCodes);
        if ($answer->isSuccess()) {
            \Ipolh\SDEK\option::set('sdekDeadServer', false);

            $response = $answer->getResponse();
            $tariffs = $response->getTariffList();

            $calculatedTariffs = [];
            $recalcRequired    = [];
            $calculationErrors = new ErrorCollection();
            $tariffs->reset();
            while ($tariff = $tariffs->getNext()) {
                if ($tariff->getDeliverySum()) {
                    $calculatedTariffs[] = array(
                        "price"           => $tariff->getTotalSum(),
                        "termMin"         => $tariff->getPeriodMin(),
                        "termMax"         => $tariff->getPeriodMax(),
                        "tarif"           => $tariff->getTariffCode(),
                        "priceByCurrency" => $tariff->getTotalSum(),
                        "success"         => 1,
                        "dateMin"         => '',
                        "dateMax"         => '',
                        "currency"        => 'RUB'
                    );
                } else if ($errors = $tariff->getErrors()) {
                    $errors->reset();
                    while ($error = $errors->getNext()) {
                        // Crutch for 'Internal server error' situation. Beware: another tariffs CAN be successfully calculated in same request!
                        if ($error->getCode() == 'v2_internal_error') {
                            $recalcRequired[] = $tariff->getTariffCode();
                        }
                        $calculationErrors->setError(new Error('Tariff '.$tariff->getTariffCode().': '.$error->getMessage(), $error->getCode()));
                    }
                } else {
                    $calculationErrors->setError(new Error('Tariff '.$tariff->getTariffCode().': Unknown data object invades from server.'));
                }
            }

            $result->setData([
                'CALCULATED_TARIFFS' => $calculatedTariffs,
                'RECALC_REQUIRED'    => $recalcRequired,
                'ERRORS'             => $calculationErrors,
            ]);
        } else {
            if ($this->application->getErrorCollection()) {
                $this->application->getErrorCollection()->reset();
                while ($error = $this->application->getErrorCollection()->getNext()) {
                    $result->addError(new Error($error->getMessage()));
                }
            } else {
                $result->addError(new Error('Error while requests \"' . __FUNCTION__ . '\", but no error messages get from application.'));
            }

            \Ipolh\SDEK\option::set('sdekDeadServer', time());
        }

        return $result;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    protected static function sortTariffs($a, $b)
    {
        switch (self::$tariffsSortOrder) {
            default:
            case 'PRICE':
                $primary_key   = 'price';
                $secondary_key = 'termMax';
                break;
            case 'DAYS':
                $primary_key   = 'termMax';
                $secondary_key = 'price';
                break;
        }

        if ($a[$primary_key] == $b[$primary_key]) {
            if ($a[$secondary_key] == $b[$secondary_key]) {
                return 0;
            }
            return ($a[$secondary_key] < $b[$secondary_key]) ? -1 : 1;
        }
        return ($a[$primary_key] < $b[$primary_key]) ? -1 : 1;
    }

    /**
     * Calculate specified tariff includes VAT, services, etc.
     * @param $tariff_code
     * @return array
     */
    public function calculateTariff($tariff_code)
    {
        $result = [];

        $answer = $this->application->calculateTariff($this->shipment, $tariff_code, null, 1, 1);
        if ($answer->isSuccess()) {
            \Ipolh\SDEK\option::set('sdekDeadServer', false);

            // Got something from server, deal with answer
            $response = $answer->getResponse();

            $tariff = $response->getTariff();
            if ($tariff->getDeliverySum()) {
                // Something calculated
                $result = array(
                    "price"           => $tariff->getTotalSum(),
                    "termMin"         => $tariff->getPeriodMin(),
                    "termMax"         => $tariff->getPeriodMax(),
                    "tarif"           => $tariff_code,
                    "priceByCurrency" => $tariff->getTotalSum(),
                    "success"         => 1,
                    "dateMin"         => '',
                    "dateMax"         => '',
                    "currency"        => 'RUB'
                );
            } else if ($errors = $tariff->getErrors()) {
                // Nothing calculated
                $errors->reset();
                while ($error = $errors->getNext()) {
                    $result[$error->getCode()] = $error->getMessage();
                }
            } else {
                $result[] = 'Unknown data object invades from server.';
            }
        } else {
            if ($this->application->getErrorCollection()) {
                $this->application->getErrorCollection()->reset();
                while ($error = $this->application->getErrorCollection()->getNext()) {
                    $result[] = $error->getMessage();
                }
            } else {
                $result[] = 'Error while requests \"' . __FUNCTION__ . '\", but no error messages get from application.';
            }

            \Ipolh\SDEK\option::set('sdekDeadServer', time());
        }

        return $result;
    }

    /**
     * Calculates list of tariffs requesting new or old API and returns result as array
     * For 2.0 this calculation DOES NOT includes VAT, services, etc. Only base delivery price.
     * @return array
     */
    public function calculateTariffList()
    {
        $obResult = $this->application->calculateList($this->shipment, null, null, 1, 1);
        return $this->reworkResult($obResult);
    }

    /**
     * Make old good array from given object
     * @param CalculateListResult $obResult
     * @return array
     */
    protected function reworkResult($obResult)
    {
        $arReturn = array();
        if ($obResult->isSuccess()) {
            $tarifCodes = $obResult->getResponse()->getTariffCodes();
            if ($tarifCodes) {
                if (is_numeric($this->application->tarif)) {
                    $tariffPriority = array($this->application->tarif);
                } else {
                    $tariffPriority = \CDeliverySDEK::getListOfTarifs($this->application->tarif, $this->application->calcMode);

                    if (\Ipolh\SDEK\abstractGeneral::isNewApp()) {
                        foreach (GetModuleEvents(self::$MODULE_ID, 'onTarifPriority', true) as $event) {
                            ExecuteModuleEventEx($event, [&$tariffPriority, $this->application->tarif, $this->shipment]);
                        }
                    }
                }

                $calculatedTariffs = [];
                $tarifCodes->reset();
                while ($obTarif = $tarifCodes->getNext()) {
                    $calculatedTariffs[$obTarif->getTariffCode()] = [
                        "price"           => $obTarif->getDeliverySum(),
                        "termMin"         => $obTarif->getPeriodMin(),
                        "termMax"         => $obTarif->getPeriodMax(),
                        "tarif"           => $obTarif->getTariffCode(),
                        "priceByCurrency" => $obTarif->getDeliverySum()
                        ];
                }

                foreach ($tariffPriority as $tariffCode) {
                    if (array_key_exists($tariffCode, $calculatedTariffs)) {
                        $arReturn = array_merge($calculatedTariffs[$tariffCode], array("success" => 1, "dateMin" => '', "dateMax" => '', "currency" => 'RUB'));
                        break;
                    }
                }
            }
        }

        if (!count($arReturn)) {
            if (\Ipolh\SDEK\abstractGeneral::isNewApp()) {
                // API 2.0 answer and application errors handling
                if ($obResult->isSuccess()) {
                    \Ipolh\SDEK\option::set('sdekDeadServer', false);

                    $response = $obResult->getResponse();
                    if ($errors = $response->getErrors()) {
                        $errors->reset();
                        while ($error = $errors->getNext()) {
                            $arReturn[$error->getCode()] = $error->getMessage();
                        }
                    } else {
                        $arReturn[3] = GetMessage('IPOLSDEK_HINT_FOR_TRANSIT');
                    }
                } else {
                    if ($this->application->getErrorCollection()) {
                        $this->application->getErrorCollection()->reset();
                        while ($error = $this->application->getErrorCollection()->getNext()) {
                            $arReturn[] = $error->getMessage();
                        }
                    } else {
                        $arReturn['noanswer'] = 'Error while requests \"' . __FUNCTION__ . '\", but no error messages get from application.';
                    }

                    \Ipolh\SDEK\option::set('sdekDeadServer', time());
                }
            } else {
                // API 1.5
                if ($obResult->getResponse()->getErrors()) {
                    $obResult->getResponse()->getErrors()->reset();
                    while ($error = $obResult->getResponse()->getErrors()->getNext()) {
                        $arReturn[$error->getCode()] = $error->getMessage();
                    }
                } else {
                    $arReturn[3] = GetMessage('IPOLSDEK_HINT_FOR_TRANSIT');
                }
            }
        }

        return $arReturn;
    }
}