<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

CBoxberry::addWidgetJs();

Loc::loadMessages(__FILE__);

$bxbModuleId = 'up.boxberrydelivery';

$bxbPvzProfileIds = CDeliveryBoxberry::getPvzDeliveryIds();

$bxbAddress = trim(Option::get($bxbModuleId, 'BB_ADDRESS'));

$bxbCustomLink = trim(Option::get($bxbModuleId, 'BB_CUSTOM_LINK'));

$bxbLinkStyle = Option::get($bxbModuleId, 'BB_BUTTON') === 'Y' ? '.bxbbutton' : '.bxblink';

$adminBoxberry = true;

if ($arOrder = CSaleOrder::GetByID($_REQUEST['orderId'])) {
    if (strpos($arOrder['DELIVERY_ID'], 'boxberry') === false) {
        $adminBoxberry = false;
    }
}

?>
<? if ($adminBoxberry){?>
<script>
    let bxbWidgetHtml = false;
    let componentObjectCache;
    let componentActive = false;
    let bxbSelectedPvzAddress = false;
    let isBxbPvzDelChecked = false;
    let bxbAddrField = false;
    let bbCustomLink = '<?=$bxbCustomLink?>';
    let bbAjaxUrl = '/bitrix/js/up.boxberrydelivery/ajax.php';
    let bbDiv = 'boxberrySelectPvzWidget';


    function delivery(result) {
        if (typeof result !== 'undefined') {
            bxbAjaxPost(bbAjaxUrl, {save_pvz_id: result.id, disable_check_pvz: 1})
                .then(function () {
                    if (componentActive && componentObjectCache.TOTAL.DELIVERY_PRICE != result.price) {
                        BX.Sale.OrderAjaxComponent.sendRequest()
                    } else {
                        checkSelectPvz()
                    }
                })
            bxbSelectedPvzAddress = result.address + " #" + result.id;
            bxbAddrField ? (document.getElementById(bxbAddrField).value = bxbSelectedPvzAddress) : '';
        }
    }

    function checkSelectPvz() {
        if (document.getElementById(bbCustomLink)) {
            if (document.getElementById(bbDiv)) {
                document.getElementById(bbDiv).innerHTML = '';
            }
            bxbGetLink()
        } else {
            bxbSetPvzAddress()
        }

        if (componentActive) {
            bxbAddrField = getBxbAddress();
            if (document.getElementById(bxbAddrField)) {
                document.getElementById(bxbAddrField).setAttribute('readonly', true)
            }
        }
    }

    function bxbGetLink() {
        let profile = componentObjectCache.DELIVERY.find(deliveries => deliveries.CHECKED === 'Y');
        if (profile !== 'undefined') {
            bxbAjaxPost(bbAjaxUrl, {get_link: profile.ID})
                .then(function () {
                    if (bxbWidgetHtml) {
                        document.getElementById(bbCustomLink).innerHTML = bxbWidgetHtml
                    }
                })
                .then(bxbSetPvzAddress)
        }
    }

    function bxbSetPvzAddress(){
        if (bxbSelectedPvzAddress) {
            document.querySelectorAll('<?=$bxbLinkStyle?>').forEach(el => {
                el.innerHTML = bxbSelectedPvzAddress
            });
        }
    }

    function afterFormReload() {
        try {
            if (typeof BX.Sale.OrderAjaxComponent.result !== 'undefined') {
                componentObjectCache = BX.Sale.OrderAjaxComponent.result;
                componentActive = true;
                isBxbPvzDelChecked = bxbPvzDeliveryChecked();
                if (isBxbPvzDelChecked) {
                    checkSelectPvz()
                } else if (document.getElementById(bbDiv)) {
                    document.getElementById(bbDiv).innerHTML = '';
                } else {
                    return false;
                }
            }
        } catch (e) {
            console.log('BX.Sale.OrderAjaxComponent не найден')
        }
    }

    async function bxbAjaxPost(url, data) {
        await fetch(url, {
            method: 'POST',
            body: new URLSearchParams(data)
        })
            .then((response) => {
                return response.text();
            })
            .then((data) => {
                bxbWidgetHtml = data
            });
    }

    document.addEventListener(`DOMContentLoaded`, function () {
        bxbAjaxPost(bbAjaxUrl, {remove_pvz: 1, check_pvz: 1})
        try {
            if (typeof BX !== 'undefined') {
                BX.addCustomEvent('onAjaxSuccess', afterFormReload);
                BX.onCustomEvent('onAjaxSuccess', afterFormReload);
            }
        } catch (e) {
            console.log('BX не найден')
        }
    })


    function bxbPvzDeliveryChecked() {
        if (typeof componentObjectCache.DELIVERY !== 'undefined') {
            let deliveries = componentObjectCache.DELIVERY;
            for (let i = 0; i < deliveries.length; i++) {
                if (deliveries[i].CHECKED === 'Y' && <?=CUtil::PhpToJSObject(
                    $bxbPvzProfileIds)?>.indexOf(deliveries[i].ID) >= 0) {
                    return true;
                }
            }
        }
        return false;
    }

    function getBxbAddress() {
        if (typeof componentObjectCache.ORDER_PROP.properties !== 'undefined') {
            let properties = componentObjectCache.ORDER_PROP.properties;
            for (let i = 0; i < properties.length; i++) {
                if (properties[i].ACTIVE === 'Y' && (properties[i].CODE === <?=CUtil::PhpToJSObject(
                    $bxbAddress
                )?> || properties[i].NAME.indexOf('<?=Loc::getMessage('BB_SEARCH_ADDR_FIELD')?>') >= 0)) {
                    return 'soa-property-' + properties[i].ID;
                }
            }
        }
        return false;
    }

</script>
<?}?>