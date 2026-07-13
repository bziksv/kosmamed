<?php
namespace Rbs\MoyskladStocks\Diagnostic\Dashboard\Core;

interface ItemInterface
{
    public function getKey(): string;
    public function getGroup(): string;
    public function getTitle(): string;
    public function getDescription(): string;
    public function getValue();
    public function getStatus(): string;
    public function getValueDescription(): string;
    public function getCardType(): string;
    public function getRecommendations(): string;
}
