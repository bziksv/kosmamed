<?php
namespace Ipolh\SDEK\Bitrix\Controller;

use Ipolh\SDEK\Api\Entity\Request\IntakesMake;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\Legacy\transitApplication;
use Ipolh\SDEK\SDEK\SdekApplication;

class CourierCall extends abstractController
{
    use ControllerHelpers;

    /**
     * @param SdekApplication|transitApplication|null $application
     */
    public function __construct($application = false)
    {
        parent::__construct($application);
    }

    /**
     * Send CourierCall request, also try to get info
     * @param IntakesMake $request
     * @param int $sleep Delay between make and info calls for 2.0
     * @return Result
     */
    public function sendCourierCall($request, $sleep = 0)
    {
        $result = new Result();

        if ($this->application instanceof SdekApplication) {
            // Async API 2.0 street magic

            // Da Make
            $answer = $this->application->intakesMake($request);
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
                } else if ($errors = $response->getErrors()) {
                    // Undocumented type of errors
                    $errors->reset();
                    while ($error = $errors->getNext()) {
                        $result->addError(new Error($error->getMessage(), $error->getCode()));
                    }
                } else {
                    $result->addError(new Error('Unknown data object has invaded from server.'));
                }

                $result->setData($dataMake);
            } else {
                $this->collectApplicationErrors($result,  __FUNCTION__);
            }

            // Get Info
            if ($result->isSuccess()) {
                // Delay before second API call, because of async methods
                $sleep = (int)$sleep;
                if ($sleep > 0)
                    sleep($sleep);

                // Unmake warnings because they come again in CourierCall::getCourierCallInfo() answer
                // $result->getWarnings()->clear();

                $getInfoResult = $this->getCourierCallInfo($result->getData()['UUID']);

                // Warnings always added here, no matter of result status
                $result->addWarnings($getInfoResult->getWarnings());

                if ($getInfoResult->isSuccess()) {
                    $result->setData(array_merge($result->getData(), $getInfoResult->getData()));
                } else {
                    $result->addErrors($getInfoResult->getErrors());
                }
            }
        } else {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        return $result;
    }

    /**
     * Get CourierCall info for given UUID
     * @param string $uuid
     * @return Result
     */
    public function getCourierCallInfo($uuid)
    {
        $result = new Result();

        if (empty($uuid)) {
            $result->addError(new Error('No UUID of CourierCall creation request given.'));
        }

        if (!($this->application instanceof SdekApplication)) {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        if ($result->isSuccess()) {
            $answer = $this->application->intakesInfo($uuid);
            if ($answer->isSuccess()) {
                $response = $answer->getResponse();
                $data = ['INTAKE_NUMBER' => null, 'STATUSES' => []];

                if ($entity = $response->getEntity()) {
                    $data['INTAKE_NUMBER'] = $entity->getIntakeNumber();

                    $entity->getStatuses()->reset();
                    while ($status = $entity->getStatuses()->getNext()) {
                        $data['STATUSES'][] = [
                            'DATETIME' => $status->getDateTime()->getTimestamp(),
                            'STATUS' => $status->getCode()
                        ];
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
                } else if ($errors = $response->getErrors()) {
                    // Undocumented type of errors
                    $errors->reset();
                    while ($error = $errors->getNext()) {
                        $result->addError(new Error($error->getMessage(), $error->getCode()));
                    }
                } else {
                    $result->addError(new Error('Unknown data object has invaded from server.'));
                }

                $result->setData($data);
            } else {
                $this->collectApplicationErrors($result,  __FUNCTION__);
            }
        }

        return $result;
    }

    /**
     * Delete CourierCall by given UUID
     * @param string $uuid
     * @return Result
     */
    public function deleteCourierCall($uuid)
    {
        $result = new Result();

        if (empty($uuid)) {
            $result->addError(new Error('No UUID of CourierCall creation request given.'));
        }

        if (!($this->application instanceof SdekApplication)) {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        if ($result->isSuccess()) {
            $answer = $this->application->intakesDelete($uuid);
            if ($answer->isSuccess()) {
                $response = $answer->getResponse();

                if ($requestsList = $response->getRequests()) {
                    $processRequestResult = $this->processRequestList($requestsList);
                    $data['DELETE_LAST_STATE_CODE'] = $processRequestResult->getData()['LAST_STATE_CODE'];
                    $data['DELETE_LAST_STATE_DATE'] = $processRequestResult->getData()['LAST_STATE_DATE'];

                    if (!$processRequestResult->getErrors()->isEmpty())
                        $result->addErrors($processRequestResult->getErrors());

                    if (!$processRequestResult->getWarnings()->isEmpty())
                        $result->addWarnings($processRequestResult->getWarnings());
                } /* else if ($errors = $response->getErrors()) {
                    // Undocumented type of errors
                    $errors->reset();
                    while ($error = $errors->getNext()) {
                        $result->addError(new Error($error->getMessage(), $error->getCode()));
                    }
                } */ else {
                    $result->addError(new Error('Unknown data object has invaded from server.'));
                }

                $result->setData($data);
            } else {
                $this->collectApplicationErrors($result,  __FUNCTION__);
            }
        }

        return $result;
    }
}