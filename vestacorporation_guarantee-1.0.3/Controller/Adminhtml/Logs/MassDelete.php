<?php
/**
 * Vesta MassDelete Controller
 */
 
namespace Vesta\Guarantee\Controller\Adminhtml\Logs;
 
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Vesta\Guarantee\Model\ResourceModel\Logs\CollectionFactory;
    
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;
    protected $_idFieldName = 'log_id';
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
 
    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $_context,
        Filter $_filter,
        CollectionFactory $_collectionFactory
    ) {
       
        $this->filter = $_filter;
        $this->collectionFactory = $_collectionFactory;
        parent::__construct($_context);
    }
 
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getItems() as $record) {
            $record->getLogId();
            $record->setId($record->getLogId());
            $record->delete();
        }
        $this->messageManager->addSuccess(__('Your logs have been deleted.'));
 
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
 
    /**
     * Check Category Map recode delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('log_id::row_data_delete');
    }
}
