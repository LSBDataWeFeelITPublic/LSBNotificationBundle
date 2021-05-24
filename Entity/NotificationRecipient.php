<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\MappedSuperclass;
use LSB\NotificationBundle\Traits\NotificationTrackingStatusTrait;
use LSB\UtilityBundle\Traits\CreatedUpdatedTrait;
use LSB\UtilityBundle\Traits\UuidTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotificationAttachment
 * @ORM\HasLifecycleCallbacks()
 * @MappedSuperclass
 */
class NotificationRecipient implements NotificationRecipientInterface
{
    use UuidTrait;
    use CreatedUpdatedTrait;
    use NotificationTrackingStatusTrait;

    /**
     * @var string[]
     */
    public static array $statusList = [
        self::STATUS_FAILED => 'Notification.Status.Failed',
        self::STATUS_RETRYING => 'Notification.Status.Retrying',
        self::STATUS_PROCESSING => 'Notification.Status.Processing',
        self::STATUS_WAITING => 'Notification.Status.Waiting',
        self::STATUS_COMPLETED => 'Notification.Status.Completed',
        self::STATUS_BLOCKED => 'Notification.Status.Blocked',
    ];

    /**
     * @ORM\ManyToOne(targetEntity="LSB\NotificationBundle\Entity\NotificationInterface", inversedBy="notificationExtendedRecipients")
     */
    protected NotificationInterface $notification;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=255)
     */
    protected string $email;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected ?string $name;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected ?string $phone;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected ?string $token;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $completedAt;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $sendLog = [];

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $errorLog = [];

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected int $status = self::STATUS_WAITING;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default" = 0})
     */
    protected int $retryCount = 0;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="LSB\NotificationBundle\Entity\NotificationLogEntryInterface", mappedBy="notificationRecipient")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected Collection $notificationLogEntries;

    /**
     * NotificationRecipient constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->generateUuid();
        $this->notificationLogEntries = new ArrayCollection();
    }

    /**
     *
     */
    public function increaseRetryCount(): void
    {
        if ($this->retryCount === null) {
            $this->retryCount = 0;
        }

        $this->retryCount++;
    }

    /**
     * Ustawienie statusy ukończenia
     */
    public function setStatusCompleted(): void
    {
        $this->completedAt = new \DateTime('NOW');
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->email;
    }

    /**
     * @return mixed|string|null
     */
    public function getMappedStatus(): ?string
    {
        if (isset(self::$statusList[$this->status])) {
            return self::$statusList[$this->status];
        } else {
            return null;
        }
    }

    /**
     * @return string[]
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @param string[] $statusList
     */
    public static function setStatusList(array $statusList): void
    {
        self::$statusList = $statusList;
    }

    /**
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface
    {
        return $this->notification;
    }

    /**
     * @param NotificationInterface $notification
     * @return $this
     */
    public function setNotification(NotificationInterface $notification): self
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return $this
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     * @return $this
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCompletedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    /**
     * @param DateTime|null $completedAt
     * @return $this
     */
    public function setCompletedAt(?DateTime $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    /**
     * @return array
     */
    public function getSendLog(): array
    {
        return $this->sendLog;
    }

    /**
     * @param ${ENTRY_HINT} $sendLog
     *
     * @return $this
     */
    public function addSendLog($sendLog): self
    {
        if (false === in_array($sendLog, $this->sendLog, true)) {
            $this->sendLog[] = $sendLog;
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $sendLog
     *
     * @return $this
     */
    public function removeSendLog($sendLog): self
    {
        if (true === in_array($sendLog, $this->sendLog, true)) {
            $index = array_search($sendLog, $this->sendLog);
            array_splice($this->sendLog, $index, 1);
        }
        return $this;
    }

    /**
     * @param array $sendLog
     * @return $this
     */
    public function setSendLog(array $sendLog): self
    {
        $this->sendLog = $sendLog;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrorLog(): array
    {
        return $this->errorLog;
    }

    /**
     * @param ${ENTRY_HINT} $errorLog
     *
     * @return $this
     */
    public function addErrorLog($errorLog): self
    {
        if (false === in_array($errorLog, $this->errorLog, true)) {
            $this->errorLog[] = $errorLog;
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $errorLog
     *
     * @return $this
     */
    public function removeErrorLog($errorLog): self
    {
        if (true === in_array($errorLog, $this->errorLog, true)) {
            $index = array_search($errorLog, $this->errorLog);
            array_splice($this->errorLog, $index, 1);
        }
        return $this;
    }

    /**
     * @param array $errorLog
     * @return $this
     */
    public function setErrorLog(array $errorLog): self
    {
        $this->errorLog = $errorLog;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    /**
     * @param int $retryCount
     * @return $this
     */
    public function setRetryCount(int $retryCount): self
    {
        $this->retryCount = $retryCount;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotificationLogEntries()
    {
        return $this->notificationLogEntries;
    }

    /**
     * @param NotificationLogEntryInterface $notificationLogEntrie
     *
     * @return $this
     */
    public function addNotificationLogEntry(NotificationLogEntryInterface $notificationLogEntry): self
    {
        if (false === $this->notificationLogEntries->contains($notificationLogEntry)) {
            $notificationLogEntry->setNotificationRecipient($this);
            $this->notificationLogEntries->add($notificationLogEntry);
        }
        return $this;
    }

    /**
     * @param NotificationLogEntryInterface $notificationLogEntry
     *
     * @return $this
     */
    public function removeNotificationLogEntry(NotificationLogEntryInterface $notificationLogEntry): self
    {
        if (true === $this->notificationLogEntries->contains($notificationLogEntry)) {
            $this->notificationLogEntries->removeElement($notificationLogEntry);
        }
        return $this;
    }

    /**
     * @param ArrayCollection $notificationLogEntries
     * @return $this
     */
    public function setNotificationLogEntries($notificationLogEntries): self
    {
        $this->notificationLogEntries = $notificationLogEntries;
        return $this;
    }
}
