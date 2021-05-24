<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\UtilityBundle\Repository\BaseRepository;
use LSB\UtilityBundle\Repository\PaginationInterface;
use LSB\UtilityBundle\Repository\PaginationRepositoryTrait;

/**
 * Class NotificationRepository
 * @package LSB\NotificationBundle\Repository
 */
class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    use PaginationRepositoryTrait;

    /**
     * NotificationRepository constructor.
     * @param ManagerRegistry $registry
     * @param string|null $stringClass
     */
    public function __construct(ManagerRegistry $registry, ?string $stringClass = null)
    {
        parent::__construct($registry, $stringClass ?? Notification::class);
    }

    /**
     * @param null $channelName
     * @param null $limit
     * @return mixed
     */
    public function getUncompletedNotifications($channelName = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.status < :completedStatus')
            ->setParameter('completedStatus', Notification::STATUS_COMPLETED)
            ->orderBy('n.id', 'ASC');

        if ($channelName) {
            $qb->andWhere('n.channelName LIKE :channelName')
                ->setParameter('channelName', $channelName);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param int $daysOld
     * @param int $limit
     * @return mixed
     * @throws \Exception
     */
    public function getCompletedNotifications(int $daysOld = 0, int $limit = 500): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.status >= :completedStatus')
            ->setParameter('completedStatus', NotificationInterface::STATUS_COMPLETED)
            ->orderBy('n.id', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($daysOld) {
            $minDate =
                (new \DateTime)
                    ->sub(new \DateInterval('P' . (int)$daysOld . 'D'));

            $qb
                ->andWhere('n.completedAt <= :minDate')
                ->setParameter('minDate', $minDate, \Doctrine\DBAL\Types\Type::DATETIME);
        }

        return $qb->getQuery()->execute();
    }
}
