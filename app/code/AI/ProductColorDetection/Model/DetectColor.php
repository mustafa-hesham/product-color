<?php

/**
 * AI_ProductColorDetection
 *
 * @category AI
 * @package AI_ProductColorDetection
 */

namespace AI\ProductColorDetection\Model;

use AI\ProductColorDetection\Api\DetectColorInterface;

class DetectColor implements DetectColorInterface
{
    /**
     * Returns product color relevant data.
     *
     * @param string $data
     * @return string
     */
    public function getColors(string $data): string
    {
        $decodedData = json_decode($data);

        $resultData = [
            'color' => 'Black',
            'imagePath' => $data
        ];

        return json_encode($resultData);
    }
}
