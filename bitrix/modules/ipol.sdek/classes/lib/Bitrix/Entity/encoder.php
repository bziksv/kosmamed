<?php

namespace Ipolh\SDEK\Bitrix\Entity;


class encoder
{
    public function encodeFromAPI($handle)
    {
        return \sdekHelper::zaDEjsonit($handle);
    }

    public function encodeToAPI($handle)
    {
        return \sdekHelper::zajsonit($handle);
    }
}