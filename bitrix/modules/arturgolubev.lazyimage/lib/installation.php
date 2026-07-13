<?
if (!class_exists('agInstaHelperLazyimage')){
	class agInstaHelperLazyimage {
		static function IncludeAdminFile($m, $p){
			global $APPLICATION, $DOCUMENT_ROOT;
			$APPLICATION->IncludeAdminFile($m, $DOCUMENT_ROOT.$p);
		}
	}
}
?>