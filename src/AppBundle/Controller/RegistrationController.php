<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Speciality;
use AppBundle\Entity\User;
use AppBundle\Form\Type\RegisterType;
use AppBundle\Form\Type\SecurityType;
use AppBundle\Form\Type\SpecialityType;
use AppBundle\Form\Type\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/*
 * Ce controlleur contient les actions effectuer par l'administrateur.
 * Tout ce qui concerne la gestion des comptes des évaluateurs (médecins, secrétaires, paramédicaux)
 */
class RegistrationController extends Controller
{
    /**
     *
     * Utilisée pour créer des nouvels utilisateurs.
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/register",name="admin")
     * @return mixed
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $encoder)
    {


        // Appel aux tables User et Speciality utilisées dans cette fonction.
        $em = $this->getDoctrine() ->getManager();
        $repository = $em->getRepository('AppBundle:User');
        $speRepository = $em->getRepository('AppBundle:Speciality');

        /*
         * La création et l'appel du formulaire de type RegisterType (cf. src/Form/Type/RegisterType.php
         * Ce formulaire interéagit avec un Objet de type User (permet de remplir un champ de la table user).
         */
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form-> handleRequest($request);

        // La création d'un mot de passe utilisateur pseudo-aléatoire.
        $plainPassword = $this->generateNewPasswordAction();

        // Le traitement commence si les données du formulaire sont cohérents/valides.
        if ($form->isSubmitted() && $form->isValid()){
            /*
             * Le mot de passe n'est pas stoqué en clair, il est encodé/chiffrer d'abord.
             * L'algorithme utilisé est précisé dans app/config/security.yml
             *
             * security:
             *       encoders:
             *          AppBundle\Entity\User:
             *              algorithm: bcrypt
             *
             */

            $password = $this->get('security.password_encoder')->encodePassword($user, $plainPassword);
            $user->setPassword($password);
            $user->setRoles($user->getSpeciality()->getRole());

            // Un traitement est effectué en fonction du type de notifications choisi
            $typeNotif = $form["notificationType"]->getData();

            if( $typeNotif == 1){
                $user->setNotifications([
                    $form["notificationType"]->getData(),
                    $form["numberNotifications"]->getData(),
                ]);

            }else if( $typeNotif == 2){
                if(isset($_POST['userId'])) {
                    $evaluator = $repository->findOneBy(['id' => $_POST['userId']]);

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

            // Enregistrer ces modifications.
            $em->persist($user);
            $em->flush();
            return $this->render('admin/register.html.twig',[
                'form' => NULL,
                'user'=> $user,
                'password' => $plainPassword
            ]);

        }

        return $this->render('admin/register.html.twig',[
            'form' => $form->createView(),
            'userlist' => $repository->findAll(),
        ]);
    }

    /**
     * Cette fonction est utilisée pour générer un mot de passe pseudo aléatoire
     * Appelée dans registerAction.
     * //
     */
    public function generateNewPasswordAction()
    {
        $characts = 'abcdefghijklmnopqrstuvwxyz';
        $characts .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characts .= '1234567890';
        $chaine = '';

        for($i=0;$i < 8 ;$i++)
        {
            $chaine .= $characts[ rand() % strlen($characts) ];
        }
        return $chaine;
    }

    /**
     *
     * Cette fonction est utilisé pour effacer un utilisateur
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/deleteuser",name="deleteuser")
     * @return mixed
     */
    public function deleteUserAction(Request $request)
    {


        $em = $this->getDoctrine() ->getManager();
        $repository = $em->getRepository('AppBundle:User');

        /*
         * La suppression s'effectue en utilisant l'id de l'utilisateur.
         * Envoyé par la méthode POST.
         */
        $id = $request->get('id');
        if($id){

            $user = $repository->findOneById($id);
            if (null === $user) {
                throw new NotFoundHttpException("Cet utilisateur n'existe pas dans la base de données.");
            }

            $repositoryPatient = $em->getRepository('AppBundle:Patient');
            if (!is_null($repositoryPatient->findOneByOwner($user))){

                $this->addFlash('alert', "Veuillez transférer les dossiers des patients de cet utilisateur avant de le supprimer !");
                return $this->render("admin/viewUsers.html.twig", array(
                    'users' => $repository->findAll(),
                    'userToChange' => NULL
                ));
            
            }

            // Suppression des notifications qui concerne ce medecin (source et target)            
            $repositoryNotif = $em->getRepository('AppBundle:Notification');
       
            $notif = $repositoryNotif->findBySource($id);
            foreach ($notif as $value) {
                $em->remove($value);
            }
            
            $notif = $repositoryNotif->findByTarget($id);
            foreach ($notif as $value) {
                $em->remove($value);
            }

            
            // Suppression des ACL qui concerne ce medecin (en source ou target)
            $repositoryACL = $em->getRepository('AppBundle:ACL');
            $acl = $repositoryACL->findByDoc($id);
            foreach ($acl as $value) {
                $em->remove($value);
            }

            $acl = $repositoryACL->findByEvaluator($id);
            foreach ($acl as $value) {
                $em->remove($value);
            }

            // Effacer l'utilisateur et enregistrer les modifications
            $em->remove($user);
            $em->flush();

            $this->addFlash('notice', "L'utilisateur a été supprimé avec succès !");

        }
        
        return $this->render("admin/viewUsers.html.twig", array(
            'users' => $repository->findAll(),
            'userToChange' => NULL
        ));
    }

    /**
     * Cette fonction permet à l'admin de modifier le mot de passe d'un utilisateur.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/edituser",name="edituser")
     * @return mixed
     */
    public function changePasswordAction(Request $request)
    {
        /*
         * Appel aux tables User et Speciality avec la liste des utilisateur et des spécialités.
         */
        $em = $this->getDoctrine()->getManager() ;
        $repository = $em->getRepository('AppBundle:User');
        $users = $repository->findAll();

        
        /*
         * Le choix de l'utilisateur s'effectue en utilisant son ID
         * Cet ID est récupéré en utilisant la méthode GET.
         */
        $userId = $request->get('id');
        if ($userId) {

            $user = $repository->findOneById($userId);
            if (null === $user) {
                throw new NotFoundHttpException("Cet utilisateur n'existe pas dans la base de données.");
            }
            
            // Création et appel au formulaire de changement de mot de passe (SecurityType, cf src/Form/Type/SecurityType
            $form= $this->createForm(SecurityType::class, $user);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid())  {

                /*
                * Le mot de passe n'est pas stocké en clair, il est encodé/chiffré d'abord.
                * L'algorithme utilisé est précisé dans app/config/security.yml
                *
                * security:
                *       encoders:
                *          AppBundle\Entity\User:
                *              algorithm: bcrypt
                */
                $password = $this
                    ->get('security.password_encoder')
                    ->encodePassword(
                        $user,
                        $user->getPlainPassword()
                    );

                $user->setPassword($password);
                $em->merge($user);
                $em->flush();
                $this->addFlash('notice', "Le mot de passe a été modifié avec succès !");
                return $this->redirectToRoute('edituser');
            }

            
            return $this->render("admin/editUser.html.twig", array(
                'form' => $form->createView(),
            ));
        

        }

        /*
                * Si aucun ID n'est envoyé (avec la méthode GET, la liste des utilisateur s'affichera
                * pour permettre à l'administreur de choisir l'utilisateur à supprimé.
                */

        return $this->render("admin/editUser.html.twig", [
            'form' => NULL,
            'users' => $users,
        ]);
    }



    /**
     * Cette fonction permer d'afficher les utilisateur avec la possibilité de modifier leurs données ou de les supprimer
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/viewusers",name="view_users")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function viewUsersAction(Request $request)
    {
        
        // Appel aux tables User et Speciality avec la liste des utilisateur.
        $em = $this->getDoctrine()->getManager() ;
        $repository = $em->getRepository('AppBundle:User');
        $speRepository = $em->getRepository('AppBundle:Speciality');

        $users = $repository->findAll();

        $id = $request->get('id');
        if($id) {

            /*
             * L'administrateri choisit le compte utilisateur qu'il souhaite modifié.
             * Le choix est envoyé en utilisant la méthode GET
             */
            $user = $repository->findOneById($id);
            if (null === $user) {
                throw new NotFoundHttpException("Cet utilisateur n'existe pas dans la base de données.");
            }

            /*
            * Les champs NotificationType et NotificationValue n'existe pas vraiment dans la bdd
            * Ils sont utilisé de manière temporaire dans doctrine.
            * Pour afficher les bonnes valeurs au chargement de la page, on doit les remplir en utilisant
            * la valeur stoquée réellement dans la bdd (Notifications)
            */
            $user->setNotificationType(array_values($user->getNotifications())[0]);
            $user->setNotificationValue(array_values($user->getNotifications())[1]);


            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid())  {

                // La modification de la spécialité génère la modification du rôle
                $speciality = $user->getSpeciality();
                $user->setRoles($speciality->getRole());

                // Changer les notifications s'ils ont été changer.
                $user->setNotifications([
                    $form["notificationType"]->getData(),
                    $form["notificationValue"]->getData(),
                ]);


                // Modifier puis enregistrer les modifications.
                $em->merge($user);
                $em->flush();

                $this->addFlash('notice', "Le compte a été modifié avec succès !");
                return $this->redirectToRoute('view_users');  
            }


            return $this->render("admin/viewUsers.html.twig", array(
                'form' => $form->createView(),
                'users' => $users,
                'userToChange' => $user
            ));

        }
     
        return $this->render("admin/viewUsers.html.twig", array(
            'users' => $users,
            'userToChange' => NULL
        ));

    }

    /**
     * Création des spécialités
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/managespeciality",name="managespeciality")
     */
    public function manageSpecialityAction(Request $request){

        /*
         * Un flag utilisé pour afficher les bons éléments dans le twig.
         *
         */
        $info = 0;

        /*
         * Appel à la table Speciality
         */
        $speciality = new Speciality();

        /*
         * La création et l'appel du formulaire qui permet de gérer les spécialités
         * Cf. src/Form/Type/SpecialityType.php
         */
        $form= $this->createForm(SpecialityType::class, $speciality);
        $form->handleRequest($request);



        if($form->isSubmitted() && $form->isValid()){

            // Pour rendre le choix du rôle automatique, on peut utiliser le code suivant :

            /*

            if($form['occupation']->getData() == 'paramedical'){
                $speciality->setRole('ROLE_PARAMEDICAL');

            } else if ($form['occupation']->getData() == 'medical'){

                if($form['speciality']->getData() == 'Secrétaire'){
                    $speciality->setRole('ROLE_SECRETARY');

                }else{
                    $speciality->setRole('ROLE_MEDICAL');
                }
            }

            */

            /*
             * Modifier et Enregistrer les modifications
             */
            $em = $this->getDoctrine()->getManager() ;
            $em->persist($speciality);
            $em->flush();

            /*
             * Le message qui sera afficher à la fin du traitement
             */
            $this->addFlash(
                'notice',
                'La création de la spécialité a été effectué avec succès !'
            );

            $info = 1;

        }

        return $this->render("admin/addSpeciality.html.twig", [
            'form' => $form->createView(),
            'info' => $info,
        ]);
    }

    /**
     * Afficher, renommer et (supprimer) les spéacialités
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/viewspeciality",name="viewspeciality")
     */
    public function viewSpecialityAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Speciality');

        $id = $request->query->get('rename');
        if (NULL === $id) {

            // if(isset($_POST['deleteId'])){
            //     $deleteSpe = $repository->findOneBy(['id' => $_POST['deleteId']]);
    
            //     if (null === $deleteSpe) {
            //         throw new NotFoundHttpException("Cette spécialité n'existe pas dans la base de données.");
            //     }
            //     $em->remove($deleteSpe);
            //     $em->flush();
            //     $this->addFlash('notice', 'La suppression a été effectué avec succès !');
    
            // }
    
            return $this->render("admin/viewSpeciality.html.twig", array(
                'specialities' => $repository->findAll(),
            ));
        
        } else { 
        

            $speciality = $repository->findOneById($id);
            if (NULL === $speciality) {
                throw new NotFoundHttpException("Cette spécialité n'existe pas dans la base de données.");
            } 
            
            
            $form= $this->createForm(SpecialityType::class, $speciality);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){

                $em->persist($speciality);
                $em->flush();

                $this->addFlash('notice', 'La spécialité a été renommé avec succès !');
                return $this->redirectToRoute('viewspeciality');

            }

            return $this->render("admin/editSpeciality.html.twig", array(
                'form' => $form->createView(),
            ));
        }

    }


}
