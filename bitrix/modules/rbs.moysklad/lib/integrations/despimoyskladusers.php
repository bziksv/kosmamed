<?php
namespace Rbs\Moysklad\Integrations;

class DespiMoyskladUsers
{
	private $emptyResult;
	public function __construct($result)
	{
		$this->emptyResult = $result;
	}

	public function buildImportResult($counterparty)
	{
		$result = $this->emptyResult;

		if(!\Despi\MoyskladUsers\Internals\Feature::check('global_enable')){
			return $result;
		}

		$logger = new \Despi\MoyskladUsers\Debug\Loger();
		try {

			$userBx = new \CDespiMoyskladUsers($counterparty);
			$userBx->setUpdateAfterAction(false);
			$userBx->searchUser();
			if (!$userBx->hasUserId()) {
				if (\Despi\MoyskladUsers\Internals\Feature::check('ui_enable') && \Despi\MoyskladUsers\Internals\Feature::check('ui_add')) {
					$userBx->create();
				}
			}

			if($userBx->hasUserId()){
				$result['USER_ID'] = $userBx->getUserId();
			}

			$logger->addMessageArray($userBx->getLogger()->getMessageArray());

			if ($userBx->hasUserId() && !$userBx->isSkipUser() && property_exists($counterparty, 'companyType')) {
				$companyType = $counterparty->companyType;
				if (\Despi\MoyskladUsers\Internals\Feature::check("pi_enable_{$companyType}")) {

					$profileBx = new \Despi\MoyskladUsers\Controller\Import\Profile($counterparty, $userBx->getUserId());
					$profileBx->setUpdateAfterAction(false);
					$profileBx->searchProfile();

					if(!$profileBx->hasProfileId()) {
						if (\Despi\MoyskladUsers\Internals\Feature::check("pi_add_{$companyType}")) {
							$profileBx->create();
						}
					}

					if ($profileBx->hasProfileId()) {
						$result['PROFILE_ID'] = $profileBx->getProfileId();
						$result['PERSON_TYPE'] = (int) \Despi\MoyskladUsers\Internals\Config::getOption("pi_ptype_{$companyType}");
					}

					$logger->addMessageArray($profileBx->getLogger()->getMessageArray());
				}
			}

		} catch (\Throwable $e) {
			$logger->addErrorMessage($e->getMessage());
		}

		$messageArray = $logger->getMessageArray();
		if (!empty($messageArray)) {
			$logger->exportLog(\Despi\MoyskladUsers\Internals\LangMsg::get('CONTROLLER_IMPORT_FROM_COUNTERPARTY_HREF', [
				'#CP_HREF#' => $counterparty->meta->uuidhref,
				'#CP_NAME#' => $counterparty->name,
			]));
		}

		return $result;

	}

}