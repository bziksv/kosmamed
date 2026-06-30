<?
namespace Rbs\MoyskladStocks\Entity;

use Rbs\MoyskladStocks\Config;

class Product extends Base
{
    function checkUpdateHook()
    {
        if(Config::checkFeature('productprices')){
            if($this->loadPrices()){
                $this->checkPrices();
            }
        }
        
        if(Config::checkFeature('productstocks')){
            if($this->loadStocks()){
                $this->checkStocks();
            }
        }

        if(Config::checkFeature('import_product')){
            \CRbsMoyskladStocks::checkUpdateHook($this->getItemMs());
        }
    }
}