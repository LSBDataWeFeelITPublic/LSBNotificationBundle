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
class NotificationBlackListRepository extends BaseRepository implements NotificationBlackListRepositoryInterface, PaginationInterface
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

}
