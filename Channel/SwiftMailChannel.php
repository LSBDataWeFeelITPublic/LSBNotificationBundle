<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Channel;

use Exception;
use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationAttachmentInterface;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\NotificationBundle\Exception\NotificationParserException;
use LSB\NotificationBundle\Exception\NotificationProcessingException;
use LSB\NotificationBundle\Manager\NotificationAttachmentManager;
use LSB\NotificationBundle\Manager\NotificationParser;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swift_Attachment;
use Swift_Image;
use Swift_Mailer;
use Swift_Plugins_LoggerPlugin;
use Swift_Plugins_Loggers_ArrayLogger;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 * Class SwiftMailChannel
 * @package LSB\NotificationBundle\Channel
 */
class SwiftMailChannel implements ChannelInterface
{
    const MAX_RECIPIENTS = 50;
    const NAME = 'swift_mail';
    const BODY_TYPE = 'text/html';

    protected string $noReplyAddress;
    protected Swift_Mailer $mailer;
    protected Environment $templating;
    protected ValidatorInterface $validator;
    protected string $rootDir;
    protected ?Swift_Plugins_Loggers_ArrayLogger $logger;
    protected NotificationAttachmentManager $attachmentManager;
    protected NotificationParser $notificationParser;
    protected ParameterBagInterface $parameterBag;

    /**
     * MailChannel constructor.
     * @param Swift_Mailer $mailer
     * @param Environment $templating
     * @param ValidatorInterface $validator
     * @param string $noReplyAddress
     * @param string $rootDir
     * @param NotificationAttachmentManager $attachmentManager
     * @param NotificationParser $notificationParser
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        Swift_Mailer $mailer,
        Environment $templating,
        ValidatorInterface $validator,
        string $noReplyAddress,
        string $rootDir,
        NotificationAttachmentManager $attachmentManager,
        NotificationParser $notificationParser,
        ParameterBagInterface $parameterBag
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->validator = $validator;
        $this->noReplyAddress = $noReplyAddress;
        $this->rootDir = $rootDir;
        $this->attachmentManager = $attachmentManager;
        $this->notificationParser = $notificationParser;
        $this->parameterBag = $parameterBag;

        $this->registerMailerLogger();
    }

    /**
     * @return bool|mixed
     * @deprecated
     */
    public function getStatus()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return int
     */
    public function getMaxRecipients(): int
    {
        return self::MAX_RECIPIENTS;
    }

    /**
     * @param Notification $notification
     * @param array $recipientsToProcess
     * @return array
     */
    public function sendSimpleNotification(NotificationInterface $notification, array $recipientsToProcess): array
    {
        $errorMessage = "";
        $sendLog = "";

        $processedRecipients = [];
        $globalSuccess = true;
        $success = false;

        try {
            $success = $this->sendEmail(
                $recipientsToProcess,
                $notification->getCc(),
                $notification->getReplyTo(),
                $notification->getSubject(),
                $notification->getContent(),
                $notification->getNotificationDomain(),
                $notification->getNotificationAttachments(),
                $notification->isConvertImagesIntoAttachments()
            );
        } catch (Exception $e) {
            $errorMessage .= $e->getMessage();
        }

        if ($success) {
            $processedRecipients = $recipientsToProcess;
        }

        $sendLog .= $this->logger->dump();


        return [$processedRecipients, $success, $errorMessage, $sendLog];
    }

    /**
     * @param Notification $notification
     * @param array $recipientsToProcess
     * @return array
     */
    public function sendExtendedNotification(NotificationInterface $notification, array $recipientsToProcess = []): array
    {
        $sendLog = '';
        $errorLog = '';
        $success = false;
        $recipientsEmailAddresses = [];

        if (count($recipientsToProcess) == 0) {
            return [$success, $errorLog, $sendLog];
        }

        //Budujemy tablicę adresów
        $recipientsEmailAddresses = [];
        foreach ($recipientsToProcess as $singleRecipient) {
            $recipientsEmailAddresses[] = $singleRecipient->getEmail();
        }

        try {
            //index 0 will always exist within the package
            $firstRecipient = $recipientsToProcess[0];

            $this->parseContent($notification, $firstRecipient);

            //Przed każdą wysyłką log swift mailera jest czyszczony
            $success = $this->sendEmail(
                $recipientsEmailAddresses,
                $notification->getCc(),
                $notification->getReplyTo(),
                $notification->getSubject(),
                $notification->getParsedContent(),
                $notification->getNotificationDomain(),
                $notification->getNotificationAttachments(),
                $notification->isConvertImagesIntoAttachments()
            );
        } catch (Exception $e) {
            $errorLog = $e->getMessage();
        }

        $sendLog = $this->logger->dump();

        return [$success, $errorLog, $sendLog];
    }

    /**
     * @param $template
     * @param array $templateData
     * @return mixed
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \Twig\Error\RuntimeError
     */
    public function convertDataIntoContent($template, array $templateData)
    {
        return $this->templating->render($template, $templateData);
    }

    /**
     * A dedicated method to validate and customize your audience to your channel's needs
     *
     * @param array $recipients
     * @return array
     */
    public function validateSimpleRecipients(array $recipients): array
    {
        $recipients = $this->processEmailAddresses($recipients);

        foreach ($recipients as $key => $recipient) {
            $errorList = $this->validateSimpleRecipient($recipient);
            if (count($errorList)) {
                unset($recipients[$key]);
            }
        }

        return $recipients;
    }

    /**
     * Validation method for a single recipient - e-mail address in case of an e-mail channel
     */
    public function validateSimpleRecipient($recipient)
    {
        $emailConstraint = new Email();
        return $this->validator->validate($recipient, $emailConstraint);
    }

    /**
     * @param NotificationRecipient $recipient
     * @return ConstraintViolationListInterface
     */
    public function validateExtendedRecipient(NotificationRecipientInterface $recipient)
    {
        return $this->validateSimpleRecipient($recipient->getEmail());
    }

    /**
     * @param array $recipients
     * @return array
     */
    public function validateCC(array $recipients): array
    {
        return $this->validateSimpleRecipients($recipients);
    }

    /**
     * @param NotificationInterface $notification
     * @param NotificationRecipientInterface $notificationRecipient
     * @return Notification
     * @throws NotificationParserException
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function parseContent(NotificationInterface $notification, NotificationRecipientInterface $notificationRecipient): NotificationInterface
    {
        $this->notificationParser->parseNotificiation($notification, $notificationRecipient);
        return $notification;
    }

    /**
     * @param iterable $recipients
     * @param array|null $cc
     * @param string|null $replyTo
     * @param string|null $subject
     * @param string|null $content
     * @param string|null $notificationDomain
     * @param iterable|null $notificationAttachments
     * @param bool $convertImagesIntoAttachments
     * @return int
     */
    private function sendEmail(
        iterable $recipients,
        ?array $cc,
        ?string $replyTo,
        ?string $subject,
        ?string $content,
        ?string $notificationDomain,
        ?iterable $notificationAttachments,
        bool $convertImagesIntoAttachments = true
    ) {

        //Before sending the message, we clean the SWIFT logs
        $this->logger->clear();

        $message = new Swift_Message();
        if ($convertImagesIntoAttachments) {
            $images = $content ? $this->fetchImagesFromContent($content) : null;
            [$message, $content] = $this->convertImagesIntoAttachments($message, $images, $content, $notificationDomain);
        }

        $message = $this->processAttachments($message, $notificationAttachments);

        $message->setSubject($subject)
            //TODO Migrate from parameter to database
            ->setFrom($this->noReplyAddress, $this->parameterBag->get('email.sender.name'))
            ->setTo($recipients)
            ->setCc($cc ? $cc : [])
            ->setBody(
                $content,
                self::BODY_TYPE
            );

        if (!is_null($replyTo)) {
            $message->setReplyTo($replyTo);
        }

        return $this->mailer->send($message);
    }

    /**
     * @param string $content
     * @return array
     */
    private function fetchImagesFromContent(string $content): array
    {
        $result = null;
        preg_match_all('/<img[^>]+>/i', $content, $result);

        if (array_key_exists(0, $result)) {
            return $result[0];
        }

        return [];
    }

    /**
     * @param string|null $resourceDomain
     * @return mixed
     * @throws NotificationProcessingException
     * @deprecated
     */
    private function getAbsolutePathForNotificationDomain(?string $resourceDomain)
    {
        $parsedNotificationDomain = preg_replace('#^www\.(.+\.)#i', '$1', parse_url($resourceDomain, PHP_URL_HOST));

        foreach ($this->parameterBag->get('notifications.domains') as $key => $domain) {
            $parsedDomain = preg_replace('#^www\.(.+\.)#i', '$1', parse_url($domain, PHP_URL_HOST));

            if ($parsedDomain == $parsedNotificationDomain) {
                $absolutePaths = $this->parameterBag->get('notifications.absolutePaths');
                if (array_key_exists($key, $absolutePaths)) {
                    return $absolutePaths[$key];
                }
            }
        }

        throw new NotificationProcessingException('Missing absolute web path');
    }

    /**
     * @param string|null $resourceDomain
     * @return mixed
     */
    private function getAbsolutePathForNotificationDomainBase(?string $resourceDomain)
    {
        return 'public';
    }

    /**
     * Automatic image conversion to attachments
     *
     * @param Swift_Message $message
     * @param array|null $imageTags
     * @param string|null $content
     * @param string|null $resourceDomain
     * @return array
     */
    private function convertImagesIntoAttachments(
        Swift_Message $message,
        ?array $imageTags,
        ?string $content,
        ?string $resourceDomain
    ): array {
        $images = [];
        $hasOtherDomains = false;
        $fetchedOtherNotificationDomain = null;
        $otherWebPath = null;
        $webPath = $this->rootDir . '/../..' . $this->getAbsolutePathForNotificationDomainBase($resourceDomain);

        foreach ($imageTags as $key => $img_tag) {
            preg_match_all('/(src)=("[^"]*")/i', $img_tag, $images[$img_tag]);
        }

        //na indeksie [2][0] mamy src
        $imgSrc = [];
        $cidSrc = [];

        /**
         * @var string $image
         */
        foreach ($images as $image) {
            //0 - image src
            $src = str_replace(['"'], [''], $image[2][0]);

            if (!$src) {
                continue;
            }

            $hasNotificationResourceDomain = strpos($src, $resourceDomain);

            if (!$hasNotificationResourceDomain) {
                foreach ($this->parameterBag->get('notifications.domains') as $otherNotificationDomain) {
                    if (!$otherNotificationDomain) {
                        continue;
                    }

                    $hasOtherDomains = strpos($src, $otherNotificationDomain);

                    if ($hasOtherDomains !== false) {
                        $fetchedOtherNotificationDomain = $otherNotificationDomain;
                        $otherWebPath = $this->rootDir . '/../..' . $this->getAbsolutePathForNotificationDomainBase($otherNotificationDomain);
                        break;
                    }
                }
            }

            if ($hasNotificationResourceDomain === false && parse_url($src, PHP_URL_SCHEME) === false) {
                // Relative coverage used, let's add the response domain
                $srcPath = $webPath . $src;
            } elseif ($hasNotificationResourceDomain !== false) {
                //The domain used is the same as notificationResourceDomain
                $srcPath = str_replace([$resourceDomain], [$webPath], $src);
            } elseif ($hasOtherDomains !== false && $fetchedOtherNotificationDomain && $otherWebPath) {
                $srcPath = str_replace([$fetchedOtherNotificationDomain], [$otherWebPath], $src);
            } else {
                //The path is a remote location, we skip
                continue;
            }

            $srcPath = str_replace('//', '/', $srcPath);

            if (!file_exists($srcPath)) {
                continue;
            }

            $imgSrc[] = $src;
            $cid = $message->embed(Swift_Image::fromPath($srcPath));
            $cidSrc[] = $cid;
        }

        if (count($cidSrc)) {
            $content = str_replace($imgSrc, $cidSrc, $content);
        }


        return [$message, $content];
    }

    private function registerMailerLogger(): void
    {
        if (!$this->mailer) {
            return;
        }

        // To use the ArrayLogger
        $this->logger = new Swift_Plugins_Loggers_ArrayLogger();
        $this->mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($this->logger));
    }

    /**
     * Adding a single attachment
     *
     * @param Swift_Message $message
     * @param NotificationAttachmentInterface $notificationAttachment
     * @return bool
     */
    private function addAttachment(Swift_Message $message, NotificationAttachmentInterface $notificationAttachment): bool
    {
        try {
            $attachmentFilePath = $this->attachmentManager->getAttachmentPath($notificationAttachment);
            $message->attach(Swift_Attachment::fromPath($attachmentFilePath)->setFilename($notificationAttachment->getDisplayFileName() ?? $attachmentFilePath));
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Processing attachments
     *
     * @param Swift_Message $message
     * @param iterable $notificationAttachments
     * @return Swift_Message
     */
    public function processAttachments(Swift_Message $message, iterable $notificationAttachments = []): Swift_Message
    {
        foreach ($notificationAttachments as $notificationAttachment) {
            $this->addAttachment($message, $notificationAttachment);
        }

        return $message;
    }

    /**
     * @param array $emails
     * @return array
     */
    protected function processEmailAddresses(array $emails): array
    {
        foreach ($emails as $key => $email) {
            $emails[$key] = $this->processEmailAddress($email);
        }

        return $emails;
    }

    /**
     * @param null|string $email
     * @return null|string
     */
    protected function processEmailAddress(?string $email): ?string
    {
        if ($email !== null) {
            $email = trim($email);
        }

        return $email;
    }
}