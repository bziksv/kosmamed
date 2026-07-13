<?php

	use Bitrix\Main\Application;
	use Bitrix\Main\Config\Option;
	use Bitrix\Main\Localization\Loc;
	use Ipol\Robokassa;

	global $USER, $APPLICATION;

	if (!$USER->isAdmin())
    {
        $APPLICATION->authForm('Nope');
    }

	/** @var \Bitrix\Main\HttpRequest $request */
	$request = Application::getInstance()->getContext()->getRequest();

	\Bitrix\Main\Loader::IncludeModule("ipol.robokassa");

	Loc::loadMessages(__FILE__);

    $sites = \Bitrix\Main\SiteTable::getList()->fetchAll();

	$tabControl = new CAdminTabControl("tabControl", []);

    if(\Bitrix\Main\Loader::IncludeModule("sale"))
    {
        $tabControl->tabs = \array_merge(
            $tabControl->tabs,
            [
                [
                    "DIV" => "edit1",
                    "TAB" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_CHECK_TITLE"),
                    "TITLE" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_CHECK_TITLE"),
                ],
                [
                    "DIV" => "edit2",
                    "TAB" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_SECOND_CHECK_TITLE"),
                    "TITLE" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_SECOND_CHECK_TITLE"),
                ],
                [
                    "DIV" => "edit3",
                    "TAB" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_TWO_STAGE_PAYMENT"),
                    "TITLE" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_TWO_STAGE_PAYMENT"),
                ],
                [
                    "DIV" => "edit4",
                    "TAB" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET"),
                    "TITLE" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET"),
                ],
                [
                    "DIV" => "edit5",
                    "TAB" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.TITLE"),
                    "TITLE" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.TITLE"),
                ],
            ]
        );

        /** @var array[] $statuses */
        $statuses = \Bitrix\Sale\Internals\StatusLangTable::getList(
            [
                'order' => ['STATUS.SORT' => 'ASC'],
                'filter' => ['STATUS.TYPE' => 'O', 'LID' => LANGUAGE_ID],
                'select' => ['STATUS_ID', 'NAME', 'DESCRIPTION']
            ]
        )->fetchAll();
    }

    if(!\Bitrix\Main\Loader::IncludeModule("sale"))
    {
        $tabControl->tabs = \array_merge(
            $tabControl->tabs,
            [
                [
                    "DIV" => "edit10",
                    "TAB" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START_TAB"),
                    "TITLE" => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START_TITLE"),
                ],
            ]
        );
    }

	if (
		(!empty($save) || !empty($restore))
		&& $request->isPost()
		&& check_bitrix_sessid())
	{

        if(\Bitrix\Main\Loader::IncludeModule("sale"))
        {
            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY',
                $request->getPost('EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY') === 'Y' ? 'Y' : 'N'
            );
            
            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'SECOND_CHECK_STATUS_ID',
                $request->getPost('SECOND_CHECK_STATUS_ID')
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'SECOND_CHECK_PROPERTY_CODE',
                $request->getPost('SECOND_CHECK_PROPERTY_CODE')
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'REPLACE_0_VAT',
                $request->getPost('REPLACE_0_VAT') === 'Y' ? 'Y' : 'N'
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'REPLACE_10_VAT',
                $request->getPost('REPLACE_10_VAT') === 'Y' ? 'Y' : 'N'
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'REPLACE_20_VAT',
                $request->getPost('REPLACE_20_VAT') === 'Y' ? 'Y' : 'N'
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'REPLACE_22_VAT',
                $request->getPost('REPLACE_22_VAT') === 'Y' ? 'Y' : 'N'
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'USE_TWO_STAGE_PAYMENT',
                $request->getPost('USE_TWO_STAGE_PAYMENT') === 'Y' ? 'Y' : 'N'
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'TAB_TWO_STAGE_PAYMENT_STATUS_ID',
                $request->getPost('TAB_TWO_STAGE_PAYMENT_STATUS_ID')
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
                (int) $request->getPost('IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT')
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'ENABLE_PAYMENT_BY_ORDER_ID',
                $request->getPost('ENABLE_PAYMENT_BY_ORDER_ID') === 'Y' ? 'Y' : 'N'
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'CHANGE_SITE_STATUS',
                \htmlspecialchars(
                    \json_encode(
                        $request->getPost('CHANGE_SITE_STATUS') ?? [],
                        JSON_THROW_ON_ERROR
                    )
                )
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'REFUND_PAYMENT_STATUSES',
                \htmlspecialchars(
                    \json_encode(
                        $request->getPost('REFUND_PAYMENT_STATUSES') ?? [],
                        JSON_THROW_ON_ERROR
                    )
                )
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'IPOL_ROBOKASSA_OPTIONS_ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE',
                $request->getPost('ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE')
            );

            $connection = \Bitrix\Main\Application::getConnection();

            $twoStagePaymentHandlerSource = new Bitrix\Main\IO\File(__DIR__ . '/install/tools/sale_ps_robokassa_two_stage_payment.php');
            $twoStagePaymentHandlerDist = new Bitrix\Main\IO\File($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tools/sale_ps_robokassa_two_stage_payment.php');

            if(
                Option::get(
                    Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                    'IPOL_ROBOKASSA_OPTIONS_ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE',
                    'N'
                ) === 'Y'
            )
            {
                \Ipol\Robokassa\Options::installIblockProperties();
            }

            if(Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'USE_TWO_STAGE_PAYMENT', 'N') === 'Y')
            {
                \Bitrix\Main\EventManager::getInstance()->registerEventHandlerCompatible(
                    'main',
                    'OnAdminSaleOrderView',
                    Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                    Ipol\Robokassa\RobokassaTwoStagePayment::class,
                    'init'
                );

                if(!$connection->isTableExists(\Ipol\Robokassa\Internals\TwoStagePaymentTable::getEntity()->getDBTableName()))
                {
                    \Ipol\Robokassa\Internals\TwoStagePaymentTable::getEntity()->createDbTable();
                }

                if(!$twoStagePaymentHandlerDist->isExists())
                {

                    $twoStagePaymentHandlerDist->open('w');

                    $twoStagePaymentHandlerDist->putContents(
                        $twoStagePaymentHandlerSource->getContents()
                    );

                    $twoStagePaymentHandlerDist->close();
                }
            }
            else
            {

                $events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers('main', 'OnAdminSaleOrderEdit');

                if(
                    $connection->isTableExists(\Ipol\Robokassa\Internals\TwoStagePaymentTable::getEntity()->getDBTableName())
                    && \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest()->getPost('USE_TWO_STAGE_PAYMENT_CLEAR_DB') === 'Y'
                )
                {
                    $connection->dropTable(\Ipol\Robokassa\Internals\TwoStagePaymentTable::getEntity()->getDBTableName());
                }

                if($twoStagePaymentHandlerDist->isExists())
                {
                    $twoStagePaymentHandlerDist->delete();
                }

                foreach ($events as $event)
                {
                    if(
                        $event['TO_MODULE_ID'] === Ipol\Robokassa\RobokassaPaymentService::$moduleId
                        && $event['TO_CLASS'] === Ipol\Robokassa\RobokassaTwoStagePayment::class
                        && $event['TO_METHOD'] === 'init'
                    )
                    {
                        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
                            'main',
                            'OnAdminSaleOrderView',
                            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                            Ipol\Robokassa\RobokassaTwoStagePayment::class,
                            'init'
                        );
                    }
                }
            }

            $widgetConfiguration = $request->getPost('WIDGET');

            $widgetConfiguration['BADGE_SHOW_LOGO'] = isset($widgetConfiguration['BADGE_SHOW_LOGO']) && $widgetConfiguration['BADGE_SHOW_LOGO'] === 'Y' ? 'Y' : 'N';
            $widgetConfiguration['WIDGET_SHOW_LOGO'] = isset($widgetConfiguration['WIDGET_SHOW_LOGO']) && $widgetConfiguration['WIDGET_SHOW_LOGO'] === 'Y' ? 'Y' : 'N';
            $widgetConfiguration['WIDGET_HAS_SECOND_LINE'] = isset($widgetConfiguration['WIDGET_HAS_SECOND_LINE']) && $widgetConfiguration['WIDGET_HAS_SECOND_LINE'] === 'Y' ? 'Y' : 'N';

            Robokassa\RobokassaWidget::updateConfiguration($widgetConfiguration);
        }

        if(!\Bitrix\Main\Loader::IncludeModule("sale"))
        {

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'ENABLE_START_FUNCTION',
                $request->getPost('ENABLE_START_FUNCTION') ?? 'N'
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'START_FUNCTION_IBLOCK_ID',
                $request->getPost('START_FUNCTION_IBLOCK_ID')
            );

            Ipol\Robokassa\Start\Configuration::createIblockProperties((int) $request->getPost('START_FUNCTION_IBLOCK_ID'));

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'START_FUNCTION_COMPONENT_SEF_FOLDER',
                $request->getPost('START_FUNCTION_COMPONENT_SEF_FOLDER')
            );

            Option::set(
                Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                'START_FUNCTION_COMPONENT_SEF_MODE',
                $request->getPost('START_FUNCTION_COMPONENT_SEF_MODE') ?? 'N'
            );

            if($request->getPost('ENABLE_START_FUNCTION') ?? 'N' === 'Y')
            {
                Ipol\Robokassa\Start\Configuration::registerPageProductHandler();
            }
            else
            {
                Ipol\Robokassa\Start\Configuration::unregisterPageProductHandler();
            }

            foreach($request->getPostList() as $key => $value)
            {
                if(\str_contains($key, 'START_FUNCTION_OPTIONS'))
                {
                    Option::set(Ipol\Robokassa\RobokassaPaymentService::$moduleId, $key, $value);
                }
            }

            foreach($request->getPostList() as $key => $value)
            {
                if(\str_contains($key, 'START_FUNCTION_BUTTON_OPTIONS'))
                {
                    Option::set(Ipol\Robokassa\RobokassaPaymentService::$moduleId, $key, $value);
                }
            }

            if($request->getPost('START_FUNCTION_OPTIONS_TEST') !== 'Y')
            {
                Option::set(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'START_FUNCTION_OPTIONS_TEST', 'N');
            }
        }

        \LocalRedirect($APPLICATION->GetCurPageParam());
	}

    if(\Bitrix\Main\Loader::IncludeModule("sale"))
    {

        $widget = Robokassa\RobokassaWidget::loadConfiguration();

        $secondCheckStatus = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'SECOND_CHECK_STATUS_ID');
        $secondCheckProperty = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'SECOND_CHECK_PROPERTY_CODE');

        $useProductObjectType = Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'IPOL_ROBOKASSA_OPTIONS_ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE',
            'N'
        ) === 'Y';

        $explodeRecipientProduct = Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY',
            'N'
        ) === 'Y';

        $replace0Vat = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'REPLACE_0_VAT', '');
        $replace5Vat = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'REPLACE_5_VAT', '');
        $replace7Vat = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'REPLACE_7_VAT', '');
        $replace10Vat = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'REPLACE_10_VAT', '');
        $replace20Vat = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'REPLACE_20_VAT', '');
        $replace22Vat = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'REPLACE_22_VAT', '');

        $useTowStagePayment = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'USE_TWO_STAGE_PAYMENT', 'N') === 'Y';
        $useTowStagePaymentStatus = Option::get(Ipol\Robokassa\RobokassaPaymentService::$moduleId, 'TAB_TWO_STAGE_PAYMENT_STATUS_ID', '');

        try
        {
            $changeSiteStatus = \json_decode(
                \htmlspecialcharsback(
                    Option::get(
                        Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                        'CHANGE_SITE_STATUS',
                        null
                    )
                ),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if(!is_array($changeSiteStatus))
            {
                $changeSiteStatus = [];
            }
        }
        catch(\Exception $e)
        {
            $changeSiteStatus = [];
        }
        try
        {

            $refundPaymentStatuses = \json_decode(
                \htmlspecialcharsback(
                    Option::get(
                        Ipol\Robokassa\RobokassaPaymentService::$moduleId,
                        'REFUND_PAYMENT_STATUSES',
                        null
                    )
                ),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if(!is_array($refundPaymentStatuses))
            {
                $refundPaymentStatuses = [];
            }
        }
        catch(\Exception $e)
        {
            $refundPaymentStatuses = [];
        }

        $paymentIdModificationCount = (int) Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
            0
        );

        $enablePaymentByOrderId = Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'ENABLE_PAYMENT_BY_ORDER_ID',
            'N'
        );
    }

    if(!\Bitrix\Main\Loader::IncludeModule("sale"))
    {

        $enableStartFunction = Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'ENABLE_START_FUNCTION',
            'N'
        ) === 'Y';

        $startFunctionComponentSefMode = Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'START_FUNCTION_COMPONENT_SEF_MODE',
            'N'
        ) === 'Y';

        $startFunctionComponentSefFolder = Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'START_FUNCTION_COMPONENT_SEF_FOLDER'
        );

        $selectStartIblock = (int) Option::get(
            Ipol\Robokassa\RobokassaPaymentService::$moduleId,
            'START_FUNCTION_IBLOCK_ID',
            0
        );

        if(\Bitrix\Main\Loader::includeModule("iblock"))
        {
            $iblocks = \Bitrix\Iblock\IblockTable::getList(
                [
                    'filter' => [
                        'ACTIVE' => 'Y',
                    ]
                ]
            )->fetchAll();
        }

        $startFunctionOptions = [];
        $startFunctionButtonOptions = [];

        foreach(Option::getForModule('ipol.robokassa') as $key => $value)
        {

            if(\str_contains($key, 'START_FUNCTION_OPTIONS'))
            {
                $startFunctionOptions[strtr($key, ['START_FUNCTION_' => ''])] = $value;
            }

            if(\str_contains($key, 'START_FUNCTION_BUTTON_OPTIONS'))
            {
                $startFunctionButtonOptions[strtr($key, ['START_FUNCTION_BUTTON_' => ''])] = $value;
            }
        }
    }

    $tabControl->begin();
?>

<form method="post" action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">
	<?php echo bitrix_sessid_post();?>

    <?php if(\Bitrix\Main\Loader::IncludeModule("sale")):?>

        <?php $tabControl->BeginNextTab(); ?>

        <tr>
            <td width="40%">
                <input type="checkbox" id="REPLACE_0_VAT" name="REPLACE_0_VAT" value="Y"<?php if($replace0Vat == "Y") echo " checked"?>>
            </td>
            <td width="60%">
                <label for="REPLACE_0_VAT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_REPLACE_0_VAT');?>
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <input type="checkbox" id="REPLACE_5_VAT" name="REPLACE_5_VAT" value="Y"<?php if($replace5Vat == "Y") echo " checked"?>>
            </td>
            <td>
                <label for="REPLACE_5_VAT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_REPLACE_5_VAT');?>
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <input type="checkbox" id="REPLACE_7_VAT" name="REPLACE_7_VAT" value="Y"<?php if($replace7Vat == "Y") echo " checked"?>>
            </td>
            <td>
                <label for="REPLACE_7_VAT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_REPLACE_7_VAT');?>
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <input type="checkbox" id="REPLACE_10_VAT" name="REPLACE_10_VAT" value="Y"<?php if($replace10Vat == "Y") echo " checked"?>>
            </td>
            <td>
                <label for="REPLACE_10_VAT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_REPLACE_10_VAT');?>
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <input type="checkbox" id="REPLACE_20_VAT" name="REPLACE_20_VAT" value="Y"<?php if($replace20Vat == "Y") echo " checked"?>>
            </td>
            <td>
                <label for="REPLACE_20_VAT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_REPLACE_20_VAT');?>
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <input type="checkbox" id="REPLACE_22_VAT" name="REPLACE_22_VAT" value="Y"<?php if($replace22Vat == "Y") echo " checked"?>>
            </td>
            <td>
                <label for="REPLACE_22_VAT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_REPLACE_22_VAT');?>
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <input type="checkbox" id="EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY" name="EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY" value="Y"<?php if($explodeRecipientProduct) echo " checked"?>>
            </td>
            <td>
                <label for="EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY');?>
                </label>
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <div class="adm-info-message-wrap" align="center">
                    <div class="adm-info-message" style="text-align: left">
                        <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_BOTTOM_DESCRIPTION');?>
                    </div>
                </div>
            </td>
        </tr>
        <?php $tabControl->EndTab(); ?>

        <?php $tabControl->BeginNextTab(); ?>
        <tr class="heading">
            <td colspan="2" valign="top" align="center"><?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_SECOND_CHECK_ABOUT');?></td>
        </tr>

        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_SECOND_CHECK_STATUS_ID');?>
            </td>
            <td width="60%">
                <select name="SECOND_CHECK_STATUS_ID">
                    <option value=""><?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_SECOND_CHECK_STATUS_ID_NONE');?></option>
                    <?php foreach($statuses as $status):?>
                        <option
                            value="<?=$status['STATUS_ID'];?>"
                            <?php if($secondCheckStatus === $status['STATUS_ID']):?> selected="selected"<?php endif;?>
                        >
                            <?=$status['NAME'];?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <?php $tabControl->EndTab(); ?>

        <?php $tabControl->BeginNextTab();?>

        <tr>
            <td width="40%">

            </td>
            <td width="60%">
                <label>

                    <input type="checkbox" name="USE_TWO_STAGE_PAYMENT" value="Y" <?php if($useTowStagePayment):?> checked="checked"<?php endif;?> />
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_TWO_STAGE_PAYMENT_LABEL');?>
                </label>
            </td>
        </tr>

        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_TWO_STAGE_PAYMENT_STATUS_ID');?>
            </td>
            <td width="60%">
                <select name="TAB_TWO_STAGE_PAYMENT_STATUS_ID">
                    <option value=""><?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_TWO_STAGE_PAYMENT_STATUS_NONE');?></option>
                    <?php foreach($statuses as $status):?>
                        <option
                                value="<?=$status['STATUS_ID'];?>"
                            <?php if($useTowStagePaymentStatus === $status['STATUS_ID']):?> selected="selected"<?php endif;?>
                        >
                            <?=$status['NAME'];?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>

        <tr>
            <td width="40%">

            </td>
            <td width="60%">
                <label>

                    <input type="checkbox" name="USE_TWO_STAGE_PAYMENT_CLEAR_DB" value="Y" />
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_TWO_STAGE_PAYMENT_CLEAR_DB');?>
                </label>
                <p>
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_TWO_STAGE_PAYMENT_CLEAR_DB_DESC');?>
                </p>
            </td>
        </tr>

        <?php $tabControl->EndTab(); ?>
        <?php $tabControl->BeginNextTab();?>

        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.USE_TYPE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[USE_TYPE]">
                    <?php foreach(
                        [
                            Robokassa\RobokassaWidget::USE_WIDGET_TYPE_NONE,
                            Robokassa\RobokassaWidget::USE_WIDGET_TYPE_BADGE,
                            Robokassa\RobokassaWidget::USE_WIDGET_TYPE_WIDGET
                        ] as $useType
                    ):?>
                        <option
                            value="<?=$useType;?>"
                            <?php if(($widget['USE_TYPE'] ?? Robokassa\RobokassaWidget::USE_WIDGET_TYPE_NONE) == $useType):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.USE_TYPE.' . $useType);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.MERCHANT');?>
            </td>
            <td width="60%">
                <input type="text" name="WIDGET[MERCHANT]" value="<?=$widget['MERCHANT'];?>" />
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.TITLE');?>
            </td>
        </tr>

        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.THEME.TITLE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[BADGE_THEME]">
                    <?php foreach(
                        [
                            Robokassa\RobokassaWidget::BADGE_THEME_LIGHT,
                            Robokassa\RobokassaWidget::BADGE_THEME_DARK,
                        ] as $theme
                    ):?>
                        <option
                                value="<?=$theme;?>"
                                <?php if($widget['BADGE_THEME'] === $theme):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.THEME.' . $theme);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.COLOR.TITLE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[BADGE_COLOR]">
                    <?php foreach(
                        [
                            Robokassa\RobokassaWidget::BADGE_COLOR_SCHEME_NONE,
                            Robokassa\RobokassaWidget::BADGE_COLOR_SCHEME_PRIMARY,
                            Robokassa\RobokassaWidget::BADGE_COLOR_SCHEME_SECONDARY,
                            Robokassa\RobokassaWidget::BADGE_COLOR_SCHEME_ACCENT,
                        ] as $color
                    ):?>
                        <option
                                value="<?=$color;?>"
                                <?php if($widget['BADGE_COLOR'] === $color):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.COLOR.' . $color);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.SIZE.TITLE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[BADGE_SIZE]">
                    <?php foreach(
                        [
                            Robokassa\RobokassaWidget::BADGE_SIZE_M,
                            Robokassa\RobokassaWidget::BADGE_SIZE_S,
                        ] as $size
                    ):?>
                        <option
                                value="<?=$size;?>"
                                <?php if($widget['BADGE_SIZE'] === $size):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.SIZE.' . $size);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%" valign="top">
                <label for="WIDGET[BADGE_SHOW_LOGO]">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.BADGE.SHOW_LOGO.TITLE');?>
                </label>
            </td>
            <td width="60%" valign="top">
                <input type="checkbox" id="WIDGET[BADGE_SHOW_LOGO]" name="WIDGET[BADGE_SHOW_LOGO]" value="Y"<?php if(($widget['BADGE_SHOW_LOGO'] ?? null) === "Y"){echo " checked='checked'";}?>>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.TITLE');?>
            </td>
        </tr>

        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.THEME.TITLE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[WIDGET_THEME]">
                    <?php foreach(
                        [
                            Robokassa\RobokassaWidget::WIDGET_THEME_LIGHT,
                            Robokassa\RobokassaWidget::WIDGET_THEME_DARK,
                        ] as $theme
                    ):?>
                        <option
                                value="<?=$theme;?>"
                                <?php if($widget['WIDGET_THEME'] === $theme):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.THEME.' . $theme);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.SIZE.TITLE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[WIDGET_SIZE]">
                    <?php foreach(
                            [
                                    Robokassa\RobokassaWidget::WIDGET_SIZE_M,
                                    Robokassa\RobokassaWidget::WIDGET_SIZE_S,
                            ] as $size
                    ):?>
                        <option
                                value="<?=$size;?>"
                                <?php if($widget['WIDGET_SIZE'] === $size):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.SIZE.' . $size);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>

        <tr>
            <td width="40%" valign="top">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.HAS_SECOND_LINE.TITLE');?>
            </td>
            <td width="60%" valign="top">
                <input type="checkbox" id="WIDGET[WIDGET_HAS_SECOND_LINE]" name="WIDGET[WIDGET_HAS_SECOND_LINE]" value="Y"<?php if($widget['WIDGET_HAS_SECOND_LINE'] ?? null === "Y") echo " checked"?>>
            </td>
        </tr>

        <tr>
            <td width="40%" valign="top">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.BORDER_RADIUS.TITLE');?>
            </td>
            <td width="60%" valign="top">
                <input type="number" min="10" name="WIDGET[WIDGET_BORDER_RADIUS]" max="48" value="<?=$widget['WIDGET_BORDER_RADIUS'] ?? 10;?>">
            </td>
        </tr>

        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.DESCRIPTION_POSITION.TITLE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[WIDGET_DESCRIPTION_POSITION]">
                    <?php foreach(
                        [
                            Robokassa\RobokassaWidget::WIDGET_DESCRIPTION_POSITION_LEFT,
                            Robokassa\RobokassaWidget::WIDGET_DESCRIPTION_POSITION_RIGHT,
                        ] as $position
                    ):?>
                        <option
                            value="<?=$position;?>"
                            <?php if($widget['WIDGET_DESCRIPTION_POSITION'] === $position):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.DESCRIPTION_POSITION.' . $position);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.WIDGET_TYPE.TITLE');?>
            </td>
            <td width="60%">
                <select name="WIDGET[WIDGET_WIDGET_TYPE]">
                    <?php foreach(
                        [
                            Robokassa\RobokassaWidget::WIDGET_TYPE_ALL,
                            Robokassa\RobokassaWidget::WIDGET_TYPE_BNPL,
                            Robokassa\RobokassaWidget::WIDGET_TYPE_CREDIT,
                        ] as $widgetType
                    ):?>
                        <option
                            value="<?=$widgetType;?>"
                            <?php if($widget['WIDGET_WIDGET_TYPE'] === $widgetType):?> selected="selected"<?php endif;?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.WIDGET_TYPE.' . $widgetType);?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>

        <tr>
            <td width="40%" valign="top">
                <label for="WIDGET[WIDGET_SHOW_LOGO]">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_WIDGET.WIDGET.SHOW_LOGO.TITLE');?>
                </label>
            </td>
            <td width="60%" valign="top">
                <input type="checkbox" id="WIDGET[WIDGET_SHOW_LOGO]" name="WIDGET[WIDGET_SHOW_LOGO]" value="Y"<?php if(($widget['WIDGET_SHOW_LOGO'] ?? null) === "Y") echo " checked"?>>
            </td>
        </tr>

        <?php $tabControl->EndTab(); ?>
        <?php $tabControl->BeginNextTab();?>

        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PAYMENT_SETTINGS');?>
            </td>
        </tr>
        <tr>
            <td valign="top" width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT');?>
            </td>
            <td width="60%">
                <input type="text" name="IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT" value="<?=$paymentIdModificationCount;?>" />
                <br>
                <div class="adm-info-message" style="text-align: left">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT_DESCRIPTION');?>
                </div>
            </td>
        </tr>
        <tr>
            <td valign="top" width="40%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ENABLE_PAYMENT_BY_ORDER_ID');?>
            </td>
            <td width="60%">
                <input type="checkbox" id="ENABLE_PAYMENT_BY_ORDER_ID" name="ENABLE_PAYMENT_BY_ORDER_ID" value="Y"<?php if(($enablePaymentByOrderId ?? null) === "Y") echo " checked"?>>
                <br>
                <div class="adm-info-message" style="text-align: left">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT_DESCRIPTION');?>
                </div>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE');?>
            </td>
        </tr>
        <tr>
            <td width="40%" valign="top">
                <label for="ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.ENABLE_PRODUCT_OBJECT_TYPE');?>
                </label>
            </td>
            <td width="60%" valign="top">
                <input type="checkbox" id="ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE" name="ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE" value="Y"<?php if($useProductObjectType ?? null === "Y") echo " checked"?>>
                <br>
                <div class="adm-info-message" style="text-align: left">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.ENABLE_PRODUCT_OBJECT_TYPE_DESCRIPTION');?>
                </div>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_ORDER_STATUSES_TITLE');?>
            </td>
        </tr>
        <?php foreach($sites as $site):?>
            <tr>
                <td width="40%">
                    <?=$site['NAME'];?>
                    <b>
                        [<?=$site['LID'];?>]
                    </b>
                </td>
                <td width="60%">
                    <select name="CHANGE_SITE_STATUS[<?=$site['LID'];?>]">
                        <option value=""><?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_ORDER_STATUSES_NONE');?></option>
                        <?php foreach($statuses as $status):?>
                            <option
                                    value="<?=$status['STATUS_ID'];?>"
                                    <?php if($changeSiteStatus[$site['LID']] === $status['STATUS_ID']):?> selected="selected"<?php endif;?>
                            >
                                <?=$status['NAME'];?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </td>
            </tr>
        <?php endforeach;?>

        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.REFUND.TITLE');?>
            </td>
        </tr>
        <?php foreach($sites as $site):?>
            <tr>
                <td width="40%" valign="top">
                    <?=$site['NAME'];?>
                    <b>
                        [<?=$site['LID'];?>]
                    </b>
                </td>
                <td width="60%">
                    <select name="REFUND_PAYMENT_STATUSES[<?=$site['LID'];?>][]"  multiple="multiple">
                        <option>
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.REFUND.STATUSES_NONE');?>
                        </option>
                        <?php foreach($statuses as $status):?>
                            <option
                                value="<?=$status['STATUS_ID'];?>"
                                <?php if(\in_array($status['STATUS_ID'], $refundPaymentStatuses[$site['LID']] ?? [])):?> selected="selected"<?php endif;?>
                            >
                                <?=$status['NAME'];?>
                            </option>
                        <?php endforeach;?>
                    </select><br>

                    <div class="adm-info-message" style="text-align: left">
                        <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.REFUND.DESCRIPTION');?>
                    </div>
                </td>
            </tr>
        <?php endforeach;?>
    <?php endif;?>

    <?php if(!\Bitrix\Main\Loader::IncludeModule("sale")):?>
        <?php $tabControl->BeginNextTab();?>
        <tr>
            <td width="40%">
                <label for="ENABLE_START_FUNCTION">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START_ENABLE_START_FUNCTION');?>
                </label>
            </td>
            <td width="60%">
                <input type="checkbox" id="ENABLE_START_FUNCTION" name="ENABLE_START_FUNCTION" value="Y"<?php if($enableStartFunction ?? null === "Y") echo " checked"?>>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_IBLOCK_ID">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START_IBLOCK');?>
                </label>
            </td>
            <td width="60%">
                <select name="START_FUNCTION_IBLOCK_ID" id="START_FUNCTION_IBLOCK_ID">
                    <?php foreach($iblocks ?? [] as $iblock):?>
                        <option value="<?=$iblock['ID'];?>"<?php if((int) $iblock['ID'] === ($selectStartIblock ?? 0)):?> selected="selected"<?php endif;?>>
                            [#<?=$iblock['ID'];?>]
                            <?=$iblock['NAME'];?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_COMPONENT_SEF_MODE">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START_START_FUNCTION_COMPONENT_SEF_MODE');?>
                </label>
            </td>
            <td width="60%">
                <input type="checkbox" id="START_FUNCTION_COMPONENT_SEF_MODE" name="START_FUNCTION_COMPONENT_SEF_MODE" value="Y"<?php if($startFunctionComponentSefMode ?? null === "Y") echo " checked"?>>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_COMPONENT_SEF_FOLDER">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START_START_FUNCTION_COMPONENT_SEF_FOLDER');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_COMPONENT_SEF_FOLDER" value="<?=$startFunctionComponentSefFolder ?? '';?>" />
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.HEADER.ROBOKASSA');?>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_SHOPLOGIN">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_SHOPLOGIN');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_OPTIONS_SHOPLOGIN" value="<?=$startFunctionOptions['OPTIONS_SHOPLOGIN'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_SHOPPASSWORD">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_SHOPPASSWORD');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_OPTIONS_SHOPPASSWORD" value="<?=$startFunctionOptions['OPTIONS_SHOPPASSWORD'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_SHOPPASSWORD2">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_SHOPPASSWORD2');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_OPTIONS_SHOPPASSWORD2" value="<?=$startFunctionOptions['OPTIONS_SHOPPASSWORD2'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_TEST">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_TEST');?>
                </label>
            </td>
            <td width="60%">
                <input type="checkbox" id="START_FUNCTION_OPTIONS_TEST" name="START_FUNCTION_OPTIONS_TEST" value="Y"<?php if(($startFunctionOptions['OPTIONS_TEST'] ?? 'N') === "Y") echo " checked"?>>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_TEST_SHOPPASSWORD">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_TEST_SHOPPASSWORD');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_OPTIONS_TEST_SHOPPASSWORD" value="<?=$startFunctionOptions['OPTIONS_TEST_SHOPPASSWORD'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_TEST_SHOPPASSWORD2">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_TEST_SHOPPASSWORD2');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_OPTIONS_TEST_SHOPPASSWORD2" value="<?=$startFunctionOptions['OPTIONS_TEST_SHOPPASSWORD2'] ?? '';?>" />
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.HEADER.SNO');?>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_SNO">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_SNO');?>
                </label>
            </td>
            <td width="60%">
                <select name="START_FUNCTION_OPTIONS_SNO" id="START_FUNCTION_OPTIONS_SNO">
                    <?php foreach(
                        [
                            '' => '',
                            'osn'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_SNO_OSN"),
                            'usn_income'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_SNO_USN_INCOME"),
                            'usn_income_outcome'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_SNO_USN_INCOME_OUTCOME"),
                            'envd'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_SNO_ENVD"),
                            'esn'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_SNO_ESN"),
                            'patent'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_SNO_PATENT")
                        ] as $value => $label
                    ):?>
                        <option
                            value="<?=$value?>"
                            <?php if(($startFunctionOptions['OPTIONS_SNO'] ?? '') === $value) echo "selected"?>
                        >
                            <?=$label?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_PAYMENT_METHOD">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_PAYMENT_METHOD');?>
                </label>
            </td>
            <td width="60%">
                <select name="START_FUNCTION_OPTIONS_PAYMENT_METHOD" id="START_FUNCTION_OPTIONS_PAYMENT_METHOD">
                    <?php foreach(
                        [
                            '' => '',
                            'full_prepayment'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_METHOD_FULL_PREPAYMENT"),
                            'prepayment'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_METHOD_PREPAYMENT"),
                            'advance'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_METHOD_ADVANCE"),
                            'full_payment'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_METHOD_FULL_PAYMENT"),
                            'partial_payment'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_METHOD_PARTIAL_PAYMENT"),
                            'credit'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_METHOD_CREDIT"),
                            'credit_payment'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_METHOD_CREDIT_PAYMENT"),
                        ] as $value => $label
                    ):?>
                        <option
                            value="<?=$value?>"
                            <?php if(($startFunctionOptions['OPTIONS_PAYMENT_METHOD'] ?? '') === $value) echo "selected"?>
                        >
                            <?=$label?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_PAYMENT_OBJECT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_PAYMENT_OBJECT');?>
                </label>
            </td>
            <td width="60%">
                <select name="START_FUNCTION_OPTIONS_PAYMENT_OBJECT" id="START_FUNCTION_OPTIONS_PAYMENT_OBJECT">
                    <?php foreach(
                        [
                            '' => '',
                            'commodity'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_COMMODITY"),
                            'excise'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_USN_EXCISE"),
                            'job'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_USN_INCOME_JOB"),
                            'service'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_SERVICE"),
                            'gambling_bet'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_GAMBLING_BET"),
                            'gambling_prize'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_GAMBLING_PRIZE"),
                            'lottery'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_LOTTERY"),
                            'lottery_prize'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_LOTTERY_PRIZE"),
                            'intellectual_activity'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_INTELLECTUAL_ACTIVITY"),
                            'payment'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_PAYMENT"),
                            'agent_commission'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_AGENT_COMMISSION"),
                            'composite'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_COMPOSITE"),
                            'another'  => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_OBJECT_ANOTHER"),
                        ] as $value => $label
                    ):?>
                        <option
                            value="<?=$value?>"
                            <?php if(($startFunctionOptions['OPTIONS_PAYMENT_OBJECT'] ?? '') === $value) echo "selected"?>
                        >
                            <?=$label?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_VAT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_VAT');?>
                </label>
            </td>
            <td width="60%">
                <select name="START_FUNCTION_OPTIONS_VAT" id="START_FUNCTION_OPTIONS_VAT">
                    <?php foreach(
                        [
                            '' => '',
                            Ipol\Robokassa\RobokassaPaymentService::NO_VAT => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_NO_VAT'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_0 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_0'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_5 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_5'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_7 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_7'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_10 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_10'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_18 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_18'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_20 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_20'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_22 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_22'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_105 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_105'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_107 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_107'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_110 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_110'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_120 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_120'),
                            Ipol\Robokassa\RobokassaPaymentService::VAT_122 => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTION_VAT_122'),

                        ] as $value => $label
                    ):?>
                        <option
                            value="<?=$value?>"
                            <?php if(($startFunctionOptions['OPTIONS_VAT'] ?? '') === $value) echo "selected"?>
                        >
                            <?=$label?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.HEADER.ADDITIONAL');?>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_OPTIONS_PAYMENT_ID_MODIFICATION_COUNT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_PAYMENT_ID_MODIFICATION_COUNT');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" id="START_FUNCTION_OPTIONS_PAYMENT_ID_MODIFICATION_COUNT" name="START_FUNCTION_OPTIONS_PAYMENT_ID_MODIFICATION_COUNT" value="<?=$startFunctionOptions['OPTIONS_PAYMENT_ID_MODIFICATION_COUNT'] ?? 0;?>" />
            </td>
        </tr>
        <tr>
            <td width="40%"></td>
            <td width="60%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_PAYMENT_ID_MODIFICATION_COUNT_DESCRIPTION');?>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.HEADER.BUTTON');?>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_BUTTON_OPTIONS_PLACEMENT">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.BUTTON_OPTIONS_PLACEMENT');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_BUTTON_OPTIONS_PLACEMENT" value="<?=$startFunctionButtonOptions['OPTIONS_PLACEMENT'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
            </td>
            <td width="60%">
                <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.BUTTON_OPTIONS_PLACEMENT_DESCRIPTION');?>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_BUTTON_OPTIONS_POSITION">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.BUTTON_OPTIONS_PLACEMENT_POSITION');?>
                </label>
            </td>
            <td width="60%">
                <select name="START_FUNCTION_BUTTON_OPTIONS_POSITION" id="START_FUNCTION_BUTTON_OPTIONS_POSITION">
                    <?php foreach(Ipol\Robokassa\Start\Configuration::BUY_BUTTON_SET as $value):?>
                        <option
                                value="<?=$value?>"
                            <?php if(($startFunctionButtonOptions['OPTIONS_POSITION'] ?? '') === $value) echo "selected"?>
                        >
                            <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.BUTTON_OPTIONS_PLACEMENT_POSITION.' . \mb_strtoupper($value))?>
                        </option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_BUTTON_OPTIONS_BUTTON_BLOCK_CLASS">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.BUTTON_OPTIONS_BUTTON_BLOCK_CLASS');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_BUTTON_OPTIONS_BUTTON_BLOCK_CLASS" value="<?=$startFunctionButtonOptions['OPTIONS_BUTTON_BLOCK_CLASS'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_BUTTON_OPTIONS_BUTTON_CLASS">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.BUTTON_OPTIONS_BUTTON_CLASS');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_BUTTON_OPTIONS_BUTTON_CLASS" value="<?=$startFunctionButtonOptions['OPTIONS_BUTTON_CLASS'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_BUTTON_OPTIONS_BUTTON_BASKET_CLASS">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.OPTIONS_BUTTON_BASKET_CLASS');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_BUTTON_OPTIONS_BUTTON_BASKET_CLASS" value="<?=$startFunctionButtonOptions['OPTIONS_BUTTON_BASKET_CLASS'] ?? '';?>" />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="START_FUNCTION_BUTTON_OPTIONS_BASKET_POPUP_CLASS">
                    <?=Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START.BUTTON_OPTIONS_BASKET_POPUP_CLASS');?>
                </label>
            </td>
            <td width="60%">
                <input type="text" name="START_FUNCTION_BUTTON_OPTIONS_BASKET_POPUP_CLASS" value="<?=$startFunctionButtonOptions['OPTIONS_BASKET_POPUP_CLASS'] ?? '';?>" />
            </td>
        </tr>
    <?php endif;?>

    <?php $tabControl->buttons(); ?>

    <input type="submit" name="save" value="<?= Loc::getMessage("MAIN_SAVE") ?>" title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save" />

	<?php $tabControl->end(); ?>
</form>
<style>
    .adm-workarea input[type="number"]:not(.ui-tag-selector-text-box) {
        background:#fff;
        border:1px solid;
        border-color:#87919c #959ea9 #9ea7b1 #959ea9;
        border-radius:4px;
        color:#000;
        box-shadow:0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);
        display:inline-block;
        outline:none;
        vertical-align:middle;
        -webkit-font-smoothing: antialiased;
        font-size: 13px;
        height: 25px;
        padding: 0 5px;
        margin: 0;
    }
</style>