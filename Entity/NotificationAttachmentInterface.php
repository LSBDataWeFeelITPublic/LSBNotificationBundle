<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use LSB\UtilityBundle\Interfaces\UuidInterface;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface NotificationAttachmentInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationAttachmentInterface extends UuidInterface
{

    /**
     * @param string|null $fileName
     * @return $this
     */
    public function setFileName(?string $fileName): self;

    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): self;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * @return string|null
     */
    public function getFileName(): ?string;

    /**
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface;

    /**
     * @param null $uploadedFileEmpty
     * @return $this
     */
    public function setUploadedFileEmpty($uploadedFileEmpty);

    /**
     * @param string|null $originalFileName
     * @return $this
     */
    public function setOriginalFileName(?string $originalFileName): self;

    /**
     * @param SfFile|null $file
     * @return $this
     */
    public function setFile(SfFile $file = null): self;

    /**
     * @param string|null $displayFileName
     * @return $this
     */
    public function setDisplayFileName(?string $displayFileName): self;

    /**
     * @return string|null
     */
    public function getExtension(): ?string;

    /**
     * @return string|null
     */
    public function getDisplayFileName(): ?string;

    /**
     * @return string|null
     */
    public function getOriginalFileName(): ?string;

    /**
     * @param NotificationInterface $notification
     * @return $this
     */
    public function setNotification(NotificationInterface $notification): self;

    /**
     * @param string|null $extension
     * @return $this
     */
    public function setExtension(?string $extension): self;

    /**
     * @return null
     */
    public function getUploadedFileEmpty();
}