<?php

/**
 * TheArchitect_ProductColorDetection
 *
 * @category TheArchitect
 * @package TheArchitect_ProductColorDetection
 */

namespace TheArchitect\ProductColorDetection\Block\Adminhtml\Product\Steps;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Escaper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Ui\Block\Component\StepsWizard\StepAbstract;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;

class DetectButton extends StepAbstract
{
    public const DETECT_COLOR_KEY = 'detect_color_key';
    public const ATTRIBUTE_CODE = 'color';
    public const SCOPE_CONFIG = 'ProductColorDetection/settings/';
    public const API_URL_ID = 'api_url';
    public const API_HOST_ID = 'api_host';
    public const API_KEY_ID = 'api_key';
    public const REMOVE_SKIN_ID = 'remove_skin';

    protected $successMessage = '';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Random
     */
    protected $randomKey;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * Constructor function
     *
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param Random $randomKey
     * @param SessionManagerInterface $sessionManager
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Escaper $escaper
     * @param ScopeConfigInterface $scopeConfig
     * @param AttributeRepository $attributeRepository
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        Random $randomKey,
        SessionManagerInterface $sessionManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
        AttributeRepository $attributeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->randomKey = $randomKey;
        $this->sessionManager = $sessionManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->escaper = $escaper;
        $this->scopeConfig = $scopeConfig;
        $this->attributeRepository = $attributeRepository;
        $this->setDetectColorKey();
    }

    /**
     * Get the form key.
     *
     * @return string
     */
    public function getDetectColorKey(): string
    {
        if (!$this->isDetectColorKeySet()) {
            $this->setDetectColorKey();
        }

        return $this->escaper->escapeJs(
            $this->cookieManager->getCookie(self::DETECT_COLOR_KEY, 'detect_color_key')
        );
    }

    /**
     * Checks if the detect color key is already set.
     *
     * @return boolean
     */
    public function isDetectColorKeySet(): bool
    {
        return (bool) $this->cookieManager->getCookie(self::DETECT_COLOR_KEY);
    }

    /**
     * Set the detect color key cookie.
     *
     * @return void
     */
    public function setDetectColorKey(): void
    {
        if (!$this->isDetectColorKeySet()) {
            $detectColorKey = $this->randomKey->getRandomString(15);
            $this->setDetectColorKeyCookies($detectColorKey);
        }
    }

    /**
     * Sets the detect color button key cookie.
     *
     * @param string $detectColorKey
     * @return boolean
     */
    public function setDetectColorKeyCookies(string $detectColorKey): bool
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setHttpOnly(false)
            ->setDomain($this->sessionManager->getCookieDomain())
            ->setSameSite('Strict')
            ->setSecure(true);

        $this->cookieManager->setPublicCookie(
            self::DETECT_COLOR_KEY,
            $detectColorKey,
            $metadata
        );

        return true;
    }

    /**
     * Returns the scope configuration by field id.
     *
     * @param string $field_Id
     * @return string|integer|null
     */
    public function getScopeConfig(string $field_Id): string
    {
        return $this->scopeConfig->getValue(self::SCOPE_CONFIG . $field_Id);
    }

    /**
     * Returns the API base URL
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->getScopeConfig(self::API_URL_ID);
    }

    /**
     * Returns the API host URL.
     *
     * @return string
     */
    public function getApiHost(): string
    {
        return $this->getScopeConfig(self::API_HOST_ID);
    }

    /**
     * Returns the API secret key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->getScopeConfig(self::API_KEY_ID);
    }

    /**
     * Returns if it should remove the human model skin colors tones from image.
     *
     * @return boolean
     */
    public function getIsRemoveSkin(): bool
    {
        return (int)$this->getScopeConfig(self::REMOVE_SKIN_ID);
    }

    /**
     * Get Color attribute ID.
     *
     * @return integer|null
     */
    public function getColorAttributeId(): int
    {
        return $this->attributeRepository->get(self::ATTRIBUTE_CODE)->getAttributeId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Detect Object Color');
    }
}
