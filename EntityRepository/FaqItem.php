<?php
namespace RedCode\FaqBundle\EntityRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @author Alexander pedectrian Permyakov <pedectrian@ruwizards.com>
 */
class FaqItem extends EntityRepository
{

    public function getPrevTopic($position, $class) {
        $qb= $this
            ->getEntityManager()
            ->createQueryBuilder();

        $qb
            ->select('f')
                ->from($class, 'f')
                ->where($qb->expr()->lt('f.position', ':pos'))
                    ->setParameter('pos', $position)
                    ->setMaxResults(1)
                    ->addOrderBy('f.position', 'DESC')
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getNextTopic($position, $className) {
        $qb= $this
            ->getEntityManager()
            ->createQueryBuilder();

        $qb
            ->select('f')
                ->from($className, 'f')
                ->where($qb->expr()->gt('f.position', ':pos'))
                    ->setParameter('pos', $position)
                    ->setMaxResults(1)
                    ->addOrderBy('f.position', 'ASC')
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function searchFor($s, $class) {
        $qb= $this
            ->getEntityManager()
            ->createQueryBuilder();

        $qb
            ->select('f')
            ->from($class, 'f')
                ->where( $qb->expr()->orX(
                    $qb->expr()->like('f.question', '?1'),
                    $qb->expr()->like('f.answer', '?1')
                ))
                ->setParameter('1', "%{$s}%")
                ->addOrderBy('f.position', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }
}