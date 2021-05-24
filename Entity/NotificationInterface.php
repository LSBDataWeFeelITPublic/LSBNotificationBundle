<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LSB\OrderBundle\Entity\OrderInterface;
use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Interface NotificationInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationInterface extends UuidInterface
{
    //Statusy wysyłki
    const STATUS_WAITING = 1;
    const STATUS_RETRYING = 20;
    const STATUS_PROCESSING = 30;
    const STATUS_COMPLETED = 100;
    const STATUS_BLOCKED = 110;
    const STATUS_FAILED = 120;
    const STATUS_DATA_ERROR = 200;

    //Strategie przetwarzania
    const STRATEGY_NOW = 1;

    //Typy powiadomień/wysyłek
    const TYPE_SIMPLE = 10;
    const TYPE_EXTENDED = 20;

    /**
     * Constructor
     */
    public function __construct(string $languageCode = 'PL');

    /**
     * @param array $statusList
     */
    public static function setStatusList(array $statusList): void;

    /**
     * @return array
     */
    public static function getStrategyList(): array;

    /**
     * @param array $strategyList
     */
    public static function setStrategyList(array $strategyList): void;

    /**
     * @return array
     */
    public static function getTypeList(): array;

    /**
     * @param array $typeList
     */
    public static function setTypeList(array $typeList): void;

    /**
     * @return array
     */
    public static function getStatusList(): array;

    /**
     * @param int $trackingClickCount
     * @return $this
     */
    public function setTrackingClickCount(int $trackingClickCount): self;

    /**
     * @return int
     */
    public function getTrackingDisplayCount(): int;

    /**
     * @param string $channelName
     * @return $this
     */
    public function setChannelName(string $channelName): self;

    /**
     * @param array $cc
     * @return $this
     */
    public function setCc(array $cc): self;

    /**
     * @param ${ENTRY_HINT} $sendLog
     *
     * @return $this
     */
    public function addSendLog($sendLog): self;

    /**
     * @param bool $isPublicViewEnabled
     * @return $this
     */
    public function setIsPublicViewEnabled(bool $isPublicViewEnabled): self;

    /**
     * @return ArrayCollection|Collection
     */
    public function getNotificationAttachments();

    /**
     * @return string|null
     */
    public function getNotificationDomain(): ?string;

    /**
     * @return bool
     */
    public function isConvertImagesIntoAttachments(): bool;

    /**
     * @return string|null
     */
    public function getMappedStatus(): ?string;

    /**
     * @param ${ENTRY_HINT} $notificationAttachment
     *
     * @return $this
     */
    public function addNotificationAttachment($notificationAttachment);

    /**
     * @param string|null $template
     * @return $this
     */
    public function setTemplate(?string $template): self;

    /**
     * @param string|null $subject
     * @return $this
     */
    public function setSubject(?string $subject): self;

    /**
  * Set completedRecipients
  *
  * @param array $completedRecipients
  *
  * @return $this
  */
    public function setCompletedRecipients($completedRecipients);

    /**
     * @param int $retryCount
     * @return $this
     */
    public function setRetryCount(int $retryCount): self;

    /**
     * @param ${ENTRY_HINT} $notificationAttachmentsList
     *
     * @return $this
     */
    public function addNotificationAttachmentsList($notificationAttachmentsList): self;

    /**
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * @param bool $convertImagesIntoAttachments
     * @return $this
     */
    public function setConvertImagesIntoAttachments(bool $convertImagesIntoAttachments): self;

    /**
     * @param ${ENTRY_HINT} $notificationExtendedRecipient
     *
     * @return $this
     */
    public function removeNotificationExtendedRecipient($notificationExtendedRecipient): self;

    /**
     * @param ${ENTRY_HINT} $errorLog
     *
     * @return $this
     */
    public function removeErrorLog($errorLog): self;

    /**
     * @param bool $keepNotificationAttachments
     * @return $this
     */
    public function setKeepNotificationAttachments(bool $keepNotificationAttachments): self;

    /**
     * @return float
     */
    public function getPercentageProgress(): float;

    /**
     * @ORM\PrePersist()
     */
    public function updateTotalRecipients(): void;

    /**
     * @param ${ENTRY_HINT} $sendLog
     *
     * @return $this
     */
    public function removeSendLog($sendLog): self;

    /**
     * @return Collection
     */
    public function getNotificationExtendedRecipients(): Collection;

    /**
     * Get maxRecipientsPerCycle
     *
     * @return integer
     */
    public function getMaxRecipientsPerCycle();

    /**
     * @param ${ENTRY_HINT} $cc
     *
     * @return $this
     */
    public function removeCc($cc): self;

    /**
     * @return DateTime|null
     */
    public function getCompletedAt(): ?DateTime;

    /**
     * @return int
     */
    public function getTrackingClickCount(): int;

    /**
     * @param ${ENTRY_HINT} $templateData
     *
     * @return $this
     */
    public function addTemplateData($templateData): self;

    /**
     * @return array
     */
    public function getSendLog(): array;

    /**
     * @param bool $isTrackingEnabled
     * @return $this
     */
    public function setIsTrackingEnabled(bool $isTrackingEnabled): self;

    /**
     * @param string|null $resourceDomain
     * @return $this
     */
    public function setResourceDomain(?string $resourceDomain): self;

    /**
     * @param string|null $parsedContent
     * @return $this
     */
    public function setParsedContent(?string $parsedContent): self;

    /**
     * @param Collection $notificationExtendedRecipients
     * @return $this
     */
    public function setNotificationExtendedRecipients(Collection $notificationExtendedRecipients): self;

    /**
     * @return array
     */
    public function getNotificationAttachmentsList(): array;

    /**
     * @return int
     */
    public function getCompletedRecipientsCount(): int;

    /**
     * @param ${ENTRY_HINT} $recipient
     *
     * @return $this
     */
    public function removeRecipient($recipient): self;

    /**
     * @param string|null $content
     * @return $this
     */
    public function setContent(?string $content): self;

    /**
     * @return int
     */
    public function getStrategy(): int;

    /**
     * @param array $templateData
     * @return $this
     */
    public function setTemplateData(array $templateData): self;

    /**
     * @param null $object
     * @return $this
     * @throws \Exception
     */
    public function setContextObject($object = null): self;

    /**
     * @param DateTime|null $completedAt
     * @return $this
     */
    public function setCompletedAt(?DateTime $completedAt): self;

    /**
     * @param int $completedRecipientsCount
     * @return $this
     */
    public function setCompletedRecipientsCount(int $completedRecipientsCount): self;

    /**
     * @return $this
     */
    public function clearContextObjects(): self;

    /**
     * @param int $recipientsTotal
     * @return $this
     */
    public function setRecipientsTotal(int $recipientsTotal): self;

    /**
     * @return $this
     */
    public function clearNotificationAttachments(): self;

    /**
     * Get completedRecipients
     *
     * @return array
     */
    public function getCompletedRecipients();

    /**
     * @return bool
     */
    public function isPublicRecipientViewEnabled(): bool;

    /**
     * @param array $notificationAttachmentsList
     * @return $this
     */
    public function setNotificationAttachmentsList(array $notificationAttachmentsList): self;

    /**
     * @param string|null $replyTo
     * @return $this
     */
    public function setReplyTo(?string $replyTo): self;

    /**
     * @return string|null
     */
    public function getResourceDomain(): ?string;

    /**
     *
     */
    public function increaseRetryCount(): void;

    /**
     * @return int
     */
    public function getRetryCount(): int;

    /**
     * @param array $errorLog
     * @return $this
     */
    public function setErrorLog(array $errorLog): self;

    /**
     * @return array
     */
    public function getTemplateData(): array;

    /**
     * @param ${ENTRY_HINT} $errorLog
     *
     * @return $this
     */
    public function addErrorLog($errorLog): self;

    /**
     * @return array
     */
    public function getRecipients(): array;

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self;

    /**
     * @param int $strategy
     * @return $this
     */
    public function setStrategy(int $strategy): self;

    /**
     * @return int
     */
    public function getRecipientsCount(): int;

    /**
     * @param ${ENTRY_HINT} $notificationLogEntrie
     *
     * @return $this
     */
    public function removeNotificationLogEntry($notificationLogEntry);

    /**
     * @ORM\PostPersist()
     * Na razie nie przewidujemy możliwości ingerencji w listę załączników po utworzeniu powiadomienia
     */
    public function updateAttachmentsList(): void;

    /**
     * @return int
     */
    public function getRecipientsTotal(): int;

    /**
     * @param int $trackingOpenCount
     * @return $this
     */
    public function setTrackingOpenCount(int $trackingOpenCount): self;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param ${ENTRY_HINT} $templateData
     *
     * @return $this
     */
    public function removeTemplateData($templateData): self;

    /**
     * @param bool $isPublicRecipientViewEnabled
     * @return $this
     */
    public function setIsPublicRecipientViewEnabled(bool $isPublicRecipientViewEnabled): self;

    /**
     * @return bool
     */
    public function isTrackingEnabled(): bool;

    /**
     * @return bool
     */
    public function isKeepNotificationAttachments(): bool;

    /**
     * @param int $trackingDisplayCount
     * @return $this
     */
    public function setTrackingDisplayCount(int $trackingDisplayCount): self;

    /**
     * @throws \Exception
     */
    public function setStatusCompleted(): void;

    /**
     * @param ${ENTRY_HINT} $notificationAttachment
     *
     * @return $this
     */
    public function removeNotificationAttachment($notificationAttachment);

    /**
     * @param ArrayCollection $notificationLogEntries
     * @return $this
     */
    public function setNotificationLogEntries($notificationLogEntries);

    /**
     * @return ArrayCollection
     */
    public function getNotificationLogEntries();

    /**
     * @param ${ENTRY_HINT} $notificationLogEntrie
     *
     * @return $this
     */
    public function addNotificationLogEntry($notificationLogEntry);

    /**
     * @return int
     */
    public function getTrackingOpenCount(): int;

    /**
     * @param OrderInterface|null $contextOrder
     * @return $this
     */
    public function setContextOrder(?OrderInterface $contextOrder): self;

    /**
     * @return array
     */
    public function getCc(): array;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @return string|null
     */
    public function getParsedContent(): ?string;

    /**
     * @param string|null $notificationDomain
     * @return $this
     */
    public function setNotificationDomain(?string $notificationDomain): self;

    /**
     * @param int $recipientsCount
     * @return $this
     */
    public function setRecipientsCount(int $recipientsCount): self;

    /**
     * @return string
     */
    public function getChannelName(): string;

    /**
     * @param ${ENTRY_HINT} $recipient
     *
     * @return $this
     */
    public function addRecipient($recipient): self;

    /**
     * Generowanie listy załączników
     */
    public function generateAttachmentsList(): void;

    /**
     * @return string|null
     */
    public function getTemplate(): ?string;

    /**
     * @param ${ENTRY_HINT} $notificationExtendedRecipient
     *
     * @return $this
     */
    public function addNotificationExtendedRecipient($notificationExtendedRecipient): self;

    /**
     * @return bool
     */
    public function isPublicViewEnabled(): bool;

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self;

    /**
     * @param array $recipients
     * @return $this
     */
    public function setRecipients(array $recipients): self;

    /**
     * @return array
     */
    public function getErrorLog(): array;

    /**
     * @return string|null
     */
    public function getSubject(): ?string;

    /**
     * @return string|null
     */
    public function getReplyTo(): ?string;

    /**
     * @param ${ENTRY_HINT} $notificationAttachmentsList
     *
     * @return $this
     */
    public function removeNotificationAttachmentsList($notificationAttachmentsList): self;

    /**
     * @return OrderInterface|null
     */
    public function getContextOrder(): ?OrderInterface;

    /**
     * @param array $sendLog
     * @return $this
     */
    public function setSendLog(array $sendLog): self;

    /**
     * @param ${ENTRY_HINT} $cc
     *
     * @return $this
     */
    public function addCc($cc): self;

    /**
     * @param ArrayCollection|Collection $notificationAttachments
     * @return $this
     */
    public function setNotificationAttachments($notificationAttachments);
}