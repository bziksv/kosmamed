<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER;
if ($USER->IsAdmin()):

	$back = true;

	$IBLOCK_ID = 24;



	$arSelect = Array("ID", "NAME", "DETAIL_TEXT");
	$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, "!DETAIL_TEXT"=>false);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		
		$back = false;

		if($arFields['DETAIL_TEXT']){

			$el = new CIBlockElement;
			$arLoadProductArray = Array(
				"DETAIL_TEXT"   => "",
			);
			$resDEL = $el->Update($arFields["ID"], $arLoadProductArray);

			 echo $arFields["ID"];
			 echo '<br><br><br>';


		}
	}

	if($back):
		?>
		<script>
			$( document ).ready(function() {
			  $(window.location).attr('href', '/del_text/');
			});
		</script>
		<?
	else:
	 ?>
	<script>
		$( document ).ready(function() {
		  $(window.location).attr('href', '/del_text/detail.php');
		});
	</script>

	<?		
	endif;
endif;

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>