<?php
namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Adapter\Printer as PrinterAdapter;
use Ipolh\SDEK\Bitrix\Controller\Printer;
use Ipolh\SDEK\Bitrix\Entity\cache;
use Ipolh\SDEK\Bitrix\Entity\encoder;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\SDEK\SdekApplication;

class PrintHandler extends abstractGeneral
{
    /**
     * Work mode variants
     */
    const WORK_MODE_ORDER    = 'order';
    const WORK_MODE_SHIPMENT = 'shipment';

    /**
     * Print form types
     */
    const PRINT_FORM_BARCODE = 'shtrih';
    const PRINT_FORM_ORDER   = 'invoice';

    /**
     * Wrapper for printing orders data request via AJAX call
     * @param array $request contains Bitrix order IDs
     * @return void
     */
    public static function getPrintOrdersRequest($request)
    {
        $result = PrinterAdapter::getPrintOrders($request['data']);

        echo Tools::jsonEncode([
            'success'  => $result->isSuccess(),
            'errors'   => $result->getErrors()->isEmpty() ? '' : $result->getErrorsString(Result::SEPARATOR_NEW_LINE),
            'warnings' => $result->getWarnings()->isEmpty() ? '' : $result->getWarningsString(Result::SEPARATOR_NEW_LINE),
            'data'     => $result->isSuccess() ? $result->getData()['EXISTED'] : false,
        ]);
    }

    /**
     * Wrapper for invoice file request via AJAX call
     * @param array $request contains order and/or shipment IDs
     * @return void
     */
    public static function getInvoiceRequest($request)
    {
        $result = self::getInvoice($request['data']);
        echo Tools::jsonEncode($result);
    }

    /**
     * Wrapper for barcode file request via AJAX call
     * @param array $request contains order and/or shipment IDs
     * @return void
     */
    public static function getBarcodeRequest($request)
    {
        $result = self::getBarcode($request['data']);
        echo Tools::jsonEncode($result);
    }

    /**
     * Wrapper for invoice file request via AJAX call
     * UUIDs of CDEK 2.0 printOrdersMake requests required
     * @param array $request contains [accountId => uuid]
     * @return void
     */
    public static function getInvoiceByUuidRequest($request)
    {
        $result = self::getInvoiceByUuid($request['data']);
        echo Tools::jsonEncode($result);
    }

    /**
     * Wrapper for barcode file request via AJAX call
     * UUIDs of CDEK 2.0 printBarcodesMake requests required
     * @param array $request contains [accountId => uuid]
     * @return void
     */
    public static function getBarcodeByUuidRequest($request)
    {
        $result = self::getBarcodeByUuid($request['data']);
        echo Tools::jsonEncode($result);
    }

    /**
     * Get invoices print forms data for given Bitrix order and/or shipment IDs
     * @param array $orders with structure [PrintHandler::WORK_MODE_ORDER => [1, 2], PrintHandler::WORK_MODE_SHIPMENT => [3, 4]]
     * @return array
     */
    public static function getInvoice($orders)
    {
        return self::getPreparedResult(self::getPrint($orders, self::PRINT_FORM_ORDER));
    }

    /**
     * Get barcodes print forms data for given Bitrix order and/or shipment IDs
     * @param array $orders with structure [PrintHandler::WORK_MODE_ORDER => [1, 2], PrintHandler::WORK_MODE_SHIPMENT => [3, 4]]
     * @return array
     */
    public static function getBarcode($orders)
    {
        return self::getPreparedResult(self::getPrint($orders, self::PRINT_FORM_BARCODE));
    }

    /**
     * Get invoices print forms data for given CDEK 2.0 UUIDs of printOrdersMake requests
     * @param array $uuids with structure [accountId => uuid]
     * @return array
     */
    public static function getInvoiceByUuid($uuids)
    {
        return self::getPreparedResult(self::getPrintByUuid($uuids, self::PRINT_FORM_ORDER));
    }

    /**
     * Get barcodes print forms data for given CDEK 2.0 UUIDs of printBarcodesMake requests
     * @param array $uuids with structure [accountId => uuid]
     * @return array
     */
    public static function getBarcodeByUuid($uuids)
    {
        return self::getPreparedResult(self::getPrintByUuid($uuids, self::PRINT_FORM_BARCODE));
    }

    /**
     * Get print forms data for given Bitrix order and/or shipment IDs
     * @param array $orders with structure [PrintHandler::WORK_MODE_ORDER => [1, 2], PrintHandler::WORK_MODE_SHIPMENT => [3, 4]]
     * @param string $type PrintHandler::PRINT_FORM_ORDER | PrintHandler::PRINT_FORM_BARCODE
     * @return Result
     */
    public static function getPrint($orders, $type = self::PRINT_FORM_ORDER)
    {
        $result = new Result();

        if (!in_array($type, [self::PRINT_FORM_ORDER, self::PRINT_FORM_BARCODE])) {
            $result->addError(new Error('Unsupported print type given: '.$type.'.'));
        }

        if (empty($orders[self::WORK_MODE_ORDER]) && empty($orders[self::WORK_MODE_SHIPMENT])) {
            $result->addError(new Error('No order Id for supported work modes given.'));
        }

        if ($result->isSuccess()) {
            $preparedOrdersResult = PrinterAdapter::prepareOrdersData($orders);

            // Warnings like 'Some orders not found in sent to CDEK'
            $result->addWarnings($preparedOrdersResult->getWarnings());

            if ($preparedOrdersResult->isSuccess()) {
                $data = ['FILES' => [], 'NOT_READY' => []];

                $cdekOrders = $preparedOrdersResult->getData()['ORDERS'];
                foreach ($cdekOrders as $accountId => $cdekNumbers) {
                    $account = \sqlSdekLogs::getById($accountId);
                    if (!empty($account)) {
                        $controller = new Printer(self::makeApplication($account['ACCOUNT'], $account['SECURE']));
                        $getFormsResult = $controller->getPrintForms($type, null, $cdekNumbers, 1);

                        if ($getFormsResult->isSuccess()) {
                            $fileContent = $getFormsResult->getData()['CONTENT'];
                            if (!empty($fileContent)) {
                                $saveContentResult = Printer::savePrintFormFile($accountId, $account['ACCOUNT'], $account['SECURE'], $fileContent, $type);
                                if ($saveContentResult->isSuccess()) {
                                    $data['FILES'][] = $saveContentResult->getData()['FILE'];
                                } else {
                                    $result->addErrors($saveContentResult->getErrors());
                                }
                            } else {
                                // Maybe not ready due to async API 2.0
                                $data['NOT_READY'][] = ['ACCOUNT_ID' => $accountId, 'UUID' => $getFormsResult->getData()['UUID']];
                            }

                            // This one for debug reasons
                            $data['DETAILS'][$accountId] = $getFormsResult->getData();
                        } else {
                            $result->addErrors($getFormsResult->getErrors());
                        }

                        $result->addWarnings($getFormsResult->getWarnings());
                    } else {
                        $result->addError(new Error(Tools::getMessage('ERR_UNKNOWN_ACCOUNT')));
                    }
                }

                // Assume result is OK if at least one file returns, or it's making in progress, no matter how many errors we got
                if (!empty($data['FILES']) || !empty($data['NOT_READY'])) {
                    $result->setSuccess(true);
                }

                $result->setData($data);
            } else {
                $result->addErrors($preparedOrdersResult->getErrors());
            }
        }

        return $result;
    }

    /**
     * Get print forms data for given CDEK 2.0 UUIDs of printOrdersMake | printBarcodesMake requests
     * @param array $uuids with structure [accountId => uuid]
     * @param string $type PrintHandler::PRINT_FORM_ORDER | PrintHandler::PRINT_FORM_BARCODE
     * @return Result
     */
    public static function getPrintByUuid($uuids, $type = self::PRINT_FORM_ORDER)
    {
        $result = new Result();

        if (!in_array($type, [self::PRINT_FORM_ORDER, self::PRINT_FORM_BARCODE])) {
            $result->addError(new Error('Unsupported print type given: '.$type.'.'));
        }

        if (empty($uuids)) {
            $result->addError(new Error('No UUID of print form generation request given.'));
        }

        if ($result->isSuccess()) {
            $data = ['FILES' => [], 'NOT_READY' => []];

            foreach ($uuids as $val) {
                $accountId   = $val['ACCOUNT_ID'];
                $account = \sqlSdekLogs::getById($accountId);
                if (!empty($account)) {
                    $controller = new Printer(self::makeApplication($account['ACCOUNT'], $account['SECURE']));
                    $getFormsResult = $controller->getPrintFormsInfo($val['UUID'], $type);

                    if ($getFormsResult->isSuccess()) {
                        $fileContent = $getFormsResult->getData()['CONTENT'];
                        if (!empty($fileContent)) {
                            $saveContentResult = Printer::savePrintFormFile($accountId, $account['ACCOUNT'], $account['SECURE'], $fileContent, $type);
                            if ($saveContentResult->isSuccess()) {
                                $data['FILES'][] = $saveContentResult->getData()['FILE'];
                            } else {
                                $result->addErrors($saveContentResult->getErrors());
                            }
                        } else {
                            $data['NOT_READY'][] = ['ACCOUNT_ID' => $accountId, 'UUID' => $val['UUID']];
                        }
                    } else {
                        $result->addErrors($getFormsResult->getErrors());
                    }

                    $result->addWarnings($getFormsResult->getWarnings());
                } else {
                    $result->addError(new Error(Tools::getMessage('ERR_UNKNOWN_ACCOUNT')));
                }
            }

            // Assume result is OK if at least one file returns, or it's making in progress, no matter how many errors we got
            if (!empty($data['FILES']) || !empty($data['NOT_READY'])) {
                $result->setSuccess(true);
            }

            $result->setData($data);
        }

        return $result;
    }

    /**
     * Makes unified data array from Result answer of PrintHandler::getPrint() | PrintHandler::getPrintByUuid()
     * @param Result $result
     * @return array
     */
    protected static function getPreparedResult($result)
    {
        $data = ['success' => false, 'errors' => '', 'warnings' => ''];

        if ($result->isSuccess()) {
            $data['success'] = true;

            $resultData = $result->getData();
            if (!empty($resultData['FILES']))
                $data['files'] = $resultData['FILES'];
            if (!empty($resultData['NOT_READY']))
                $data['notReady'] = $resultData['NOT_READY'];
        }

        if (!$result->getErrors()->isEmpty())
            $data['errors'] = $result->getErrorsString(Result::SEPARATOR_NEW_LINE);

        if (!$result->getWarnings()->isEmpty())
            $data['warnings'] = $result->getWarningsString(Result::SEPARATOR_NEW_LINE);

        return $data;
    }

    /**
     * Unmake old files
     * @param string $prefix
     * @param int $lifetime in seconds
     */
    public static function unmakeOldFiles($prefix = '', $lifetime = 3600)
    {
        $path  = Printer::getFilePath();
        $files = scandir($path);
        $time  = time();

        foreach ($files as $file) {
            if (in_array($file, array('.', '..')))
                continue;

            if ($prefix && strpos($file, $prefix) === false)
                continue;

            $filePath = $path.$file;
            if (is_dir($filePath))
                continue;

            if ($time - filectime($filePath) > $lifetime)
                unlink($filePath);
        }
    }

    /**
     * Adds group action JS
     * @return void
     */
    public static function loadActionJS()
    {
        ?>
        <script type="text/javascript">
            var IPOLSDEK_actions = {
                fetchData: async function(data){
                    let body = new URLSearchParams();
                    let queryBuilder = function(parentKey, object) {
                        for (let key in object) {
                            if (typeof(object[key]) === 'function')
                                continue;

                            let propertyName = (parentKey.length > 0) ? parentKey + '[' + key + ']' : key;

                            if (typeof(object[key]) === 'string' || typeof(object[key]) === 'number') {
                                body.append(propertyName, object[key]);
                            } else if (typeof object[key] === 'object') {
                                queryBuilder(propertyName, object[key]);
                            }
                        }
                    };

                    data['isdek_token'] = '<?=\sdekHelper::getModuleToken()?>';
                    queryBuilder('', data);

                    let response = await fetch(
                        '/bitrix/js/<?=self::$MODULE_ID?>/ajax.php',
                        {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: body.toString()
                        });
                    if (response.ok) {
                        return await response.json();
                    } else {
                        return {success: false, errors: 'HTTP error ' + response.status, warnings: ''};
                    }
                },

                /* print invoice and barcode files */
                printer: {
                    done: false,
                    tryCount: 0,
                    tryMax: 10,
                    ordersData: false,
                    formType: '<?=PrintHandler::PRINT_FORM_ORDER?>',
                    onlyActs: true,
                    messages: '',
                    canShipment: <?=\CUtil::PhpToJSObject(\sdekHelper::canShipment());?>,

                    get: function(formType, onlyActs) {
                        IPOLSDEK_actions.printer.done = false;
                        IPOLSDEK_actions.printer.tryCount = 0;
                        IPOLSDEK_actions.printer.ordersData = false;
                        IPOLSDEK_actions.printer.formType = formType;
                        IPOLSDEK_actions.printer.onlyActs = onlyActs;
                        IPOLSDEK_actions.printer.messages = '';

                        let request = {data: [], isdek_action: 'getPrintOrdersRequest'};

                        /* this one stolen from Bitrix 'as is' */
                        let orderForm = document.form_tbl_sale_order;
                        let total = orderForm.elements.length;
                        for (let i = 0; i < total; i++) {
                            if (orderForm.elements[i].tagName.toUpperCase() == "INPUT"
                                && orderForm.elements[i].type.toUpperCase() == "CHECKBOX"
                                && orderForm.elements[i].name.toUpperCase() == "ID[]"
                                && orderForm.elements[i].checked == true) {
                                request.data.push(orderForm.elements[i].value);
                            }
                        }

                        BX.showWait();
                        IPOLSDEK_actions.fetchData(request).then(IPOLSDEK_actions.printer.onRequestPrintOrders).catch(error => console.log(error));
                    },

                    onRequestPrintOrders: function(data) {
                        if (data.success === true) {
                            IPOLSDEK_actions.printer.ordersData = data.data;

                            if (IPOLSDEK_actions.printer.formType === '<?=PrintHandler::PRINT_FORM_ORDER?>') {
                                IPOLSDEK_actions.printer.makeAct();
                            }

                            if (IPOLSDEK_actions.printer.onlyActs) {
                                IPOLSDEK_actions.printer.done = true;
                            } else {
                                IPOLSDEK_actions.printer.makeFile();
                            }
                        } else {
                            IPOLSDEK_actions.printer.done = true;

                            if (data.errors.length) {
                                IPOLSDEK_actions.printer.messages += "<br><br>" + data.errors;
                            }
                        }

                        if (data.warnings.length) {
                            IPOLSDEK_actions.printer.messages += "<br><br>" + data.warnings;
                        }

                        if (IPOLSDEK_actions.printer.done) {
                            IPOLSDEK_actions.printer.finish();
                        }
                    },

                    makeAct: function(){
                        let url = '/bitrix/js/<?=self::$MODULE_ID?>/printActs.php?';
                        let orders = IPOLSDEK_actions.printer.ordersData.order.join(':');
                        let shipments = IPOLSDEK_actions.printer.ordersData.shipment.join(':');

                        if (IPOLSDEK_actions.printer.canShipment)
                            url += 'orders=' + orders + '&shipments=' + shipments;
                        else
                            url += 'ORDER_ID=' + orders;

                        window.open(url);
                    },

                    makeFile: function(){
                        let request = {data: IPOLSDEK_actions.printer.ordersData};
                        switch (IPOLSDEK_actions.printer.formType) {
                            case '<?=PrintHandler::PRINT_FORM_ORDER?>': request['isdek_action'] = 'getInvoiceRequest'; break;
                            case '<?=PrintHandler::PRINT_FORM_BARCODE?>': request['isdek_action'] = 'getBarcodeRequest'; break;
                        }

                        IPOLSDEK_actions.fetchData(request).then(IPOLSDEK_actions.printer.onRequestFile).catch(error => console.log(error));
                    },

                    getFile: function(data){
                        let request = {data: data};
                        switch (IPOLSDEK_actions.printer.formType) {
                            case '<?=PrintHandler::PRINT_FORM_ORDER?>': request['isdek_action'] = 'getInvoiceByUuidRequest'; break;
                            case '<?=PrintHandler::PRINT_FORM_BARCODE?>': request['isdek_action'] = 'getBarcodeByUuidRequest'; break;
                        }

                        IPOLSDEK_actions.fetchData(request).then(IPOLSDEK_actions.printer.onRequestFile).catch(error => console.log(error));
                    },

                    onRequestFile: function(data){
                        if (data.success === true) {
                            if (typeof(data.files) !== 'undefined') {
                                for (let i in data.files) {
                                    window.open(data.files[i]);
                                }
                            }

                            if (typeof(data.notReady) !== 'undefined') {
                                if (++IPOLSDEK_actions.printer.tryCount > IPOLSDEK_actions.printer.tryMax) {
                                    IPOLSDEK_actions.printer.done = true;
                                    IPOLSDEK_actions.printer.messages += "<br><br>" + '<?=GetMessage('IPOLSDEK_JS_PR_MESS_MAXTRY')?>';
                                } else {
                                    /* wait a bit and try again */
                                    setTimeout(IPOLSDEK_actions.printer.getFile, 300, data.notReady);
                                }
                            } else {
                                IPOLSDEK_actions.printer.done = true;
                            }
                        } else {
                            IPOLSDEK_actions.printer.done = true;

                            if (data.errors.length) {
                                IPOLSDEK_actions.printer.messages += "<br><br>" + data.errors;
                            }
                        }

                        if (data.warnings.length) {
                            IPOLSDEK_actions.printer.messages += "<br><br>" + data.warnings;
                        }

                        if (IPOLSDEK_actions.printer.done) {
                            IPOLSDEK_actions.printer.finish();
                        }
                    },

                    finish: function(){
                        BX.closeWait();

                        if (IPOLSDEK_actions.printer.messages.length) {
                            let dialog = new BX.CDialog({
                                title: '<?=GetMessage('IPOLSDEK_JS_PR_WND_TITLE')?>',
                                content: '<?=GetMessage('IPOLSDEK_JS_PR_MESS_ERRORS')?>' + IPOLSDEK_actions.printer.messages,
                                icon: 'head-block',
                                resizable: true,
                                draggable: true,
                                height: '250',
                                width: '300',
                                buttons: [BX.CDialog.prototype.btnClose]
                            });
                            dialog.Show();
                        }
                    },
                },
            };
        </script>
        <?php
    }

    /**
     * Da 2.0 only
     * @param string $account
     * @param string $secure
     * @return SdekApplication
     */
    public static function makeApplication($account, $secure)
    {
        return new SdekApplication(
            $account,
            $secure,
            false,
            30,
            new encoder(),
            new cache()
        //, new \Ipolh\SDEK\Admin\IvanInlineLoggerController()
        );
    }
}