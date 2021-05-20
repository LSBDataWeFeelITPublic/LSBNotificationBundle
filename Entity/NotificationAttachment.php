<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use LSB\UtilityBundle\Traits\CreatedUpdatedTrait;
use LSB\UtilityBundle\Traits\UuidTrait;
use LSB\UtilityBundle\Traits\FileDataTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File as SfFile;

/**
 * Class NotificationAttachment
 *
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks()
 * @MappedSuperclass
 */
class NotificationAttachment implements NotificationAttachmentInterface
{
    use UuIdTrait;
    use CreatedUpdatedTrait;
    use FileDataTrait;

    //Aktualnie wykorzystywany system plików
    const FILE_SYSTEM_NAME = 'notification_attachments_fs';
    const FILE_MAPPING_NAME = 'notification_attachment';
    const FILE_COLUMN = 'file';

    /**
     * @ORM\ManyToOne(targetEntity="LSB\NotificationBundle\Entity\NotificationInterface", inversedBy="notificationAttachments")
     */
    protected NotificationInterface $notification;

    /**
     * @Vich\UploadableField(mapping="notification_attachment", fileNameProperty="fileName")
     */
    protected ?SfFile $file;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $displayFileName;

    /**
     * NotificationAttachment constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->generateUuid();
    }

    /**
     * @param SfFile|null $file
     * @return $this
     */
    public function setFile(SfFile $file = null): self
    {

        if ($file && $file->isFile() && $file instanceof UploadedFile && $file->getSize() > 0) {
            $this->uploadedFileEmpty = false;
            $this->file = $file;
            $this->size = $file->getSize();
            $this->originalFileName = $file->getFilename();
            $this->extension = $file->guessExtension();
            $this->updatedAt = new \DateTime('now');
        } elseif ($file && $file instanceof UploadedFile && $file->getSize() == 0) {
            $this->uploadedFileEmpty = true;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return $this
     */
    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOriginalFileName(): ?string
    {
        return $this->originalFileName;
    }

    /**
     * @param string|null $originalFileName
     * @return $this
     */
    public function setOriginalFileName(?string $originalFileName): self
    {
        $this->originalFileName = $originalFileName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string|null $extension
     * @return $this
     */
    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return null
     */
    public function getUploadedFileEmpty()
    {
        return $this->uploadedFileEmpty;
    }

    /**
     * @param null $uploadedFileEmpty
     * @return $this
     */
    public function setUploadedFileEmpty($uploadedFileEmpty)
    {
        $this->uploadedFileEmpty = $uploadedFileEmpty;
        return $this;
    }


}
