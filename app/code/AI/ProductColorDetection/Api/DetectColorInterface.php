<?php

/**
 * AI_ProductColorDetection
 *
 * @category AI
 * @package AI_ProductColorDetection
 */

namespace AI\ProductColorDetection\Api;

interface DetectColorInterface
{
    /**
     * Returns product color relevant data.
     *
     * @param string $data
     * @return string
     */
    public function getColors(string $data): string;
}
