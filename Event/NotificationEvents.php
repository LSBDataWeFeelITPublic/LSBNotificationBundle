<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Event;

/**
 * Class NotificationEvents
 * @package LSB\NotificationBundle\Event
 */
class NotificationEvents
{
    /**
     * @var string
     */
    const COMMON_NOTIFICATION_COMPLETED = 'common.notification.completed';

    const COMMON_NOTIFICATION_PROCESSED = 'common.notification.processed';

    const COMMON_NOTIFICATION_RECIPIENTS_CHANGED = 'common.notification.recipients.changed';

    const COMMON_NOTIFICATION_TRACKING_DISPLAYED = 'common.notification.tracking.displayed';

    const COMMON_NOTIFICATION_TRACKING_OPENED = 'common.notification.tracking.opened';

    const COMMON_NOTIFICATION_TRACKING_CLICKED = 'common.notification.tracking.clicked';
}
