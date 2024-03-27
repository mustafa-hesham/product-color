<?php

/**
 * AI_ProductColorDetection
 *
 * @category AI
 * @package AI_ProductColorDetection
 */

namespace AI\ProductColorDetection\Block\Adminhtml\Product\Steps;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Escaper;

class DetectButton extends Template
{
    public const DETECT_COLOR_KEY = 'detect_color_key';

    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Random
     */
    protected Random $randomKey;

    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManager;

    /**
     * @var CookieManagerInterface
     */
    protected CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var Escaper
     */
    protected Escaper $escaper;

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
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->randomKey = $randomKey;
        $this->sessionManager = $sessionManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->escaper = $escaper;
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
     * Set the detect color key.
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

    public function setDetectColorKeyCookies($detectColorKey)
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
}
