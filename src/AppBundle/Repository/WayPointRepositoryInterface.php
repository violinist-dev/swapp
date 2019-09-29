<?php
declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\WayPoint;

interface WayPointRepositoryInterface
{
    public function findOneById($id): ?WayPoint;

    public function save(WayPoint $wayPoint): void;

    /**
     * @return WayPoint[]
     */
    public function findAll(): array;
}
