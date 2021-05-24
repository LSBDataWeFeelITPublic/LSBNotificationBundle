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
     * The links provided as part of the notification were clicked
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isNotificationClicked = false;

    /**
     * Date when the notification was clicked
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $notificationClickedAt;

    /**
     * The notification has been displayed (without clicking the open link)
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isNotificationDisplayed = false;

    /**
     * First view date
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $notificationDisplayedAt;

    /**
     * Whether the notification has been opened (preview in the browser)
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected bool $isNotificationOpened = false;

    /**
     * Date opened in the browser
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
