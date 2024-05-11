<?php

/**
 * TheArchitect_ProductColorDetection
 *
 * @category TheArchitect
 * @package TheArchitect_ProductColorDetection
 */

namespace TheArchitect\ProductColorDetection\ViewModel\Product\Steps;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\SerializerInterface;

class DetectButtonView implements ArgumentInterface
{
    public const COMPONENT_NAME = 'detect_button';
    public const BUTTON_VALUE = 'Detect Object Color';
    public const SCOPE = '.DetectButton-Wrapper';
    public const CLASSES = 'DetectButton action-primary';

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * Constructor function
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Returns the button classes
     *
     * @return string
     */
    public function getDetectButtonClasses(): string
    {
        return self::CLASSES;
    }

    /**
     * Returns scope component name
     *
     * @return string
     */
    public function getComponentName(): string
    {
        return self::COMPONENT_NAME;
    }

    /**
     * Returns Detect color button text
     *
     * @return string
     */
    public function getButtonValue(): string
    {
        return self::BUTTON_VALUE;
    }

    /**
     * Serialize data into string
     *
     * @param array $data
     * @return string|boolean
     */
    public function serializeData(array $data): string
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Get the scope
     *
     * @return string
     */
    public function getScope(): string
    {
        return self::SCOPE;
    }
}
