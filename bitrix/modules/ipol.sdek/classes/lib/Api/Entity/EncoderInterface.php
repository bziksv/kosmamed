<?php
namespace Ipolh\SDEK\Api\Entity;

/**
 * Interface EncoderInterface
 * @package Ipolh\SDEK\Api\Entity
 * Encodes handle from API-server into cms encoding
 */
interface EncoderInterface
{
    public function encodeToAPI($handle);
    public function encodeFromAPI($handle);
}