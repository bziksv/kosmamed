<?php
/**
 * @author Isaev Danil
 * @package Isaev\Seotemplate
 */
namespace Isaev\Seotemplate;

/**
 * {=strip this.Name}
 * {=strip "&quot;Строка с </br> html и сущностями &quot;"}
 */
class Strip extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
    public function calculate($parameters)
    {
        $this->parametersToArray($parameters);
        return strip_tags(html_entity_decode($parameters[0], ENT_QUOTES));
    }
}
