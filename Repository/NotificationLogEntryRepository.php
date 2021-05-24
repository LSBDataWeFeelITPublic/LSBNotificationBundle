<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use LSB\NotificationBundle\Entity\NotificationLogEntry;
use LSB\UtilityBundle\Repository\BaseRepository;
use LSB\UtilityBundle\Repository\PaginationInterface;
use LSB\UtilityBundle\Repository\PaginationRepositoryTrait;

/**
 * Class NotificationLogEntryRepository
 * @package LSB\NotificationBundle\Repository
 */
class NotificationLogEntryRepository extends BaseRepository implements NotificationLogEntryRepositoryInterface
{
    use PaginationRepositoryTrait;

    /**
     * NotificationLogEntryRepository constructor.
     * @param ManagerRegistry $registry
     * @param string|null $stringClass
     */
    public function __construct(ManagerRegistry $registry, ?string $stringClass = null)
    {
        parent::__construct($registry, $stringClass ?? NotificationLogEntry::class);
    }

}
