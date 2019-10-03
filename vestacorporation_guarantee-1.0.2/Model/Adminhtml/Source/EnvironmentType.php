<?php
/**
 * Vesta Fraud protection module environment Source model
 *
 * @author Chetu Team.
 */
namespace Vesta\Guarantee\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class for environment type
 *
 * @author Chetu Team.
 */
class EnvironmentType implements ArrayInterface
{
    const PRODUCTION_ENVIRONMENT = 'production';
    const SANDBOX_ENVIRONMENT = 'sandbox';

    /**
     * Vesta environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SANDBOX_ENVIRONMENT,
                'label' => 'Sandbox',
            ],
            [
                'value' => self::PRODUCTION_ENVIRONMENT,
                'label' => 'Production'
            ]
        ];
    }
}
