<?php

namespace AppBundle\Controller;


use AppBundle\Form\Type\NotificationConfigType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{


    /**
     * @Route("/notifications", name="notifications")
     * @return Response
     */
    public function notificationsAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $notificationRepository = $em->getRepository('AppBundle:Notification');
        $patientRepository = $em->getRepository('AppBundle:Patient');

        $user = $this->getUser();
        
        $idNotif = $request->request->get('notifId'); //POST request in symfony
        if ($idNotif !== NULL) {
            
            /* Si on souhaite supprimer toutes les notifications */
            if ($idNotif == 0) {

                foreach ($notificationRepository->findByTarget($user) as $notification) {
                    $em->remove($notification);
                }
                
                $this->addFlash(
                    'notice',
                    'Les notifications ont été annulées avec succès !'
                );

            } else {

                $notification = $notificationRepository->findOneById($idNotif);

                if (null === $notification) {
                    throw new NotFoundHttpException("Cette notification n'existe pas dans la base de données.");
                }

                $em->remove($notification);
                $this->addFlash(
                    'notice',
                    'La notification a été annulé avec succès !'
                );

            }
        }

        $idNotif = $request->request->get('notifIdShare'); //POST request in symfony
        if($idNotif) {

            $notification = $notificationRepository->findOneById($idNotif);

            if (null === $notification) {
                    throw new NotFoundHttpException("Cette notification n'existe pas dans la base de données.");
            }

            $em->remove($notification);
            

            $this->addFlash(
                'notice',
                'La notification a été annulée avec succès !'
            );
        }

        $em->flush();

        $notifications = $notificationRepository->findByTarget($user);
        foreach ($notifications as $notif) {
            $patient = $patientRepository->findOneBy(['patientId' => $notif->getPatientId()]);
            $notif->setPatient($patient);
        }


        return $this->render('notifications/notificationPage.html.twig',[
            'notifications' => $notifications
        ]);

    }

    /**
     *
     * Cette page permet de configurer le type de notification que l'on souhaite recevoir
     *
     * @Route("/confignotifications", name="confignotifications")
     * @return Response
     */
    public function notificationConfigAction(Request $request)
    {

        // Cf. src/Services/Notifications.php
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:User');

        $user = $this->getUser();

        if($user->getNotifications()[0]==1){
            $message="Actuellement : Vous êtes notifiés à partir de ".$user->getNotifications()[1]." notification(s)";
        }elseif($user->getNotifications()[0]==2){
            $message="Actuellement : Vous êtes notifiés lorsqu'une notification provient de l'évaluateur ".$repository->findOneBy(['id' => $user->getNotifications()[1]])->getUsername();
        }else{
            $message="Actuellement : Vous êtes notifiés lorsqu'une notification date de plus de ".$user->getNotifications()[1]." jour(s)";
        }


        $userList = $repository->findall();
        unset($userList[array_search($user,$userList)]);

        $form = $this->createForm(NotificationConfigType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $typeNotif = $form["notificationType"]->getData();

            if( $typeNotif == 1){
                $user->setNotifications([
                    $form["notificationType"]->getData(),
                    $form["numberNotifications"]->getData(),
                ]);

            }else if( $typeNotif == 2){
                if(isset($_POST['userId'])) {
                    $evaluator = $repository->findOneById($_POST['userId']);

                    if (null === $evaluator) {
                        throw new NotFoundHttpException("Cet évaluateur n'existe pas dans la base de données.");
                    }

                    $user->setNotifications([
                        $form["notificationType"]->getData(),
                        $_POST['userId'],
                    ]);
                }
            }else if( $typeNotif == 3){
                $user->setNotifications([
                    $form["notificationType"]->getData(),
                    $form["dateNotifications"]->getData(),
                ]);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('notice','La procédure de notifications a été modifiée avec succès !');
            return $this->redirectToRoute('confignotifications');

        }

        return $this->render('notifications/notificationConfig.html.twig',[
            'form' => $form->createView(),
            'userlist' => $userList,
            'message' => $message,
        ]);


    }

}
