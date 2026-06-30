<?
namespace Arturgolubev\Lazyimage;

class Tools {
	const MODULE_ID = 'arturgolubev.lazyimage';
	var $MODULE_ID = 'arturgolubev.lazyimage'; 
	
	static function chechEnable(){
		return (\Arturgolubev\Lazyimage\Unitools::getSiteSetting('enabled') == 'Y');
	}
	
	static function cl($dm, $text){
		return (($dm) ? 'console.log("'.$text.'");' : '');
	}
}