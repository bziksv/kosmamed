<?
namespace R52\QrCode;
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/r52.qrcode/handlers/r52qrcode/phpqrcode.php');
use Bitrix\Main\Config\Option;


class CreateQrcode extends \QRcode
{
    function create($str, $level = 'H', $size = 3, $margin = 2,  $outfile = '/tmp.png')
    {
        /* Генерация QR-кода во временный файл */
        $this->png($str, $_SERVER['DOCUMENT_ROOT'] . '/upload/qrcode/'.$outfile, $level, $size, $margin);
        /* Конвертация PNG8 в PNG24 */
        $im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . '/upload/qrcode/'.$outfile);
        $width = imagesx($im);
        $height = imagesy($im);

        $dst = imagecreatetruecolor($width, $height);
        imagecopy($dst, $im, 0, 0, 0, 0, $width, $height);
        imagedestroy($im);

        /* Наложение логотипа */
        if(Option::get('r52.qrcode', "LOGO_PATH")) {
            $logo = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . Option::get('r52.qrcode', "LOGO_PATH"));
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);

            $new_width = $width / 3;
            $new_height = $logo_height / ($logo_width / $new_width);

            $x = ceil(($width - $new_width) / 2);
            $y = ceil(($height - $new_height) / 2);

            imagecopyresampled($dst, $logo, $x, $y, 0, 0, $new_width, $new_height, $logo_width, $logo_height);
        }
        return $dst;
    }

    public function saveQrLogo($dst, $namePng = 'logo_qrcode') {
        imagepng($dst, $_SERVER['DOCUMENT_ROOT'] .'/upload/qrcode/' . $namePng . '.png');
        return $_SERVER['DOCUMENT_ROOT'] .'/upload/qrcode/' . $namePng . '.png';
    }

    public function getQrLogo($image, $filename = false, $pixelPerPoint = 4, $outerFrame = 4,$saveandprint=FALSE){
        ob_start();
        ImagePng($image);
        $bin = ob_get_clean();
        $b64 = base64_encode($bin);
        return '<img src="' . self::base64_encode_image ($b64,'png') . '"/>';
    }

    public static function base64_encode_image ($b64, $fileType=string) {
        return 'data:image/' . $fileType . ';base64,' . $b64;
    }

}