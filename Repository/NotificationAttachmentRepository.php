<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use LSB\NotificationBundle\Entity\NotificationAttachment;
use LSB\UtilityBundle\Repository\BaseRepository;
use LSB\UtilityBundle\Repository\PaginationInterface;
use LSB\UtilityBundle\Repository\PaginationRepositoryTrait;

/**
 * Class NotificationAttachmentRepository
 * @package LSB\NotificationBundle\Repository
 */
class NotificationAttachmentRepository extends BaseRepository implements NotificationAttachmentRepositoryInterface
{
    use PaginationRepositoryTrait;

    /**
     * NotificationAttachmentRepository constructor.
     * @param ManagerRegistry $registry
     * @param string|null $stringClass
     */
    public function __construct(ManagerRegistry $registry, ?string $stringClass = null)
    {
        parent::__construct($registry, $stringClass ?? NotificationAttachment::class);
    }

}
