<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\UtilityBundle\Repository\BaseRepository;
use LSB\UtilityBundle\Repository\PaginationRepositoryTrait;

/**
 * Class NotificationRecipientRepository
 * @package LSB\NotificationBundle\Repository
 */
class NotificationRecipientRepository extends BaseRepository implements NotificationRecipientRepositoryInterface
{
    use PaginationRepositoryTrait;

    /**
     * NotificationRecipientRepository constructor.
     * @param ManagerRegistry $registry
     * @param string|null $stringClass
     */
    public function __construct(ManagerRegistry $registry, ?string $stringClass = null)
    {
        parent::__construct($registry, $stringClass ?? NotificationRecipient::class);
    }

    /**
     * @param int $notificationId
     * @return array
     */
    public function getQBForNotificationRecipientList(int $notificationId): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.notification = :notificationId')
            ->setParameter('notificationId', $notificationId);

        return [$qb, ['e']];
    }

    /**
     * @param int $notificationId
     * @param int $limit
     * @return array|null
     */
    public function getRecipientsToProcess(int $notificationId, int $limit): ?array
    {
        $qb = $this->createQueryBuilder('nr')
            ->where('nr.notification = :notificationId')
            ->andWhere('nr.status < :statusCompleted')
            ->setMaxResults($limit)
            ->addOrderBy('nr.id', 'ASC')
            ->setParameter('notificationId', $notificationId)
            ->setParameter('statusCompleted', NotificationRecipientInterface::STATUS_COMPLETED);

        return $qb->getQuery()->execute();
    }

    /**
     * @param int $notificationId
     * @return int|null
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCompletedRecipientsCount(int $notificationId): ?int
    {
        $qb = $this->createQueryBuilder('nr')
            ->select('COUNT(DISTINCT nr.id) as CNT')
            ->where('nr.notification = :notificationId')
            ->andWhere('nr.status = :completedStatus')
            ->setParameter('notificationId', $notificationId)
            ->setParameter('completedStatus', NotificationRecipient::STATUS_COMPLETED);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $notificationId
     * @return int|null
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getRemainingRecipientsCount(int $notificationId): ?int
    {
        $qb = $this->createQueryBuilder('nr')
            ->select('COUNT(DISTINCT nr.id) as CNT')
            ->where('nr.notification = :notificationId')
            ->andWhere('nr.status < :completedStatus')
            ->setParameter('notificationId', $notificationId)
            ->setPArameter('completedStatus', NotificationRecipient::STATUS_COMPLETED);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
