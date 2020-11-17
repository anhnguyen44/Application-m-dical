<?php

namespace AppBundle\Services;

use AppBundle\Form\Type\ContactFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\NotificationsController;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Constraints\DateTime;

class Notifications
{
    private $em;
    private $container;

    public function __construct($doctrine, Container $container)
    {
        $this->em = $doctrine->getManager(); ;
        $this->container = $container;
    }

    public function notify(array $info)
    {

        $notification = new Notification();
        $notification->setSource($info['source']);
        $notification->setTarget($info['target']);
        $notification->setPatientId($info['patient']);
        $notification->setType($info['type']);
        $notification->setDate(new \DateTime());

        $this->em->persist($notification);

    }

//    transition est un string pour changer le comportement d'alerte:
//      -  en fonction du nombre d’alerte : transition1(int nb_alerte)
//      -  en fonction d’un commentaire d’un évaluateur particulier : transition2(string nomEvaluateur)
//      -  en fonction de la date : si plus de 3 jours par exemple : transition3(date date)

    public function verifyNotification()
    {

        //Récupération de l'utilisateur si admin on a pas de notification
        $user = $this->container->get('security.token_storage')->getToken()->getUser();


        if ($user->getUsername() != 'admin') {

            //Récupération de la session pour les flash messages qui seront récupéres dans le index.twig
            $session = $this->container->get('session');

            //Récupération de l'id de l'utilisateur et de sa méthode de notification

            $repositoryNotif= $this->em->getRepository('AppBundle:Notification');

            /*
             * Alerte en fonction de chaque compte
             * 1 : En fonction d'un du nombre de notification
             * 2 : En fonction d'un évaluateur
             * 3 : En fonction d'une date
            */
            switch ($user->getNotifications()['0']) {
                case 1:

                    //Récupération des notifications dont nous sommes destinataires
                    $notificationList = $repositoryNotif->findByTarget($user);
                    $notifications = count($notificationList);
                    //Pour le moment : à la première notification => Alerte
                    if ($notifications >= $user->getNotifications()['1']) {
                        $session->getFlashBag()->add('notif', 'Vous avez '. $notifications .' notification(s) !');
                        return true;
                    }
                    return false;
                    break;

                case 2:

                    //Récupération des notifications
                    $notificationList = $repositoryNotif->findBy(['source' => $user->getNotifications()['1'], 'target' => $user]);

                    //Récupération de la session pour les flashmessages qui seront récupéres dans le index.twig
                    $session = $this->container->get('session');

                    $pers = $repositoryUser->findOnebyId($user->getNotifications()['1']);
                    if (!empty($notificationList)) {
                        $session->getFlashBag()->add('notif', 'Vous avez une nouvelle notification de la part de ' . $pers->getUsername() . ' !');
                        return true;
                    }
                    return false;
                    break;

                case 3:

                    date_default_timezone_set('Europe/Paris');
                    $timeNow = new \DateTime();

                    //Récupération des notifications dont nous sommes destinataires
                    $notificationList = $repositoryNotif->findByTarget($user);
                    $urgentNotif = array();


                    foreach ($notificationList as $key => $val) {
                        $interval = date_diff($val->getDate(), $timeNow);

                        //Si la différence est plus importante : on garde la notif
                        if ($interval->d >= $user->getNotifications()['1'] || $interval->m > 0 || $interval->y > 0) {
                            array_push($urgentNotif, $val);
                        }
                    }

                    if (!empty($urgentNotif)) {
                        $session->getFlashBag()->add('notif', 'Vous avez '. count($urgentNotif) .' notification(s) !');
                        return true;
                    }
                    return false;
                    break;
            }
        }
    }
}
