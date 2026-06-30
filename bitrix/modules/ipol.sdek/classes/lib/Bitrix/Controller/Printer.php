<?php
namespace Ipolh\SDEK\Bitrix\Controller;

use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\PrintHandler;
use Ipolh\SDEK\SDEK\SdekApplication;

class Printer extends abstractController
{
    use ControllerHelpers;

    /**
     * Get print forms data for given CDEK order UUIDs or numbers
     * BEWARE: all given orders must refer to one CDEK account passed to Application
     * @param string $type
     * @param string $uuids
     * @param int[] $cdekNumbers
     * @param int $sleep Delay between make and info calls for 2.0
     * @return Result
     */
    public function getPrintForms($type = PrintHandler::PRINT_FORM_ORDER, $uuids = null, $cdekNumbers = null, $sleep = 0)
    {
        $result = new Result();

        if (!in_array($type, [PrintHandler::PRINT_FORM_ORDER, PrintHandler::PRINT_FORM_BARCODE])) {
            $result->addError(new Error('Unsupported print type given: '.$type.'.'));
        }

        if (empty($uuids) && empty($cdekNumbers)) {
            $result->addError(new Error('No order UUID or cdekNumber given.'));
        }

        if (!($this->application instanceof SdekApplication)) {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        if ($result->isSuccess()) {
            switch ($type) {
                case PrintHandler::PRINT_FORM_ORDER:
                default:
                    $copyCount = (int)$this->options->fetchNumberOfPrints();
                    $copyCount = ($copyCount < 1) ? 1 : $copyCount;

                    $answer = $this->application->printOrdersMake($uuids, $cdekNumbers, $copyCount);
                    break;
                case PrintHandler::PRINT_FORM_BARCODE:
                    $copyCount = (int)$this->options->fetchNumberOfStrihs();
                    $copyCount = ($copyCount < 1) ? 1 : $copyCount;
                    $format = $this->options->fetchFormatOfStrihs();
                    $format = in_array($format, ['A4', 'A5', 'A6']) ? $format : 'A4';

                    $answer = $this->application->printBarcodesMake($uuids, $cdekNumbers, $copyCount, $format);
                    break;
            }

            /** @var \Ipolh\SDEK\SDEK\Entity\PrintBarcodesMakeResult|\Ipolh\SDEK\SDEK\Entity\PrintOrdersMakeResult $answer */
            if ($answer->isSuccess()) {
                $response = $answer->getResponse();
                $dataMake['UUID'] = $response->getEntity() ? $response->getEntity()->getUuid() : null;

                if ($requestsList = $response->getRequests()) {
                    $processRequestResult = $this->processRequestList($requestsList);
                    $dataMake['MAKE_LAST_STATE_CODE'] = $processRequestResult->getData()['LAST_STATE_CODE'];
                    $dataMake['MAKE_LAST_STATE_DATE'] = $processRequestResult->getData()['LAST_STATE_DATE'];

                    if (!$processRequestResult->getErrors()->isEmpty())
                        $result->addErrors($processRequestResult->getErrors());

                    if (!$processRequestResult->getWarnings()->isEmpty())
                        $result->addWarnings($processRequestResult->getWarnings());
                } else {
                    $result->addError(new Error('Unknown data object has invaded from server.'));
                }

                $result->setData($dataMake);
            } else {
                $this->collectApplicationErrors($result, __FUNCTION__);
            }

            // Get Info
            if ($result->isSuccess()) {
                // Delay before second API call, because of async methods
                $sleep = (int)$sleep;
                if ($sleep > 0)
                    sleep($sleep);

                // Unmake warnings because they come again in Printer::getPrintFormsInfo() answer
                $result->getWarnings()->clear();

                // No errors and UUID returns so try to get print form file content
                $getInfoResult = $this->getPrintFormsInfo($result->getData()['UUID'], $type);

                // Warnings always added here, no matter of result status
                $result->addWarnings($getInfoResult->getWarnings());

                if ($getInfoResult->isSuccess()) {
                    $result->setData(array_merge($result->getData(), $getInfoResult->getData()));
                } else {
                    $result->addErrors($getInfoResult->getErrors());
                }
            }
        }

        return $result;
    }

    /**
     * Get print forms file URL for given UUID of orders / barcodes creation request
     * @param string $uuid
     * @param string $type
     * @return Result
     */
    public function getPrintFormsInfo($uuid, $type = PrintHandler::PRINT_FORM_ORDER)
    {
        $result = new Result();

        if (!in_array($type, [PrintHandler::PRINT_FORM_ORDER, PrintHandler::PRINT_FORM_BARCODE])) {
            $result->addError(new Error('Unsupported print type given: '.$type.'.'));
        }

        if (empty($uuid)) {
            $result->addError(new Error('No UUID of print forms creation request given.'));
        }

        if (!($this->application instanceof SdekApplication)) {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        if ($result->isSuccess()) {
            switch ($type) {
                case PrintHandler::PRINT_FORM_ORDER:
                default:
                    $answer = $this->application->printOrdersInfo($uuid);
                    break;
                case PrintHandler::PRINT_FORM_BARCODE:
                    $answer = $this->application->printBarcodesInfo($uuid);
                    break;
            }

            if ($answer->isSuccess()) {
                $response = $answer->getResponse();
                $data = ['URL' => null, 'CONTENT' => null, 'STATUSES' => []];

                if ($entity = $response->getEntity()) {
                    if ($url = $entity->getUrl()) {
                        $data['URL'] = $url;

                        // No direct viewing from browser because of stupid token requirement
                        if (function_exists('curl_init')) {
                            try {
                                $ch = curl_init($url);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 10);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$this->application->getToken()]);
                                $downloadResponse = curl_exec($ch);
                                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                curl_close($ch);

                                if (empty($downloadResponse) || !in_array($code, [200])) {
                                    $result->addError(new Error('Fail while trying to download file '.$url.'. HTTP status '.$code.'.'));
                                } else {
                                    // Finally, da print form file content returns
                                    $data['CONTENT'] = $downloadResponse;
                                }
                            } catch (\Exception $e) {
                                // Case: something wrong with token
                                $result->addError(new Error($e->getMessage()));
                            }
                        } else {
                            $result->addError(new Error('No PHP CURL extension installed.'));
                        }
                    }

                    $entity->getStatuses()->reset();
                    while ($status = $entity->getStatuses()->getNext()) {
                        $data['STATUSES'][] = ['DATETIME' => $status->getDateTime()->getTimestamp(), 'STATUS' => $status->getCode()];
                    }
                }

                if ($requestsList = $response->getRequests()) {
                    $processRequestResult = $this->processRequestList($requestsList);
                    $data['INFO_LAST_STATE_CODE'] = $processRequestResult->getData()['LAST_STATE_CODE'];
                    $data['INFO_LAST_STATE_DATE'] = $processRequestResult->getData()['LAST_STATE_DATE'];

                    if (!$processRequestResult->getErrors()->isEmpty())
                        $result->addErrors($processRequestResult->getErrors());

                    if (!$processRequestResult->getWarnings()->isEmpty())
                        $result->addWarnings($processRequestResult->getWarnings());
                } else {
                    $result->addError(new Error('Unknown data object has invaded from server.'));
                }

                $result->setData($data);
            } else {
                $this->collectApplicationErrors($result, __FUNCTION__);
            }
        }

        return $result;
    }

    /**
     * Save given file content to file with hashed filename
     * @param int $accountId
     * @param string $account
     * @param string $secure
     * @param string $content
     * @param string $type
     * @return Result
     */
    public static function savePrintFormFile($accountId, $account, $secure, $content, $type = PrintHandler::PRINT_FORM_ORDER)
    {
        $result = new Result();
        $time   = time();

        switch ($type) {
            case PrintHandler::PRINT_FORM_ORDER:
            default:
                $prefix = 'invoice';
                break;
            case PrintHandler::PRINT_FORM_BARCODE:
                $prefix = 'barcode';
                break;
        }

        $filename = $prefix.'_'.$accountId.'_'.md5($account.$time).md5($secure.$time).'.pdf';
        if ($file = self::saveToFile($content, $filename)) {
            $result->setData(['FILE' => $file]);
        } else {
            $result->addError(new Error(Tools::getMessage('ERR_CAN_NOT_SAVE_FILE').$filename));
        }

        return $result;
    }

    /**
     * Make and return files upload directory
     * @param bool $noDocumentRoot
     * @return string
     */
    public static function getFilePath($noDocumentRoot = false)
    {
        $uploadPath = '/upload/'.self::$MODULE_ID.'/';

        if (!file_exists($_SERVER['DOCUMENT_ROOT'].$uploadPath))
            mkdir($_SERVER['DOCUMENT_ROOT'].$uploadPath);

        return (($noDocumentRoot) ? '' : $_SERVER['DOCUMENT_ROOT']).$uploadPath;
    }

    /**
     * Save data to file
     * @param mixed $data
     * @param string $filename
     * @return string|false
     */
    protected static function saveToFile($data, $filename)
    {
        return (file_put_contents(self::getFilePath().$filename, $data) ? self::getFilePath(true).$filename : false);
    }
}