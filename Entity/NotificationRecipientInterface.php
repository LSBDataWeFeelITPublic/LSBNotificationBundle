<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Interface NotificationLogEntryInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationRecipientInterface extends UuidInterface
{
    //Statusy wysyłki
    const STATUS_WAITING = 1;
    const STATUS_RETRYING = 2;
    const STATUS_PROCESSING = 10;
    const STATUS_COMPLETED = 100;
    const STATUS_BLOCKED = 101;
    const STATUS_FAILED = 102;

    /**
     * @param string[] $statusList
     */
    public static function setStatusList(array $statusList): void;

    /**
     * @return string[]
     */
    public static function getStatusList(): array;

    /**
     * @return $this
     */
    public function removeSendLog($sendLog): self;

    /**
     *
     */
    public function increaseRetryCount(): void;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * @return int
     */
    public function getRetryCount(): int;

    /**
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface;

    /**
     * @return DateTime|null
     */
    public function getCompletedAt(): ?DateTime;

    /**
     * @param array $errorLog
     * @return $this
     */
    public function setErrorLog(array $errorLog): self;

    /**
     * @return array
     */
    public function getSendLog(): array;

    /**
     * @param ${ENTRY_HINT} $errorLog
     *
     * @return $this
     */
    public function addErrorLog($errorLog): self;

    /**
     * @param NotificationLogEntryInterface $notificationLogEntry
     *
     * @return $this
     */
    public function removeNotificationLogEntry(NotificationLogEntryInterface $notificationLogEntry): self;

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self;

    /**
     * @return $this
     */
    public function addSendLog($sendLog): self;

    /**
     * @param NotificationInterface $notification
     * @return $this
     */
    public function setNotification(NotificationInterface $notification): self;

    /**
     * @return mixed|string|null
     */
    public function getMappedStatus(): ?string;

    /**
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self;

    /**
     * @param DateTime|null $completedAt
     * @return $this
     */
    public function setCompletedAt(?DateTime $completedAt): self;

    /**
     * @param int $retryCount
     * @return $this
     */
    public function setRetryCount(int $retryCount): self;

    /**
     * @return array
     */
    public function getErrorLog(): array;

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): self;

    /**
     * Ustawienie statusy ukończenia
     */
    public function setStatusCompleted(): void;

    /**
     * @param string|null $phone
     * @return $this
     */
    public function setPhone(?string $phone): self;

    /**
     * @param ArrayCollection $notificationLogEntries
     * @return $this
     */
    public function setNotificationLogEntries($notificationLogEntries): self;

    /**
     * @param NotificationLogEntryInterface $notificationLogEntrie
     *
     * @return $this
     */
    public function addNotificationLogEntry(NotificationLogEntryInterface $notificationLogEntry): self;

    /**
     * @return ArrayCollection
     */
    public function getNotificationLogEntries();

    /**
     * @return $this
     */
    public function removeErrorLog($errorLog): self;

    /**
     * @param array $sendLog
     * @return $this
     */
    public function setSendLog(array $sendLog): self;

    /**
     * @param string|null $token
     * @return $this
     */
    public function setToken(?string $token): self;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @return string|null
     */
    public function getName(): ?string;
}