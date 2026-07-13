<?php

    namespace Ipol\Robokassa;

    use App\Lib\Robokassa\Helper;
    use Bitrix\Main;
    use Bitrix\Main\Localization\Loc;
    use Bitrix\Sale;
    use Bitrix\Main\Web\JWT;
    use DomainException;

    /**
     * Class RobokassaRefund
     * @package Ipol\Robokassa
     */
    final class RobokassaRefundJWT extends \Bitrix\Main\Web\JWT
    {

        public static function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null)
        {
            $header = array('typ' => 'JWT', 'alg' => $alg);
            if ($keyId !== null) {
                $header['kid'] = $keyId;
            }
            if (isset($head) && is_array($head)) {
                $header = array_merge($head, $header);
            }
            $segments = array();
            $segments[] = parent::urlsafeB64Encode(static::jsonEncode($header));
            $segments[] = parent::urlsafeB64Encode(static::jsonEncode($payload));
            $signing_input = implode('.', $segments);

            $signature = static::sign($signing_input, $key, $alg);
            $segments[] = static::urlsafeB64Encode($signature);

            return implode('.', $segments);
        }

        public static function content(string $content)
        {
            return '"' . $content . '"';
        }

        /**
         * Encode a string with URL-safe Base64.
         *
         * @param string $input The string you want encoded
         *
         * @return string The base64 encode of what you passed in
         */
        public static function urlsafeB64Encode($input)
        {
            return \strtr(\base64_encode($input), '+/', '-_');
        }
    }