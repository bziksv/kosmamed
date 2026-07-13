<?
namespace Rbs\MoyskladStocks\Entity;

use Rbs\MoyskladStocks\Config;

class Bundle extends Base
{
    function checkUpdateHook()
    {        
        if(Config::checkFeature('bundleprices')){
            if($this->loadPrices()){
                $this->checkPrices();
            }
        }
        if(Config::checkFeature('bundlestocks')){
            $this->checkStocks();
        }
        if(Config::checkFeature('import_bundle')){
            \CRbsMoyskladStocks::checkUpdateHook($this->getItemMs());
        }
    }

    function checkStocks()
    {
        if(!$this->isLoaded()) return false;
        $logger = new \Rbs\MoyskladStocks\Debug\Loger();
        \Rbs\MoyskladStocks\Import\Type\Stocks::update_bundle_stocks([$this->getItemMs()], $logger);
    }
}