<?php
namespace Rbs\Moysklad\Integrations;

class DespiMsCounterParty
{
	private $emptyResult;
	public function __construct($result)
	{
		$this->emptyResult = $result;
	}

	public function buildImportResult($counterparty)
	{
		$result = $this->emptyResult;

		$agentHref = $counterparty->meta->href;
		if (\Despi\Mscounterparty\Config::checkFeature('global_enabled')) {
			$agentHref = str_replace('1.1', '1.2', $agentHref);
			$counterDespi = \CDespiMscounterparty::importCounterPartyFromHref($agentHref);
			if ($counterDespi->isLoadedCounterParty() && $counterDespi->isLoadedUserBx()) {
				$result = [
					'USER_ID' => $counterDespi->getUserId(),
					'PROFILE_ID' => $counterDespi->getProfileId(),
					'IS_NEW_PROFILE' => false,
					'PERSON_TYPE' =>  $counterDespi->getPersonTypeId(),
					'AGENT' => $counterDespi->getCounterParty(),
				];
			}
		}
		
		return $result;
	}
}