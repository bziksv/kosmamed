<?
CBoxberry::addWidgetJs();
if (!CBoxberry::initApi()){
    return;
}
$getKey = CBoxberry::getKeyIntegration();
$key = $getKey['key'] ?? '';
?>

<div id="boxberry_widget"></div>
<script>
    boxberry.openOnPage('boxberry_widget');
    boxberry.open('', '<?=$key?>', '', '', '', 100);
</script>