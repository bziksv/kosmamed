<?php
namespace Rbs\MoySklad\Entity;

use \Rbs\Moysklad\ApiNew;
use \Rbs\Moysklad\Utils;

class TaxRate
{
    public static function getTaxRateArray()
    {
		$result = [];
        $taxRate = ApiNew::get('/entity/taxrate', ['filter' => 'archived=false']);

        if(Utils::is_success($taxRate)) {
            foreach($taxRate->rows as $taxRateItem) {
                $result[$taxRateItem->id] = $taxRateItem->rate;
            }
        }

        return $result;
    }
}