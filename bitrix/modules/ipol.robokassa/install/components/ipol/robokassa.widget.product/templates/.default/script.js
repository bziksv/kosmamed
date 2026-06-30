

    function handleRobokassaBadgeCheckout()
    {
        return false;
    }

    function handleRobokassaWidgetCheckout()
    {
        return false;
    }

    const RobokassaTargetSkuCallback = function (mutationsList, observer)
    {

        if(robokassaWidgetOptions.useType === 0)
        {
            return;
        }

        let offer = window[robokassaWidgetOptions.skuVariable]['offers'][window[robokassaWidgetOptions.skuVariable].offerNum];

        let boxContainer = document.getElementById('robokassa-widget-box');
        let parent = document.createElement('div');

        let node = null;

        let params = {...robokassaWidgetOptions.params};
        params.outSum = parseFloat(offer['ITEM_PRICES'][offer['ITEM_PRICE_SELECTED']]['PRICE']);

        let attributes = new Map(Object.entries(params));

        if(robokassaWidgetOptions.useType === 1)
        {
            /**
             * badge
             */
            document.querySelectorAll('#robokassa-widget-box .js-robokassa-badge-box')
                .forEach(element => element.remove());


            parent.classList.add('js-robokassa-badge-box');
            node = document.createElement('robokassa-badge');
        }

        if(robokassaWidgetOptions.useType === 2)
        {
            /**
             * widget
             */
            document.querySelectorAll('#robokassa-widget-box .js-robokassa-widget-box')
                .forEach(element => element.remove());

            parent.classList.add('js-robokassa-widget-box');
            node = document.createElement('robokassa-widget');
        }

        if(node === null)
        {
            return;
        }

        attributes.forEach((value, key) => node.setAttribute(key, value));

        parent.appendChild(node);
        boxContainer.appendChild(parent);

        window.initRobokassaBadges();
    };

    document.addEventListener(
        "DOMContentLoaded",
        function(event)
        {

            if(robokassaWidgetOptions.skuVariable === null)
            {
                window.initRobokassaBadges();
                return;
            }

            if(window[robokassaWidgetOptions.skuVariable] === undefined)
            {
                return;
            }

            const observer = new MutationObserver(RobokassaTargetSkuCallback);

            observer.observe(
                window[robokassaWidgetOptions.skuVariable].obTree,
                {
                    attributes: true,
                    childList: true,
                    subtree: true
                }
            );

            RobokassaTargetSkuCallback();
        }
    );