<?php

/**
 * TheArchitectAI_ProductColorDetection
 *
 * @category TheArchitectAI
 * @package TheArchitectAI_ProductColorDetection
 */

namespace TheArchitectAI\ProductColorDetection\Api;

interface DetectColorInterface
{
    /**
     * Returns product color relevant data.
     *
     * @param mixed $data
     * @return string
     */
    public function getColors($data): string;
}
