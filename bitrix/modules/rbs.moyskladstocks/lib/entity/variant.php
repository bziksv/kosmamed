<?
namespace Rbs\MoyskladStocks\Entity;

use Rbs\MoyskladStocks\Config;

class Variant extends Base
{
    function checkUpdateHook()
    {
        if(Config::checkFeature('variantprices')){
            if($this->loadPrices()){
                $this->checkPrices();
            }
        }
        
        if(Config::checkFeature('variantstocks')){
            if($this->loadStocks()){
                $this->checkStocks();
            }
        }

        if(Config::checkFeature('import_variant')){
            \CRbsMoyskladStocks::checkUpdateHook($this->getItemMs());
        }
    }
}