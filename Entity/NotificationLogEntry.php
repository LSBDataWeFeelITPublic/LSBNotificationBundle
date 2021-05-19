<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping as ORM;
use LSB\UserBundle\Entity\UserInterface;
use LSB\UtilityBundle\Traits\CreatedUpdatedTrait;
use LSB\UtilityBundle\Traits\UuidTrait;

/**
 * Class NotificationLogEntry
 * @MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class NotificationLogEntry implements NotificationLogEntryInterface
{
    use UuidTrait;
    use CreatedUpdatedTrait;

    /**
     * Lista typów zdarzeń
     *
     * @var string[]
     */
    public static array $typeList = [
        self::TYPE_TRACKING_DISPLAY => 'Notification.LogEntry.Type.Display',
        self::TYPE_TRACKING_OPEN => 'Notification.LogEntry.Type.Open',
        self::TYPE_TRACKING_CLICK => 'Notification.LogEntry.Type.Click',
    ];

    /**
     * Powiadomienie
     *
     * @var NotificationInterface|null
     * @ORM\ManyToOne(targetEntity="LSB\NotificationBundle\Entity\NotificationInterface", inversedBy="notificationLogEntries")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected ?NotificationInterface $notification;

    /**
     * Odbiorca
     *
     * @var NotificationRecipientInterface|null
     * @ORM\ManyToOne(targetEntity="LSB\NotificationBundle\Entity\NotificationRecipientInterface", inversedBy="notificationLogEntries")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected ?NotificationRecipientInterface $notificationRecipient;

    /**
     * ID sesji
     *
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected ?string $sessionId;

    /**
     * Adres IP
     *
     * @var string|null
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected ?string $ipAddress;

    /**
     * @var UserInterface|null
     * @ORM\ManyToOne(targetEntity="LSB\UserBundle\Entity\UserInterface")
     */
    protected ?UserInterface $user;

    /**
     * Typ zdarzenia
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    protected int $type;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    protected ?string $requestUserAgent;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=2084, nullable=true)
     */
    protected ?string $requestReferer;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $requestAcceptLanguage;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected ?string $xForwardedIpAddress;

    /**
     * Adres URL użyty podczas przekierowania (kliknięty link)
     *
     * @var string|null
     * @ORM\Column(type="string", length=2084, nullable=true)
     */
    protected ?string $clickedUrl;

    /**
     * NotificationLogEntry constructor.
     * @param int $type
     * @throws \Exception
     */
    public function __construct(int $type)
    {
        $this->type = $type;
        $this->generateUuid();
    }

    /**
     * @return string|null
     */
    public function getMappedType(): ?string
    {
        if (isset(self::$typeList[$this->type])) {
            return self::$typeList[$this->type];
        } else {
            return null;
        }
    }

    /**
     * @return string[]
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @param string[] $typeList
     */
    public static function setTypeList(array $typeList): void
    {
        self::$typeList = $typeList;
    }

    /**
     * @return NotificationInterface|null
     */
    public function getNotification(): ?NotificationInterface
    {
        return $this->notification;
    }

    /**
     * @param NotificationInterface|null $notification
     * @return $this
     */
    public function setNotification(?NotificationInterface $notification): self
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @return NotificationRecipientInterface|null
     */
    public function getNotificationRecipient(): ?NotificationRecipientInterface
    {
        return $this->notificationRecipient;
    }

    /**
     * @param NotificationRecipientInterface|null $notificationRecipient
     * @return $this
     */
    public function setNotificationRecipient(?NotificationRecipientInterface $notificationRecipient): self
    {
        $this->notificationRecipient = $notificationRecipient;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * @param string|null $sessionId
     * @return $this
     */
    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param string|null $ipAddress
     * @return $this
     */
    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface|null $user
     * @return $this
     */
    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestUserAgent(): ?string
    {
        return $this->requestUserAgent;
    }

    /**
     * @param string|null $requestUserAgent
     * @return $this
     */
    public function setRequestUserAgent(?string $requestUserAgent): self
    {
        $this->requestUserAgent = $requestUserAgent;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestReferer(): ?string
    {
        return $this->requestReferer;
    }

    /**
     * @param string|null $requestReferer
     * @return $this
     */
    public function setRequestReferer(?string $requestReferer): self
    {
        $this->requestReferer = $requestReferer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestAcceptLanguage(): ?string
    {
        return $this->requestAcceptLanguage;
    }

    /**
     * @param string|null $requestAcceptLanguage
     * @return $this
     */
    public function setRequestAcceptLanguage(?string $requestAcceptLanguage): self
    {
        $this->requestAcceptLanguage = $requestAcceptLanguage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getXForwardedIpAddress(): ?string
    {
        return $this->xForwardedIpAddress;
    }

    /**
     * @param string|null $xForwardedIpAddress
     * @return $this
     */
    public function setXForwardedIpAddress(?string $xForwardedIpAddress): self
    {
        $this->xForwardedIpAddress = $xForwardedIpAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClickedUrl(): ?string
    {
        return $this->clickedUrl;
    }

    /**
     * @param string|null $clickedUrl
     * @return $this
     */
    public function setClickedUrl(?string $clickedUrl): self
    {
        $this->clickedUrl = $clickedUrl;
        return $this;
    }
}
