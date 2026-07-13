<?php
namespace Ipolh\SDEK\Api\Entity\Response;

class ErrorResponse extends AbstractResponse
{
    public function __construct($json)
    {
        parent::__construct(['temp_test']); //ToDo make actual class by api info
    }
}