<?php
namespace RedCode\FaqBundle\Controller;
use RedCode\FaqBundle\Form\FaqType;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;

/**
 * @author Alexander pedectrian Permyakov <pedectrian@ruwizards.com>
 */

class FaqAdminController extends Controller
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

        return new Response($this->renderView('RedCodeFaqBundle:Admin:list.html.twig', array('topics' => $topics)));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
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

    /**
     * @param $id
     * @return Response
     */
    public function editAction($id)
    {
        $request = $this->getRequest();
        $format = $request->getRequestFormat();

        $faqItem = $this->getFaqRepository()->find($id);
        $form = $this->createForm(new FaqType(), $faqItem);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($faqItem);
                $em->flush();

                if($format == 'json') {
                    $response['status'] = 'SUCCESS';
                } else {
                    return $this->redirect($this->generateUrl('RedCodeFaqBundle_AdminFaq'));
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

    /**
     * @return Response
     */
    public function createAction()
    {
        $request = $this->getRequest();
        $format = $request->getRequestFormat();

        $faqInfo = $this
                        ->getDoctrine()
                        ->getEntityManager()
                        ->getClassMetadata($this->container->getParameter('redcode.faq.class'));

        $faqItem = new $faqInfo->name;
        $form = $this->createForm(new FaqType(), $faqItem);

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
                } else {
                    return $this->redirect($this->generateUrl('RedCodeFaqBundle_AdminFaq'));
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

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function moveTopicUpAction($id) {
        $topic = $this->getTopic($id);
        $pos = $topic->getPosition();
        $em = $this->getDoctrine()->getEntityManager();

        $prevTopic = $this->getPrevTopic($pos, $this->container->getParameter('redcode.faq.class'));

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

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function moveTopicDownAction($id) {
        $topic = $this->getTopic($id);
        $pos = $topic->getPosition();
        $em = $this->getDoctrine()->getEntityManager();

        $nextTopic = $this->getNextTopic($pos, $this->container->getParameter('redcode.faq.class'));

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

    /**
     * @param $position
     * @param $class
     * @return mixed
     */
    public function getPrevTopic($position, $class) {
        $qb= $this
            ->getDoctrine()
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

    /**
     * @param $position
     * @param $className
     * @return mixed
     */
    public function getNextTopic($position, $className) {
        $qb= $this
            ->getDoctrine()
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