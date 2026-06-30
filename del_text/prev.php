<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER;
if ($USER->IsAdmin()):

	CModule::IncludeModule("iblock");

	$back = true;

	$IBLOCK_ID = 24;



	$arSelect = Array("ID", "NAME", "PREVIEW_TEXT");
	$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, "!PREVIEW_TEXT"=>false);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		
		$back = false;

		if($arFields['PREVIEW_TEXT']){


			// CIBlockElement::SetPropertyValuesEx($arFields["ID"], $IBLOCK_ID, array('avito' => Array ("VALUE" => array("del" => "Y")))); 
			$el = new CIBlockElement;
			$arLoadProductArray = Array(
				"PREVIEW_TEXT"   => "",
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
		  $(window.location).attr('href', '/del_text/prev.php');
		});
	</script>

	<?	
	endif;
endif;

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>