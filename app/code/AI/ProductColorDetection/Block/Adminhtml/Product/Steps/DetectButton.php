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
use Magento\Framework\Stdlib\CookieManagerInterface;

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
     * @var CookieManagerInterface
     */
    protected CookieManagerInterface $cookieManager;

    /**
     * Constructor function
     *
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param FormKey $formKey
     * @param CookieManagerInterface $cookieManager
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        FormKey $formKey,
        CookieManagerInterface $cookieManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->formKey = $formKey;
        $this->cookieManager = $cookieManager;
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

    /**
     * Returns admin auth token.
     *
     * @return string
     */
    public function getAdminAuthToken(): string
    {
        return $this->cookieManager->getCookie('admin');
    }
}
