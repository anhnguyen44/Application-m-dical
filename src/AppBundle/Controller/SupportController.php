<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\ContactFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\NotificationsController;



class SupportController extends Controller
{
    /**
     *
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {

        /*
         * Appel au service Notification qui va avertir ou non l'utilisateur connecté
         */
        $NotificationService=$this->get('notification');
        $response=$NotificationService->verifyNotification();

        return $this->render('support/index.html.twig', [
            'response' => $response
        ]);
    }

}
