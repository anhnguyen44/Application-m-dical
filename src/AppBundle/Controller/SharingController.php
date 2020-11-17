<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ACL;
use AppBundle\Form\Type\ACLType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class SharingController extends Controller
{
    /**
     *
     * Cette fonction permet de partager le dossier d'un patient avec un utilisateur.
     * @Security("has_role('ROLE_MEDICAL')")
     * @Route("/sharepatient", name="sharepatient")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sharePatientAction(Request $request)
    {

        /*
         * Des flags utilisés pour afficher les bons éléments dans le twig.
         *
         */
        $already = 0;
        $success = 0;

        /*
         * Appel aux tables Patient, User, ACL et Speciality
         */
        $em = $this->getDoctrine()->getManager();
        $repositoryPatient = $em ->getRepository('AppBundle:Patient');
        $repositoryEvaluator = $em ->getRepository('AppBundle:User');
        $repositoryShared = $em->getRepository('AppBundle:ACL');
        $speRepository = $em->getRepository('AppBundle:Speciality');


        /*
         * Créer une liste avec tous les utilisateurs et retirer l'utilisateur actuel de cette liste
         * Pour éviter de partager un dossier avec lui même
         */
        $user = $this->getUser();
        $listEvaluator = $repositoryEvaluator->findAll();
        unset($listEvaluator[array_search($user,$listEvaluator)]);

        /*
         * Retirer l'utilisateur avec rôle SECRETARY de la liste.
         * Cet utilisateur a accès à tous les dossiers
         * Cette fonctionnalité n'est pas définie pour lui.
         *
         */
        $listSecretaire = $repositoryEvaluator->findBy(['speciality' => 2 ]);
        foreach ($listSecretaire as $sec){
            unset($listEvaluator[array_search($sec,$listEvaluator)]);
        }


        /*
         * Une liste avec les patients de l'utilisateur courant (Tous les dossiers non archivés)
         */
        $listPatient = $repositoryPatient->findBy(['owner' => $user, 'archived' => 0]);

        /*
         * Retirer les dossiers partagés avec tous
         */
        $listFile = $repositoryPatient->findBy(['partage' => 1 ]);
        foreach ($listFile as $publicFile){
            unset($listPatient[array_search($publicFile,$listPatient)]);
        }

        if(isset($_GET['evaluatorSelection']) and isset($_GET['patientSelection']) and isset($_GET['expire']))
		{

            $expire = $_GET['expire'];
			$listEvaluatorSelected = $_GET['evaluatorSelection'];
			$listPatientSelected = $_GET['patientSelection'];

			if ($listEvaluatorSelected[0] == "ALL") {
				$listEvaluatorSelected = null;
				foreach ($listEvaluator as $evaluator) {
					$listEvaluatorSelected[] = $evaluator->getId();
				}
			}
			if ($listPatientSelected[0] == "ALL") {
				$listPatientSelected = null;
				foreach ($listPatient as $patient)
					$listPatientSelected[] = $patient->getPatientId();
			}

			$form = $this->createForm(ACLType::class);
			$form->handleRequest($request);

			if($form->isSubmitted() && $form->isValid()) {

				foreach ($listPatientSelected as $patientID)
				{
					$patient = $repositoryPatient->findOneBy(array('patientId' => $patientID));

					if ($patient === null) {
						throw new NotFoundHttpException("Le patient n'existe pas dans la base de données.");
					}

					foreach ($listEvaluatorSelected as $evaluatorID)
					{
                        $already = 0; /* Remise à zéro du booléen already à chaque itération */
						$evaluator = $repositoryEvaluator->findOneBy(array('id' => $evaluatorID));

						if ($evaluator === null) {
							throw new NotFoundHttpException("L'évaluateur n'existe pas dans la base de données.");
						}

					   /*
						* Vérifier que le dossier n'a pas été déjà partagé
						*/
						$sharedList = $repositoryShared->findBy(["doc" => $user, "evaluator" => $evaluator]);
						$var = in_array(["idPatient" => $patient->getPatientId()], $sharedList);

						if($var){
							$already = 1;
							$message = 'Vous avez déjà partagé le dossier de '.$patient->getPrenom().' '.$patient->getNom().' avec '.$evaluator->getUsername().'.';
							$this->addFlash('alert', $message);
						}

						if (!$already)
						{
							$ACL = new ACL();
							/*
							 * Si la date d'expiration n'est pas précisée le dossier sera partagé de manière permanente.
							 */
							if($expire == 'false'){
								$ACL->setDate(null);
							}

                            $ACL->setDoc($user);
							$ACL->setEvaluator($evaluator);
							$ACL->setIdPatient($patient->getPatientId());


							/*
							 * Modifier et enregistrer les modifications
							 */
							$em->persist($ACL);

							/*
							 * Appel à la fonction notifyAction pour notifier les utilisateurs
							 */
							$info=array(
								'source' => $user,
								'target' => $evaluator,
								'patient' => $patient->getPatientId(),
								'type' => "share"
							);
							$NotificationService=$this->get('notification');
							$NotificationService->notify($info);
						}
					}
				}

				$em->flush();
				$success=1;
				$this->addFlash(
						'notice',
						'Le partage a été effectué avec succès !'
				);
			}

            return $this->render('sharing/sharePatient.html.twig',[
                'form' => $form->createView(),
                'already' => $already,
                'success' => $success,
                'expire' => $expire,
                'listPatient' => $listPatient,
                'listEvaluator' => $listEvaluator,
            ]);


        }else{

            $form = null;
            $expire = false;

            return $this->render('sharing/sharePatient.html.twig',[
                'form' => $form,
                'already' => $already,
                'success' => $success,
                'expire' => $expire,
                'listPatient' => $listPatient,
                'listEvaluator' => $listEvaluator,
            ]);
        }
    }

    /**
     *
     * Cette fonction permet d'afficher les dossiers que l'utilisateur a partagé
     *
     * @Security("has_role('ROLE_MEDICAL')")
     * @Route("/sharedpatient", name="sharedpatient")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sharedPatientAction(){

        $em = $this->getDoctrine()->getManager();
        $repositoryACL = $em->getRepository('AppBundle:ACL');
        $repositoryPatient = $em ->getRepository('AppBundle:Patient');
        $repositoryEvaluator = $em ->getRepository('AppBundle:User');
        $repositorySpe = $em->getRepository('AppBundle:Speciality');


        $user = $this->getUser();

        $listShared = $repositoryACL->findByDoc($user);
        $list = array();
        
        // Les dossiers publiques
        $listFile = $repositoryPatient->findBy(['owner' => $user, 'partage' => 1, 'archived' => 0]);

        foreach($listFile as $item){

            $evaluatorSpeciality = $user->getSpeciality();
            array_push($list,[
                $item->getPatientId(),$item->getNom(),$item->getPrenom(),$user->getUsername(),$evaluatorSpeciality,null,null
            ]);
        }

        foreach ($listShared as $item)
        {


            /*
             * Informations de l'utilisateurs.
             */
            $patientId = $item->getIdPatient();

            $patient = $repositoryPatient->findOneBy([
                'patientId' => $patientId
            ]);

            /*
             * N'afficher que les dossiers non archivés.
             */
            if($patient->isArchived() == false){

                $patientNom = $patient->getNom();
                $patientPrenom = $patient->getPrenom();

                // Informations concernant l'évaluateur
                $evaluator = $item->getEvaluator();
                $evaluatorUsername = $evaluator->getUsername();
                $evaluatorSpeciality = $evaluator->getSpeciality();

                $date = $item->getDate();
                $idACL = $item->getId();

                /*
                 * Tous les informations qui concernent le partage sans stoquées dans un tableau/list
                 * qui sera afficher dans le twig.
                 */
                array_push($list,[
                    $patientId,$patientNom,$patientPrenom,$evaluatorUsername,$evaluatorSpeciality,$date,$idACL
                ]);
            }
        }

        return $this->render('sharing/sharedPatient.html.twig',[
            'listShared' => $list,
        ]);

    }

    /**
     *
     * Cette fonction permet de visualiser les fichiers partager avec l'utilisateur courant.
     *
     * @Route("/sharedwithme", name="sharedwithme")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sharedWithMeAction()
    {
        /*
         * La date est utilisée pour vérifier si le partage d'un dossier est expiré
         */
        date_default_timezone_set('Europe/Paris');
        $todayDate = new \DateTime();

        $repositoryACL = $this->getDoctrine()->getManager()->getRepository('AppBundle:ACL');
        $repositoryPatient = $this->getDoctrine()->getManager()->getRepository('AppBundle:Patient');
        $repositoryDoc = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');

        $user = $this->getUser();

        /*
         * La liste des dossiers partagés avec cet utilisateur
         * ou
         * publiques et non archivés
         */
        $listShared = $repositoryACL->findByEvaluator($user);
        $listPatientShared = $repositoryPatient->findBy(['partage' => true, 'archived' => false,]);

        
        // Récupérer les informations nécessaires.
        $list = array();
        foreach ($listPatientShared as $patient){
            $doc = $patient->getOwner();
            array_push($list, [$patient->getPatientId(), $patient->getNom(), $patient->getPrenom(), $doc->getUsername(), null]);
        }

        foreach ($listShared as $item)
        {
            $expirationDate = $item->getDate();
            //Montrer le fichier si la date est bonne
            if($todayDate < $expirationDate || $expirationDate == null)
            {
                //Information concernant le patient
                $patientId = $item->getIdPatient();
                $patient = $repositoryPatient->findOneByPatientId($patientId);

                if(!$patient->isArchived()){

                    $patientNom = $patient->getNom();
                    $patientPrenom = $patient->getPrenom();

                    $doc = $item->getDoc();
                    $docUsername = $doc->getUsername();
                    $date = $item->getDate();

                    
                    // Stoquer les informations dans un tableau utilisé en suite dant le twig.
                    array_push($list, [$patientId, $patientNom, $patientPrenom, $docUsername, $date]);
                }
            }

        }

        return $this->render('sharing/sharedWithMe.html.twig',[
            'listShared' => $list,
        ]);


    }

    /**
     *
     * Cette fonction permet d'annuler le partage d'un dossier
     *
     * @Security("has_role('ROLE_MEDICAL')")
     * @Route("/cancelsharing", name="cancelsharing")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancelSharingAction(Request $request)
    {

        $em = $this->getDoctrine() ->getManager();
        $repositoryACL = $em->getRepository('AppBundle:ACL');
        $repositoryUser = $em->getRepository('AppBundle:User');

        // La suppression d'un partage s'effectue en utilisant son ID
        $id = $request->get('idACL');
        if($id) {

            $itemACL = $repositoryACL->findOneById($id);
            if (null === $itemACL) {
                throw new NotFoundHttpException("L'évaluateur n'existe pas dans la base de données.");
            }

            $user = $this->getUser();

            // Envoyer une notification pour avertir l'évaluateur de l'annulation du partage
            $info = array(
                'source' => $user,
                'target' => $itemACL->getEvaluator(),
                'patient' => $itemACL->getIdPatient(),
                'type' => "cancelShare"
            );

            $NotificationService = $this->get('notification');
            $NotificationService->notify($info);

            $em->remove($itemACL);
            $em->flush();

            $this->addFlash('notice', "Le partage a été annulé avec succès !");

        } else {

            $this->addFlash('alert', "Le partage n'a pas été annulé !");
        }

        return $this->redirectToRoute("sharedpatient");
    }

}
