<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER;
if ($USER->IsAdmin()):

	CModule::IncludeModule("iblock");

	$back = true;

	$IBLOCK_ID = 24;
	$SECTION_ID = 14012;


	$arSelect = Array("ID", "NAME", "PREVIEW_PICTURE");
	$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, "SECTION_ID"=>$SECTION_ID, "INCLUDE_SUBSECTIONS" => "Y", "!PREVIEW_PICTURE"=>false);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>100), $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		
		$back = false;

		if($arFields['PREVIEW_PICTURE']){


			// CIBlockElement::SetPropertyValuesEx($arFields["ID"], $IBLOCK_ID, array('avito' => Array ("VALUE" => array("del" => "Y")))); 

			$el = new CIBlockElement;
			$arLoadProductArray = Array(
				"PREVIEW_PICTURE"   => ['del' => 'Y'],
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
		  $(window.location).attr('href', '/del_text/prev_pict.php');
		});
	</script>

	<?	
	endif;
endif;

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>