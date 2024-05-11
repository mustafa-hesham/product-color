<?php

/**
 * TheArchitect_ProductColorDetection
 *
 * @category TheArchitect
 * @package TheArchitect_ProductColorDetection
 */

namespace TheArchitect\ProductColorDetection\Model;

use TheArchitect\ProductColorDetection\Api\DetectColorInterface;
use Magento\Framework\Encryption\Helper\Security;
use TheArchitect\ProductColorDetection\Block\Adminhtml\Product\Steps\DetectButton as BlockDetectColor;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Exception;
use TheArchitect\ProductColorDetection\Helper\ProductColorHelper;

class DetectColor implements DetectColorInterface
{
    /**
     * @var BlockDetectColor
     */
    protected $blockDetectColor;

    /**
     * @var ProductColorHelper
     */
    protected $productColorHelper;

    /**
     * Constructor function
     *
     * @param BlockDetectColor $blockDetectColor
     * @param ProductColorHelper $productColorHelper
     */
    public function __construct(
        BlockDetectColor $blockDetectColor,
        ProductColorHelper $productColorHelper
    ) {
        $this->blockDetectColor = $blockDetectColor;
        $this->productColorHelper = $productColorHelper;
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
            $isRemoveSkin = $data['isRemoveSkin'];
            $response = $this->sendPostRequestToApi($base64, $isRemoveSkin);
            $response = json_decode($response, true);
            $objectApproxColor = $response['object_dominant_color_rgb'];
            $hexColor = sprintf("#%02x%02x%02x", $objectApproxColor[0], $objectApproxColor[1], $objectApproxColor[2]);
            $response['object_dominant_color_hex'] = $hexColor;
            $response['object_closest_saved_color'] = $this->productColorHelper->getTheClosestColor($objectApproxColor);
            $response['top_colors_closest_colors'] = $this->productColorHelper->getTopColorsClosestColors($response['top_colors']);
            $response = json_encode($response);
        }

        return $response;
    }

    /**
     * Adds new color attribute option
     *
     * @param mixed $data
     * @return string
     */
    public function addColorOption($data): string
    {
        if (!Security::compareStrings($this->blockDetectColor->getDetectColorKey(), $data['detect_color_key'])) {
            throw new AuthenticationException(__('The detect color key is not valid.'));
        }

        $isOptionAdded = false;

        if (isset($data['colorName'])) {
            if ($this->productColorHelper->isOptionExists($data['colorName'])) {
                return json_encode([
                    'is_added' => $isOptionAdded
                ]);
            }

            $colorName =  $data['colorName'];
            $colorHex = $data['colorHex'];
            $isOptionAdded = $this->productColorHelper->addNewColorOption($colorName, $colorHex);

            return json_encode([
                'is_added' => $isOptionAdded
            ]);
        }

        return json_encode([
            'is_added' => $isOptionAdded
        ]);
    }

    /**
     * Gets the color options.
     *
     * @param mixed $data
     * @return string
     */
    public function getColorOptions($data): string
    {
        if (!Security::compareStrings($this->blockDetectColor->getDetectColorKey(), $data['detect_color_key'])) {
            throw new AuthenticationException(__('The detect color key is not valid.'));
        }

        $options = $this->productColorHelper->getRawColorOptions();
        $formattedOptions = [];

        if ($options) {
            foreach ($options as $option) {
                if (preg_match(ProductColorHelper::OPTION_LABEL_REGEX, $option->getLabel())) {
                    array_push(
                        $formattedOptions,
                        [
                            'value' => $option->getValue(),
                            'label' => $option->getLabel()
                        ]
                    );
                }
            }
        }

        return json_encode($formattedOptions);
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
    public function sendPostRequestToApi(string $imageBase64, bool $isRemoveSkin = true): string
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
}
