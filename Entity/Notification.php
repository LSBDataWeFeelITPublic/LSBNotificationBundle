<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use LSB\LocaleBundle\Entity\LanguageDataTrait;
use LSB\NotificationBundle\Traits\NotificationTrackingStatusTrait;
use LSB\OrderBundle\Entity\OrderInterface;
use LSB\UtilityBundle\Traits\CreatedUpdatedTrait;
use LSB\UtilityBundle\Traits\UuidTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\HasLifecycleCallbacks()
 * @MappedSuperclass
 */
class Notification implements NotificationInterface
{
    use UuidTrait;
    use CreatedUpdatedTrait;
    use NotificationTrackingStatusTrait;
    use LanguageDataTrait;

    /**
     * @var array
     */
    public static array $statusList = [
        self::STATUS_FAILED => 'Notification.Status.Failed',
        self::STATUS_RETRYING => 'Notification.Status.Retrying',
        self::STATUS_PROCESSING => 'Notification.Status.Processing',
        self::STATUS_WAITING => 'Notification.Status.Waiting',
        self::STATUS_COMPLETED => 'Notification.Status.Completed',
        self::STATUS_BLOCKED => 'Notification.Status.Blocked',
        self::STATUS_DATA_ERROR => 'Notification.Status.GenerationFailed'
    ];

    /**
     * Strategies for generating notifications
     *
     * @var array
     */
    public static array $strategyList = [
        self::STRATEGY_NOW => 'Strategy.Now'
    ];

    /**
     * Notification type
     *
     * @var array
     */
    public static array $typeList = [
        self::TYPE_SIMPLE => 'Notification.Type.Simple',
        self::TYPE_EXTENDED => 'Notification.Type.Extended'
    ];

    /**
     * Channel name
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     * Assert\@Assert\Length(max=255)
     */
    protected string $channelName;

    /**
     * The list of recipients
     *
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $recipients = [];

    /**
     * Realized recipients
     *
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $completedRecipients = [];

    /**
     * CC
     *
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $cc = [];

    /**
     * Shipping strategy
     *
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected int $strategy;

    /**
     * Shipment status
     *
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected int $status;

    /**
     * The subject of the notification
     *
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $subject;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $template;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $templateData = [];

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $content;

    /**
     * Notification generation error log
     *
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    protected array $errorLog = [];

    /**
     * Shipping log
     *
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $sendLog = [];

    /**
     * The date the notification was completed
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $completedAt;

    /**
     * Number of recipients per cycle
     *
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected int $maxRecipientsPerCycle = 1;

    /**
     * Total number of recipients
     *
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected int $recipientsTotal = 0;

    /**
     * Number of Remaining Recipients
     *
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected int $recipientsCount = 0;

    /**
     * Number of recipients completed
     *
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected int $completedRecipientsCount = 0;

    /**
     * Notification domain (for links)
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $notificationDomain;

    /**
     * Domain for resources (photos, files)
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $resourceDomain;

    /**
     * Attachments
     *
     * @ORM\OneToMany(targetEntity="LSB\NotificationBundle\Entity\NotificationAttachmentInterface", mappedBy="notification", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    protected Collection $notificationAttachments;

    /**
     * Attachment preservation flag
     *
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true, options={"default": true})
     */
    protected bool $keepNotificationAttachments = true;

    /**
     * List of attachments
     *
     * @var array
     * @ORM\Column(type="json")
     */
    protected array $notificationAttachmentsList = [];

    /**
     * Extended notification recipients
     *
     * @ORM\OneToMany(targetEntity="LSB\NotificationBundle\Entity\NotificationRecipientInterface", cascade={"persist", "remove"}, orphanRemoval=true, mappedBy="notification")
     */
    protected Collection $notificationExtendedRecipients;

    /**
     * Notification type (simplified, extended)
     *
     * @var integer
     * @ORM\Column(type="integer", options={"default" = 10})
     */
    protected int $type = self::TYPE_SIMPLE;

    /**
     * Processed content
     *
     * @var string|null
     */
    protected ?string $parsedContent;

    /**
     * Number of re-shipments
     *
     * @var int
     * @ORM\Column(type="integer", options={"default" = 0})
     */
    protected int $retryCount = 0;

    /**
     * Convert photos to attachments
     *
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     */
    protected bool $convertImagesIntoAttachments = true;

    /**
     * Return address
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected ?string $replyTo;

    /**
     * RELATIONSHIP TO CLASSES RELATED TO NOTIFICATIONS
     * Contextual Relationships
     */

    /**
     * @var OrderInterface|null
     * @ORM\ManyToOne(targetEntity="LSB\OrderBundle\Entity\OrderInterface", inversedBy="notifications")
     * @ORM\JoinColumn(onDelete="SET NULL", fieldName="context_order_id")
     */
    protected $contextOrder;

    /**
     * Flag for activating public preview of content through the website (without a dedicated recipient)
     *
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isPublicViewEnabled = false;

    /**
     * Web content public preview activation flag (for extended audiences)
     *
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isPublicRecipientViewEnabled = false;

    /**
     * Event log
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="LSB\NotificationBundle\Entity\NotificationLogEntryInterface", mappedBy="notification")
     */
    protected Collection $notificationLogEntries;

    /**
     * Impression tracking activation flag
     *
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isTrackingEnabled = false;

    /**
     * Statistics, cumulative openings in the browser
     *
     * @var integer
     * @ORM\Column(type="integer", options={"default": 0})
     */
    protected int $trackingOpenCount = 0;

    /**
     * Statistics, cumulative views
     *
     * @var integer
     * @ORM\Column(type="integer", options={"default": 0})
     */
    protected int $trackingDisplayCount = 0;

    /**
     * Statistics, cumulative clicks
     *
     * @var integer
     * @ORM\Column(type="integer", options={"default": 0})
     */
    protected int $trackingClickCount = 0;

    /**
     * @return string
     */
    public function _toString()
    {
        return $this->subject;
    }

    /**
     *
     */
    public function increaseTrackingOpenCount(): void
    {
        $this->trackingOpenCount++;
    }

    /**
     *
     */
    public function increaseTrackingDisplayCount(): void
    {
        $this->trackingDisplayCount++;
    }

    /**
     *
     */
    public function increaseTrackingClickCount(): void
    {
        $this->trackingClickCount++;
    }

    /**
     * @return $this
     */
    public function clearNotificationAttachments(): self
    {
        $this->notificationAttachments->clear();

        return $this;
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
     * @throws \Exception
     */
    public function setStatusCompleted(): void
    {
        $this->completedAt = new \DateTime('NOW');
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * @ORM\PostPersist()
     * Na razie nie przewidujemy możliwości ingerencji w listę załączników po utworzeniu powiadomienia
     */
    public function updateAttachmentsList(): void
    {
        $this->generateAttachmentsList();
    }

    /**
     * Generowanie listy załączników
     */
    public function generateAttachmentsList(): void
    {
        $notificationAttachmentsList = [];

        if (count($this->notificationAttachments)) {
            foreach ($this->getNotificationAttachments() as $notificationAttachment) {
                $notificationAttachmentsList[] = $notificationAttachment->getDisplayFileName() . ' - ' . $notificationAttachment->getName(); //toString
            }
        }

        $this->setNotificationAttachmentsList($notificationAttachmentsList);
    }

    /**
     * @return string|null
     */
    public function getMappedStatus(): ?string
    {
        if (array_key_exists($this->status, self::$statusList)) {
            return self::$statusList[$this->status];
        } else {
            return null;
        }
    }

    /**
     * @ORM\PrePersist()
     */
    public function updateTotalRecipients(): void
    {
        $this->recipientsTotal = (int)$this->recipientsCount;
    }

    /**
     * @return float
     */
    public function getPercentageProgress(): float
    {
        return $this->recipientsTotal ? round($this->completedRecipientsCount / $this->recipientsTotal * 100, 1) : 0;
    }

       /**
     * Set completedRecipients
     *
     * @param array $completedRecipients
     *
     * @return $this
     */
    public function setCompletedRecipients($completedRecipients)
    {
        $this->completedRecipients = $completedRecipients;
        $this->completedRecipientsCount = count($completedRecipients);

        return $this;
    }

    /**
     * Get completedRecipients
     *
     * @return array
     */
    public function getCompletedRecipients()
    {
        return $this->completedRecipients;
    }

    /**
     * Get maxRecipientsPerCycle
     *
     * @return integer
     */
    public function getMaxRecipientsPerCycle()
    {
        if ($this->type == self::TYPE_EXTENDED) {
            return 1;
        }

        return $this->maxRecipientsPerCycle;
    }


    /**
     * Constructor
     */
    public function __construct(string $languageCode = 'PL')
    {
        $this->generateUuid();
        $this->notificationAttachments = new ArrayCollection();
        $this->notificationLogEntries = new ArrayCollection();
        $this->notificationExtendedRecipients = new ArrayCollection();
        $this->languageCode = $languageCode;
    }



    /**
     * @param null $object
     * @return $this
     * @throws \Exception
     */
    public function setContextObject($object = null): self
    {
        if ($object !== null) {
            switch (true) {
//                case $object instanceof Order:
//                    return $this->setOrder($object);
                default:
                    throw new \Exception('Context object not supported');
                    break;
            }
        } else {
            $this->clearContextObjects();
        }

        return $this;
    }


    /**
     * @return $this
     */
    public function clearContextObjects(): self
    {
//        $this->order = null;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @param array $statusList
     */
    public static function setStatusList(array $statusList): void
    {
        self::$statusList = $statusList;
    }

    /**
     * @return array
     */
    public static function getStrategyList(): array
    {
        return self::$strategyList;
    }

    /**
     * @param array $strategyList
     */
    public static function setStrategyList(array $strategyList): void
    {
        self::$strategyList = $strategyList;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @param array $typeList
     */
    public static function setTypeList(array $typeList): void
    {
        self::$typeList = $typeList;
    }

    /**
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    /**
     * @param string $channelName
     * @return $this
     */
    public function setChannelName(string $channelName): self
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * @return array
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @param ${ENTRY_HINT} $recipient
     *
     * @return $this
     */
    public function addRecipient($recipient): self
    {
        if (false === in_array($recipient, $this->recipients, true)) {
            $this->recipients[] = $recipient;
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $recipient
     *
     * @return $this
     */
    public function removeRecipient($recipient): self
    {
        if (true === in_array($recipient, $this->recipients, true)) {
            $index = array_search($recipient, $this->recipients);
            array_splice($this->recipients, $index, 1);
        }
        return $this;
    }

    /**
     * @param array $recipients
     * @return $this
     */
    public function setRecipients(array $recipients): self
    {
        $this->recipientsCount = count($recipients);
        $this->recipients = $recipients;
        return $this;
    }

    /**
     * @return array
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param ${ENTRY_HINT} $cc
     *
     * @return $this
     */
    public function addCc($cc): self
    {
        if (false === in_array($cc, $this->cc, true)) {
            $this->cc[] = $cc;
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $cc
     *
     * @return $this
     */
    public function removeCc($cc): self
    {
        if (true === in_array($cc, $this->cc, true)) {
            $index = array_search($cc, $this->cc);
            array_splice($this->cc, $index, 1);
        }
        return $this;
    }

    /**
     * @param array $cc
     * @return $this
     */
    public function setCc(array $cc): self
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return int
     */
    public function getStrategy(): int
    {
        return $this->strategy;
    }

    /**
     * @param int $strategy
     * @return $this
     */
    public function setStrategy(int $strategy): self
    {
        $this->strategy = $strategy;
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
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     * @return $this
     */
    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     * @return $this
     */
    public function setTemplate(?string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * @param ${ENTRY_HINT} $templateData
     *
     * @return $this
     */
    public function addTemplateData($templateData): self
    {
        if (false === in_array($templateData, $this->templateData, true)) {
            $this->templateData[] = $templateData;
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $templateData
     *
     * @return $this
     */
    public function removeTemplateData($templateData): self
    {
        if (true === in_array($templateData, $this->templateData, true)) {
            $index = array_search($templateData, $this->templateData);
            array_splice($this->templateData, $index, 1);
        }
        return $this;
    }

    /**
     * @param array $templateData
     * @return $this
     */
    public function setTemplateData(array $templateData): self
    {
        $this->templateData = $templateData;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return $this
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;
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
     * @return int
     */
    public function getRecipientsTotal(): int
    {
        return $this->recipientsTotal;
    }

    /**
     * @param int $recipientsTotal
     * @return $this
     */
    public function setRecipientsTotal(int $recipientsTotal): self
    {
        $this->recipientsTotal = $recipientsTotal;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecipientsCount(): int
    {
        return $this->recipientsCount;
    }

    /**
     * @param int $recipientsCount
     * @return $this
     */
    public function setRecipientsCount(int $recipientsCount): self
    {
        $this->recipientsCount = $recipientsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompletedRecipientsCount(): int
    {
        return $this->completedRecipientsCount;
    }

    /**
     * @param int $completedRecipientsCount
     * @return $this
     */
    public function setCompletedRecipientsCount(int $completedRecipientsCount): self
    {
        $this->completedRecipientsCount = $completedRecipientsCount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotificationDomain(): ?string
    {
        return $this->notificationDomain;
    }

    /**
     * @param string|null $notificationDomain
     * @return $this
     */
    public function setNotificationDomain(?string $notificationDomain): self
    {
        $this->notificationDomain = $notificationDomain;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getResourceDomain(): ?string
    {
        return $this->resourceDomain;
    }

    /**
     * @param string|null $resourceDomain
     * @return $this
     */
    public function setResourceDomain(?string $resourceDomain): self
    {
        $this->resourceDomain = $resourceDomain;
        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getNotificationAttachments()
    {
        return $this->notificationAttachments;
    }

    /**
     * @param ${ENTRY_HINT} $notificationAttachment
     *
     * @return $this
     */
    public function addNotificationAttachment($notificationAttachment)
    {
        if (false === $this->notificationAttachments->contains($notificationAttachment)) {
            $notificationAttachment->setNotification($this);
            $this->notificationAttachments->add($notificationAttachment);
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $notificationAttachment
     *
     * @return $this
     */
    public function removeNotificationAttachment($notificationAttachment)
    {
        if (true === $this->notificationAttachments->contains($notificationAttachment)) {
            $this->notificationAttachments->removeElement($notificationAttachment);
        }
        return $this;
    }

    /**
     * @param ArrayCollection|Collection $notificationAttachments
     * @return $this
     */
    public function setNotificationAttachments($notificationAttachments)
    {
        $this->notificationAttachments = $notificationAttachments;
        return $this;
    }

    /**
     * @return bool
     */
    public function isKeepNotificationAttachments(): bool
    {
        return $this->keepNotificationAttachments;
    }

    /**
     * @param bool $keepNotificationAttachments
     * @return $this
     */
    public function setKeepNotificationAttachments(bool $keepNotificationAttachments): self
    {
        $this->keepNotificationAttachments = $keepNotificationAttachments;
        return $this;
    }

    /**
     * @return array
     */
    public function getNotificationAttachmentsList(): array
    {
        return $this->notificationAttachmentsList;
    }

    /**
     * @param ${ENTRY_HINT} $notificationAttachmentsList
     *
     * @return $this
     */
    public function addNotificationAttachmentsList($notificationAttachmentsList): self
    {
        if (false === in_array($notificationAttachmentsList, $this->notificationAttachmentsList, true)) {
            $this->notificationAttachmentsList[] = $notificationAttachmentsList;
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $notificationAttachmentsList
     *
     * @return $this
     */
    public function removeNotificationAttachmentsList($notificationAttachmentsList): self
    {
        if (true === in_array($notificationAttachmentsList, $this->notificationAttachmentsList, true)) {
            $index = array_search($notificationAttachmentsList, $this->notificationAttachmentsList);
            array_splice($this->notificationAttachmentsList, $index, 1);
        }
        return $this;
    }

    /**
     * @param array $notificationAttachmentsList
     * @return $this
     */
    public function setNotificationAttachmentsList(array $notificationAttachmentsList): self
    {
        $this->notificationAttachmentsList = $notificationAttachmentsList;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getNotificationExtendedRecipients(): Collection
    {
        return $this->notificationExtendedRecipients;
    }

    /**
     * @param ${ENTRY_HINT} $notificationExtendedRecipient
     *
     * @return $this
     */
    public function addNotificationExtendedRecipient($notificationExtendedRecipient): self
    {
        if (false === $this->notificationExtendedRecipients->contains($notificationExtendedRecipient)) {
            $notificationExtendedRecipient->setNotification($this);
            $this->notificationExtendedRecipients->add($notificationExtendedRecipient);
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $notificationExtendedRecipient
     *
     * @return $this
     */
    public function removeNotificationExtendedRecipient($notificationExtendedRecipient): self
    {
        if (true === $this->notificationExtendedRecipients->contains($notificationExtendedRecipient)) {
            $this->notificationExtendedRecipients->removeElement($notificationExtendedRecipient);
        }
        return $this;
    }

    /**
     * @param Collection $notificationExtendedRecipients
     * @return $this
     */
    public function setNotificationExtendedRecipients(Collection $notificationExtendedRecipients): self
    {
        $this->notificationExtendedRecipients = $notificationExtendedRecipients;
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
    public function getParsedContent(): ?string
    {
        return $this->parsedContent;
    }

    /**
     * @param string|null $parsedContent
     * @return $this
     */
    public function setParsedContent(?string $parsedContent): self
    {
        $this->parsedContent = $parsedContent;
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
     * @return bool
     */
    public function isConvertImagesIntoAttachments(): bool
    {
        return $this->convertImagesIntoAttachments;
    }

    /**
     * @param bool $convertImagesIntoAttachments
     * @return $this
     */
    public function setConvertImagesIntoAttachments(bool $convertImagesIntoAttachments): self
    {
        $this->convertImagesIntoAttachments = $convertImagesIntoAttachments;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    /**
     * @param string|null $replyTo
     * @return $this
     */
    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublicViewEnabled(): bool
    {
        return $this->isPublicViewEnabled;
    }

    /**
     * @param bool $isPublicViewEnabled
     * @return $this
     */
    public function setIsPublicViewEnabled(bool $isPublicViewEnabled): self
    {
        $this->isPublicViewEnabled = $isPublicViewEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublicRecipientViewEnabled(): bool
    {
        return $this->isPublicRecipientViewEnabled;
    }

    /**
     * @param bool $isPublicRecipientViewEnabled
     * @return $this
     */
    public function setIsPublicRecipientViewEnabled(bool $isPublicRecipientViewEnabled): self
    {
        $this->isPublicRecipientViewEnabled = $isPublicRecipientViewEnabled;
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
     * @param ${ENTRY_HINT} $notificationLogEntrie
     *
     * @return $this
     */
    public function addNotificationLogEntry($notificationLogEntry)
    {
        if (false === $this->notificationLogEntries->contains($notificationLogEntry)) {
            $this->notificationLogEntries->add($notificationLogEntry);
        }
        return $this;
    }

    /**
     * @param ${ENTRY_HINT} $notificationLogEntrie
     *
     * @return $this
     */
    public function removeNotificationLogEntry($notificationLogEntry)
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
    public function setNotificationLogEntries($notificationLogEntries)
    {
        $this->notificationLogEntries = $notificationLogEntries;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTrackingEnabled(): bool
    {
        return $this->isTrackingEnabled;
    }

    /**
     * @param bool $isTrackingEnabled
     * @return $this
     */
    public function setIsTrackingEnabled(bool $isTrackingEnabled): self
    {
        $this->isTrackingEnabled = $isTrackingEnabled;
        return $this;
    }

    /**
     * @return int
     */
    public function getTrackingOpenCount(): int
    {
        return $this->trackingOpenCount;
    }

    /**
     * @param int $trackingOpenCount
     * @return $this
     */
    public function setTrackingOpenCount(int $trackingOpenCount): self
    {
        $this->trackingOpenCount = $trackingOpenCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getTrackingDisplayCount(): int
    {
        return $this->trackingDisplayCount;
    }

    /**
     * @param int $trackingDisplayCount
     * @return $this
     */
    public function setTrackingDisplayCount(int $trackingDisplayCount): self
    {
        $this->trackingDisplayCount = $trackingDisplayCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getTrackingClickCount(): int
    {
        return $this->trackingClickCount;
    }

    /**
     * @param int $trackingClickCount
     * @return $this
     */
    public function setTrackingClickCount(int $trackingClickCount): self
    {
        $this->trackingClickCount = $trackingClickCount;
        return $this;
    }

    /**
     * @return OrderInterface|null
     */
    public function getContextOrder(): ?OrderInterface
    {
        return $this->contextOrder;
    }

    /**
     * @param OrderInterface|null $contextOrder
     * @return $this
     */
    public function setContextOrder(?OrderInterface $contextOrder): self
    {
        $this->contextOrder = $contextOrder;
        return $this;
    }
}
