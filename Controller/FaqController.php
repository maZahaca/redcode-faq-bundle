<?php
namespace RedCode\FaqBundle\Controller;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;


class FaqController extends Controller
{
    public function listAction()
    {
        $request = $this->getRequest();
        if($request->get('s')) {
            $topics = $this->getFaqRepository()->searchFor($request->get('s'), $this->container->getParameter('redcode.faq.class'));
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
}