<?php

/**
 * AI_ProductColorDetection
 *
 * @category AI
 * @package AI_ProductColorDetection
 */

namespace AI\ProductColorDetection\Controller\DetectColor;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;

class Index extends Action implements HttpPostActionInterface
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function execute()
    {
    }
}
