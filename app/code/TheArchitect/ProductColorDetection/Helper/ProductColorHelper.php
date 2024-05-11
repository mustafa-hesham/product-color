<?php

/**
 * TheArchitect_ProductColorDetection
 *
 * @category TheArchitect
 * @package TheArchitect_ProductColorDetection
 */

namespace TheArchitect\ProductColorDetection\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use TheArchitect\ProductColorDetection\Block\Adminhtml\Product\Steps\DetectButton as BlockDetectColor;
use Magento\Framework\Exception\LocalizedException;
use Exception;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Swatches\Model\SwatchFactory;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor as ProductProcessor;

class ProductColorHelper extends AbstractHelper
{
    public const OPTION_LABEL_REGEX = '/^[A-Za-z][A-Za-z-\s]+$/D';

    /**
     * @var AttributeRepository
     */
    protected AttributeRepository $attributeRepository;

    /**
     * @var SwatchCollectionFactory
     */
    protected SwatchCollectionFactory $swatchCollectionFactory;

    /**
     * @var MessageManager
     */
    protected MessageManager $messageManager;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    protected AttributeOptionLabelInterfaceFactory $attributeOptionLabelFactory;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    protected AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory;

    /**
     * @var AttributeOptionManagementInterface
     */
    protected AttributeOptionManagementInterface $attributeOptionManagement;

    /**
     * @var SwatchFactory
     */
    protected SwatchFactory $swatchFactory;

    /**
     * @var IndexerFactory
     */
    protected IndexerFactory $indexerFactory;

    /**
     * Constructor function
     *
     * @param AttributeRepository $attributeRepository
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param MessageManager $messageManager
     * @param AttributeOptionLabelInterfaceFactory $attributeOptionLabelFactory
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory
     * @param SwatchFactory $swatchFactory
     * @param IndexerFactory $indexerFactory
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SwatchCollectionFactory $swatchCollectionFactory,
        MessageManager $messageManager,
        AttributeOptionLabelInterfaceFactory $attributeOptionLabelFactory,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory,
        SwatchFactory $swatchFactory,
        IndexerFactory $indexerFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->messageManager = $messageManager;
        $this->attributeOptionLabelFactory = $attributeOptionLabelFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOptionInterfaceFactory = $attributeOptionInterfaceFactory;
        $this->swatchFactory = $swatchFactory;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * Return all colors options.
     *
     * @return array
     */
    public function getColorOptions(): array
    {
        $colors = [];
        $options = $this->attributeRepository->get(BlockDetectColor::ATTRIBUTE_CODE)->getOptions();

        foreach ($options as $option) {
            if (preg_match(self::OPTION_LABEL_REGEX, $option->getLabel())) {
                $swatchCollection = $this->swatchCollectionFactory->create();
                $hexColor = $swatchCollection->addFieldToFilter('option_id', intval($option->getValue()))->load()->getFirstItem()->getValue();
                list($r, $g, $b) = sscanf($hexColor, "#%02x%02x%02x");
                array_push($colors, [$option->getLabel(), $hexColor, [$r, $g, $b]]);
            }
        }

        return $colors;
    }

    /**
     * Returns the closest saved color.
     *
     * @param array $colorRgb
     * @return array
     */
    public function getTheClosestColor(array $colorRgb): array
    {
        $colors = $this->getColorOptions();
        $distances = [];

        if (!count($colors)) {
            return $distances;
        }

        foreach ($colors as $c) {
            $squaredSum = 0;
            foreach ($c[2] as $i => $value) {
                $squaredSum += pow(($value - $colorRgb[$i]), 2);
            }
            $distance = sqrt($squaredSum);
            $distances[] = $distance;
        }

        $index_of_smallest = array_keys($distances, min($distances));

        return $colors[$index_of_smallest[0]];
    }

    /**
     * Check if the option already exists.
     *
     * @param string $label
     * @return boolean
     */
    public function isOptionExists(string $label): bool
    {
        $options = $this->getColorOptions();

        foreach ($options as $option) {
            if ($label == $option[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the color option id.
     *
     * @param string $label
     * @return integer|null
     */
    public function getOptionId(string $label): int|null
    {
        $options = $this->attributeRepository->get(BlockDetectColor::ATTRIBUTE_CODE)->getOptions();

        foreach ($options as $option) {
            if ($option->getLabel() == $label) {
                return intval($option->getValue());
            }
        }

        return null;
    }

    /**
     * Add new product color option.
     *
     * @param string $colorName
     * @param string $colorHex
     * @throws LocalizedException
     * @return bool
     */
    public function addNewColorOption(string $colorName, string $colorHex): bool
    {
        $optionLabel = $this->attributeOptionLabelFactory->create();
        $optionLabel->setStoreId(0);
        $optionLabel->setLabel($colorName);

        $option = $this->attributeOptionInterfaceFactory->create()
            ->setLabel($optionLabel->getLabel())
            ->setStoreLabels([$optionLabel])
            ->setIsDefault(false);

        $this->attributeOptionManagement->add(Product::ENTITY, BlockDetectColor::ATTRIBUTE_CODE, $option);

        $optionId = $this->getOptionId($colorName);
        $swatch = $this->swatchFactory->create()
            ->setOptionId($optionId)
            ->setValue($colorHex)
            ->settype(1)
            ->setStoreId(0);

        try {
            $swatch->save();
            $indexer = $this->indexerFactory->create();
            $indexer->load(ProductProcessor::INDEXER_ID);
            $indexer->reindexAll();

            return true;
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to save swatch, Error: ', $e->getMessage()));
        }
    }

    /**
     * Gets top colors closest saved colors.
     *
     * @param array $topColors
     * @return array
     */
    public function getTopColorsClosestColors(array $topColors): array
    {
        $closestTopColors = [];

        foreach ($topColors as $color) {
            array_push($closestTopColors, $this->getTheClosestColor($color[1]));
        }

        return $closestTopColors;
    }

    /**
     * Return options of the color attribute.
     *
     * @return array|null
     */
    public function getRawColorOptions(): array|null
    {
        return $this->attributeRepository->get(BlockDetectColor::ATTRIBUTE_CODE)->getOptions();
    }
}
