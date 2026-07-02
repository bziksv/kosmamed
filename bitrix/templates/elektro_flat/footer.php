<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);?>
							</div>
                            <?if($APPLICATION->GetCurPage(true)== SITE_DIR."index.php" &&
                                (in_array("VENDORS", $arSetting["HOME_PAGE"]["VALUE"] ?: []))):?>
                                <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                                    array(
                                        "AREA_FILE_SHOW" => "file",
                                        "PATH" => SITE_DIR . "include/vendors_bottom.php",
                                        "AREA_FILE_RECURSIVE" => "N",
                                        "EDIT_MODE" => "html",
                                    ),
                                    false,
                                    array("HIDE_ICONS" => "Y")
                                );?>
					    	<?endif;?>
						</div>
						<?if(!CSite::InDir('/news/')):?>
							<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
								array(
									"AREA_FILE_SHOW" => "file",
									"PATH" => SITE_DIR."include/news_bottom.php",
									"AREA_FILE_RECURSIVE" => "N",
									"EDIT_MODE" => "html",
								),
								false,
								array("HIDE_ICONS" => "Y")
							);?>
						<?endif;?>
						<?if(!CSite::InDir('/reviews/')):?>
<?if( !preg_match('/(product)/', $APPLICATION->GetCurPage(false)) && !preg_match('/(catalog)/', $APPLICATION->GetCurPage(false)) ){?>
							<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
								array(
									"AREA_FILE_SHOW" => "file",
									"PATH" => SITE_DIR."include/reviews_bottom.php",
									"AREA_FILE_RECURSIVE" => "N",
									"EDIT_MODE" => "html",
								),
								false,
								array("HIDE_ICONS" => "Y")
							);?>
<?}?>
						<?endif;?>
					</div>
					<?/*$APPLICATION->IncludeComponent("bitrix:subscribe.form", "bottom",
						array(
							"USE_PERSONALIZATION" => "Y",
							"PAGE" => SITE_DIR."personal/mailings/",
							"SHOW_HIDDEN" => "N",
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "36000000",
							"CACHE_NOTES" => ""
						),
						false
					);*/?>
				</div>
			</div>
			<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/viewed_products.php"), false);?>
			<footer>
				<div class="center<?=($arSetting['SITE_BACKGROUND']['VALUE'] == 'Y' ? ' inner' : '');?>">
					<div class="footer_menu_soc_pay">
						<div class="footer_menu">
							<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom",
								array(
									"ROOT_MENU_TYPE" => "footer1",
									"MENU_CACHE_TYPE" => "A",
									"MENU_CACHE_TIME" => "36000000",
									"MENU_CACHE_USE_GROUPS" => "Y",
									"MENU_CACHE_GET_VARS" => array(),
									"MAX_LEVEL" => "1",
									"CHILD_MENU_TYPE" => "",
									"USE_EXT" => "N",
									"ALLOW_MULTI_SELECT" => "N",
									"CACHE_SELECTED_ITEMS" => "N"
								),
								false
							);?>
							<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom",
								array(
									"ROOT_MENU_TYPE" => "footer2",
									"MENU_CACHE_TYPE" => "A",
									"MENU_CACHE_TIME" => "36000000",
									"MENU_CACHE_USE_GROUPS" => "Y",
									"MENU_CACHE_GET_VARS" => array(),
									"MAX_LEVEL" => "1",
									"CHILD_MENU_TYPE" => "",
									"USE_EXT" => "N",
									"ALLOW_MULTI_SELECT" => "N",
									"CACHE_SELECTED_ITEMS" => "N"
								),
								false
							);?>
							<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom",
								array(
									"ROOT_MENU_TYPE" => "footer3",
									"MENU_CACHE_TYPE" => "A",
									"MENU_CACHE_TIME" => "36000000",
									"MENU_CACHE_USE_GROUPS" => "Y",
									"MENU_CACHE_GET_VARS" => array(),
									"MAX_LEVEL" => "1",
									"CHILD_MENU_TYPE" => "",
									"USE_EXT" => "N",
									"ALLOW_MULTI_SELECT" => "N",
									"CACHE_SELECTED_ITEMS" => "N"
								),
								false
							);?>
							<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom",
								array(
									"ROOT_MENU_TYPE" => "footer4",
									"MENU_CACHE_TYPE" => "A",
									"MENU_CACHE_TIME" => "36000000",
									"MENU_CACHE_USE_GROUPS" => "Y",
									"MENU_CACHE_GET_VARS" => array(),
									"MAX_LEVEL" => "1",
									"CHILD_MENU_TYPE" => "",
									"USE_EXT" => "N",
									"ALLOW_MULTI_SELECT" => "N",
									"CACHE_SELECTED_ITEMS" => "N"
								),
								false
							);?>
						</div>
						<div class="footer_soc_pay">
							<div class="footer_pay">
								<?global $arPayIcFilter;
								$arPayIcFilter = array();?>
								<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/payments_icons.php"), false, array("HIDE_ICONS" => "Y"));?>
							</div>
						</div>
					</div>
					<div class="footer-bottom">
						<div class="footer-bottom__blocks">
							<div class="footer-bottom__block-wrap fb-left">
								<div class="footer-bottom__block footer-bottom__copyright">
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/copyright.php"), false);?>
								</div>
								<div class="footer-bottom__block footer-bottom__links">
									<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom",
										array(
											"ROOT_MENU_TYPE" => "bottom",
											"MENU_CACHE_TYPE" => "A",
											"MENU_CACHE_TIME" => "36000000",
											"MENU_CACHE_USE_GROUPS" => "Y",
											"MENU_CACHE_GET_VARS" => array(),
											"MAX_LEVEL" => "1",
											"CHILD_MENU_TYPE" => "",
											"USE_EXT" => "N",
											"ALLOW_MULTI_SELECT" => "N",
											"CACHE_SELECTED_ITEMS" => "N"
										),
										false
									);?>
								</div>
							</div>
						</div>
						<div class="footer-bottom__blocks">
							<div class="footer-bottom__block-wrap fb-right">
								<div class="footer-bottom__block footer-bottom__counter">
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/counter_1.php"), false);?>
								</div>
								<div class="footer-bottom__block footer-bottom__counter">
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/counter_2.php"), false);?>
								</div>
								<div class="footer-bottom__block footer-bottom__design">
									<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/developer.php"), false);?>
								</div>
							</div>
						</div>
					</div>

				</div>
			</footer>
			<?if($arSetting["SITE_BACKGROUND"]["VALUE"] == "Y"){?>
				</div>
			<?}else{?>
			    </div>
			<?}?>
		</div>
	</div>
	<div class="<?=($arSetting['CATALOG_LOCATION']['VALUE'] == 'HEADER') ? ' clvh' : ''?><?=($arSetting['CART_LOCATION']['VALUE'] == 'TOP') ? ' clvt' : ''?><?=($arSetting['CART_LOCATION']['VALUE'] == 'RIGHT') ? ' clvr' : ''?><?=($arSetting['CART_LOCATION']['VALUE'] == 'LEFT') ? ' clvl' : ''?>">
		<?/*
	<div class="foot_panel_all">
						<div id="for-quick-view-footer"   class="foot_panel">
							<div class="foot_panel_1">
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
								<?$APPLICATION->IncludeComponent("bitrix:main.include", "",
									array(
										"AREA_FILE_SHOW" => "file",
										"PATH" => SITE_DIR."include/footer_compare.php"
									),
									false,
									array("HIDE_ICONS" => "Y")
								);?>
								<?$APPLICATION->IncludeComponent("altop:sale.basket.delay", ".default",
									array(
										"PATH_TO_DELAY" => SITE_DIR."personal/cart/?delay=Y",
									),
									false,
									array("HIDE_ICONS" => "Y")
								);?>
							</div>
							<div class="foot_panel_2">
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
					</div>
*/?>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function(m,e,t,r,i,k,a){
	m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
	m[i].l=1*new Date();
	for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
	k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
})(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

ym(93152340, "init", {
	webvisor: true,
	clickmap: true,
	ecommerce: "dataLayer",
	referrer: document.referrer,
	url: location.href,
	accurateTrackBounce: true,
	trackLinks: true
});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/93152340" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<?if(!empty($arSetting["SITE_BACKGROUND"]["VALUE"]) && $arSetting["SITE_BACKGROUND"]["VALUE"] == "Y" && !empty($arSetting["SITE_BACKGROUND_PICTURE"]["VALUE"])):
	$kmBgFile = CFile::GetFileArray($arSetting["SITE_BACKGROUND_PICTURE"]["VALUE"]);
	if(is_array($kmBgFile)):?>
<style id="km-force-body-bg">
body {
	background-image: url('<?=CHTTP::urnEncode($kmBgFile["SRC"], "UTF-8")?>') !important;
	background-repeat: repeat !important;
	background-position: center top !important;
	background-size: 640px auto !important;
	background-color: #e2e8f0 !important;
}
</style>
<?endif;
endif;?>

</body>
</html>
