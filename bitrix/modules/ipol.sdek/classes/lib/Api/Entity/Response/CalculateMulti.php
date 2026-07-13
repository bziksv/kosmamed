<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use stdClass;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\CalculateMulti\TariffList;

/**
 * Class CalculateMulti
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class CalculateMulti extends AbstractResponse
{
    /**
     * @var TariffList
     */
    protected $tariffList;

    /**
     * CalculateMulti constructor
     * @param $data
     * @throws BadResponseException
     */
    public function __construct($data)
    {
        // NO PARENT CONSTRUCTOR called due to specific multi POST answer format

        $this->origin = $data;

        if (empty($data)) {
            throw new BadResponseException('Empty server answer '.__CLASS__);
        }

        if (is_array($data)) {
            $prepared = [];
            foreach ($data as $val) {
                $response = json_decode($val['response']);
                if (is_null($response) || json_last_error() !== JSON_ERROR_NONE) {
                    // Something wrong if response not JSON
                    $error = new stdClass();
                    $error->code    = "CALCULATE_MULTI_BAD_SERVER_ANSWER";
                    $error->message = $val['response'];

                    $tmp = new stdClass();
                    $tmp->errors = [$error];
                } else {
                    $tmp = $response;
                }

                // Adds HTTP status of this request in multi POST for debug reasons
                $tmp->http_status = $val['code'];

                // Adds CDEK tariff number to response
                $request = json_decode($val['request']);
                $tmp->tariff_code = $request->tariff_code;

                $prepared[] = $tmp;
            }

            if (empty($prepared)) {
                throw new BadResponseException('Incorrect data format ' . __CLASS__);
            }

            $this->setDecoded($prepared);
            if (is_null($this->decoded)) {
                throw new BadResponseException('Incorrect server answer (fail to decode) ' . __CLASS__);
            }
        } else {
            throw new BadResponseException('Unknown data format '.__CLASS__);
        }
    }

    /**
     * @return TariffList
     */
    public function getTariffList()
    {
        return $this->tariffList;
    }

    /**
     * @param array $array
     * @return CalculateMulti
     * @throws BadResponseException
     */
    public function setTariffList(array $array)
    {
        $collection = new TariffList();
        $this->tariffList = $collection->fillFromArray($array);
        return $this;
    }

    public function setFields($fields)
    {
        return parent::setFields(['tariffList' => $fields]);
    }
}