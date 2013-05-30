<?php
namespace RedCode\FaqBundle\Controller;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Wizards\AccountBundle\Entity\Faq;
use Wizards\AccountBundle\Form\FaqCreateType;


class FaqAdminController extends Controller
{
    public function listAction()
    {
        $request = $this->getRequest();
        if($request->get('s')) {
            $topics = $this->getFaqRepository()->searchFor($request->get('s'), $this->container->getParameter('redcode.faq.class'));
        } else {
            $topics = $this->getFaqRepository()->findBy(array(), array('position' => 'ASC'));
        }

        return new Response($this->renderView('RedCodeFaqBundle:Admin:list.html.twig', array('topics' => $topics)));
    }

    public function viewAction()
    {

    }
    public function deleteAction($id)
    {
        $format = $this->getRequest()->getRequestFormat();
        $faqItem = $this->getFaqRepository()->find($id);
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($faqItem);
        $em->flush();

        if($format == 'json') {
            $response['status'] = 'SUCCESS';

            return new Response(json_encode($response));
        }

        return $this->redirect($this->generateUrl('RedCodeFaqBundle_AdminFaq'));
    }
    public function editAction($id)
    {
        $request = $this->getRequest();
        $format = $request->getRequestFormat();

        $faqItem = $this->getFaqRepository()->find($id);
        $form = $this->createForm(new FaqCreateType(), $faqItem);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($faqItem);
                $em->flush();

                if($format == 'json') {
                    $response['status'] = 'SUCCESS';
                }
            } else {
                if($format == 'json') {
                    $response['status'] = 'ERROR';
                }
            }
        }

        switch($format) {
            case 'json':
                $response['html'] = $this->renderView('RedCodeFaqBundle:Admin:edit_form.html.twig', array('form' => $form->createView(), 'id' => $id));

                return new Response(json_encode($response));

                break;
            case 'html':
            default:
                $response = $this->renderView('RedCodeFaqBundle:Admin:edit.html.twig', array('form' => $form->createView(), 'id' => $id));

                break;
        }

        return new Response($response);
    }

    public function createAction()
    {
        $request = $this->getRequest();
        $format = $request->getRequestFormat();

        $faqItem = new Faq();
        $form = $this->createForm(new FaqCreateType(), $faqItem);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                $em->persist($faqItem);

                $faqItem->setPosition($faqItem->getId());

                $em->persist($faqItem);
                $em->flush();

                if($format == 'json') {
                    $response['status'] = 'SUCCESS';
                }
            } else {
                if($format == 'json') {
                    $response['status'] = 'ERROR';
                }
            }
        }

        switch($format) {
            case 'json':
                $response['html'] = $this->renderView('RedCodeFaqBundle:Admin:create_form.html.twig', array('form' => $form->createView()));

                return new Response(json_encode($response));

                break;
            case 'html':
            default:
                $response = $this->renderView('RedCodeFaqBundle:Admin:create.html.twig', array('form' => $form->createView()));

                break;
        }

        return new Response($response);
    }

    public function moveTopicUpAction($id) {
        $topic = $this->getTopic($id);
        $pos = $topic->getPosition();
        $em = $this->getDoctrine()->getEntityManager();

        $prevTopic = $this->getFaqRepository()->getPrevTopic($pos, $this->container->getParameter('redcode.faq.class'));

        if($prevTopic) {
            $topic->setPosition($prevTopic->getPosition());
            $prevTopic->setPosition($pos);

            $em->persist($prevTopic);
        } else {
            $topic->setPosition(1);
        }

        $em->persist($topic);

        $em->flush();

        return $this->redirect($this->generateUrl('RedCodeFaqBundle_AdminFaq'));
    }

    public function moveTopicDownAction($id) {
        $topic = $this->getTopic($id);
        $pos = $topic->getPosition();
        $em = $this->getDoctrine()->getEntityManager();

        $nextTopic = $this->getFaqRepository()->getNextTopic($pos, $this->container->getParameter('redcode.faq.class'));

        if($nextTopic) {
            $topic->setPosition($nextTopic->getPosition());
            $nextTopic->setPosition($pos);

            $em->persist($nextTopic);
        }


        $em->persist($topic);

        $em->flush();

        return $this->redirect($this->generateUrl('RedCodeFaqBundle_AdminFaq'));

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
     * @param $id
     * @return object
     */
    private function getTopic($id)
    {
        /** @var EntityRepository $repo */
        $repo = $this->get('doctrine')->getRepository($this->container->getParameter('redcode.faq.class'));

        return $repo->find($id);
    }
}