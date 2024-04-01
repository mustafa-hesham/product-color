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
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Exception;

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
        if (!Security::compareStrings($this->blockDetectColor->getDetectColorKey(), $data['detect_color_key'])) {
            throw new AuthenticationException(__('The detect color key is not valid.'));
        }

        $imagePath = $data['image'];
        $base64 = $this->convertImageToBase64($imagePath);

        $resultData = [
            'color' => 'Black',
            'imagePath' => $data['image']
        ];

        return json_encode($resultData);
    }


    /**
     * Converts image to base64 encoding.
     *
     * @param string $imagePath
     * @return string
     * @throws LocalizedException
     */
    public function convertImageToBase64(string $imagePath): string
    {
        try {
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = file_get_contents($imagePath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (Exception $e) {
            throw new LocalizedException(__('Please provide a valid image path. Error: ' . $e->getMessage()));
        }
    }
}
