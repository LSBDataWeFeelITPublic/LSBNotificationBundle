<?php
declare(strict_types=1);


namespace LSB\NotificationBundle\Model;

use LSB\NotificationBundle\Manager\NotificationLogEntryManager;
use LSB\NotificationBundle\Service\NotificationLogManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ExtractedUserData
 * @package LSB\NotificationBundle\Model
 */
class ExtractedUserData
{
    const HEADER_USER_AGENT = 'User-Agent';
    const HEADER_REFERER = 'Referer';
    const HEADER_X_FORWARDED_FOR = 'X-Forwarded-For';

    /**
     * @var string|null
     */
    protected $requestUserAgent;

    /**
     * @var string|null
     */
    protected $requestReferer;

    /**
     * @var string|null
     */
    protected $requestAcceptLanguage;

    /**
     * @var string|null
     */
    protected $requestIpAddress;

    /**
     * @var string|null
     */
    protected $sessionId;

    /**
     * @var string|null
     */
    protected $xForwardedIpAddress;

    /**
     * @var string|null
     */
    protected $clickedUrl;

    /**
     * ExtractedUserData constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->extractUserAgent($request);
        $this->extractAcceptLanguage($request);
        $this->extractReferer($request);
        $this->extractIpAddress($request);
        $this->extractSessionId($request);
        $this->extractXForwardedIpAddress($request);
        $this->extractClickedUrl($request);
    }

    /**
     * @param Request $request
     */
    protected function extractUserAgent(Request $request): ?string
    {
        return $this->requestUserAgent = $request->headers->has(self::HEADER_USER_AGENT) ? $request->headers->get(self::HEADER_USER_AGENT) : null;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function extractAcceptLanguage(Request $request): ?string
    {
        return $this->requestAcceptLanguage = $request->getPreferredLanguage();
    }

    /**
     * @param Request $request
     * @return array|bool|string|string[]|null
     */
    protected function extractReferer(Request $request): ?string
    {
        return $this->requestReferer = $request->headers->has(self::HEADER_REFERER) ? $request->headers->get(self::HEADER_REFERER) : null;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function extractIpAddress(Request $request): ?string
    {
        return $this->requestIpAddress = $request->getClientIp();
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function extractSessionId(Request $request): ?string
    {
        return $this->sessionId = $request->getSession() ? $request->getSession()->getId() : null;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function extractXForwardedIpAddress(Request $request): ?string
    {
        return $this->xForwardedIpAddress = $request->headers->has(self::HEADER_X_FORWARDED_FOR) ? $request->headers->get(self::HEADER_X_FORWARDED_FOR) : null;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function extractClickedUrl(Request $request): ?string
    {
        $redirectTo = $request->get(NotificationLogEntryManager::REQUEST_PARAM_REDIRECT_URL);

        if ($redirectTo && filter_var($redirectTo, FILTER_VALIDATE_URL)) {
            $this->clickedUrl = $redirectTo;
        }

        return $this->clickedUrl;
    }

    /**
     * @return string|null
     */
    public function getRequestUserAgent(): ?string
    {
        return $this->requestUserAgent;
    }

    /**
     * @return string|null
     */
    public function getRequestReferer(): ?string
    {
        return $this->requestReferer;
    }

    /**
     * @return string|null
     */
    public function getRequestAcceptLanguage(): ?string
    {
        return $this->requestAcceptLanguage;
    }

    /**
     * @return string|null
     */
    public function getRequestIpAddress(): ?string
    {
        return $this->requestIpAddress;
    }

    /**
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * @return string|null
     */
    public function getXForwardedIpAddress(): ?string
    {
        return $this->xForwardedIpAddress;
    }

    /**
     * @return string|null
     */
    public function getClickedUrl(): ?string
    {
        return $this->clickedUrl;
    }
}
