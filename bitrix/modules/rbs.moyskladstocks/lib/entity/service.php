<?
namespace Rbs\MoyskladStocks\Entity;

use Rbs\MoyskladStocks\Config;

class Service extends Base
{
    function checkUpdateHook()
    {
        if(Config::checkFeature('serviceprices')){
            if($this->loadPrices()){
                $this->checkPrices();
            }
        }

        if(Config::checkFeature('import_service')){
            \CRbsMoyskladStocks::checkUpdateHook($this->getItemMs());
        }
    }
}