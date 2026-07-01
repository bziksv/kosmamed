<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
Loc::loadMessages(__FILE__);?>
<!DOCTYPE html>
<html lang="<?=LANGUAGE_ID?>">
<head>
	<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
	<link rel="apple-touch-icon" sizes="57x57" href="<?=SITE_TEMPLATE_PATH?>/images/apple-touch-icon-114.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="<?=SITE_TEMPLATE_PATH?>/images/apple-touch-icon-114.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="<?=SITE_TEMPLATE_PATH?>/images/apple-touch-icon-144.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="<?=SITE_TEMPLATE_PATH?>/images/apple-touch-icon-144.png" />
	<meta name='viewport' content='width=device-width, initial-scale=1.0' />
	<title><?$APPLICATION->ShowTitle()?></title>
	<meta property="og:title" content="<?=$APPLICATION->ShowTitle();?>"/>
    <meta property="og:description" content="<?=$APPLICATION->ShowProperty("description");?>"/>
    <meta property="og:type" content="<?=$APPLICATION->ShowProperty("ogtype");?>"/>
    <meta property="og:url" content= "<?=(CMain::IsHTTPS()? 'https' : 'http')."://".SITE_SERVER_NAME.$APPLICATION->GetCurPage();?>" />
    <meta property="og:image" content="<?=$APPLICATION->ShowProperty("ogimage");?>">
	<meta property='og:image:width' content="<?=$APPLICATION->ShowProperty("ogimagewidth");?>" />
	<meta property='og:image:height' content="<?=$APPLICATION->ShowProperty("ogimageheight");?>" />
	<link rel='image_src' href="<?=$APPLICATION->ShowProperty("ogimage")?>" />
	<?$APPLICATION->SetPageProperty("ogtype", "website");
	$APPLICATION->ShowProperty('google_prev_next');
	$APPLICATION->SetPageProperty("ogimage", (CMain::IsHTTPS()? 'https' : 'http')."://".SITE_SERVER_NAME.SITE_TEMPLATE_PATH."/images/apple-touch-icon-144.png");
	$APPLICATION->SetPageProperty("ogimagewidth", "144");
	$APPLICATION->SetPageProperty("ogimageheight", "144");
	Asset::getInstance()->addCss("https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");
	if(!CModule::IncludeModule("altop.elastofont"))
		// TODO Asset::getInstance()->addCss("https://d1azc1qln24ryf.cloudfront.net/130672/ELASTOFONT/style-cf.css?xk463o");
	Asset::getInstance()->addCss("https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,cyrillic-ext");
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/colors.css");

	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/js/anythingslider/slider.css");
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/js/custom-forms/custom-forms.css");
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/js/fancybox/jquery.fancybox-1.3.1.css");
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/js/spectrum/spectrum.css");
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/css/slick.css");
	CJSCore::Init(array("jquery", "popup"));
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/jquery.cookie.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/moremenu.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/jquery.inputmask.bundle.min.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/anythingslider/jquery.easing.1.2.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/anythingslider/jquery.anythingslider.min.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/custom-forms/jquery.custom-forms.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/fancybox/jquery.fancybox-1.3.1.pack.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/spectrum/spectrum.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/countUp.min.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/countdown/jquery.plugin.js");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/countdown/jquery.countdown.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/TweenMax.min.js");
	Asset::getInstance()->addString("
		<script type='text/javascript'>
			$(function() {
				$.countdown.regionalOptions['ru'] = {
					labels: ['".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_YEAR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_MONTH")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_WEEK")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_DAY")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_HOUR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_MIN")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_SEC")."'],
					labels1: ['".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS1_YEAR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS1_MONTH")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS1_WEEK")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS1_DAY")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS1_HOUR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_MIN")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_SEC")."'],
					labels2: ['".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS2_YEAR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS2_MONTH")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS2_WEEK")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS2_DAY")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS2_HOUR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_MIN")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_LABELS_SEC")."'],
					compactLabels: ['".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS_YEAR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS_MONTH")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS_WEEK")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS_DAY")."'],
					compactLabels1: ['".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS1_YEAR")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS_MONTH")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS_WEEK")."', '".Loc::getMessage("COUNTDOWN_REGIONAL_COMPACT_LABELS_DAY")."'],
					whichLabels: function(amount) {
						var units = amount % 10;
						var tens = Math.floor((amount % 100) / 10);
						return (amount == 1 ? 1 : (units >= 2 && units <= 4 && tens != 1 ? 2 : (units == 1 && tens != 1 ? 1 : 0)));
					},
					digits: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
					timeSeparator: ':',
					isRTL: false
				};
				$.countdown.setDefaults($.countdown.regionalOptions['ru']);
			});
		</script>
	");
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/slick.min.js");
	$kmMainJsPath = $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/js/main.js';
	$kmMainJsVer = file_exists($kmMainJsPath) ? filemtime($kmMainJsPath) : time();
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/main.js?v=".$kmMainJsVer);
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/script.js");
	$APPLICATION->ShowHead();
	$kmThemeCssPath = $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/kosmamed-theme.css';
	$kmThemeCssVer = file_exists($kmThemeCssPath) ? filemtime($kmThemeCssPath) : time();
?>
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/kosmamed-theme.css?v=<?=$kmThemeCssVer?>" />
<?if(CModule::IncludeModule("altop.elektroinstrument")) {
	    CElektroinstrument::getBackground(SITE_ID);
        CElektroinstrument::SetCannonicalURL($APPLICATION->GetCurPageParam());
    }
$kmBgUrl = '';
if (preg_match('/url\([\'"]?([^\'"\)]+)/', (string)$APPLICATION->GetProperty('backgroundImage'), $kmBgMatch)) {
	$kmBgUrl = $kmBgMatch[1];
}
?>
<style id="km-critical">
html { background-color: #e2e8f0 !important; }
<?php if ($kmBgUrl !== ''): ?>
body, body.km-body-bg, body.agll, body.agll_loaded { background-image: url('<?=htmlspecialcharsbx($kmBgUrl)?>') !important; background-repeat: repeat !important; background-size: 640px auto !important; background-color: #e2e8f0 !important; background-position: center top !important; }
<?php endif; ?>
.page-wrapper, .content-wrapper { max-width: 100vw !important; box-sizing: border-box !important; overflow-x: visible !important; }
.center.outer, #for-quick-view-header.center.outer { width: min(1234px, calc(100vw - 16px)) !important; max-width: min(1234px, calc(100vw - 16px)) !important; margin-left: auto !important; margin-right: auto !important; display: block !important; box-sizing: border-box !important; }
.content-wrapper > .center, .content-wrapper > .center.inner, .page-wrapper .center.inner, header .center.inner { width: 100% !important; max-width: 100% !important; margin-left: 0 !important; margin-right: 0 !important; display: block !important; box-sizing: border-box !important; }
@media (min-width: 1500px) { .center.outer, #for-quick-view-header.center.outer { width: min(1500px, calc(100vw - 16px)) !important; max-width: min(1500px, calc(100vw - 16px)) !important; } }
.breadcrumb-share { display: block !important; visibility: visible !important; opacity: 1 !important; background: #eef2f7 !important; border: 1px solid #64748b !important; border-radius: 6px !important; padding: 12px 14px !important; margin: 0 0 12px !important; overflow: visible !important; box-sizing: border-box !important; position: relative !important; z-index: 2 !important; }
.breadcrumb { display: flex !important; flex-wrap: wrap !important; align-items: center !important; gap: 4px 8px !important; width: 100% !important; min-height: 24px !important; overflow: visible !important; float: none !important; }
.breadcrumb__item, .breadcrumb__arrow { float: none !important; margin: 0 !important; }
.breadcrumb__title_main { display: inline !important; }
.breadcrumb__item > .breadcrumb__link, .breadcrumb__item > .breadcrumb__title { color: #1e293b !important; font-size: 14px !important; font-weight: 500 !important; }
#pagetitle { display: block !important; clear: both !important; position: relative !important; z-index: 2 !important; }
@media (min-width: 788px) {
.catalog-detail-element > .catalog-detail { display: grid !important; grid-template-columns: minmax(0, 1fr) clamp(260px, 34%, 450px) !important; gap: 20px !important; align-items: start !important; width: 100% !important; max-width: 100% !important; overflow: visible !important; box-sizing: border-box !important; }
.catalog-detail-element > .catalog-detail > .column.first, .catalog-detail-element > .catalog-detail > .column.second, .catalog-detail-element .catalog-detail .column.first, .catalog-detail-element .catalog-detail .column.three { float: none !important; width: auto !important; max-width: 100% !important; min-width: 0 !important; margin: 0 !important; display: block !important; }
.catalog-detail-element > .catalog-detail > .column.second { grid-column: 2 !important; position: relative !important; z-index: 2 !important; }
.catalog-detail-element > .catalog-detail > .column.first { grid-column: 1 !important; overflow: hidden !important; z-index: 1 !important; }
}
@media (min-width: 788px) and (max-width: 1253px) {
.catalog-detail-element > .catalog-detail { grid-template-columns: minmax(0, 1fr) clamp(240px, 38%, 320px) !important; }
}
@media screen and (max-width: 787px) {
.breadcrumb-share { display: block !important; }
.breadcrumb { display: block !important; flex-wrap: nowrap !important; white-space: nowrap !important; overflow-x: auto !important; -webkit-overflow-scrolling: touch; }
.breadcrumb::-webkit-scrollbar { display: none; }
.breadcrumb__item, .breadcrumb__arrow { display: inline !important; }
.catalog-detail-element > .catalog-detail { display: block !important; }
}
</style>
<?
$APPLICATION->IncludeComponent("bitrix:menu", "catalog_menu_interface_2_0_1_style", 
    array(
        "ROOT_MENU_TYPE" => "left",
        "MENU_CACHE_TYPE" => "A",
        "MENU_CACHE_TIME" => "36000000",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS" => array(),
        "MAX_LEVEL" => "4",
        "CHILD_MENU_TYPE" => "left",
        "USE_EXT" => "Y",
        "ALLOW_MULTI_SELECT" => "N",
        "CACHE_SELECTED_ITEMS" => "N"
    ),
    false
);
?>
</head>
<body  <?=$APPLICATION->ShowProperty("bgClass")?><?=$APPLICATION->ShowProperty("backgroundColor")?><?=$APPLICATION->ShowProperty("backgroundImage")?>>
<script>
(function () {
	function applySiteBackground() {
		var body = document.body;
		if (!body) {
			return;
		}
		var bg = body.getAttribute('data-background-image');
		if (!bg) {
			return;
		}
		body.style.setProperty('background-image', "url('" + bg + "')", 'important');
		body.style.setProperty('background-repeat', 'repeat', 'important');
		body.style.setProperty('background-size', '640px auto', 'important');
		body.style.setProperty('background-color', '#e2e8f0', 'important');
		body.style.setProperty('background-position', 'center top', 'important');
		body.classList.add('agll_loaded');
		body.classList.remove('agll');
		body.removeAttribute('data-background-image');
	}
	applySiteBackground();
	document.addEventListener('DOMContentLoaded', applySiteBackground);
	window.addEventListener('load', function () {
		applySiteBackground();
		kmApplyLazyBackgrounds();
		setTimeout(applySiteBackground, 50);
		setTimeout(kmApplyLazyBackgrounds, 50);
		setTimeout(kmApplyLazyBackgrounds, 300);
	});
	function kmApplyLazyBackgrounds(root) {
		(root || document).querySelectorAll('[data-background-image].agll:not(.agll_loaded), [data-background-image].agll_inited:not(.agll_loaded)').forEach(function (el) {
			var bg = el.getAttribute('data-background-image');
			if (!bg) return;
			el.style.backgroundImage = "url('" + bg.replace(/'/g, "\\'") + "')";
			el.style.backgroundSize = el.style.backgroundSize || 'cover';
			el.style.backgroundPosition = el.style.backgroundPosition || 'center center';
			el.style.backgroundRepeat = el.style.backgroundRepeat || 'no-repeat';
			el.classList.add('agll_loaded');
			el.classList.remove('agll');
		});
	}
	window.kmApplyLazyBackgrounds = kmApplyLazyBackgrounds;
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', kmApplyLazyBackgrounds);
	} else {
		kmApplyLazyBackgrounds();
	}
})();
</script>
	<?global $arSetting;?>
	<?
    $arSetting = $APPLICATION->IncludeComponent("altop:settings", "", array(), false, array("HIDE_ICONS" => "Y"));
    ?>
<?if($arSetting["GENERAL_SETTINGS"]["LIST"]["BREADCRUMB"]["CURRENT"]=="Y") {
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/bread.css");
}
?>
	<div class="bx-panel<?=($arSetting['CART_LOCATION']['VALUE'] == 'TOP') ? ' clvt' : ''?>">
		<?$APPLICATION->ShowPanel();?>
	</div>
	<div class="bx-include-empty">
		<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => ""), false);?>
	</div>
	<div class="body<?=($arSetting['CATALOG_LOCATION']['VALUE'] == 'HEADER') ? ' clvh' : ''?><?=($arSetting['CART_LOCATION']['VALUE'] == 'TOP') ? ' clvt' : ''?><?=($arSetting['CART_LOCATION']['VALUE'] == 'RIGHT') ? ' clvr' : ''?><?=($arSetting['CART_LOCATION']['VALUE'] == 'LEFT') ? ' clvl' : ''?>">
		<div class="page-wrapper">
			<?if($arSetting["SITE_BACKGROUND"]["VALUE"] == "Y"){?>
				<div id="for-quick-view-header" class="center outer">
			<?}else{?>
		    	<div id="for-quick-view-header">
			<?}
			if($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER"):?>
				<div class="top-menu">
					<div class="center<?=($arSetting['SITE_BACKGROUND']['VALUE'] == 'Y' ? ' inner' : '');?>">
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/top_menu.php"), false, array("HIDE_ICONS" => "Y"));?>
					</div>
				</div>
			<?endif;?>
			<header class="fixed">
				<div class="center<?=($arSetting['SITE_BACKGROUND']['VALUE'] == 'Y' ? ' inner' : '');?>">
					<div class="header_1">
						<div class="logo">
							<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_logo.php"), false);?>
						</div>
					</div>
					<div class="header_2">
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/header_search.php"), false, array("HIDE_ICONS" => "Y"));?>
					</div>
					<div class="header_3">
						<div class="schedule">
							<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/schedule.php"), false);?>
						</div>
					</div>
					<div class="header_4">
						<div class="contacts">
							<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/geolocation.php"), false, array("HIDE_ICONS" => "Y"));?>
						</div>
					</div>
					<div class="header_5">
							<?$APPLICATION->IncludeComponent("altop:sale.basket.delay", ".default",
								array(
									"PATH_TO_DELAY" => SITE_DIR."personal/cart/?delay=Y",
								),
								false,
								array("HIDE_ICONS" => "Y")
							);?>
							<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "login",
								array(
									"REGISTER_URL" => SITE_DIR."personal/private/",
									"FORGOT_PASSWORD_URL" => SITE_DIR."personal/private/",
									"PROFILE_URL" => SITE_DIR."personal/private/",
									"SHOW_ERRORS" => "N"
								 ),
								 false,
								 array("HIDE_ICONS" => "Y")
							);?>
							<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket.line", ".default",
								array(
									"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
									"PATH_TO_ORDER" => SITE_DIR."personal/order/make/",
									"HIDE_ON_BASKET_PAGES" => "N",
								),
								false,
								array("HIDE_ICONS" => "Y")
							);?>
					</div>
				</div>
			</header>
			<?if($arSetting["CATALOG_LOCATION"]["VALUE"] == "LEFT"):?>
				<?/*<div class="top-menu">
					<div class="center<?=($arSetting['SITE_BACKGROUND']['VALUE'] == 'Y' ? ' inner' : '');?>">
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/top_menu.php"), false, array("HIDE_ICONS" => "Y"));?>
					</div>
				</div>*/?>
			<?elseif($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER"):?>
				<div class="top-catalog">
					<div class="center<?=($arSetting['SITE_BACKGROUND']['VALUE'] == 'Y' ? ' inner' : '');?>">
						<?$APPLICATION->IncludeComponent("bitrix:menu", $arSetting["CATALOG_VIEW"]["VALUE"] == "FOUR_LEVELS" ? "tree" : "sections",
							array(
								"ROOT_MENU_TYPE" => "left",
								"MENU_CACHE_TYPE" => "A",
								"MENU_CACHE_TIME" => "36000000",
								"MENU_CACHE_USE_GROUPS" => "Y",
								"MENU_CACHE_GET_VARS" => array(),
								"MAX_LEVEL" => "4",
								"CHILD_MENU_TYPE" => "left",
								"USE_EXT" => "Y",
								"DELAY" => "N",
								"ALLOW_MULTI_SELECT" => "N",
								"CACHE_SELECTED_ITEMS" => "N"
							),
							false
						);?>
					</div>
				</div>
			<?endif;?>
			<div class="top_panel">
				<div class="center<?=($arSetting['SITE_BACKGROUND']['VALUE'] == 'Y' ? ' inner' : '');?>">

					<div class="panel_1">
						<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/sections.php"), false, array("HIDE_ICONS" => "Y"));?>
					</div>
					<div class="panel_4">
						<ul class="search-vertical">
							<li>
								<a class="showsearch" href="javascript:void(0)"><i class="fa fa-search"></i></a>
							</li>
						</ul>
					</div>
					<?/*<div class="panel_2">
						<?$APPLICATION->IncludeComponent("bitrix:menu", "panel",
							array(
								"ROOT_MENU_TYPE" => "top",
								"MENU_CACHE_TYPE" => "A",
								"MENU_CACHE_TIME" => "36000000",
								"MENU_CACHE_USE_GROUPS" => "Y",
								"MENU_CACHE_GET_VARS" => array(),
								"MAX_LEVEL" => "3",
								"CHILD_MENU_TYPE" => "topchild",
								"USE_EXT" => "N",
								"ALLOW_MULTI_SELECT" => "N",
								"CACHE_SELECTED_ITEMS" => "N"
							),
							false
						);?>
					</div>*/?>
					<div class="panel_3">
						<ul class="contacts-vertical">
							<li>
								<a class="showcontacts" href="tel:<?=htmlspecialcharsbx(ksMainPhoneTel())?>"><i class="fa fa-phone"></i></a>
							</li>
						</ul>
					</div>
					<div class="panel_2">
							<?$APPLICATION->IncludeComponent("altop:sale.basket.delay", ".default",
								array(
									"PATH_TO_DELAY" => SITE_DIR."personal/cart/?delay=Y",
								),
								false,
								array("HIDE_ICONS" => "Y")
							);?>
						<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "login",
							array(
								"REGISTER_URL" => SITE_DIR."personal/private/",
								"FORGOT_PASSWORD_URL" => SITE_DIR."personal/private/",
								"PROFILE_URL" => SITE_DIR."personal/private/",
								"SHOW_ERRORS" => "N"
							 ),
							 false,
							 array("HIDE_ICONS" => "Y")
						);?>
					</div>
					<div class="panel_5 foot_panel_2">
						<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket.line", ".default",
							array(
								"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
								"PATH_TO_ORDER" => SITE_DIR."personal/order/make/",
								"HIDE_ON_BASKET_PAGES" => "N",
							),
							false,
							array("HIDE_ICONS" => "Y")
						);?>
					</div>

				</div>
			</div>
			<div class="content-wrapper">
				<div class="center<?=($arSetting['SITE_BACKGROUND']['VALUE'] == 'Y' ? ' inner' : '');?>">
					<div class="content">
						<?$inOrderPage = CSite::InDir("/personal/order/make/");
						if(!$inOrderPage):?>
							<div class="left-column">
								<?if($APPLICATION->GetDirProperty("PERSONAL_SECTION")):?>
									<div class="h3"><?=Loc::getMessage("PERSONAL_HEADER");?></div>
									<?$APPLICATION->IncludeComponent("altop:user", ".default",
										array(
											"PATH_TO_PERSONAL" => SITE_DIR."personal/",
											"CACHE_TYPE" => "A",
											"CACHE_TIME" => "36000000"
										),
										false
									);?>
									<?$APPLICATION->IncludeComponent("bitrix:menu", "tree",
										array(
											"ROOT_MENU_TYPE" => "personal",
											"MENU_CACHE_TYPE" => "A",
											"MENU_CACHE_TIME" => "36000000",
											"MENU_CACHE_USE_GROUPS" => "Y",
											"MENU_CACHE_GET_VARS" => array(),
											"MAX_LEVEL" => "1",
											"CHILD_MENU_TYPE" => "personal",
											"USE_EXT" => "Y",
											"DELAY" => "N",
											"ALLOW_MULTI_SELECT" => "N",
											"CACHE_SELECTED_ITEMS" => "N"
										),
										false
									);?>
									<?if($USER->IsAuthorized()):?>
										<a class="personal-exit" href="<?=$APPLICATION->GetCurPageParam('logout=yes', array('logout'));?>"><?=Loc::getMessage("PERSONAL_EXIT");?></a>
									<?endif;
								else:
									if($arSetting["CATALOG_LOCATION"]["VALUE"] == "LEFT"):?>
                                        <? //  echo"<pre>"; print_r($arSetting["BLOCK_LEFT"]["LIST"]["CATALOG_MENU_LEFT"]); echo "</pre>";  ?>
                                        <div class="h3" <?=$arSetting["BLOCK_LEFT"]["LIST"]["CATALOG_MENU_LEFT"]["CURRENT"]=="Y" ? 'id="catalog_wrap_btn"': ""?> ><?=Loc::getMessage("BASE_HEADER");?>
                                            <?if($arSetting["BLOCK_LEFT"]["LIST"]["CATALOG_MENU_LEFT"]["CURRENT"]=="Y"){?><a class="showfilter"><i class="fa fa-angle-down"></i><i class="fa fa-angle-up"></i></a>
                                            <?}?></div>
                                        <div  <?=$arSetting["BLOCK_LEFT"]["LIST"]["CATALOG_MENU_LEFT"]["CURRENT"]=="Y" ? 'id="catalog_wrap"': ""?> >
										<?$APPLICATION->IncludeComponent("bitrix:menu", $arSetting["CATALOG_VIEW"]["VALUE"] == "FOUR_LEVELS" ? "tree" : "sections",
											array(
												"ROOT_MENU_TYPE" => "left",
												"MENU_CACHE_TYPE" => "A",
												"MENU_CACHE_TIME" => "36000000",
												"MENU_CACHE_USE_GROUPS" => "Y",
												"MENU_CACHE_GET_VARS" => array(),
												"MAX_LEVEL" => "4",
												"CHILD_MENU_TYPE" => "left",
												"USE_EXT" => "Y",
												"DELAY" => "N",
												"ALLOW_MULTI_SELECT" => "N",
												"CACHE_SELECTED_ITEMS" => "N"
											),
											false
										);?>
                                        </div>
									<?endif;
								endif;
								if($arSetting["SMART_FILTER_LOCATION"]["VALUE"] == "VERTICAL"):
									$APPLICATION->ShowViewContent("filter_vertical");
								endif;
                                if ($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER"):?>
                                    <?if(in_array("BANNERS", $arSetting["BLOCK_LEFT"]["VALUE"])) {?>
                                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                                            array(
                                                "AREA_FILE_SHOW" => "file",
                                                "PATH" => SITE_DIR . "include/banners_left.php",
                                                "AREA_FILE_RECURSIVE" => "N",
                                                "EDIT_MODE" => "html",
                                            ),
                                            false,
                                            array("HIDE_ICONS" => "Y")
                                        );?>
                                    <?}?>
                                <?if(in_array("SLIDER", $arSetting["BLOCK_LEFT"]["VALUE"])) {?>
                                    <?if($APPLICATION->GetCurPage(true) != SITE_DIR . "index.php") {?>
                                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                                            array(
                                                "AREA_FILE_SHOW" => "file",
                                                "PATH" => SITE_DIR . "include/slider_left.php",
                                                "AREA_FILE_RECURSIVE" => "N",
                                                "EDIT_MODE" => "html",
                                            ),
                                            false,
                                            array("HIDE_ICONS" => "Y")
                                        );?>
                                    <?}?>

                                <?}?>
								<?endif;?>
                                <?if(in_array("LINK_N_S_D", $arSetting["BLOCK_LEFT"]["VALUE"] ?: [])) {?>
                                    <ul class="new_leader_disc">
                                        <li>
                                            <a class="new" href="<?= SITE_DIR ?>catalog/newproduct/">
                                                <span class="icon"><?= Loc::getMessage("CR_TITLE_ICON_NEWPRODUCT") ?></span>
                                                <span class="text"><?= Loc::getMessage("CR_TITLE_NEWPRODUCT") ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="hit" href="<?= SITE_DIR ?>catalog/saleleader/">
                                                <span class="icon"><?= Loc::getMessage("CR_TITLE_ICON_SALELEADER") ?></span>
                                                <span class="text"><?= Loc::getMessage("CR_TITLE_SALELEADER") ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="discount" href="<?= SITE_DIR ?>catalog/discount/">
                                                <span class="icon"><?= Loc::getMessage("CR_TITLE_ICON_DISCOUNT") ?></span>
                                                <span class="text"><?= Loc::getMessage("CR_TITLE_DISCOUNT") ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                <?}?>
								<?if($arSetting["CATALOG_LOCATION"]["VALUE"] == "LEFT"):?>

                                    <?if(in_array("BANNERS", $arSetting["BLOCK_LEFT"]["VALUE"])) {?>
                                        <? $APPLICATION->IncludeComponent("bitrix:main.include", "",
                                            array(
                                                "AREA_FILE_SHOW" => "file",
                                                "PATH" => SITE_DIR . "include/banners_left.php",
                                                "AREA_FILE_RECURSIVE" => "N",
                                                "EDIT_MODE" => "html",
                                            ),
                                            false,
                                            array("HIDE_ICONS" => "Y")
                                        );?>
                                    <?}?>
									<?if($APPLICATION->GetCurPage(true)!= SITE_DIR."index.php") {?>
                                        <?if(in_array("SLIDER", $arSetting["BLOCK_LEFT"]["VALUE"])) {?>
                                            <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => SITE_DIR . "include/slider_left.php",
                                                    "AREA_FILE_RECURSIVE" => "N",
                                                    "EDIT_MODE" => "html",
                                                ),
                                                false,
                                                array("HIDE_ICONS" => "Y")
                                            );?>
                                        <?}?>
									<?}?>
								<?endif;?>
                                <?if(in_array("VENDORS", $arSetting["BLOCK_LEFT"]["VALUE"] ?: [])) {?>
                                    <div class="vendors">
                                        <div class="h3"><?=Loc::getMessage("MANUFACTURERS");?></div>
                                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                                            array(
                                                "AREA_FILE_SHOW" => "file",
                                                "PATH" => SITE_DIR . "include/vendors_left.php",
                                                "AREA_FILE_RECURSIVE" => "N",
                                                "EDIT_MODE" => "html",
                                            ),
                                            false,
                                            array("HIDE_ICONS" => "Y")
                                        );?>
                                    </div>
                                <?}?>
                                <?if(in_array("SUBSCRIBE", $arSetting["BLOCK_LEFT"]["VALUE"] ?: [])) {?>
                                    <div class="subscribe">
                                        <div class="h3"><?= Loc::getMessage("SUBSCRIBE");?></div>
                                        <p><?=Loc::getMessage("SUBSCRIBE_TEXT");?></p>
                                        <?$APPLICATION->IncludeComponent("bitrix:subscribe.form", "left",
                                            array(
                                                "USE_PERSONALIZATION" => "Y",
                                                "PAGE" => SITE_DIR . "personal/mailings/",
                                                "SHOW_HIDDEN" => "N",
                                                "CACHE_TYPE" => "A",
                                                "CACHE_TIME" => "36000000",
                                                "CACHE_NOTES" => ""
                                            ),
                                            false
                                        );?>
                                    </div>
                                <?}?>
                                <?if(in_array("NEWS", $arSetting["BLOCK_LEFT"]["VALUE"] ?: [])) {?>
                                    <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                                        array(
                                            "AREA_FILE_SHOW" => "file",
                                            "PATH" => SITE_DIR . "include/news_left.php",
                                            "AREA_FILE_RECURSIVE" => "N",
                                            "EDIT_MODE" => "html",
                                        ),
                                        false,
                                        array("HIDE_ICONS" => "Y")
                                    );?>
                                <?}?>
                                <?if(in_array("REVIEWS", $arSetting["BLOCK_LEFT"]["VALUE"] ?: [])) {?>
	                                    <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
	                                        array(
	                                            "AREA_FILE_SHOW" => "file",
	                                            "PATH" => SITE_DIR . "include/reviews_left.php",
	                                            "AREA_FILE_RECURSIVE" => "N",
	                                            "EDIT_MODE" => "html",
	                                        ),
	                                        false,
	                                        array("HIDE_ICONS" => "Y")
	                                    );?>
                                <?}?>


                                    <?
                                        if(preg_match('/(catalog)/', $APPLICATION->GetCurPage(false))){
                                            $arSectionCode = explode('/', $APPLICATION->GetCurPage(false));
                                            if($arSectionCode[1] == 'catalog' && $arSectionCode[2]){
                                                $sectionCode = $arSectionCode[2];
                                                $db_listTag = CIBlockSection::GetList([], ['IBLOCK_ID' => 24, 'CODE' => $sectionCode, 'UF_TAGS_LIST_BAR' => '%'], true, ['UF_TAGS_LIST_BAR', 'UF_TAGS_BAR_TITLE', 'UF_TAGS_ACTIVE']);
                                                $ar_resultTag = $db_listTag->GetNext();
                                                ?>
                                                <? if($ar_resultTag && !$ar_resultTag['UF_TAGS_ACTIVE']):?>
                                                    <div class="tag_menu">
                                                        <? if($ar_resultTag['UF_TAGS_BAR_TITLE']): ?>
                                                            <div class="h3"><?=$ar_resultTag['UF_TAGS_BAR_TITLE']?></div>
                                                        <? endif; ?>
                                                        <? foreach($ar_resultTag['UF_TAGS_LIST_BAR'] as $inc => $tag):
                                                            $tag = explode('@', $tag);
                                                            ?>
                                                            <a href="<?=$tag[1]?>" class="<?=($inc < 5) ? 'active' : ''?>"><?=$tag[0]?></a>
                                                            <? if($inc == 4): ?>
                                                                <a href="javascript:void(0)" class="active" onclick="$(this).closest('.tag_menu').find('a').css('display', 'block'); $(this).hide();" style="text-align: center">Показать все</a>
                                                            <? endif; ?>
                                                        <? endforeach; ?>
                                                    </div>
                                                <? endif; ?>
                                                <?
                                            }
                                        }
                                    ?>


							</div>
						<?endif;?>
						<div class="workarea<?=($inOrderPage ? ' workarea-order' : '');?>">
							<?if($APPLICATION->GetCurPage(true)== SITE_DIR."index.php"):
								if(in_array("SLIDER", $arSetting["HOME_PAGE"]["VALUE"] ?: [])):?>
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
										array(
											"AREA_FILE_SHOW" => "file",
											"PATH" => SITE_DIR."include/slider.php",
											"AREA_FILE_RECURSIVE" => "N",
											"EDIT_MODE" => "html",
										),
										false,
										array("HIDE_ICONS" => "Y")
									);?>
								<?endif;
								if(in_array("ADVANTAGES", $arSetting["HOME_PAGE"]["VALUE"] ?: [])):
									global $arAdvFilter;
									$arAdvFilter = array(
										"!PROPERTY_SHOW_HOME" => false
									);?>
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
										array(
											"AREA_FILE_SHOW" => "file",
											"PATH" => SITE_DIR."include/advantages.php",
											"AREA_FILE_RECURSIVE" => "N",
											"EDIT_MODE" => "html",
										),
										false,
										array("HIDE_ICONS" => "Y")
									);?>
								<?endif;
								if(in_array("PROMOTIONS", $arSetting["HOME_PAGE"]["VALUE"] ?: [])):?>
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
										array(
											"AREA_FILE_SHOW" => "file",
											"PATH" => SITE_DIR."include/promotions.php",
											"AREA_FILE_RECURSIVE" => "N",
											"EDIT_MODE" => "html",
										),
										false,
										array("HIDE_ICONS" => "Y")
									);?>
								<?endif;
								if(in_array("BANNERS", $arSetting["HOME_PAGE"]["VALUE"] ?: [])):?>
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
										array(
											"AREA_FILE_SHOW" => "file",
											"PATH" => SITE_DIR."include/banners_main.php",
											"AREA_FILE_RECURSIVE" => "N",
											"EDIT_MODE" => "html",
										),
										false,
										array("HIDE_ICONS" => "Y")
									);?>
								<?endif;
								if(in_array("TABS", $arSetting["HOME_PAGE"]["VALUE"] ?: [])):?>
									<div class="tabs-wrap tabs-main">
										<ul class="tabs">
											<?if(in_array("RECOMMEND", $arSetting["HOME_PAGE"]["VALUE"])):?>
												<li class="tabs__tab recommend">
													<a href="javascript:void(0)"><span><?=Loc::getMessage("CR_TITLE_RECOMMEND")?></span></a>
												</li>
											<?endif;?>
											<li class="tabs__tab new">
												<a href="javascript:void(0)"><span><?=Loc::getMessage("CR_TITLE_NEWPRODUCT")?></span></a>
											</li>
											<li class="tabs__tab hit">
												<a href="javascript:void(0)"><span><?=Loc::getMessage("CR_TITLE_SALELEADER")?></span></a>
											</li>
											<li class="tabs__tab discount">
												<a href="javascript:void(0)"><span><?=Loc::getMessage("CR_TITLE_DISCOUNT")?></span></a>
											</li>
										</ul>
										<?if(in_array("RECOMMEND", $arSetting["HOME_PAGE"]["VALUE"])):?>
											<div class="tabs__box recommend">
												<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
													array(
														"AREA_FILE_SHOW" => "file",
														"PATH" => SITE_DIR."include/recommend.php",
														"AREA_FILE_RECURSIVE" => "N",
														"EDIT_MODE" => "html",
													),
													false,
													array("HIDE_ICONS" => "Y")
												);?>
											</div>
										<?endif;?>
										<div class="tabs__box new">
											<div class="catalog-top">
												<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
													array(
														"AREA_FILE_SHOW" => "file",
														"PATH" => SITE_DIR."include/newproduct.php",
														"AREA_FILE_RECURSIVE" => "N",
														"EDIT_MODE" => "html",
													),
													false,
													array("HIDE_ICONS" => "Y")
												);?>
												<a class="all" href="<?=SITE_DIR?>catalog/newproduct/"><?=Loc::getMessage("CR_TITLE_ALL_NEWPRODUCT");?></a>
											</div>
										</div>
										<div class="tabs__box hit">
											<div class="catalog-top">
												<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
													array(
														"AREA_FILE_SHOW" => "file",
														"PATH" => SITE_DIR."include/saleleader.php",
														"AREA_FILE_RECURSIVE" => "N",
														"EDIT_MODE" => "html",
													),
													false,
													array("HIDE_ICONS" => "Y")
												);?>
												<a class="all" href="<?=SITE_DIR?>catalog/saleleader/"><?=Loc::getMessage("CR_TITLE_ALL_SALELEADER");?></a>
											</div>
										</div>
										<div class="tabs__box discount">
											<div class="catalog-top">
												<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
													array(
														"AREA_FILE_SHOW" => "file",
														"PATH" => SITE_DIR."include/discount.php",
														"AREA_FILE_RECURSIVE" => "N",
														"EDIT_MODE" => "html",
													),
													false,
													array("HIDE_ICONS" => "Y")
												);?>
												<a class="all" href="<?=SITE_DIR?>catalog/discount/"><?=Loc::getMessage("CR_TITLE_ALL_DISCOUNT");?></a>
											</div>
										</div>
										<div class="clr"></div>
									</div>
								<?endif;
							endif;?>
							<div class="body_text" style="<?=($APPLICATION->GetCurPage(true) == SITE_DIR.'index.php') ? 'padding:0px 15px;' : 'padding:0px;';?>">
								<?if($APPLICATION->GetCurPage(true)!= SITE_DIR."index.php"):?>

									<div class="breadcrumb-share">
										<div id="navigation" class="breadcrumb">
											<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", ".default",
												array(
													"START_FROM" => "0",
													"PATH" => "",
													"SITE_ID" => "-"
												),
												false,
												array("HIDE_ICONS" => "Y")
											);?>
										</div>
<?/*if(!CSite::InDir('/product/')):?>
										<div class="share">
										<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
<script src="//yastatic.net/share2/share.js"></script>
<div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,viber,whatsapp,telegram" data-size="s"></div>

										</div>
<?endif;*/?>
									</div>
									<h1 id="pagetitle"><?=$APPLICATION->ShowTitle(false);?></h1>
								<?endif;?>
