<?php

/**
 * Vesta Fraud protection admin orders.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Ui\Component\vSafeGridColoumn;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Vesta fraud protection admin orders status related functions.
 *
 * @author Chetu Team.
 */
class GuaranteeStatus extends Column
{

    /**
     *
     * @var object
     */
    protected $orderRepository;

    /**
     *
     * @var object
     */
    protected $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
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
                    $order = $this->orderRepository->get($item["entity_id"]);
                    $status = $order->getData("vesta_guarantee_status");

                    switch ($status) {
                        case "0":
                            $vesta_guarantee_status = "Declined";
                            break;
                        case "1":
                            $vesta_guarantee_status = "Approved";
                            break;
                        default:
                            $vesta_guarantee_status = "N/A";
                            break;
                    }
                    $item[$this->getData('name')] = $vesta_guarantee_status;
                } else {
                    $item[$this->getData('name')] = "";
                }
            }
        }
        return $dataSource;
    }
}
