<?php

/**
 * AI_ProductColorDetection
 *
 * @category AI
 * @package AI_ProductColorDetection
 */

namespace AI\ProductColorDetection\Model;

use AI\ProductColorDetection\Api\DetectColorInterface;
use Magento\Framework\Encryption\Helper\Security;
use AI\ProductColorDetection\Block\Adminhtml\Product\Steps\DetectButton as BlockDetectColor;

class DetectColor implements DetectColorInterface
{
    /**
     * @var BlockDetectColor
     */
    protected BlockDetectColor $blockDetectColor;

    /**
     * Constructor function
     *
     * @param BlockDetectColor $blockDetectColor
     */
    public function __construct(BlockDetectColor $blockDetectColor)
    {
        $this->blockDetectColor = $blockDetectColor;
    }

    /**
     * Returns product color relevant data.
     *
     * @param mixed $data
     * @return string
     */
    public function getColors($data): string
    {
        if (!Security::compareStrings($this->blockDetectColor->getDetectColorKey(), $data['form_key'])) {
            return 'Error';
        }

        $sessionDetectKey = $this->blockDetectColor->getDetectColorKey();
        $formKey = $data['form_key'];

        $resultData = [
            'color' => 'Black',
            'imagePath' => $data['image']
        ];

        return json_encode($resultData);
    }
}
