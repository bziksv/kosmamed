<?
if(\Bitrix\Main\Loader::includeModule('arturgolubev.chatgpt')){
    $taskID = 1;
    $elementID = 100;
    $type = 'E'; // E or S
    $autostart = true; // autostart task after add

    $result = \Arturgolubev\Chatgpt\Api::addElementToTask($taskID, $elementID, $type, $autostart);

    echo '<pre>'; print_r($result); echo '</pre>';
}
?>