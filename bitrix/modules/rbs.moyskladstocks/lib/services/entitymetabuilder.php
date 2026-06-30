<?php
namespace Rbs\MoyskladStocks\Services;

class EntityMetaBuilder
{
	private $entity;
	private $entityId;
	private $meta;

	public function __construct(string $entity, string $entityId)
	{
		$this->entity = $entity;
		$this->entityId = $entityId;

		$apiEndpointUrl = \Rbs\MoyskladStocks\ApiNew::getApiEndPointUrl();

		$this->meta = (object)[
			'href' => "{$apiEndpointUrl}/entity/{$this->entity}/{$this->entityId}",
			'metadataHref' => "{$apiEndpointUrl}/entity/{$this->entity}/metadata",
			'type' => $this->entity,
			'mediaType' => 'application/json'
		];
	}

	public function getMeta(): object
	{
		return $this->meta;
	}

	public function getMetaHref(): string
	{
		return $this->meta->href;
	}

	public function getMetaDataHref(): string
	{
		return $this->meta->metadataHref;
	}

	public function getEntity(): string
	{
		return $this->entity;
	}

	public function getEntityId(): string
	{
		return $this->entityId;
	}
}