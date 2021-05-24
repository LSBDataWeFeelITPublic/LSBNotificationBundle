<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Gaufrette\FilesystemMap;
use LSB\NotificationBundle\Entity\NotificationAttachmentInterface;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Exception\MissingAttachmentFileException;
use LSB\NotificationBundle\Exception\MissingSourceFileException;
use LSB\NotificationBundle\Factory\NotificationAttachmentFactoryInterface;
use LSB\NotificationBundle\Repository\NotificationAttachmentRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Class NotificationAttachmentManager
 * @package LSB\NotificationBundle\Manager
 */
class NotificationAttachmentManager extends BaseManager
{

    const FILE_SYSTEM_NAME = 'notification_attachments_fs';
    const FILE_MAPPING_NAME = 'notification_attachment';
    const FILE_COLUMN = 'file';

    /**
     * @var FilesystemMap
     */
    protected $fileSystemMap;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $manager;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * NotificationAttachmentManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationAttachmentFactoryInterface $factory
     * @param NotificationAttachmentRepositoryInterface $repository
     * @param BaseEntityType|null $form
     * @param KernelInterface $kernel
     * @param StorageInterface $storage
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationAttachmentFactoryInterface $factory,
        NotificationAttachmentRepositoryInterface $repository,
        ?BaseEntityType $form,
        KernelInterface $kernel,
        StorageInterface $storage
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);

        $this->kernel = $kernel;
        $this->storage = $storage;
    }

    /**
     * @return NotificationAttachmentInterface|object
     */
    public function createNew(): NotificationAttachmentInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationAttachmentFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationAttachmentFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationAttachmentRepositoryInterface|RepositoryInterface
     * @throws \Exception
     */
    public function getRepository(): NotificationAttachmentRepositoryInterface
    {
        return parent::getRepository();
    }

    /**
     * @param NotificationInterface $notification
     * @param string $absoluteFilePath
     * @param string $displayFileName
     * @return NotificationAttachmentInterface
     * @throws MissingSourceFileException
     */
    public function addNewAttachment(NotificationInterface $notification, string $absoluteFilePath, string $displayFileName): NotificationAttachmentInterface
    {
        $fileSize = @filesize($absoluteFilePath);

        if (!$fileSize) {
            throw new MissingSourceFileException();
        }

        /**
         * @var NotificationAttachmentInterface
         */
        $notificationAttachment = $this->createNew();

        $notificationAttachment
            ->setFile(new UploadedFile($absoluteFilePath, basename($absoluteFilePath)))
            ->setDisplayFileName($displayFileName);

        $notification->addNotificationAttachment($notificationAttachment);
        $this->getObjectManager()->persist($notification);

        return $notificationAttachment;
    }

    /**
     * @param NotificationInterface $notification
     * @param iterable $absoluteFilePaths
     * @return NotificationInterface
     * @throws MissingSourceFileException
     */
    public function addNewAttachments(NotificationInterface $notification, iterable $absoluteFilePaths = []): NotificationInterface
    {

        if (count($absoluteFilePaths)) {
            foreach ($absoluteFilePaths as $absoluteFilePath => $displayFileName) {
                $this->addNewAttachment($notification, $absoluteFilePath, $displayFileName);
            }

            $this->flush();
        }

        return $notification;
    }

    /**
     * @param NotificationAttachmentInterface $notificationAttachment
     * @return BinaryFileResponse
     * @throws MissingAttachmentFileException
     */
    public function downloadAttachment(NotificationAttachmentInterface $notificationAttachment)
    {

        $attachmentPath = $this->getAttachmentPath($notificationAttachment);

        if (!$attachmentPath) {
            throw new MissingAttachmentFileException('Missing attachment file path');
        }

        $fileSize = @filesize($attachmentPath);

        if (!$fileSize) {
            throw new MissingAttachmentFileException('Missing attachment file, filesize 0');
        }

        $response = new BinaryFileResponse($attachmentPath);
        BinaryFileResponse::trustXSendfileTypeHeader();
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $notificationAttachment->getDisplayFileName() ?? $notificationAttachment->getOriginalFileName()
            )
        );
        return $response;
    }

    /**
     * @param NotificationAttachmentInterface $notificationAttachment
     * @return string|null
     */
    public function getAttachmentPath(NotificationAttachmentInterface $notificationAttachment): ?string
    {
        return $this->storage->resolvePath(
            $notificationAttachment,
            static::FILE_COLUMN,
            $className = null,
            $relative = false
        );
    }
}
