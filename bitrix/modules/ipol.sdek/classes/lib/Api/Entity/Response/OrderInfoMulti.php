<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use stdClass;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\OrderInfoMulti\OrderInfoList;

/**
 * Class OrderInfoMulti
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class OrderInfoMulti extends AbstractResponse
{
    /**
     * @var OrderInfoList
     */
    protected $orderInfoList;

    /**
     * OrderInfoMulti constructor
     * @param $data
     * @throws BadResponseException
     */
    public function __construct($data)
    {
        // NO PARENT CONSTRUCTOR called due to specific multi GET answer format

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
                    $error->code    = 'ORDER_INFO_MULTI_BAD_SERVER_ANSWER';
                    $error->message = $val['response'];

                    $tmp = new stdClass();
                    $tmp->errors = [$error];
                } else {
                    $tmp = $response;
                }

                // Adds HTTP status of this request in multi GET for debug reasons
                $tmp->http_status = $val['code'];

                // Adds CDEK order number to response
                $tmp->cdek_number = $val['request']['cdek_number'];

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
     * @return OrderInfoList
     */
    public function getOrderInfoList()
    {
        return $this->orderInfoList;
    }

    /**
     * @param array $array
     * @return OrderInfoMulti
     * @throws BadResponseException
     */
    public function setOrderInfoList(array $array)
    {
        $collection = new OrderInfoList();
        $this->orderInfoList = $collection->fillFromArray($array);
        return $this;
    }

    public function setFields($fields)
    {
        return parent::setFields(['orderInfoList' => $fields]);
    }
}