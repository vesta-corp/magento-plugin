<?php
/**
 * VestaResponseCodes File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Helper;

/**
 * VestaResponseCodes Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

class VestaResponseCodes
{
    /**
     * response Codes auth.
     *
     * @var array
     */
    public static $authCodes = [
        '1' => 'Bank denied.',
        '3' => 'vSafe denied.',
        '6' => 'Authorization communication error.',
        '13' => 'Business rules denied.',
    ];

    /**
     * response Codes payment.
     *
     * @var array
     */
    public static $payResCodes = [
        '1003' => 'Payment is already confirmed.',
        '1' => 'Vesta Payment, Bank Denied.',
        '2' => 'vSafe Pended.',
        '3' => 'vSafe Denied.',
        '6' => 'Authorization Communication Error.',
        '1001' => 'Vesta Payment method Login Failed.',
        '6' => 'Authorization Communication Error.',
        '52' => 'Post-Authorization completed.',
        '51' => 'Pre-Authorization completed.',
        '10' => 'Successful payment.',
        '5' => 'Authorized.',
        '61' => 'Pre-Authorization communication failure.',
        '62' => 'Post-Authorization communication failure.',
    ];

    /**
     * get auth response text.
     *
     * @param int $code
     * @return string
     */
    public static function getAuthCodeText(int $code = null)
    {
        return self::$authCodes[$code];
    }

    /**
     * get response text.
     *
     * @param int $code
     * @return string
     */
    public static function getPaymentCodeText(int $code = null)
    {
        return self::$payResCodes[$code];
    }
}
