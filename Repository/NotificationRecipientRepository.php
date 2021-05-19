<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\UtilityBundle\Repository\BaseRepository;
use LSB\UtilityBundle\Repository\PaginationInterface;
use LSB\UtilityBundle\Repository\PaginationRepositoryTrait;

/**
 * Class NotificationRecipientRepository
 * @package LSB\NotificationBundle\Repository
 */
class NotificationRecipientRepository extends BaseRepository implements NotificationRecipientRepositoryInterface, PaginationInterface
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

}
