<?php

/**
 * Vesta Fraud protection admin status class.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Ui\Component\vSafeGridColoumn;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Vesta fraud protection admin grid related functions.
 *
 * @author Chetu Team.
 */
class VsafeStatus extends Column
{
    
    /**
     * HTML escaper
     *
     * @var object
     */
    protected $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource = [])
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (array_key_exists($this->getData('name'), $item)) {
                    $item[$this->getData('name')] = $item['vesta_guarantee_response'];
                } else {
                    $item[$this->getData('name')] = "";
                }
            }
        }

        return $dataSource;
    }
}
