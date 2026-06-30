<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\Internals\Profiles;

if (!Config::isProfilesOn() || $request->get("process") == "Y") {
	return;
}

$profileSelect = Profiles::getProfiles();

$isNewProfile = false;
$currProfileId = Config::getProfileId();
if(!isset($profileSelect[$currProfileId	])) {
	$profileSelect[$currProfileId] = GetMessage('PROFILE_DEF_NAME', ['#NUM#' => $currProfileId]);
	$isNewProfile = true;
}

$currentProfileName = Profiles::getProfileName((int)$currProfileId);

$actionStr = $APPLICATION->GetCurPage() . "?mid={$mid_orig}&lang=" . LANG;
?>
<style>
	.despi-profile-wrapper {
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.despi-profile-edit-btn {
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 29px;
		height: 29px;
		border-radius: 4px;
		border: none;
		background-color: #e0e9ec;
		background-image: linear-gradient(to top, #d7e3e7, #fff);
		box-shadow: 0 0 1px rgba(0,0,0,.3), 0 1px 1px rgba(0,0,0,.3), inset 0 1px 0 #fff, inset 0 0 1px rgba(255,255,255,.5);
		color: #3f4b54;
		font-size: 14px;
		vertical-align: middle;
	}
	.despi-profile-edit-btn:hover {
		background-image: linear-gradient(to top, #cdd9dd, #f5f5f5);
	}
	.despi-profile-rename-block {
		display: none;
		align-items: center;
		gap: 8px;
	}
	.despi-profile-rename-block.active {
		display: inline-flex;
	}
	.despi-profile-rename-input {
		width: 200px;
		font-size: 13px;
		height: 27px;
		padding: 0 8px;
		background: #fff;
		border: 1px solid;
		border-color: #87919c #959ea9 #9ea7b1 #959ea9;
		border-radius: 4px;
		color: #000;
		box-shadow: 0 1px 0 0 rgba(255,255,255,.3), inset 0 2px 2px -1px rgba(180,188,191,.7);
		outline: none;
		vertical-align: middle;
	}
	.despi-profile-rename-input:focus {
		border-color: #86ad00;
		box-shadow: 0 0 0 2px rgba(134,173,0,.15), inset 0 2px 2px -1px rgba(180,188,191,.7);
	}
</style>
<script>
	BX.ready(function() {
		let currProfileId = <?= Config::getProfileId(); ?>;

		$('#profile_id').on('change', function() {
			let profileId = $(this).val();
			if(profileId != currProfileId) {
				window.location.href = '<?= $actionStr ?>&profile_id=' + profileId;
			}
		});

		$('#despi_profile_edit_toggle').on('click', function() {
			let block = $('#despi_profile_rename_block');
			block.toggleClass('active');
			if (block.hasClass('active')) {
				$('#despi_profile_name_input').focus();
			}
		});

		$('#despi_profile_name_save').on('click', function() {
			let btn = $(this);
			let name = $('#despi_profile_name_input').val().trim();
			btn.addClass('btn-wait btn-disabled');
			despiAjaxController.get('renameProfile', {
				profileId: currProfileId,
				name: name
			}, function(response) {
				window.location.reload();
			}, function() {
				btn.removeClass('btn-wait btn-disabled');
			});
		});

		$('#despi_profile_name_input').on('keydown', function(e) {
			if (e.keyCode === 13) {
				e.preventDefault();
				$('#despi_profile_name_save').trigger('click');
			}
			if (e.keyCode === 27) {
				$('#despi_profile_rename_block').removeClass('active');
			}
		});
	});
</script>
<div class="despi-profile-wrapper">
	<select name="profile_id" id="profile_id">
		<?php foreach ($profileSelect as $profileId => $profileName) : ?>
			<option value="<?= $profileId ?>" <?= $profileId == Config::getProfileId() ? 'selected' : '' ?>><?= $profileName ?></option>
		<?php endforeach; ?>
		<?php if(!$isNewProfile) : ?>
			<option value="<?= $profileId + 1?>"><?= GetMessage('ADD_PROFILE'); ?></option>
		<?php endif; ?>
	</select>

	<button type="button" id="despi_profile_edit_toggle" class="despi-profile-edit-btn" title="<?= GetMessage('PROFILE_NAME_EDIT_BTN') ?>">&#9998;</button>

	<span id="despi_profile_rename_block" class="despi-profile-rename-block">
		<input type="text" id="despi_profile_name_input" class="despi-profile-rename-input" value="<?= htmlspecialcharsbx($currentProfileName) ?>" placeholder="<?= GetMessage('PROFILE_DEF_NAME', ['#NUM#' => $currProfileId]) ?>" />
		<button type="button" id="despi_profile_name_save" class="btn-option btn-option-active"><?= GetMessage('PROFILE_NAME_SAVE_BTN') ?></button>
	</span>
</div>