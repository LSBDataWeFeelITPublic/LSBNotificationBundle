<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use LSB\LocaleBundle\Manager\LanguageManager;
use LSB\NotificationBundle\Channel\ChannelInterface;
use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationLogEntry;
use LSB\NotificationBundle\Entity\NotificationLogEntryInterface;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\NotificationBundle\Event\NotificationEvent;
use LSB\NotificationBundle\Event\NotificationEvents;
use LSB\NotificationBundle\Exception\MissingSourceFileException;
use LSB\NotificationBundle\Exception\NotificationProcessingException;
use LSB\NotificationBundle\Factory\NotificationFactoryInterface;
use LSB\NotificationBundle\Repository\NotificationRepositoryInterface;
use LSB\OrderBundle\Entity\Order;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
* Class NotificationManager
* @package LSB\NotificationBundle\Manager
*/
class NotificationManager extends BaseManager
{

    const NOTIFICATION_LIMIT_CRON = 50;
    const NOTIFICATION_REMOVE_LIMIT = 500;
    const RETRY_COUNT = 50;
    const CHANNEL_VAR = '{channel}';
    const TRANSLATION_PREFIX = 'Notification.Subject';
    const PARAMETER_NAME = 'frontend_routes';

    protected NotificationAttachmentManager $attachmentManager;

    protected LanguageManager $languageManager;

    protected TranslatorInterface $translator;

    protected KernelInterface $kernel;

    protected RequestStack $requestStack;

    protected ParameterBagInterface $parameterBag;

    protected EventDispatcherInterface $eventDispatcher;

    protected array $channels = [];

    protected ChannelInterface $currentChannel;

    protected NotificationRecipientManager $notificationRecipientManager;

    protected ?array $domains = null;

    protected ChannelModuleInventory $channelModuleInventory;

    /**
     * NotificationManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationFactoryInterface $factory
     * @param NotificationRepositoryInterface $repository
     * @param BaseEntityType|null $form
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param NotificationAttachmentManager $notificationAttachmentManager
     * @param ParameterBagInterface $parameterBag
     * @param EventDispatcherInterface $eventDispatcher
     * @param LanguageManager $languageManager
     * @param NotificationRecipientManager $notificationRecipientManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationFactoryInterface $factory,
        NotificationRepositoryInterface $repository,
        ?BaseEntityType $form,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        NotificationAttachmentManager $notificationAttachmentManager,
        ParameterBagInterface $parameterBag,
        EventDispatcherInterface $eventDispatcher,
        LanguageManager $languageManager,
        NotificationRecipientManager $notificationRecipientManager,
        ChannelModuleInventory $channelModuleInventory
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);

        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->parameterBag = $parameterBag;
        $this->eventDispatcher = $eventDispatcher;
        $this->attachmentManager = $notificationAttachmentManager;
        $this->notificationRecipientManager = $notificationRecipientManager;
        $this->languageManager = $languageManager;
        $this->channelModuleInventory = $channelModuleInventory;
    }

    /**
     * @return NotificationInterface|object
     */
    public function createNew(): NotificationInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationRepositoryInterface|RepositoryInterface
     */
    public function getRepository(): NotificationRepositoryInterface
    {
        return parent::getRepository();
    }

    /**
     * @param $channelName
     * @return ChannelInterface|null
     * @throws Exception
     */
    public function getChannelByName($channelName): ?ChannelInterface
    {
        return $this->channelModuleInventory->getModuleByName($channelName);
    }

    /**
     * @param NotificationRecipient $notificationRecipient
     * @param NotificationLogEntry $notificationLogEntry
     * @param int $type
     * @return bool
     */
    protected function canNotificationRecipientLogEntryChangeState(
        NotificationRecipientInterface $notificationRecipient,
        NotificationLogEntryInterface $notificationLogEntry,
        int $type
    ): bool {
        //Weryfikacja typu i flag
        switch ($type) {
            case NotificationLogEntry::TYPE_TRACKING_DISPLAY:
                if ($notificationRecipient->isNotificationDisplayed()) {
                    return false;
                }
                break;
            case NotificationLogEntry::TYPE_TRACKING_OPEN:
                if ($notificationRecipient->isNotificationOpened()) {
                    return false;
                }
                break;
            case NotificationLogEntry::TYPE_TRACKING_CLICK:
                if ($notificationRecipient->isNotificationClicked()) {
                    return false;
                }
                break;
        }

        if (!$notificationLogEntry->getNotificationRecipient() instanceof NotificationRecipient
            || $notificationLogEntry->getNotificationRecipient() !== $notificationRecipient
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param NotificationLogEntryInterface $notificationLogEntry
     * @return DateTime
     */
    protected function getNotificationRecipientNotificationChangeStateDate(NotificationLogEntryInterface $notificationLogEntry): DateTime
    {
        $date = $notificationLogEntry->getCreatedAt();

        if (!$date instanceof DateTime) {
            $date = new DateTime('now');
        }

        return $date;
    }

    /**
     *
     * @param NotificationRecipient $notificationRecipient
     * @param NotificationLogEntry $notificationLogEntry
     * @param bool $flush
     */
    public function markNotificationRecipientNotificationAsClicked(NotificationRecipient $notificationRecipient, NotificationLogEntry $notificationLogEntry, bool $flush = true): void
    {
        if (!$this->canNotificationRecipientLogEntryChangeState($notificationRecipient, $notificationLogEntry, NotificationLogEntry::TYPE_TRACKING_CLICK)) {
            return;
        }

        $notificationRecipient
            ->setIsNotificationClicked(true)
            ->setNotificationClickedAt($this->getNotificationRecipientNotificationChangeStateDate($notificationLogEntry));

        if ($flush) {
            $this->notificationRecipientManager->update($notificationRecipient);
        }
    }

    /**
     * @param NotificationRecipient $notificationRecipient
     * @param NotificationLogEntry $notificationLogEntry
     * @param bool $flush
     */
    public function markNotificationRecipientNotificationAsOpened(
        NotificationRecipientInterface $notificationRecipient,
        NotificationLogEntryInterface $notificationLogEntry,
        bool $flush = true
    ): void {
        if (!$this->canNotificationRecipientLogEntryChangeState($notificationRecipient, $notificationLogEntry, NotificationLogEntry::TYPE_TRACKING_OPEN)) {
            return;
        }

        $notificationRecipient
            ->setIsNotificationOpened(true)
            ->setNotificationOpenedAt($this->getNotificationRecipientNotificationChangeStateDate($notificationLogEntry));

        if ($flush) {
            $this->notificationRecipientManager->update($notificationRecipient);
        }
    }

    /**
     * @param NotificationRecipient $notificationRecipient
     * @param NotificationLogEntry $notificationLogEntry
     * @param bool $flush
     */
    public function markNotificationRecipientNotificationAsDisplayed(
        NotificationRecipientInterface $notificationRecipient,
        NotificationLogEntryInterface $notificationLogEntry,
        bool $flush = true
    ): void {
        if (!$this->canNotificationRecipientLogEntryChangeState($notificationRecipient, $notificationLogEntry, NotificationLogEntry::TYPE_TRACKING_DISPLAY)) {
            return;
        }

        $notificationRecipient
            ->setIsNotificationDisplayed(true)
            ->setNotificationDisplayedAt($this->getNotificationRecipientNotificationChangeStateDate($notificationLogEntry));

        if ($flush) {
            $this->notificationRecipientManager->update($notificationRecipient);
        }
    }

    /**
     * Mechanizm tworzenia powiadomien bez personalizacji treści na podstawie listy odbiorców
     *
     * @param string $channelName
     * @param $recipients
     * @param $cc
     * @param int $strategy
     * @param string $subject
     * @param string $template
     * @param array|null $templateData
     * @param string|null $notificationDomain
     * @param iterable|null $attachments
     * @param string|null $replyTo
     * @param string|null $resourceDomain
     * @param null $contextObject
     * @param bool $isPublicViewEnabled Podgląd powiadomienia
     * @param bool $isTrackingEnable Aktualnie brak wsparcia dla śledzenia prostych powiadomień (przyszłościwo)
     * @param string|null $languageCode
     * @return Notification
     * @throws MissingSourceFileException
     * @throws NotificationProcessingException
     */
    public function createSimpleNotification(
        string $channelName,
        $recipients,
        $cc,
        int $strategy,
        string $subject,
        string $template,
        ?array $templateData = [],
        ?string $notificationDomain = null,
        ?iterable $attachments = [],
        ?string $replyTo = null,
        ?string $resourceDomain = null,
        $contextObject = null,
        bool $isPublicViewEnabled = false,
        bool $isTrackingEnable = false,
        ?string $languageCode = null
    ): NotificationInterface {
        $isValid = false;

        $channel = $this->getChannelByName($channelName);

        //sprawdzamy czy odbiorcy to tablica czy string
        $recipients = $this->checkForSingleArray($recipients);
        $cc = $this->checkForSingleArray($cc);

        //sprawdzamy czy jest znacznik kanału w template
        $template = $this->checkChannelInTemplate($template, $channelName);

        //ustalenie domeny dla powiadomienia
        $notificationDomain = $this->determineNotificationDomain($notificationDomain);

        $replyTo = $this->prepareReplyToAddress($replyTo);

        $notification = ($this->createNew())
            ->setChannelName($channelName)
            ->setRecipients($channel->validateSimpleRecipients($recipients))
            ->setCC($channel->validateCC($cc))
            ->setStrategy(Notification::STRATEGY_NOW)
            ->setSubject($subject)
            ->setStatus(Notification::STATUS_WAITING)
            ->setNotificationDomain($notificationDomain)
            ->setResourceDomain($resourceDomain ? $resourceDomain : $notificationDomain)
            ->setTemplate($template)
            ->setConvertImagesIntoAttachments(true)
            ->setReplyTo($replyTo)
            ->setContextObject($contextObject)
            ->setIsPublicViewEnabled($isPublicViewEnabled)
            ->setIsTrackingEnabled($isTrackingEnable)
        ;

        $this->processNotificationLanguageCode($notification, $languageCode);

        try {
            $this->validateNotificationData($notification);
            $isValid = true;
        } catch (NotificationProcessingException $exception) {
            $this->processLogs($notification, $exception->getFile() . " " . $exception->getLine() . "\n" . $exception->getMessage(), null);
            $notification->setStatus(Notification::STATUS_DATA_ERROR);
        }

        if ($isValid) {
            if ($strategy !== Notification::STRATEGY_NOW) {
                $notification
                    ->setTemplateData($templateData);
            } else {
                $this->generateNotificationContent($notification, $templateData);
            }

            $this->attachmentManager->addNewAttachments($notification, $attachments);
        }

        $this->update($notification);

        return $notification;
    }

    /**
     * Walidacja wstępna powiadomienia
     *
     * @param Notification $notification
     * @throws NotificationProcessingException
     */
    protected function validateNotificationData(NotificationInterface $notification): void
    {
        //Na tym etapie powiadomienie nie posiada jeszcze przeliczonej ilości odbiorców
        if ($notification->getType() === Notification::TYPE_SIMPLE && (!$notification->getRecipients() || count($notification->getRecipients()) <= 0)) {
            throw new NotificationProcessingException("No valid recipients passed.");
        }

        if (!$notification->getTemplate()) {
            throw new NotificationProcessingException("Missing template path.");
        }

        if (!$notification->getSubject()) {
            throw new NotificationProcessingException("Missing subject.");
        }
    }

    /**
     * @param NotificationInterface $notification
     * @param array $templateData
     * @return Notification
     * @throws NotificationProcessingException
     */
    protected function generateNotificationContent(NotificationInterface $notification, array $templateData): NotificationInterface
    {
        $templateData = $this->mergeNotificationIntoTemplateData($templateData, $notification);

        if (!$notification->getChannelName() || $notification->getChannelName() && $notification->getChannelName() == '') {
            throw new NotificationProcessingException('Missing channel name');
        }

        $channel = $this->getChannelByName($notification->getChannelName());

        if (!$channel) {
            throw new NotificationProcessingException(sprintf('Missing %s channel', $notification->getChannelName()));
        }

        try {
            $content = $channel->convertDataIntoContent($notification->getTemplate(), $templateData);
            $notification
                ->setTemplateData([])//jeżeli treść jest statyczna i została wygenerowana, nie zapisujemy danych do templatki
                ->setContent($content);
        } catch (Exception $e) {
            $notification
                ->setContent(null)
                ->setStatus(Notification::STATUS_DATA_ERROR)
                ->addErrorLog($e->getFile() . " " . $e->getLine() . "\n" . $e->getMessage());
        }

        return $notification;
    }



    /**
     * Ustalenie adresu zwrotnego
     *
     * @param string|null $replyTo
     * @return string|null
     */
    protected function prepareReplyToAddress(?string $replyTo): ?string
    {
        if ($replyTo) {
            return $replyTo;
        }

        //TODO some logic

        return $replyTo;
    }

    /**
     * @param string $channelName
     * @param $recipients
     * @param $cc
     * @param int $strategy
     * @param string $subject
     * @param string $template
     * @param array|null $templateData
     * @param string|null $notificationDomain
     * @param iterable|null $attachments
     * @param bool $convertImagesIntoAttachments
     * @param string|null $replyTo
     * @param string|null $resourceDomain
     * @param null $contextObject
     * @param bool $isPublicViewEnabled
     * @param bool $isPublicRecipientViewEnabled
     * @param bool $isTrackingEnabled
     * @param string|null $languageCode
     * @return bool|Notification
     * @throws MissingSourceFileException
     * @throws NotificationProcessingException
     */
    public function createExtendedNotification(
        string $channelName,
        $recipients,
        $cc,
        int $strategy,
        string $subject,
        string $template,
        ?array $templateData = [],
        ?string $notificationDomain = null,
        ?iterable $attachments = [],
        bool $convertImagesIntoAttachments = true,
        ?string $replyTo = null,
        ?string $resourceDomain = null,
        $contextObject = null,
        bool $isPublicViewEnabled = false,
        bool $isPublicRecipientViewEnabled = false,
        bool $isTrackingEnabled = false,
        ?string $languageCode = null
    ): Notification {
        $isValid = false;

        $channel = $this->getChannelByName($channelName);

        if (!$channel) {
            return false;
        }

        $recipients = $this->checkForSingleArray($recipients);
        $cc = $this->checkForSingleArray($cc);

        $template = $this->checkChannelInTemplate($template, $channelName);
        $notificationDomain = $this->determineNotificationDomain($notificationDomain);

        $replyTo = $this->prepareReplyToAddress($replyTo);

        $notification = ($this->createNew())
            ->setType(Notification::TYPE_EXTENDED)
            ->setChannelName($channelName)
            ->setCC($channel->validateCC($cc))
            ->setStrategy(Notification::STRATEGY_NOW)
            ->setCc($cc)
            ->setSubject($subject)
            ->setStatus(Notification::STATUS_WAITING)
            ->setNotificationDomain($notificationDomain)
            ->setResourceDomain($resourceDomain ? $resourceDomain : $notificationDomain)
            ->setTemplate($template)
            ->setConvertImagesIntoAttachments($convertImagesIntoAttachments)
            ->setReplyTo($replyTo)
            ->setContextObject($contextObject)
            ->setIsPublicViewEnabled($isPublicViewEnabled)
            ->setIsPublicRecipientViewEnabled($isPublicRecipientViewEnabled)
            ->setIsTrackingEnabled($isTrackingEnabled)
        ;

        $this->processNotificationLanguageCode($notification, $languageCode);

        try {
            $this->validateNotificationData($notification);
            $isValid = true;
        } catch (NotificationProcessingException $exception) {
            $this->processLogs($notification, $exception->getFile() . " " . $exception->getLine() . "\n" . $exception->getMessage(), null);
            $notification->setStatus(Notification::STATUS_DATA_ERROR);
        }

        if ($isValid) {
            if ($strategy !== Notification::STRATEGY_NOW) {
                //Obecnie brak wsparcia
                $notification
                    ->setTemplateData($templateData);
            } else {
                $this->generateNotificationContent($notification, $templateData);
            }

            $this->buildExtendedRecipientListForNotification($notification, $recipients);

            $this->attachmentManager->addNewAttachments($notification, $attachments);
        }


        $this->update($notification);

        return $notification;
    }

    /**
     * @param Notification $notification
     * @param array $recipients
     * @return Notification
     * @throws Exception
     */
    protected function buildExtendedRecipientListForNotification(NotificationInterface $notification, array $recipients): NotificationInterface
    {
        $extendedRecipientsCnt = 0;
        $channel = $this->getChannelByName($notification->getChannelName());

        foreach ($recipients as $key => $recipient) {
            $recipientName = null;
            $recipientEmail = null;
            $recipientPhone = null;
            $recipientToken = null;
            $recipientExtendedData = null;

            if (is_array($recipient)) {
                //Sprawdzamy czy tablica posiada dane specjalne name, email, phone, extendedData
                if (array_key_exists('name', $recipient)) {
                    $recipientName = $recipient['name'];
                }

                if (array_key_exists('email', $recipient)) {
                    $recipientEmail = $recipient['email'];
                }

                if (array_key_exists('phone', $recipient)) {
                    $recipientPhone = $recipient['phone'];
                }

                if (array_key_exists('token', $recipient)) {
                    $recipientToken = $recipient['token'];
                }

                if (array_key_exists('extendedData', $recipient)) {
                    $recipientExtendedData = $recipient['extendedData'];
                }
            } else {
                $recipientEmail = $recipient;
            }

            $extendedRecipient = $this->createExtendedRecipient(
                $recipientName,
                $recipientEmail,
                $recipientPhone,
                $recipientToken,
                $recipientExtendedData
            );

            $errorList = $channel->validateExtendedRecipient($extendedRecipient);

            if (count($errorList) == 0) {
                //Dodajemy odbiorcę po poprawnej walidacji danych
                $notification->addNotificationExtendedRecipient($extendedRecipient);
                $extendedRecipientsCnt++;
            }
        }

        $notification->setRecipientsCount($extendedRecipientsCnt);

        return $notification;
    }

    /**
     * @param string|null $name
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $token
     * @param array|null $extendedData
     * @param Notification|null $notification
     * @return NotificationRecipient
     * @throws Exception
     */
    public function createExtendedRecipient(
        ?string $name,
        ?string $email,
        ?string $phone,
        ?string $token,
        ?array $extendedData, //notused
        ?Notification $notification = null
    ): NotificationRecipientInterface {
        $extendedRecipient = $this->notificationRecipientManager->createNew();

        $extendedRecipient
            ->setEmail($email)
            ->setName($name)
            ->setPhone($phone)
            ->setToken($token);

        if ($notification) {
            $extendedRecipient->setNotification($notification);
        }

        return $extendedRecipient;
    }

    /**
     * @param string|null $notificationDomain
     * @param string|null $appCode
     * @return string
     */
    protected function determineNotificationDomain(string $notificationDomain = null, string $appCode = null)
    {
        if (!$notificationDomain && $appCode && array_key_exists($appCode . 'Front', $this->parameterBag->get('notifications.domains'))) {
            // Jeżeli nie podano, to pobiera dane z configa dla danej aplikacji
            $notificationDomain = $this->parameterBag->get('notifications.domains')[$appCode . 'Front'];
        }

        $request = $this->requestStack->getCurrentRequest();

        //Jeżeli nie jest ustalona domena dla linków, próbujemy pobrać dane z http request (o ile jest dostępny)
        if (!$notificationDomain && $request && $request->getSchemeAndHttpHost()) {
            $notificationDomain = $request->getSchemeAndHttpHost();
        }

        return $notificationDomain;
    }

    /**
     * @param string|null $resourceDomain
     * @param string|null $appCode
     * @return string
     */
    protected function determineResourceDomain(string $resourceDomain = null, string $appCode = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$resourceDomain && $appCode && array_key_exists($appCode . 'Static', $this->parameterBag->get('notifications.domains'))) {
            // Jeżeli nie podano, to pobiera dane z configa dla danej aplikacji
            $resourceDomain = $this->parameterBag->get('notifications.domains')[$appCode . 'Static'];
        }
        //Jeżeli nie jest ustalona domena dla linków, próbujemy pobrać dane z http request (o ile jest dostępny)
        if (!$resourceDomain && $request && $request->getSchemeAndHttpHost()) {
            $resourceDomain = $request->getSchemeAndHttpHost();
        }

        return $resourceDomain;
    }

    /**
     * @param Notification $notification
     * @return bool|Notification
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function sendNotification(NotificationInterface $notification)
    {
        $channelName = $notification->getChannelName();
        $channel = $this->getChannelByName($channelName);
        $recipientsToProcessCnt = null;
        $status = null;

        if (!$channel) {
            return false;
        }

        $notification->setStatus(Notification::STATUS_PROCESSING);

        switch ($notification->getType()) {
            case Notification::TYPE_SIMPLE:
                [$recipientsToProcessCnt, $status] = $this->sendSimpleNotification($notification, $channel);
                break;
            case Notification::TYPE_EXTENDED:
                [$recipientsToProcessCnt, $status] = $this->sendExtendedNotification($notification, $channel);
                break;
            default:
                throw new Exception('Wrong notification type');
                break;
        }

        $event = new NotificationEvent($notification);
        $this->eventDispatcher->dispatch($event, NotificationEvents::COMMON_NOTIFICATION_PROCESSED);

        if ($recipientsToProcessCnt !== null && $recipientsToProcessCnt == 0 && $status && $notification->getCompletedRecipientsCount() > 0) {
            $notification->setStatusCompleted();
        } elseif ($recipientsToProcessCnt !== null && $recipientsToProcessCnt == 0 && $notification->getCompletedRecipientsCount() == 0) {
            $notification->setStatus(Notification::STATUS_FAILED);
        }

        if ($notification->getStatus() == Notification::STATUS_COMPLETED) {
            //W zależności od ustawienia kasujemy albo zachowujemy załączniki po zakończeniu wysyłki powiadomień
            if (!$notification->isKeepNotificationAttachments()) {
                $notification->clearNotificationAttachments();
            }

            // NotificationCompletedEvent
            // jeżeli potrzebna jest informacja zwrotna o ukończeniu wysyłki dla zewnętrznej logiki
            // należy skorzystać z eventu
            $event = new NotificationEvent($notification);
            $this->eventDispatcher->dispatch($event, NotificationEvents::COMMON_NOTIFICATION_COMPLETED);
        }

        //Wykonuje pełnego flusha
        return $this->update($notification);
    }

    /**
     * Metoda obsługująca wysyłkę "prostego" powiadomienia
     * Powiadomienie bazuje jedynie na liście adresów
     *
     * @param Notification $notification
     * @param ChannelInterface $channel
     * @return array
     */
    protected function sendSimpleNotification(NotificationInterface $notification, ChannelInterface $channel): array
    {
        $status = true;
        $errorLog = '';
        $sendLog = '';

        if ($notification->getRecipientsCount() > $channel->getMaxRecipients()) {
            $recipientsToProcess = array_slice($notification->getRecipients(), 0, $channel->getMaxRecipients());
        } else {
            $recipientsToProcess = $notification->getRecipients();
        }

        //We divide recipients into smaller packages
        $packages = $recipientsToProcess = array_chunk($recipientsToProcess, $notification->getMaxRecipientsPerCycle());
        $processedRecipients = [];

        //We provide shipment for each of the recipients' packages
        foreach ($packages as $recipientsPackage) {
            [$processedRecipientsFromPackage, $processStatus, $errorLog, $sendLog] = $channel->sendSimpleNotification($notification, $recipientsPackage);
            $processedRecipients = array_merge($processedRecipients, $processedRecipientsFromPackage);
            if (!$processStatus) {
                $status = false;
            }
        }

        $recipientsToProcess = array_diff($notification->getRecipients(), $processedRecipients);

        //If the shipment fails at this stage, we will mark the shipment status for renewal.
        if (!$status && $notification->getRetryCount() < self::RETRY_COUNT) {
            $notification->setStatus(Notification::STATUS_RETRYING);
            $notification->increaseRetryCount();
        } else {
            $notification->setStatus(Notification::STATUS_FAILED);
        }

        $notification->setRecipients($recipientsToProcess);

        if (is_array($notification->getCompletedRecipients())) {
            $notification->setCompletedRecipients(array_merge($notification->getCompletedRecipients(), $processedRecipients));
        } else {
            $notification->setCompletedRecipients($processedRecipients);
        }

        $this->processLogs($notification, $errorLog, $sendLog);

        return [count($recipientsToProcess), $status, $notification];
    }

    /**
     * @param Notification $notification
     * @param ChannelInterface $channel
     * @return array
     */
    protected function sendExtendedNotification(Notification $notification, ChannelInterface $channel): array
    {
        $recipientsPackageToProcess = $this->notificationRecipientManager->getRepository()->getRecipientsToProcess($notification->getId(), $channel->getMaxRecipients());
        $recipientsToProcessCnt = $this->notificationRecipientManager->getRepository()->getRemainingRecipientsCount($notification->getId());

        if ($recipientsToProcessCnt == 0) {
            return [$recipientsToProcessCnt, true, $notification];
        }

        $completedRecipientsCnt = 0;
        $status = null;

        // we divide recipients into smaller packages, in the case of the so-called extended notification, and thus parsing
        // there should be only 1 recipient in the package, if there will be more than one recipient in the package, the parsing will be based on the data of the first recipient
        $packages = array_chunk($recipientsPackageToProcess, $notification->getMaxRecipientsPerCycle());

        foreach ($packages as $recipientsPackage) {
            foreach ($recipientsPackage as $singleRecipient) {
                $singleRecipient->setStatus(NotificationRecipientInterface::STATUS_PROCESSING);
            }

            //In the case of an extended version of notifications, the shipping log is saved at the recipient level
            [$success, $errorLog, $sendLog] = $channel->sendExtendedNotification($notification, $recipientsPackage);

            if ($status === null) {
                $status = $success;
            } elseif ($status !== null && $status == true && $success == false) {
                $status = false;
            }

            foreach ($recipientsPackage as $singleRecipient) {
                $this->processRecipientLogs($singleRecipient, $errorLog, $sendLog);
            }

            if ($success) {
                foreach ($recipientsPackage as $singleRecipient) {
                    $singleRecipient->setStatusCompleted();
                    $completedRecipientsCnt++;
                }
            } else {
                foreach ($recipientsPackage as $singleRecipient) {
                    //W przypadku niepowodzenia wysyłki na tym etapie, oznaczymy status przesyłki do ponowienia
                    if ($singleRecipient->getRetryCount() < self::RETRY_COUNT) {
                        $singleRecipient
                            ->setStatus(NotificationRecipient::STATUS_RETRYING)
                            ->increaseRetryCount();
                    } else {
                        $singleRecipient->setStatus(Notification::STATUS_FAILED);
                    }
                }
            }
        }

        $recipientsToProcessCnt -= $completedRecipientsCnt;

        $totalCompletedRecipients = (int)$notification->getCompletedRecipientsCount() + $completedRecipientsCnt;
        $notification
            ->setCompletedRecipientsCount($totalCompletedRecipients)
            ->setRecipientsCount($recipientsToProcessCnt);

        // We save the data for all transferred recipients - we assume that a one-time batch of processed recipients should not exceed 1000
        foreach ($recipientsPackageToProcess as $singleRecipient) {
            $this->notificationRecipientManager->persist($singleRecipient);
        }

        return [$recipientsToProcessCnt, $status, $notification];
    }

    /**
     * @param NotificationInterface $notification
     * @param NotificationRecipientInterface $notificationRecipient
     * @return NotificationInterface
     * @throws Exception
     */
    public function previewParsedNotification(NotificationInterface $notification, NotificationRecipientInterface $notificationRecipient): NotificationInterface
    {
        $channelName = $notification->getChannelName();
        $channel = $this->getChannelByName($channelName);

        $channel->parseContent($notification, $notificationRecipient);

        return $notification;
    }

    /**
     * @param NotificationRecipient $notificationRecipient
     * @param $errorLog
     * @param $sendLog
     * @return NotificationRecipient
     */
    protected function processRecipientLogs(NotificationRecipientInterface $notificationRecipient, $errorLog, $sendLog): NotificationRecipientInterface
    {
        $date = date('Y-m-d H:i:s');

        if ($errorLog) {
            $notificationRecipient->addErrorLog([$date => $errorLog]);
        }

        if ($sendLog) {
            $notificationRecipient->addSendLog([$date => $sendLog]);
        }

        return $notificationRecipient;
    }

    /**
     * @param NotificationInterface $notification
     * @param $errorLog
     * @param $sendLog
     * @return NotificationInterface
     */
    protected function processLogs(NotificationInterface $notification, $errorLog, $sendLog): NotificationInterface
    {
        $date = date('Y-m-d H:i:s');
        $dateSeparator = "\n\n----{$date}----\n\n";

        if ($errorLog) {
            $notification->addErrorLog([$date => $errorLog]);
        }

        if ($sendLog) {
            $notification->addSendLog([$date => $sendLog]);
        }

        return $notification;
    }

    /**
     * @param string|null $channel
     * @param int|null $limit
     * @return array
     */
    public function getNotificationsToProcess(?string $channel = null, ?int $limit = null): array
    {
        return $this->getRepository()->getUncompletedNotifications($channel, self::NOTIFICATION_LIMIT_CRON);
    }

    /**
     * @param OutputInterface|null $output
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function processNotifications(?OutputInterface $output = null): void
    {
        $notifications = $this->getNotificationsToProcess(null, self::NOTIFICATION_LIMIT_CRON);

        if ($output && count($notifications) == 0) {
            $output->writeln('No notifications for processing');
        }

        /**
         * @var NotificationInterface $notification
         */
        foreach ($notifications as $notification) {
            if ($output) {
                $output->writeln(
                    sprintf(
                        'Processing notification ID: %d, recipients to process: %d, completed recipients: %d',
                        $notification->getId(),
                        $notification->getRecipientsCount(),
                        $notification->getCompletedRecipientsCount()
                    )
                );
            }

            $this->sendNotification($notification);

            if ($output) {
                $output->writeln(
                    sprintf(
                        'Processed notification ID: %d, recipients to process: %d, completed recipients: %d',
                        $notification->getId(),
                        $notification->getRecipientsCount(),
                        $notification->getCompletedRecipientsCount()
                    )
                );
            }

        }

        if ($output) {
            $output->writeln("Done");
        }
    }

    /**
     * @param Notification $notification
     * @return bool
     */
    public function resetNotification(NotificationInterface $notification): bool
    {
        if ($notification->getStatus() < Notification::STATUS_COMPLETED
            || $notification->getStatus() === Notification::STATUS_DATA_ERROR
        ) {
            return false;
        }

        $notification
            ->setStatus(Notification::STATUS_RETRYING)
            ->setRecipients(is_countable($notification->getCompletedRecipients()) && count($notification->getCompletedRecipients()) ? $notification->getCompletedRecipients() : $notification->getRecipients())
            ->setCompletedRecipients([])
            ->setCompletedRecipientsCount(0)
            ->setRetryCount(0);

        $this->update($notification);

        return true;
    }

    /**
     * Wymusza skierowanie powiadomienia do ponownej wysyłki (wszyscy odbiorcy)
     *
     * @param NotificationRecipient $notificationRecipient
     * @return bool
     */
    public function resetNotificationRecipient(NotificationRecipientInterface $notificationRecipient): bool
    {
        // re-dispatch is only possible after the notification is completed
        if ($notificationRecipient->getStatus() < NotificationRecipient::STATUS_COMPLETED) {
            return false;
        }

        $notificationRecipient
            ->setStatus(Notification::STATUS_RETRYING)
            ->setCompletedAt(null)
            ->setRetryCount(0);

        $this->notificationRecipientManager->update($notificationRecipient);

        return true;
    }

    /**
     * @param Notification $notification
     * @return bool
     */
    public function blockNotification(NotificationInterface $notification): bool
    {
        if ($notification->getStatus() == Notification::STATUS_COMPLETED) {
            return false;
        }

        $notification->setStatus(Notification::STATUS_BLOCKED);
        $this->update($notification);

        return true;
    }

    /**
     * @param NotificationRecipient $notificationRecipient
     * @return bool
     */
    public function blockNotificationRecipient(NotificationRecipientInterface $notificationRecipient): bool
    {
        if ($notificationRecipient->getStatus() == NotificationRecipient::STATUS_COMPLETED) {
            return false;
        }

        $notificationRecipient->setStatus(NotificationRecipient::STATUS_BLOCKED);

        $this->notificationRecipientManager->update($notificationRecipient);

        return true;
    }

    /**
     * @param string $templateName
     * @param string|null $channelName
     * @return string
     */
    protected function checkChannelInTemplate(string $templateName, ?string $channelName): string
    {
        if (!$channelName) {
            return $templateName;
        }

        return str_replace(self::CHANNEL_VAR, $channelName, $templateName);
    }

    /**
     * @param $templateData
     * @param $notification
     * @return array
     */
    protected function mergeNotificationIntoTemplateData($templateData, $notification): array
    {
        return array_merge($templateData, ['notification' => $notification]);
    }

    /**
     * Sprawdza przekazywane dane odbiorców, w wyniku zawsze musimy otrzymać tablicę!
     * @param $recipients
     * @return array
     */
    protected function checkForSingleArray($recipients): array
    {
        if (!$recipients) {
            return [];
        } elseif (!is_array($recipients) && $recipients != '') {
            return $this->splitBySpacesAndOther($recipients);
        } elseif (is_array($recipients) && count($recipients)) {
            return $recipients;
        }

        return [];
    }

    /**
     * @param string|null $recipients
     * @return array|array[]|false|string[]
     */
    protected function splitBySpacesAndOther(?string $recipients)
    {
        $result = preg_split('( |,|;|\\|)', $recipients, -1, PREG_SPLIT_NO_EMPTY);
        return $result ?? [];
    }

    /**
     * @param OutputInterface|null $outputInterface
     */
    public function removeOldNotifications(OutputInterface $outputInterface = null): void
    {
        if (!$this->parameterBag->get('notifications.remove_days')) {
            if ($outputInterface) {
                $outputInterface->writeln('Removing old notifications disabled.');
            }

            return;
        }

        //Kasujemy paczki max
        $oldNotifications = $this->getRepository()->getCompletedNotifications($this->parameterBag->get('notifications.remove_days'), self::NOTIFICATION_REMOVE_LIMIT);

        if (count($oldNotifications) > 0) {
            if ($outputInterface) {
                $outputInterface->writeln(sprintf('Removing old notifications: %d', count($oldNotifications)));
            }

            /**
             * @var Notification $oldNotification
             */
            foreach ($oldNotifications as $oldNotification) {
                if ($outputInterface) {
                    $outputInterface->writeln(sprintf('Removing notification: %s', $oldNotification->getSubject()));
                }

                $this->remove($oldNotification);
            }

            $this->flush();

            if ($outputInterface) {
                $outputInterface->writeln('Done');
            }
        } elseif ($outputInterface) {
            $outputInterface->writeln('Nothing to remove');
        }
    }

    /**
     * @param Notification $notification
     * @return Notification
     */
    public function recalculateExtendedNotificationRecipient(NotificationInterface $notification): NotificationInterface
    {
        if ($notification->getType() !== NotificationInterface::TYPE_EXTENDED) {
            return $notification;
        }

        $this->getObjectManager()->refresh($notification);

        $totalRecipientCount = $notification->getNotificationExtendedRecipients()->count();
        $completedRecipientCount = $this->notificationRecipientManager->getRepository()->getCompletedRecipientsCount($notification->getId());
        $remainingRecipientCount = $this->notificationRecipientManager->getRepository()->getRemainingRecipientsCount($notification->getId());

        $notification
            ->setRecipientsTotal($totalRecipientCount)
            ->setCompletedRecipientsCount($completedRecipientCount)
            ->setRecipientsCount($remainingRecipientCount);

        $this->update($notification);

        return $notification;
    }

    /**
     * @param string $class
     * @param string $eventName
     * @return bool
     */
    public function checkForTemplateConfiguration(string $class, string $eventName)
    {
        $this->templates = $this->parameterBag->get('notifications.templates');

        if (!array_key_exists($class, $this->templates)) {
            return false;
        }

        if (!array_key_exists($eventName, $this->templates[$class])) {
            return false;
        }

        return $this->templates[$class][$eventName];
    }

    /**
     * @param string $class
     * @param string $eventName
     * @param string|null $key
     * @return bool|mixed
     * @throws Exception
     */
    public function getTemplate(string $class, string $eventName, ?string $key = null)
    {
        $template = $this->checkForTemplateConfiguration($class, $eventName);

        //Brak różnicowania templatek
        if (!$key && !is_array($template)) {
            return $template;
        } elseif ($key && isset($template[$key])) {
            return $template[$key];
        }

        throw new Exception("Template configuration missing: {$eventName} {$key} ");
    }

    /**
     * ON/OFF
     *
     * @return bool
     */
    public function checkIfNotificationsEnabled(): bool
    {
        if ($this->parameterBag->get('notifications.enabled')) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getNotificationDomains(): array
    {
        if ($this->domains === null) {
            $this->domains = $this->parameterBag->get('notifications.domains');
        }

        if (!is_array($this->domains)) {
            $this->domains = [];
        }

        return $this->domains;
    }

    /**
     * @param string $class
     * @param string $eventName
     * @param null|string $customPart
     * @return string
     */
    public function getSubject(string $class, string $eventName, ?string $customPart = null): string
    {
        $subject = self::TRANSLATION_PREFIX . sprintf('.%s.%s', $class, $eventName);

        if ($customPart) {
            $subject .= '.' . $customPart;
        }

        return $subject;
    }

    /**
     * @param Notification $notification
     * @param string|null $languageCode
     */
    protected function processNotificationLanguageCode(Notification $notification, ?string $languageCode): void
    {
        if (!$languageCode) {
            // In this situation we determine the language version based on the translator's localization, we are dealing with the language version of the notification
            $languageCode = $this->translator->getLocale();
        }

        $language = $this->languageManager->getRepository()->getByIsoCode($languageCode);

        $notification
            ->setLanguage($language)
            ->setLanguageCode($languageCode);
    }
}