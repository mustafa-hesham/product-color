<?php

/**
 * TheArchitectAI_ProductColorDetection
 *
 * @category TheArchitectAI
 * @package TheArchitectAI_ProductColorDetection
 */

namespace TheArchitectAI\ProductColorDetection\Model;

use TheArchitectAI\ProductColorDetection\Api\DetectColorInterface;
use Magento\Framework\Encryption\Helper\Security;
use TheArchitectAI\ProductColorDetection\Block\Adminhtml\Product\Steps\DetectButton as BlockDetectColor;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Exception;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class DetectColor implements DetectColorInterface
{
    /**
     * @var BlockDetectColor
     */
    protected BlockDetectColor $blockDetectColor;

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
     * Constructor function
     *
     * @param BlockDetectColor $blockDetectColor
     * @param AttributeRepository $attributeRepository
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param MessageManager $messageManager
     */
    public function __construct(
        BlockDetectColor $blockDetectColor,
        AttributeRepository $attributeRepository,
        SwatchCollectionFactory $swatchCollectionFactory,
        MessageManager $messageManager
    ) {
        $this->blockDetectColor = $blockDetectColor;
        $this->attributeRepository = $attributeRepository;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->messageManager = $messageManager;
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

        $imagePath = '';
        $base64 = '';

        $response = json_encode([
            'object_dominant_color_rgb' => null
        ]);

        if (isset($data['image'])) {
            $imagePath = $data['image'];
            $base64 = $this->convertImageToBase64($imagePath);
            $isRemoveSkin = $this->blockDetectColor->getIsRemoveSkin();
            $response = $this->sendPostRequestToApi($base64, $isRemoveSkin);
            $response = json_decode($response, true);
            $objectApproxColor = $response['object_dominant_color_rgb'];
            $hexColor = sprintf("#%02x%02x%02x", $objectApproxColor[0], $objectApproxColor[1], $objectApproxColor[2]);
            $response['object_dominant_color_hex'] = $hexColor;
            $options = $this->getColorOptions();
            $response['object_closest_saved_color'] = $this->getTheClosestColor($objectApproxColor, $options);
            $response = json_encode($response);
        }

        return $response;
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

    /**
     * Send the image base 64 code and isRemoveSkin parameter by a post request to API.
     *
     * @param string $imageBase64
     * @param boolean $isRemoveSkin
     * @return string|boolean
     */
    public function sendPostRequestToApi(string $imageBase64, bool $isRemoveSkin = true): string|bool
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->blockDetectColor->getApiUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'image_base64' => $imageBase64,
                'remove_skin' => $isRemoveSkin
            ]),
            CURLOPT_HTTPHEADER => [
                "Accept-Encoding: gzip",
                "X-RapidAPI-Host: " . $this->blockDetectColor->getApiHost(),
                "X-RapidAPI-Key: " . $this->blockDetectColor->getApiKey(),
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new LocalizedException(__('Error: %s', $err));
        } else {
            return $response;
        }
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
            if (ctype_alpha($option->getLabel())) {
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
    public function getTheClosestColor(array $colorRgb, array $colors): array
    {
        $distances = [];

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
}
