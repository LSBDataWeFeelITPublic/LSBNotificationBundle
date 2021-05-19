<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use LSB\UtilityBundle\Traits\CreatedUpdatedTrait;
use LSB\UtilityBundle\Traits\UuidTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotificationBlackList
 * @MappedSuperclass
 */
class NotificationBlackList implements NotificationBlackListInterface
{
    use UuidTrait;
    use CreatedUpdatedTrait;

    /**
     * @var string
     * @ORM\Column(type="string", length=400)
     * @Assert\Length(max=400)
     */
    protected string $email;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="is_active", nullable=true, options={"default": true})
     */
    protected bool $isActive = true;

    /**
     * NotificationBlackList constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->generateUuid();
    }

    /**
     * @throws \Exception
     */
    public function __clone()
    {
        $this->generateUuid(true);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }


}
