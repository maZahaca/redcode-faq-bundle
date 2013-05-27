<?php
namespace RedCode\FaqBundle\Controller;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;


class FaqController extends Controller
{
    public function listAction()
    {

    }

    public function viewAction()
    {

    }

    public function editAction()
    {

    }

    /**
     * @return EntityRepository
     */
    private function getFaqRepository()
    {
        /** @var Registry $doctrine */
        $doctrine = $this->get('doctrine');
        return $doctrine->getRepository($this->get('container')->getParameter('redcode.faq.class'));
    }
}