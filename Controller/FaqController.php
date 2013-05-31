<?php
namespace RedCode\FaqBundle\Controller;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;

/**
 * @author Alexander pedectrian Permyakov <pedectrian@ruwizards.com>
 */
class FaqController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        $request = $this->getRequest();
        if($request->get('s')) {
            $topics = $this->searchFor($request->get('s'), $this->container->getParameter('redcode.faq.class'));
        } else {
            $topics = $this->getFaqRepository()->findBy(array(), array('position' => 'ASC'));
        }
        return new Response($this->renderView('RedCodeFaqBundle:Public:list.html.twig', array('topics' => $topics)));
    }

    public function viewAction()
    {

    }

    /**
     * @return EntityRepository
     */
    private function getFaqRepository()
    {
        /** @var Registry $doctrine */
        $doctrine = $this->get('doctrine');
        return $doctrine->getRepository($this->container->getParameter('redcode.faq.class'));
    }

    /**
     * @param $s
     * @param $class
     * @return array
     */
    public function searchFor($s, $class) {
        $qb= $this
            ->getDoctrine()
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