<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use LSB\NotificationBundle\Entity\Notifcation;
use LSB\UtilityBundle\Repository\BaseRepository;
use LSB\UtilityBundle\Repository\PaginationInterface;
use LSB\UtilityBundle\Repository\PaginationRepositoryTrait;

/**
 * Class NotifcationRepository
 * @package LSB\NotificationBundle\Repository
 */
class NotifcationRepository extends BaseRepository implements NotifcationRepositoryInterface, PaginationInterface
{
    use PaginationRepositoryTrait;

    /**
     * NotifcationRepository constructor.
     * @param ManagerRegistry $registry
     * @param string|null $stringClass
     */
    public function __construct(ManagerRegistry $registry, ?string $stringClass = null)
    {
        parent::__construct($registry, $stringClass ?? Notifcation::class);
    }

}
