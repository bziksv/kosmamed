<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?=ShowError($arResult["strProfileError"]);?>
<?if($arResult['DATA_SAVED'] == 'Y')
	echo ShowNote(GetMessage('PROFILE_DATA_SAVED'));?>

<div class="content-form profile-form" id="profile-form">
	<form method="post" name="form1" action="<?=$arResult["FORM_TARGET"]?>" enctype="multipart/form-data">
		<?=$arResult["BX_SESSION_CHECK"]?>
		<input type="hidden" name="lang" value="<?=LANG?>" />
		<input type="hidden" name="ID" value="<?=$arResult["ID"]?>" />
		<input type="hidden" name="LOGIN" value="<?=htmlspecialcharsbx($arResult["arUser"]["LOGIN"])?>" />

		<div class="fields">
			<section class="profile-form__section">
				<h2 class="profile-form__section-title"><?=GetMessage("LEGEND_PROFILE")?></h2>

				<div class="field">
					<label class="field-title" for="profile-name"><?=GetMessage('NAME')?></label>
					<div class="form-input">
						<input type="text" id="profile-name" name="NAME" maxlength="50" value="<?=htmlspecialcharsbx($arResult["arUser"]["NAME"])?>" autocomplete="given-name" />
					</div>
				</div>

				<div class="field">
					<label class="field-title" for="profile-last-name"><?=GetMessage('LAST_NAME')?></label>
					<div class="form-input">
						<input type="text" id="profile-last-name" name="LAST_NAME" maxlength="50" value="<?=htmlspecialcharsbx($arResult["arUser"]["LAST_NAME"])?>" autocomplete="family-name" />
					</div>
				</div>

				<div class="field">
					<label class="field-title" for="profile-email"><?=GetMessage('EMAIL')?><?if(!empty($arResult["EMAIL_REQUIRED"])):?><span class="starrequired">*</span><?endif?></label>
					<div class="form-input">
						<input type="email" id="profile-email" name="EMAIL" maxlength="255" value="<?=htmlspecialcharsbx($arResult["arUser"]["EMAIL"])?>" autocomplete="email" />
					</div>
				</div>

				<div class="field field--photo">
					<label class="field-title" for="profile-photo"><?=GetMessage('PERSONAL_PHOTO')?></label>
					<div class="form-input profile-form__photo">
						<?if(!empty($arResult["arUser"]["PERSONAL_PHOTO"])):?>
							<span class="profile-form__photo-preview">
								<img src="<?=$arResult["arUser"]["PERSONAL_IMG"]["SRC"]?>" width="<?=$arResult["arUser"]["PERSONAL_IMG"]["WIDTH"]?>" height="<?=$arResult["arUser"]["PERSONAL_IMG"]["HEIGHT"]?>" alt="" />
							</span>
						<?endif;?>
						<input type="file" id="profile-photo" name="PERSONAL_PHOTO" size="20" accept="image/*" class="profile-form__file" />
					</div>
				</div>
			</section>

			<section class="profile-form__section">
				<h2 class="profile-form__section-title"><?=GetMessage("MAIN_PSWD")?></h2>

				<div class="field">
					<label class="field-title" for="profile-new-password"><?=GetMessage('NEW_PASSWORD_REQ')?></label>
					<div class="form-input">
						<input type="password" id="profile-new-password" name="NEW_PASSWORD" maxlength="50" value="" autocomplete="new-password" />
					</div>
				</div>

				<div class="field">
					<label class="field-title" for="profile-new-password-confirm"><?=GetMessage('NEW_PASSWORD_CONFIRM')?></label>
					<div class="form-input">
						<input type="password" id="profile-new-password-confirm" name="NEW_PASSWORD_CONFIRM" maxlength="50" value="" autocomplete="new-password" />
					</div>
				</div>
			</section>

			<div class="field field-button">
				<button type="submit" name="save" class="btn_buy popdef" value="<?=GetMessage('MAIN_SAVE')?>"><?=GetMessage("MAIN_SAVE")?></button>
			</div>
		</div>
	</form>
</div>

<?if($arResult["SOCSERV_ENABLED"]) {?>
	<div class="profile-form__socserv">
		<?$APPLICATION->IncludeComponent("bitrix:socserv.auth.split", ".default",
			array(
				"SHOW_PROFILES" => "Y",
				"ALLOW_DELETE" => "Y"
			),
			false
		);?>
	</div>
<?}?>
