<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use LSB\UserBundle\Entity\UserInterface;
use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Interface NotificationLogEntryInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationLogEntryInterface extends UuidInterface
{
    /** @var int The message was displayed in client window */
    const TYPE_TRACKING_DISPLAY = 10;

    /** @var int The message was opened in the browser*/
    const TYPE_TRACKING_OPEN = 100;

    /** @var int Message links were clicked */
    const TYPE_TRACKING_CLICK = 200; //Kliknięcie w link

    /**
     * @return string[]
     */
    public static function getTypeList(): array;

    /**
     * @param string[] $typeList
     */
    public static function setTypeList(array $typeList): void;

    /**
     * @param string|null $requestUserAgent
     * @return $this
     */
    public function setRequestUserAgent(?string $requestUserAgent): self;

    /**
     * @return string|null
     */
    public function getClickedUrl(): ?string;

    /**
     * @return string|null
     */
    public function getRequestAcceptLanguage(): ?string;

    /**
     * @return NotificationInterface|null
     */
    public function getNotification(): ?NotificationInterface;

    /**
     * @return string|null
     */
    public function getRequestUserAgent(): ?string;

    /**
     * @return string|null
     */
    public function getIpAddress(): ?string;

    /**
     * @param string|null $sessionId
     * @return $this
     */
    public function setSessionId(?string $sessionId): self;

    /**
     * @param NotificationInterface|null $notification
     * @return $this
     */
    public function setNotification(?NotificationInterface $notification): self;

    /**
     * @param NotificationRecipientInterface|null $notificationRecipient
     * @return $this
     */
    public function setNotificationRecipient(?NotificationRecipientInterface $notificationRecipient): self;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return NotificationRecipientInterface|null
     */
    public function getNotificationRecipient(): ?NotificationRecipientInterface;

    /**
     * @return string|null
     */
    public function getXForwardedIpAddress(): ?string;

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self;

    /**
     * @return string|null
     */
    public function getRequestReferer(): ?string;

    /**
     * @param string|null $requestReferer
     * @return $this
     */
    public function setRequestReferer(?string $requestReferer): self;

    /**
     * @param string|null $ipAddress
     * @return $this
     */
    public function setIpAddress(?string $ipAddress): self;

    /**
     * @param string|null $clickedUrl
     * @return $this
     */
    public function setClickedUrl(?string $clickedUrl): self;

    /**
     * @param UserInterface|null $user
     * @return $this
     */
    public function setUser(?UserInterface $user): self;

    /**
     * @return string|null
     */
    public function getSessionId(): ?string;

    /**
     * @param string|null $requestAcceptLanguage
     * @return $this
     */
    public function setRequestAcceptLanguage(?string $requestAcceptLanguage): self;

    /**
     * @return string|null
     */
    public function getMappedType(): ?string;

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

    /**
     * @param string|null $xForwardedIpAddress
     * @return $this
     */
    public function setXForwardedIpAddress(?string $xForwardedIpAddress): self;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return NotificationRecipientInterface|null
     */
    public function getNotificationRecipient(): ?NotificationRecipientInterface;

    /**
     * @return string|null
     */
    public function getXForwardedIpAddress(): ?string;

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self;

    /**
     * @return string|null
     */
    public function getRequestReferer(): ?string;

    /**
     * @param string|null $requestReferer
     * @return $this
     */
    public function setRequestReferer(?string $requestReferer): self;

    /**
     * @param string|null $ipAddress
     * @return $this
     */
    public function setIpAddress(?string $ipAddress): self;

    /**
     * @param string|null $clickedUrl
     * @return $this
     */
    public function setClickedUrl(?string $clickedUrl): self;

    /**
     * @param UserInterface|null $user
     * @return $this
     */
    public function setUser(?UserInterface $user): self;

    /**
     * @return string|null
     */
    public function getSessionId(): ?string;

    /**
     * @param string|null $requestAcceptLanguage
     * @return $this
     */
    public function setRequestAcceptLanguage(?string $requestAcceptLanguage): self;

    /**
     * @return string|null
     */
    public function getMappedType(): ?string;

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

    /**
     * @param string|null $xForwardedIpAddress
     * @return $this
     */
    public function setXForwardedIpAddress(?string $xForwardedIpAddress): self;
}