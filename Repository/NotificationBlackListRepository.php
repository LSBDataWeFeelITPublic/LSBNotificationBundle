<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use LSB\NotificationBundle\Entity\NotificationBlackList;
use LSB\UtilityBundle\Repository\BaseRepository;
use LSB\UtilityBundle\Repository\PaginationInterface;
use LSB\UtilityBundle\Repository\PaginationRepositoryTrait;

/**
 * Class NotificationBlackListRepository
 * @package LSB\NotificationBundle\Repository
 */
class NotificationBlackListRepository extends BaseRepository implements NotificationBlackListRepositoryInterface
{
    use PaginationRepositoryTrait;

    /**
     * NotificationBlackListRepository constructor.
     * @param ManagerRegistry $registry
     * @param string|null $stringClass
     */
    public function __construct(ManagerRegistry $registry, ?string $stringClass = null)
    {
        parent::__construct($registry, $stringClass ?? NotificationBlackList::class);
    }

    /**
     * @param string $email
     * @return NotificationBlackList|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByEmail(string $email): ?NotificationBlackList
    {
        $qb = $this->createQueryBuilder('nbl');
        $qb->where('lower(nbl.email) LIKE lower(:email)')
            ->andWhere('nbl.isActive = TRUE')
            ->setParameter('email', $email)
            ->orderBy('nbl.id', 'ASC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
