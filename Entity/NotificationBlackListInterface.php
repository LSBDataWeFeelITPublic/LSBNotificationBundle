<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Interface NotificationBlackListInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationBlackListInterface extends UuidInterface
{

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self;

    /**
     * @return string
     */
    public function getEmail(): string;
}