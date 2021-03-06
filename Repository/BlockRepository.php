<?php

namespace Opera\CoreBundle\Repository;

use Opera\CoreBundle\Entity\Block;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Opera\CoreBundle\Entity\Page;

/**
 * @method Block|null find($id, $lockMode = null, $lockVersion = null)
 * @method Block|null findOneBy(array $criteria, array $orderBy = null)
 * @method Block[]    findAll()
 * @method Block[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlockRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Block::class);
    }

    public function findForPageGroupedByAreas(Page $page)
    {
        $blocks = $this->createQueryBuilder('b')
                    ->andWhere('b.page = :page')
                    ->setParameter(':page', $page)
                    ->orderBy('b.area, b.position')
                    ->getQuery()
                    ->getResult()
        ;

        $return = [];

        foreach ($blocks as $block) {
            if (!isset($return[$block->getArea()])) {
                $return[$block->getArea()] = [];
            }

            $return[$block->getArea()][] = $block;
        }

        return $return;
    }

    public function findForAreaAndPage(string $area, ?Page $page = null)
    {
        if (!$page) {
            return $this->findForAreaAndGlobalPage($area);
        }

        return $this->createQueryBuilder('b')
                    ->andWhere('b.page = :page')
                    ->setParameter(':page', $page)
                    ->andWhere('b.area = :area')
                    ->setParameter(':area', $area)
                    ->orderBy('b.position')
                    ->getQuery()
                    ->getResult()
        ;
    }

    public function findForAreaAndGlobalPage(string $area)
    {
        return $this->createQueryBuilder('b')
                    ->innerJoin('b.page', 'p')
                    ->andWhere('p.slug = :slug')
                    ->setParameter(':slug', '_global')
                    ->andWhere('b.area = :area')
                    ->setParameter(':area', $area)
                    ->orderBy('b.position')
                    ->getQuery()
                    ->getResult()
        ;
    }

    public function movePageBlockTo(Page $page, string $area, string $blockId, int $position)
    {
        $this->createQueryBuilder('b')
             ->update()
             ->set('b.position', ':position')
             ->setParameter(':position', $position)
             ->andWhere('b.page = :page')
             ->setParameter(':page', $page)
             ->andWhere('b.area = :area')
             ->setParameter(':area', $area)
             ->andWhere('b.id = :id')
            ->setParameter(':id', $blockId)
             ->getQuery()
             ->execute();
    }
}
