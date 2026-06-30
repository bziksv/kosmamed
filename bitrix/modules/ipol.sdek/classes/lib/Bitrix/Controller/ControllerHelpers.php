<?php
namespace Ipolh\SDEK\Bitrix\Controller;

use Ipolh\SDEK\Api\Entity\Response\Part\Common\RequestList;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;

/**
 * Some stuff for API 2.0
 */
trait ControllerHelpers
{
    /**
     * Process RequestList and returns last state and collection of errors and warnings if exists
     *
     * @param RequestList|null $requestList
     * @return Result
     */
    public function processRequestList($requestList)
    {
        $result = new Result();
        $data   = ['LAST_STATE_CODE' => 'UNKNOWN', 'LAST_STATE_DATE' => null];

        if ($requestList) {
            // The last state comes first in the server response
            if ($firstRequest = $requestList->getFirst()) {
                $data['LAST_STATE_CODE'] = $firstRequest->getState();
                $data['LAST_STATE_DATE'] = $firstRequest->getDateTime()->getTimestamp();
            }

            $requestList->reset();
            while ($request = $requestList->getNext()) {
                if ($errors = $request->getErrors()) {
                    $errors->reset();
                    while ($error = $errors->getNext()) {
                        $result->addError(new Error($error->getMessage(), $error->getCode()));
                    }
                }

                if ($warnings = $request->getWarnings()) {
                    $warnings->reset();
                    while ($warning = $warnings->getNext()) {
                        $result->addWarning(new Warning($warning->getMessage(), $warning->getCode()));
                    }
                }
            }
        } else {
            $result->addError(new Error('Empty RequestList returns in server answer.'));
        }

        $result->setData($data);

        return $result;
    }

    /**
     * Process application error collection and add errors messages to result
     *
     * @param Result $result
     * @param string $method
     */
    public function collectApplicationErrors(&$result, $method)
    {
        if ($this->application->getErrorCollection()) {
            $this->application->getErrorCollection()->reset();
            while ($error = $this->application->getErrorCollection()->getNext()) {
                $result->addError(new Error($error->getMessage()));
            }
        } else {
            $result->addError(new Error('Error while requests \"'.$method.'\", but no error message was received from Application.'));
        }
    }
}