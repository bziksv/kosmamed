<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

use \Arturgolubev\Lazyimage\Tools as Tools;
use \Arturgolubev\Lazyimage\Unitools as UTools;

include 'autoload.php';

Class CArturgolubevLazyimage 
{
	const MODULE_ID = 'arturgolubev.lazyimage';
	
	static function prepareTag($tag){
		return str_replace(["\n", "\t"], ' ', $tag);
	}
	
	static function checkException($tag, $exceptions){
		if(count($exceptions) > 0){
			foreach($exceptions as $exc){
				if(strpos($tag, $exc) !== false) return false;
			}
		}
		
		return true;
	}
	
	static function createIframe($tag, $option){
		$tag = self::prepareTag($tag);
		
		if(!self::checkException($tag, $option["iframe_e"])) return false;
		
		if($option["iframe_btech"]){
			return str_replace('<iframe','<iframe loading="lazy"', $tag);
		}
		
		preg_match('/ src=[\",\'](.*)[\",\']/siU', $tag, $matchSrc);
		
		if($matchSrc[1]){
			$newSrc = ' src="'.$option["default_iframe_path"].'" data-src';
			
			preg_match('/ class=[\",\'](.*)[\",\']/siU', $tag, $matchClass);
			if($matchClass[0]){
				$nTag = str_replace([" src", $matchClass[0]], [$newSrc, ' class="'.$matchClass[1].' '.$option["class_lazy"].'"'], $tag);
			}else{
				$nTag = str_replace(" src", ' class="'.$option["class_lazy"].'" '.$newSrc, $tag);
			}
		}
		
		return $nTag;
	}
	
	static function createImage($tag, $option){
		$tag = self::prepareTag($tag);
		
		if(!self::checkException($tag, $option["image_e"])) return false;
		
		if($option["image_btech"]){
			return str_replace('<img','<img loading="lazy"', $tag);
		}
		
		preg_match('/ src=[\",\'](.*)[\",\']/siU', $tag, $matchSrc);
		
		if($matchSrc[1]){
			$newSrc = ' src="'.$option["default_image_path"].'" data-src';
			
			preg_match('/ class=[\",\'](.*)[\",\']/siU', $tag, $matchClass);
			if($matchClass[0]){
				$nTag = str_replace([" src", $matchClass[0]], [$newSrc, ' class="'.$matchClass[1].' '.$option["class_lazy"].'"'], $tag);
			}else{
				$nTag = str_replace(" src", ' class="'.$option["class_lazy"].'" '.$newSrc, $tag);
			}
		}
		
		if($option["image_noscript"]){
			$nTag .= '<noscript>'.$tag.'</noscript>';
		}
		
		return $nTag;
	}
	
	static function createBlock($tag, $option){
		$tag = self::prepareTag($tag);
		
		if(!self::checkException($tag, $option["background_e"])) return false;
		
		$attr = ($option['javascriptType'] == 'js') ? 'background-image': 'src';
		
		preg_match('/background[^;\"]+url\([\",\' ]+(.*)[\",\' ]+\)/siU', $tag, $matchSrc);
		
		if(!$matchSrc[1])
			preg_match('/background[^;\"]+url\((.*)\)/siU', $tag, $matchSrc);
		
		if($matchSrc[1]){
			preg_match('/ class=[\",\'](.*)[\",\']/siU', $tag, $matchClass);

			if($matchClass[0]){
				$nTag = str_replace(
					[
						$matchSrc[0],
						" style=",
						$matchClass[0]
					],
					[
						str_replace($matchSrc[1],$option["default_image_path"],$matchSrc[0]),
						' data-'.$attr.'="'.$matchSrc[1].'" style=',
						' class="'.$matchClass[1].' '.$option["class_lazy"].'"'
					],
					$tag
				);
			}else{
				$nTag = str_replace(
					[
						$matchSrc[0],
						" style="
					],
					[
						str_replace($matchSrc[1],$option["default_image_path"],$matchSrc[0]),
						' data-'.$attr.'="'.$matchSrc[1].'" class="'.$option["class_lazy"].'" style='
					],
					$tag
				);
			}
		}
		
		return $nTag;
	}
	
	
	static function onBufferContent(&$bufferContent){		
		if(!Loader::includeModule(self::MODULE_ID) || defined('LOCK_LAZYLOAD')) return false;
		if(!UTools::checkStatus() || !UTools::checkAjax() || !Tools::chechEnable()) return false;
		if(!UTools::checkPageException(UTools::getSiteSetting('page_exceptions'))) return false;

		if(!UTools::isHtmlPage($bufferContent)) return false;
		
		$javascriptType = UTools::getSiteSetting('javascript_lib', 'jquery');
		$dm = (UTools::getSiteSetting('debug', "N") == 'Y');

		$option = [
			'debug' => $dm,

			'default_image_path' => '/bitrix/images/arturgolubev.lazyimage/pixel.gif',
			'default_iframe_path' => '/bitrix/tools/arturgolubev.lazyimage/pixel.html',
			
			'javascriptType' => $javascriptType,

			'class_lazy' => 'agll',
			'class_lazy_loaded' => 'agll_loaded',
			'class_lazy_inited' => 'agll_inited',
			
			'iframe' => UTools::getBoolSiteSetting('enable_iframe'),
			'iframe_e' => UTools::explodeByEOL(UTools::getSiteSetting('exceptions_iframe')),
			'iframe_btech' => UTools::getBoolSiteSetting('browser_ll_iframe'),
			
			'image' => UTools::getBoolSiteSetting('enable_image'),
			'image_e' => UTools::explodeByEOL(UTools::getSiteSetting('exceptions_image')),
			'image_noscript' => UTools::getBoolSiteSetting('noscript_image'),
			'image_btech' => UTools::getBoolSiteSetting('browser_ll_image'),
			
			'background' => UTools::getBoolSiteSetting('enable_bg'),
			'background_e' => UTools::explodeByEOL(UTools::getSiteSetting('exceptions_bg')),
			
			'effect_type' => (UTools::getSiteSetting('effect_type', "fadeIn") == "Y" ? 'fadeIn' : 'show'),
			'effect_speed' => UTools::getSiteSetting('effect_speed', "500"),
			'preloading' => IntVal(UTools::getSiteSetting('preloading', "1")),
		];
		
		$option['load_iframe_script'] = ($option['iframe'] && !$option['iframe_btech']);
		$option['load_main_script'] = ($option['load_iframe_script'] || $option['background'] || ($option['image'] && !$option['image_btech']));

		if($option['effect_type'] == 'show') $option['effect_speed'] = 0;
		if($option['preloading'] <= 0) $option['preloading'] = 1;
		
		$option["iframe_e"][] = 'www.googletagmanager.com';
		
		$option["image_e"][] = 'mc.yandex.ru';
		$option["image_e"][] = 'mail.ru';
		$option["image_e"][] = 'vk.com';
		$option["image_e"][] = 'facebook.com';
		
		// echo '<pre>'; print_r($option); echo '</pre>';
		// echo '<pre>javascriptType '; print_r($javascriptType); echo '</pre>';
		
		$mainSearch = [];
		$mainReplace = [];
		
		if($option['iframe']){
			preg_match_all('/\<iframe.*\>/sU', $bufferContent, $frames);
			$frames = $frames[0];
			
			if(!empty($frames))
			{
				foreach($frames as $frame){
					if($newFrame = self::createIframe($frame, $option))
					{
						$mainSearch[] = $frame;
						$mainReplace[] = $newFrame;
					}
				}
			}
		}
		
		if($option['image']){
			preg_match_all('/\<img.*\>/sU', $bufferContent, $images);
			$images = $images[0];
			
			if(!empty($images))
			{
				foreach($images as $image){
					if($newImage = self::createImage($image, $option))
					{
						$mainSearch[] = $image;
						$mainReplace[] = $newImage;
					}
				}
			}
		}
		
		if($option['background']){
			preg_match_all('/\<[^\/][^>]+background[^>]+url[^>]+\>/', $bufferContent, $divs);
			$divs = $divs[0];
			
			if(!empty($divs))
			{
				foreach($divs as $div){
					if($newDiv = self::createBlock($div, $option))
					{
						$mainSearch[] = $div;
						$mainReplace[] = $newDiv;
					}
				}
			}
		}
		
		if(!empty($mainSearch))
			$bufferContent = str_replace($mainSearch, $mainReplace, $bufferContent);
		
		if($javascriptType == 'jquery' && $option['load_main_script']){
			$s = '<script src="/bitrix/js/arturgolubev.lazyimage/lazy.min.js"></script>';

			if($option['load_iframe_script']){
				$s .= '<script src="/bitrix/js/arturgolubev.lazyimage/lazy.iframe.min.js"></script>';
			}

			$vers = filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/arturgolubev.lazyimage/script.min.js');
			$s .= '<script src="/bitrix/js/arturgolubev.lazyimage/script.min.js?v='.$vers.'"></script>';

			$s .= '<script>
				function initAgLazyImage(){
					var $lazyEl = $(".'.$option["class_lazy"].'").not(".'.$option["class_lazy_inited"].'");
					if($lazyEl.length > 0){
						'.Tools::cl($dm, 'Lazy Init Length = "+ $lazyEl.length + "').'
						$lazyEl.lazy({
							effect: "'.$option['effect_type'].'",
							effectTime: '.$option['effect_speed'].',
							threshold: '.$option['preloading'].',
							visibleOnly: false,
							
							afterLoad: function(element) {
								'.Tools::cl($dm, "Lazy: image loaded").'
								element.addClass("'.$option["class_lazy_loaded"].'");
							},
						});
						$lazyEl.addClass("'.$option["class_lazy_inited"].'");
					};
				};
				
				function initAgImageChecker(){
					setTimeout(function(){
						'.Tools::cl($dm, "Lazy: checks").'
						
						var noLoaded = $(".'.$option["class_lazy"].'.'.$option["class_lazy_inited"].'").not(".'.$option["class_lazy_loaded"].'");
						if(noLoaded.length > 0) {
							var instance = noLoaded.data("plugin_lazy"); if(typeof instance == "object"){
								'.Tools::cl($dm, 'Lazy: image pixel length = " + noLoaded.length + "').'
								instance.update();
							}
						}
						initAgLazyImage();
						initAgImageChecker();
					}, 1500);
				}
				
				'.Tools::cl($dm, 'jQuery "+ (jQuery ? $().jquery : "NOT") +" loaded').'
				
				if(window.frameCacheVars !== undefined){
					BX.addCustomEvent("onFrameDataReceived", function(json){
						'.Tools::cl($dm, "Lazy Init Type = composite").'
						initAgLazyImage();
						initAgImageChecker();
					});
				}else{
					$(function(){
						'.Tools::cl($dm, "Lazy Init Type = ready").'
						initAgLazyImage();
						initAgImageChecker();
					});
				}
			</script>';
		}elseif($javascriptType == 'js' && $option['load_main_script']){
			// $s = '<script src="/bitrix/js/arturgolubev.lazyimage/lozad.min.js"></script>';
			// $vers = filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/arturgolubev.lazyimage/script.min.js');
			// $s .= '<script src="/bitrix/js/arturgolubev.lazyimage/script.min.js?v='.$vers.'"></script>';

			$s = '<script>
				var aglazyimage_params = '.CUtil::PhpToJSObject($option).';				
				var aglazyimage_obj = new JAgLazyimage(aglazyimage_params);
			</script>';
		}
		
		if($s){
			$bufferContent = UTools::addBodyScript(str_replace([PHP_EOL, "\t"], '', $s), $bufferContent, 1);
		}
	}
	
	static function onEpilog(){
		if(!Loader::includeModule(self::MODULE_ID) || defined('LOCK_LAZYLOAD')) return false;
		if(!UTools::checkStatus() || !UTools::checkAjax() || !Tools::chechEnable()) return false;
		if(!UTools::checkPageException(UTools::getSiteSetting('page_exceptions'))) return false;
		
		$addJquery = (UTools::getBoolSiteSetting('jquery') && UTools::getSiteSetting('javascript_lib', 'jquery') == 'jquery');
		if($addJquery){
			CJSCore::Init(["jquery"]);
		}

		if(UTools::getSiteSetting('javascript_lib', 'jquery') == 'js'){
			UTools::addJs('/bitrix/js/arturgolubev.lazyimage/lozad.min.js');
			UTools::addJs('/bitrix/js/arturgolubev.lazyimage/script.min.js');
		}
	}
}
?>