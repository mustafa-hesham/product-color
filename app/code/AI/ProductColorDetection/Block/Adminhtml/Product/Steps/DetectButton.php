<?php

/**
 * AI_ProductColorDetection
 *
 * @category AI
 * @package AI_ProductColorDetection
 */

namespace AI\ProductColorDetection\Block\Adminhtml\Product\Steps;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;

class DetectButton extends Template
{
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var FormKey
     */
    protected FormKey $formKey;

    /**
     * Constructor function
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager, Context $context, FormKey $formKey, array $data = [])
    {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->formKey = $formKey;
    }

    /**
     * Get the form key.
     *
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }
}
