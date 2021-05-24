<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationLogEntryInterface;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\NotificationBundle\Factory\NotificationLogEntryFactoryInterface;
use LSB\NotificationBundle\Model\ExtractedUserData;
use LSB\NotificationBundle\Repository\NotificationLogEntryRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* Class NotificationLogEntryManager
* @package LSB\NotificationBundle\Manager
*/
class NotificationLogEntryManager extends BaseManager
{
    /**
     * @var NotificationManager
     */
    protected NotificationManager $notificationManager;

    /**
     * @var ParameterBagInterface
     */
    protected ParameterBagInterface $parameterBag;


    const HEADER_CACHE_CONTROL = 'Cache-Control';
    const HEADER_CONTENT_TYPE = 'Content-type';
    const HEADER_CONTENT_TYPE_IMAGE_PNG = 'image/png';
    const HEADER_CACHE_CONTROL_PRIVATE = 'private';
    const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';
    const HEADER_CONTENT_LENGTH = 'Content-length';
    const REQUEST_PARAM_REDIRECT_URL = 'redirectTo';

    /**
     * NotificationLogEntryManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationLogEntryFactoryInterface $factory
     * @param NotificationLogEntryRepositoryInterface $repository
     * @param BaseEntityType|null $form
     * @param NotificationManager $notificationManager
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationLogEntryFactoryInterface $factory,
        NotificationLogEntryRepositoryInterface $repository,
        ?BaseEntityType $form,
        NotificationManager $notificationManager,
        ParameterBagInterface $parameterBag
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);

        $this->notificationManager = $notificationManager;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return NotificationLogEntryInterface|object
     */
    public function createNew(): NotificationLogEntryInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationLogEntryFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationLogEntryFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationLogEntryRepositoryInterface|RepositoryInterface
     */
    public function getRepository(): NotificationLogEntryRepositoryInterface
    {
        return parent::getRepository();
    }


    /**
     * Utworzenie wpisu loga (uniwersalny)
     *
     * @param int $type
     * @param Request $request
     * @param NotificationInterface $notification
     * @param NotificationRecipientInterface $notificationRecipient
     * @return NotificationLogEntryInterface
     */
    public function createNotificationLogEntry(
        int $type,
        Request $request,
        NotificationInterface $notification,
        NotificationRecipientInterface $notificationRecipient
    ): NotificationLogEntryInterface {
        $extractedUserData = $this->getExtractedUserDataFromRequest($request);

        $notificationLogEntry = $this->createNew();

        $notificationLogEntry
            ->setType($type)
            ->setIpAddress($extractedUserData->getRequestIpAddress())
            ->setSessionId($extractedUserData->getSessionId())
            ->setRequestReferer($extractedUserData->getRequestReferer())
            ->setRequestAcceptLanguage($extractedUserData->getRequestAcceptLanguage())
            ->setRequestUserAgent($extractedUserData->getRequestUserAgent())
            ->setXForwardedIpAddress($extractedUserData->getXForwardedIpAddress())
            ->setNotification($notification)
            ->setNotificationRecipient($notificationRecipient)
            ->setClickedUrl($extractedUserData->getClickedUrl())
        ;

        return $this->saveNotificationLogEntry($notificationLogEntry);
    }

    /**
     * Utworzenie wpisu wyświetlenia zawartości (w programie pocztowym)
     *
     * @param Request $request
     * @param NotificationInterface $notification
     * @param NotificationRecipientInterface $notificationRecipient
     * @return NotificationLogEntryInterface
     * @throws \Exception
     */
    public function createNotificationDisplayLogEntry(
        Request $request,
        NotificationInterface $notification,
        NotificationRecipientInterface $notificationRecipient
    ): NotificationLogEntryInterface {
        $notification->increaseTrackingDisplayCount();

        return $this->createNotificationLogEntry(
            NotificationLogEntryInterface::TYPE_TRACKING_DISPLAY,
            $request,
            $notification,
            $notificationRecipient
        );
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isDashboardEntry(
        Request $request
    ): bool {
        $referer = $request->headers->has(ExtractedUserData::HEADER_REFERER) ? $request->headers->get(ExtractedUserData::HEADER_REFERER) : null;

        if (!$referer) {
            return false;
        }

        $hostname = parse_url($referer, PHP_URL_HOST);

        if (trim($hostname) == trim($this->parameterBag->get('app.domain.admin'))) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isOpenEntry(
        Request $request
    ): bool {
        $referer = $request->headers->has(ExtractedUserData::HEADER_REFERER) ? $request->headers->get(ExtractedUserData::HEADER_REFERER) : null;

        if (!$referer) {
            return false;
        }

        $hostname = parse_url($referer, PHP_URL_HOST);

        if (trim($hostname) == trim($this->parameterBag->get('app.domain.preview'))) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @param NotificationInterface $notification
     * @param NotificationRecipientInterface $notificationRecipient
     * @return NotificationLogEntryInterface
     */
    public function createNotificationOpenLogEntry(
        Request $request,
        NotificationInterface $notification,
        NotificationRecipientInterface $notificationRecipient
    ): NotificationLogEntryInterface {
        $notification->increaseTrackingOpenCount();

        return $this->createNotificationLogEntry(
            NotificationLogEntryInterface::TYPE_TRACKING_OPEN,
            $request,
            $notification,
            $notificationRecipient
        );
    }

    /**
     * Utworzenie wpisu kliknięcia w link
     *
     * @param Request $request
     * @param NotificationInterface $notification
     * @param NotificationRecipientInterface $notificationRecipient
     * @return NotificationLogEntryInterface
     */
    public function createNotificationClickLogEntry(
        Request $request,
        NotificationInterface $notification,
        NotificationRecipientInterface $notificationRecipient
    ): NotificationLogEntryInterface {
        $notification->increaseTrackingClickCount();

        return $this->createNotificationLogEntry(
            NotificationLogEntryInterface::TYPE_TRACKING_CLICK,
            $request,
            $notification,
            $notificationRecipient
        );
    }

    /**
     * Entry update
     *
     * @param NotificationLogEntryInterface $notificationLogEntry
     * @return NotificationLogEntryInterface
     */
    public function saveNotificationLogEntry(NotificationLogEntryInterface $notificationLogEntry): NotificationLogEntryInterface
    {
        $this->persist($notificationLogEntry);
        $this->flush();

        return $notificationLogEntry;
    }


    /**
     * User data determined on the basis of the Request
     *
     * @param Request $request
     * @return ExtractedUserData
     */
    protected function getExtractedUserDataFromRequest(Request $request): ExtractedUserData
    {
        return new ExtractedUserData($request);
    }

    /**
     * Returns a ready response with a tracking image
     *
     * @param int $width
     * @param int $height
     * @return Response
     */
    public function prepareTrackingImageResponse(int $width = 300, int $height = 5): Response
    {
        $stream = fopen("php://memory", "w+");
        $i = imagecreatetruecolor($width, $height);
        imagesavealpha($i, true);
        $color = imagecolorallocatealpha($i, 0, 0, 0, 127);
        imagefill($i, 0, 0, $color);
        imagepng($i, $stream);
        rewind($stream);
        $png = stream_get_contents($stream);

        // Generate response
        $response = new Response();

        // Set headers
        $response->headers->set(self::HEADER_CACHE_CONTROL, self::HEADER_CACHE_CONTROL_PRIVATE);
        $response->headers->set(self::HEADER_CONTENT_TYPE, self::HEADER_CONTENT_TYPE_IMAGE_PNG);
        $response->headers->set(self::HEADER_CONTENT_DISPOSITION, 'attachment; filename="image.png";');
        $response->headers->set(self::HEADER_CONTENT_LENGTH, strlen($png));

        //Powoduje duplikaty nagłówków
        //$response->sendHeaders();
        $response->setContent($png);

        return $response;
    }
}
