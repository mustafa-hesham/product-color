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

    /**
     * Adds new color attribute option
     *
     * @param mixed $data
     * @return string
     */
    public function addColorOption($data): string;

    /**
     * Gets the color options.
     *
     * @param mixed $data
     * @return string
     */
    public function getColorOptions($data): string;
}
