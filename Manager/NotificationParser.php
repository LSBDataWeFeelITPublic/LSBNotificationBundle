<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use DOMDocument;
use DOMNodeList;
use Exception;
use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\NotificationBundle\Exception\NotificationParserException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 *
 * @author Krzysztof Mazur
 * Klasa odpowiedzialna za parsowanie zawartości powiadomienia pod kątem dedykowanych odbiorców
 */
class NotificationParser
{
    const OPEN_VAR_BRACKET = '[[';
    const CLOSE_VAR_BRACKET = ']]';

    const OPEN_LOGIC_BRACKET = '[%';
    const CLOSE_LOGIC_BRACKET = '%]';

    const OPEN_VAR_TWIG_BRACKET = '{{';
    const CLOSE_VAR_TWIG_BRACKET = '}}';

    const OPEN_LOGIC_TWIG_BRACKET = '{%';
    const CLOSE_LOGIC_TWIG_BRACKET = '%}';

    //VARS
    const VAR_NAME = 'recipientName';
    const VAR_EMAIL = 'recipientEmail';
    const VAR_TOKEN = 'recipientToken';
    const VAR_PHONE = 'recipientPhone';
    const VAR_RECIPIENT_UUID = 'recipientUuid';
    const VAR_NOTIFICATION_UUID = 'notificationUuid';
    const VAR_REDIRECT_TO = 'redirectToUrl';
    const VAR_DISPLAY_TRACKING_URL = 'displayTrackingUrl';

    const HREF_TARGET_PARENT = '_PARENT';
    const HREF_TARGET_BLANK = '_BLANK';

    /**
     * @var string[]
     */
    public static $availableVarList = [
        self::VAR_NAME => 'Notification.Vars.Name',
        self::VAR_EMAIL => 'Notification.Vars.Email',
        self::VAR_TOKEN => 'Notification.Vars.Token',
        self::VAR_RECIPIENT_UUID => 'Notification.Vars.RecipientUuid',
        self::VAR_NOTIFICATION_UUID => 'Notification.Vars.NotificationUuid'
    ];

    /**
     * @var ParameterBagInterface
     */
    protected ParameterBagInterface $parameterBag;

    /**
     * @var Environment
     */
    protected Environment $twigEnv;

    /**
     * @var Notification|null
     */
    protected ?NotificationInterface $notification;

    /**
     * NotificationParser constructor.
     * @param ParameterBagInterface $parameterBag
     * @param Environment $twigEnv
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        Environment $twigEnv
    ) {
        $this->parameterBag = $parameterBag;
        $this->twigEnv = $twigEnv;
    }

    /**
     * @param NotificationInterface $notification
     */
    public function setNotification(NotificationInterface $notification): void
    {
        $this->notification = $notification;
    }

    /**
     * @return NotificationInterface
     * @throws NotificationParserException
     */
    public function getNotification(): NotificationInterface
    {
        if (!$this->notification instanceof NotificationInterface) {
            throw new NotificationParserException('Missing notification object');
        }

        return $this->notification;
    }

    /**
     * Parsowanie powiadomienia w kontekście odbiorcy
     *
     * @param Notification|null $notification
     * @param NotificationRecipient $recipient
     * @return Notification
     * @throws NotificationParserException
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function parseNotificiation(?NotificationInterface $notification, NotificationRecipientInterface $recipient): NotificationInterface
    {
        return $this->parseTwig($notification, $recipient);
    }

    /**
     * Zamiana zmiennych %var% na {{var}}
     *
     * @param string|null $notificationContent
     * @return mixed|string|string[]|void
     */
    public function convertCustomBracketsIntoTwig(?string $notificationContent): ?string
    {
        return str_replace(
            [
                self::OPEN_LOGIC_BRACKET,
                self::CLOSE_LOGIC_BRACKET,
                self::OPEN_VAR_BRACKET,
                self::CLOSE_VAR_BRACKET,
            ],
            [
                self::OPEN_LOGIC_TWIG_BRACKET,
                self::CLOSE_LOGIC_TWIG_BRACKET,
                self::OPEN_VAR_TWIG_BRACKET,
                self::CLOSE_VAR_TWIG_BRACKET,
            ],
            $notificationContent
        );
    }

    /**
     * Podmiana parametru tokena
     *
     * @param string|null $content
     * @return mixed|string|string[]|void
     */
    public function urlTokenReplace(?string $content): ?string
    {
        if (is_null($content)) {
            return null;
        }

        return str_replace(
            ['%' . self::VAR_TOKEN . '%'],
            [NotificationParser::OPEN_VAR_TWIG_BRACKET . ' ' . NotificationParser::VAR_TOKEN . ' ' . NotificationParser::CLOSE_VAR_TWIG_BRACKET],
            $content
        );
    }

    /**
     * Podmiana parametrów adresu podglądu
     *
     * @param string|null $content
     * @return string|null
     */
    public function publicViewUrlTokenReplace(?string $content): ?string
    {
        if (is_null($content)) {
            return null;
        }

        return str_replace(
            [
                '%' . self::VAR_RECIPIENT_UUID . '%',
                '%' . self::VAR_NOTIFICATION_UUID . '%'
            ],
            [
                NotificationParser::OPEN_VAR_TWIG_BRACKET . ' ' . NotificationParser::VAR_RECIPIENT_UUID . ' ' . NotificationParser::CLOSE_VAR_TWIG_BRACKET,
                NotificationParser::OPEN_VAR_TWIG_BRACKET . ' ' . NotificationParser::VAR_NOTIFICATION_UUID . ' ' . NotificationParser::CLOSE_VAR_TWIG_BRACKET
            ],
            $content
        );
    }

    /**
     * Podmiana adresu zdjęcia śledzącego
     *
     * @param string|null $content
     * @return string|null
     */
    public function displayTrackingUrlReplace(?string $content): ?string
    {
        return str_replace(
            [
                '%' . self::VAR_DISPLAY_TRACKING_URL . '%',
            ],
            [
                NotificationParser::OPEN_VAR_TWIG_BRACKET . ' ' . NotificationParser::VAR_DISPLAY_TRACKING_URL . ' ' . NotificationParser::CLOSE_VAR_TWIG_BRACKET,
            ],
            $content
        );
    }

    /**
     * Przetwarzanie treści z użyciem TWIG
     *
     * @param Notification|null $notification
     * @param NotificationRecipient $recipient
     * @return Notification
     * @throws NotificationParserException
     * @throws LoaderError
     * @throws SyntaxError
     */
    protected function parseTwig(?NotificationInterface $notification, NotificationRecipientInterface $recipient): Notification
    {
        if (!$notification instanceof NotificationInterface) {
            $notification = $this->getNotification();
        }

        $notificationContent = $notification->getContent();
        $notificationContent = $this->urlTokenReplace($notificationContent);
        $notificationContent = $this->publicViewUrlTokenReplace($notificationContent);
        $notificationContent = $this->displayTrackingUrlReplace($notificationContent);

        //deprecated
        //$notificationContent = $this->convertCustomBracketsIntoTwig($notificationContent);
        $template = $this->twigEnv->createTemplate($notificationContent);

        try {
            $twigResult = $template->render(
                [
                    self::VAR_NAME => $recipient->getName(),
                    self::VAR_EMAIL => $recipient->getEmail(),
                    self::VAR_TOKEN => $recipient->getToken(),
                    self::VAR_NOTIFICATION_UUID => $notification->getUuid(),
                    self::VAR_RECIPIENT_UUID => $recipient->getUuid(),
                    self::VAR_DISPLAY_TRACKING_URL => $notification->isTrackingEnabled() ? $this->prepareTrackingDisplay($notification->getUuid(), $recipient->getUuid()) : null
                ]
            );

            if ($notification->isTrackingEnabled()) {
                $twigResult = $this->addTracking($notification, $recipient, $twigResult);
            }

            if ($notification->isPublicViewEnabled() || $notification->isPublicRecipientViewEnabled()) {
                $twigResult = $this->forceParentTarget($twigResult);
            }

            $notification->setParsedContent($twigResult);
        } catch (Exception $e) {
            $date = date('Y-m-d H:i:s');
            $recipient->addErrorLog([ $date => $e->getMessage()]);
            $notification->setParsedContent('');
        }

        return $notification;
    }

    /**
     * Dodanie elementów śledzenia
     *
     * @param Notification $notification
     * @param NotificationRecipient $notificationRecipient
     * @param string $twigResult
     * @return string
     * @throws NotificationParserException
     */
    protected function addTracking(
        NotificationInterface $notification,
        NotificationRecipientInterface $notificationRecipient,
        string $twigResult
    ): string {
        //Śledzenie kliknięć
        return $this->addClickTracking($notification, $notificationRecipient, $twigResult);
    }

    /**
     * Śledzenie kliknięć
     *
     * @param Notification $notification
     * @param NotificationRecipient $notificationRecipient
     * @param string $twigResult
     * @return string
     * @throws NotificationParserException
     */
    protected function addClickTracking(
        NotificationInterface $notification,
        NotificationRecipientInterface $notificationRecipient,
        string $twigResult
    ): string {
        $utfDecl = '<?xml encoding="utf-8" ?>';
        $doc = new DOMDocument();
        $doc->loadHTML($utfDecl.$twigResult);

        /**
         * @var DOMNodeList $anchor
         */
        foreach ($doc->getElementsByTagName('a') as $anchor) {
            $link = $anchor->getAttribute('href');

            if (!$anchor->getAttribute('data-skip-forward')) {
                //Osadzamy link śledzący
                $link = $this->prepareClickTrackingUrl($link, $notification->getUuid(), $notificationRecipient->getUuid());
            }

            $anchor->setAttribute('href', $link);
        }
        $twigResult = $doc->saveHTML();
        return str_replace($utfDecl, '', $twigResult);
    }

    /**
     * Wymuszenie otwierania linków w poza IFRAME w podglądzie
     *
     * @param string $twigResult
     * @return string
     */
    protected function forceParentTarget(string $twigResult): string
    {
        $utfDecl = '<?xml encoding="utf-8" ?>';
        $doc = new DOMDocument();
        $doc->loadHTML($utfDecl.$twigResult);

        /**
         * @var DOMNodeList $anchor
         */
        foreach ($doc->getElementsByTagName('a') as $anchor) {
            $anchor->setAttribute('target', self::HREF_TARGET_PARENT);
        }
        $twigResult = $doc->saveHTML();
        return str_replace($utfDecl, '', $twigResult);
    }

    /**
     * Osadzenie linku śledzącego
     *
     * @param string $url
     * @param string $notificationUuid
     * @param string $recipientUuid
     * @return string
     * @throws NotificationParserException
     */
    protected function prepareClickTrackingUrl(string $url, string $notificationUuid, string $recipientUuid): string
    {
        $clickTrackingRoute = $this->parameterBag->get('notifications.channel.mail.click_tracking_route');

        if (!$clickTrackingRoute) {
            throw new NotificationParserException('Missing click tracking route');
        }

        //Użyte zostały stałe zmiennych, w przypadku zmiany należy skorygować routing
        return str_replace(
            [
                self::OPEN_VAR_TWIG_BRACKET. self::VAR_NOTIFICATION_UUID . self::CLOSE_VAR_TWIG_BRACKET,
                self::OPEN_VAR_TWIG_BRACKET . self::VAR_RECIPIENT_UUID . self::CLOSE_VAR_TWIG_BRACKET,
                self::OPEN_VAR_TWIG_BRACKET . self::VAR_REDIRECT_TO . self::CLOSE_VAR_TWIG_BRACKET
            ],
            [
                $notificationUuid,
                $recipientUuid,
                urlencode($url)
            ],
            $clickTrackingRoute
        );
    }

    /**
     * Przygotowanie zmiennej obrazu śledzącego
     *
     * @param string $notificationUuid
     * @param string $recipientUuid
     * @return string
     */
    protected function prepareTrackingDisplay(
        string $notificationUuid,
        string $recipientUuid
    ): string {
        $displayTrackingRoute = $this->parameterBag->get('notifications.channel.mail.display_tracking_route');

        return str_replace(
            [
                self::OPEN_VAR_TWIG_BRACKET. self::VAR_NOTIFICATION_UUID . self::CLOSE_VAR_TWIG_BRACKET,
                self::OPEN_VAR_TWIG_BRACKET . self::VAR_RECIPIENT_UUID . self::CLOSE_VAR_TWIG_BRACKET
            ],
            [
                $notificationUuid,
                $recipientUuid
            ],
            $displayTrackingRoute
        );
    }
}