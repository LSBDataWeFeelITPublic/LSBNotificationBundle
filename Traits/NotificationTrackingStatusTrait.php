<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait NotificationTrackingStatusTrait
 * @package LSB\NotificationBundle\Traits
 */
trait NotificationTrackingStatusTrait
{
    /**
     * Kliknięto w linki udostępnione w ramach powiadomienia
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isNotificationClicked = false;

    /**
     * Data kliknięcia w powiadomienie
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $notificationClickedAt;

    /**
     * Powiadomienie zostało wyświetlone (bez kliknięcia w link otwórz)
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isNotificationDisplayed = false;

    /**
     * Data pierwszego wyświetlenia
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $notificationDisplayedAt;

    /**
     * Czy powiadomienie zostało otwarte (podgląd w przeglądarce)
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isNotificationOpened = false;

    /**
     * Data otwarcia w przeglądarce
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $notificationOpenedAt;

    /**
     * @return bool
     */
    public function isNotificationClicked(): bool
    {
        return $this->isNotificationClicked;
    }

    /**
     * @param bool $isNotificationClicked
     * @return $this
     */
    public function setIsNotificationClicked(bool $isNotificationClicked): self
    {
        $this->isNotificationClicked = $isNotificationClicked;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getNotificationClickedAt(): ?DateTime
    {
        return $this->notificationClickedAt;
    }

    /**
     * @param DateTime|null $notificationClickedAt
     * @return $this
     */
    public function setNotificationClickedAt(?DateTime $notificationClickedAt): self
    {
        $this->notificationClickedAt = $notificationClickedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNotificationDisplayed(): bool
    {
        return $this->isNotificationDisplayed;
    }

    /**
     * @param bool $isNotificationDisplayed
     * @return $this
     */
    public function setIsNotificationDisplayed(bool $isNotificationDisplayed): self
    {
        $this->isNotificationDisplayed = $isNotificationDisplayed;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getNotificationDisplayedAt(): ?DateTime
    {
        return $this->notificationDisplayedAt;
    }

    /**
     * @param DateTime|null $notificationDisplayedAt
     * @return $this
     */
    public function setNotificationDisplayedAt(?DateTime $notificationDisplayedAt): self
    {
        $this->notificationDisplayedAt = $notificationDisplayedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNotificationOpened(): bool
    {
        return $this->isNotificationOpened;
    }

    /**
     * @param bool $isNotificationOpened
     * @return $this
     */
    public function setIsNotificationOpened(bool $isNotificationOpened): self
    {
        $this->isNotificationOpened = $isNotificationOpened;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getNotificationOpenedAt(): ?DateTime
    {
        return $this->notificationOpenedAt;
    }

    /**
     * @param DateTime|null $notificationOpenedAt
     * @return $this
     */
    public function setNotificationOpenedAt(?DateTime $notificationOpenedAt): self
    {
        $this->notificationOpenedAt = $notificationOpenedAt;
        return $this;
    }
}
