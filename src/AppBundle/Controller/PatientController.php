<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Patient;
use AppBundle\Services\FileUploader;
use AppBundle\Form\Type\PatientFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use function Sodium\randombytes_buf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;



function contains($haystack, $needle, $caseSensitive = false) {
    return $caseSensitive ?
        (strpos($haystack, $needle) === FALSE ? FALSE : TRUE):
        (stripos($haystack, $needle) === FALSE ? FALSE : TRUE);
}

function contains_none($haystack, $needle_list, $caseSensitive=false) {
    for ($i=0; $i < count($needle_list); $i++) { 
        if (contains($haystack, $needle_list[$i], $caseSensitive)) {
            return false;
        }
    }

    return true;
}

/**
 * retourne vrai si le la données envoyé est un fichier.
 * Cela veut dire que file_description décrit un fichier et que $file
 * contient un fichier.
 * $test_type permet d'accepter le fichier si il est d'un type
 * "image", "video", "comment" ou "file"
 */
function isDataFileType($file_description, $file, $test_type) {
    return contains($file_description, $test_type) && contains_none($file_description, [
        "perm", 
        "creation",
        "pro_id",
        "validation",
        "cat",
        "archive",
        "original"
    ]) && $file !== "" && !is_file($file);
} 

function extractOneExtraData($type, $extraData, $number, $time, $userId) {
    $validation = ((int)$extraData['data' . $type . 'perm' . $number] == 1) ? 1 : 0;
    if (!strcmp($type, "comment")) {
        return array(
            "comm" => $extraData['datacomment' . $number],
            "perm" => (int)$extraData['datacommentperm' . $number],
            "created_at" => $time,
            "pro_id" => $userId,
            "validation" => $validation,
            "categorie" => (int)$extraData['datacommentcat' . $number]
        );
    } else {
        return array(
            "path" => $extraData['data' . $type . $number],
            "name" => $extraData['data' . $type . 'originalName' . $number],
            "perm" => (int)$extraData['data' . $type . 'perm' . $number],
            "created_at" => $time,
            "pro_id" => $userId,
            "validation" => $validation,
            "categorie" => $extraData['data' . $type . 'cat' . $number]
        );
    }
}

function extractExtraDatas($extraData, $type, $amount, $time, $patient, $user) {
    $datas = [];

    for ($i = 0, $j = 0; $i < $amount; $i++, $j++) {
        if ($extraData['data' . $type . $j] === "") {
            $i--;
        } else {
            $data = extractOneExtraData($type, $extraData, $j, $time, $user->getId());
            array_push($datas, $data);

            /*
             * Si la donnée est public, envoi d'une notification au médecin qui 
             * gère le dossier pour le valider/archiver
             */
            if ($data['perm'] == 0) {
                $NotificationService = $this->get('notification');
                $NotificationService->notify([
                    'source' => $user,
                    'target' => $patient->getOwner(),
                    'patient' => $patient->getPatientId(),
                    'type' => "add" . ucfirst($type)
                ]);
            }
        }
    }

    return $datas;
}


function hasExtraDataChanges($type, $extraData) {
    $haschanged = false;
    foreach ($extraData[$type] as $key => $val) {
        if ($haschanged || !isset($val['validation'])) {
            // on s'arrete dès qu'on a une data qui ne possède pas de champs validation
            // QUESTION: est-ce qu'il est possible que cela arrive ?
            // Pourrait s'implifier cette fonction
            return $haschanged;
        }

        if ($val['validation'] == 0 || $val['validation'] == 2) {
            $haschanged = true;
        }
    }

    return $haschanged;
}

function extraDataHasNoChanges($patient) {
    $extraData = $patient->getData();
    return (
        !hasExtraDataChanges("comments", $extraData) &&
        !hasExtraDataChanges("images", $extraData) &&
        !hasExtraDataChanges("videos", $extraData) &&
        !hasExtraDataChanges("files", $extraData)
    );
}


class PatientController extends Controller
{
    /* 
        le nom activatePermCheck sera surement à changer quand une meilleure
        compréhension de la modélisation aura été effectuée
    */
    function getEvaluatorNamesByExtraDataType($patient, $type, $activatePermCheck=false) {
        $repositoryUser = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $evaluators = [];

        foreach ($patient->getData()[$type] as $key => $val) {
            // activatePermCheck permet de schinter le test $val['perm']
            if (!$activatePermCheck || $val['perm'] === 0) {
                $evaluators[$val["pro_id"]] = $repositoryUser->findById($val["pro_id"])[0]->getUsername();
            }
        }
        return $evaluators;
    }

    /**
     * Retourne la liste des évaluateurs ayant fait des commentaires, posté des
     * images / vidéos / fichiers sur un patient donné
     */
    function getEvaluatorNames($patient, $activatePermCheck=false) {
        return [
            "comments" => $this->getEvaluatorNamesByExtraDataType($patient, "comments", $activatePermCheck),
            "images" => $this->getEvaluatorNamesByExtraDataType($patient, "images", $activatePermCheck),
            "videos" => $this->getEvaluatorNamesByExtraDataType($patient, "videos", $activatePermCheck),
            "files" => $this->getEvaluatorNamesByExtraDataType($patient, "files", $activatePermCheck)
        ];
    }

    public function generateIdAction()
    {
        $characts = 'abcdefghijklmnopqrstuvwxyz';
        $characts .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characts .= '1234567890';
        $chaine = '';
        $isUniqueId = false; //Variable qui va être à false si l'id est déjà utilisé par un patient, true si l'id est libre et n'est pas utilisée
        $em = $this->getDoctrine()->getManager();
        $repositoryPatient = $em->getRepository('AppBundle:Patient'); //On récupère la liste des patients

        while ($isUniqueId == false) {
            for ($i = 0; $i < 8; $i++) {
                $chaine .= $characts[rand() % strlen($characts)];
            }
            $duplicatedId = $repositoryPatient->findOneBy( array ('patientId' => $chaine ) ); //Si il existe un patient avec cet id, alors on reboucle sinon on sort
            if (is_null($duplicatedId)){
                $isUniqueId = true;
            }
        }
        return $chaine;
    }


    /**
     *
     * This action allows to add a patient
     * It is used by everyone with role MEDICAL or SECRETARY
     * and it is associated with the patient/addPatient.html.twig view
     *
     * @Route("/addpatient",name="addpatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function addPatientAction(Request $request)
    {

        date_default_timezone_set('Europe/Paris');
        $time = (new \DateTime())->format('d-m-Y H:i:s');

        /*
         * Calling the table User in order to choose the right Evaluator (médecin soignant)
         * If the user is a doctor (ROLE_MEDICAL) he we will be automatically chosen as an Evaluator (médecin soignant)
         * Otherwise if the user has a secretary Role he shall choose the doctor with whom the patient will be associated with.
         * Choosing an evaluator means that the chosen one will own the record.
         * A secretary creating and associating a record to a doctor is equivalent to a doctor creating the record.
         */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:User');

        $user = $this->getUser();

        
        // Liste contenant tous les utilisateurs (médecins, paramédicaux, secrétaires) sauf l'utilisateur courant
        $listPro = $repository->findByRoles('ROLE_MEDICAL');
        if (in_array($user, $listPro)) {
            unset($listPro[array_search($user, $listPro)]);
        }

        /*
         * Creating a new patient object and a new PatientFormType form with extraData allowed.
         * PatientFormType is defined in src/Form/Type
         * Extradata is used in order to allow adding data (comments, pictures, videos...) dynamically.
         */
        $patient = new Patient();
        $form = $this->createForm(PatientFormType::class, $patient, ["allow_extra_fields" => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /*
             * The patient Id field must be filled with the "real" patient ID
             * Since in this particular case (@IEM) the patient ID is not defined
             * The patient ID is filled with : initial + a random value.
             *
             */
            $nom = $form["nom"]->getData();
            $prenom = $form["prenom"]->getData();
            $randomID = $this->generateIdAction();
            $patient->setPatientId($nom[0] . $prenom[0] . '_' . $randomID);

            /*
             * Setting archived value into false
             * The record once created is not archived.
             */
            $patient->setArchived(false);

            // Les extraData du formulaire, contiennent les images/vidéos/fichiers à uploader
            $extraData = $this->get('file.uploader')->upload($form->getExtraData());
            $nbr_extra_datas = $this->countExtraDatas($extraData);

            /*
             * Si l'utilisateur courant est la secrétaire, on affecte le dossier patient à un médecin en particulier
             * Si l'utilisateur courant est un médecin, il s'approprie le dossier du patient
             */
            if ($user->getRoles()[0] == 'ROLE_MEDICAL') {
                $patient->setOwner($user);
            } else if ($user->getRoles()[0] == 'ROLE_SECRETARY') {
                $patient->setOwner($repository->findOneById($extraData['proId']));
            }

            /*
            * Initialisation du tableau contenant toutes les données relatives à un patient
            */
            $data = [
                "comments" => extractExtraDatas($extraData, "comment", $nbr_extra_datas["comments"], $time, $patient, $user),
                "images" => extractExtraDatas($extraData, "image", $nbr_extra_datas["images"], $time, $patient, $user),
                "videos" => extractExtraDatas($extraData, "video", $nbr_extra_datas["videos"], $time, $patient, $user),
                "files" => extractExtraDatas($extraData, "file", $nbr_extra_datas["files"], $time, $patient, $user)
            ];

            $patient->setData($data);

            $em = $this->getDoctrine()->getManager();
            $em->persist($patient);
            $em->flush();
            $this->addFlash('notice', "Le dossier a été ajouté avec succès !");
            return $this->render("patient/addPatient.html.twig", [
                'form' => NULL,
            ]);
        }

        return $this->render("patient/addPatient.html.twig", [
            'form' => $form->createView(),
            'listPro' => $listPro,
        ]);
    }





	public function countExtraDatas($extraData)
	{
		/*
        * Calcul du nombre de commentaires/images/vidéos/fichiers insérés dans le formulaire
        */
        $nbr_extra_datas = [
            "comments" => 0,
            "images" => 0,
            "videos" => 0,
            "files" => 0
        ];

        foreach ($extraData as $key => $val) {
            if (isDataFileType($key, $val, "comment")) {
                $nbr_extra_datas["comments"]++;
            } else if (isDataFileType($key, $val, "image")) {
                $nbr_extra_datas["images"]++;
            } else if (isDataFileType($key, $val, "video") ) {
                $nbr_extra_datas["videos"]++;
            } else if (isDataFileType($key, $val, "file")) {
                $nbr_extra_datas["files"]++;
            }
        }

        return $nbr_extra_datas;
	}

	/*
    * Parcours et ajout de données (commentaires, images, videos ou fichiers) au dossier
    */
    public function addData(&$extraData, &$validation, &$data, $nbrData, $DataType, $DataPerm, $DataCat, $typeOperation, $PersistantType, $DataName, $edit, $time, $user, $patient) {
        
        for ($i = 0, $j = 0; $i < $nbrData; $i++, $j++) {
            if ($extraData[$DataType . $j] === "") {
                $i--;
            }
            else {
                if (($extraData[$DataType . $j] !== null && ($PersistantType == 'comments')) || ($extraData[$DataType . $j] !== null && !is_file($extraData[$DataType . $j]))) {
                    
                    $validation = ((int)$extraData[$DataPerm . $j] == 1) ? 1 : 0;
                    if ($edit == 1) {
                        $validation = 1; // Valider et partager
                    }

                    /**************************************************************************************************************************************/

                    $role = $this->getUser()->getRoles()[0];
                    $em = $this->getDoctrine()->getManager();
                    $repositoryACL = $em->getRepository('AppBundle:ACL'); /* Permet de récupèrer la table ACL */
                    $repositoryUser = $em->getRepository('AppBundle:User');

                    /* Si l'utilisateur est un médecin */
                    if ($role == 'ROLE_MEDICAL' || $role == 'ROLE_SECRETARY') {
                        if ($extraData[$DataPerm . $j] == 0 && ($validation == 0 || $validation == 1)) { /* Si la ressource est publique ET la ressource est nouvelle ou bien deja partagée, le médecin ajoute une nouvelle donnée publque */
                        
                            $acls = $repositoryACL->findById_patient($patient->getPatientId()); /* Permet de récupérer les tuples de la table ACL dont l'Id du patient est $patient->getPatientId() */
                            $NotificationService = $this->get('notification');

                            /* Création de l'objet notification */
                            $info = ['source' => $user,
                                     'target' => null,
                                     'patient' => $patient->getPatientId(),
                                     'type' => 'addInfos'];

                            if ($role == 'ROLE_SECRETARY') {
                                $info['target'] = $patient->getOwner();
                                $NotificationService->notify($info);
                            }
                            /*Envoie d'une notification au médecin si c'est la secretaire*/

                            $info['target'] = $patient->getOwner();
                            $NotificationService->notify($info);

                            
                            foreach($acls as $acl) {
                                $info['target'] = $acl->getEvaluator();
                                $NotificationService->notify($info);
                            }

                            $validation = 1; /* Valider et partager */
                        }
                    }

                    /**************************************************************************************************************************************/

                    if ($PersistantType == 'comments') {
                        $DataRegister = ["comm" => $extraData[$DataType . $j], ];
                    } else {
                        $DataRegister = ["path" => $extraData[$DataType . $j], "name" => $extraData[$DataName . $j], ];
                    }

                    $DataRegister = $DataRegister + ["perm" => (int)$extraData[$DataPerm . $j], "created_at" => $time, "pro_id" => $user->getId(), "validation" => $validation, "categorie" => (int)$extraData[$DataCat . $j]];
                    if ($PersistantType == 'comments') {
                        $dataReg = $DataRegister["perm"];
                        echo "datacommentperm, DataRegister[DataPerm . j] = $DataPerm, $dataReg";
                    }

                    if ($DataRegister['perm'] == 0 && $edit == 0) {
                        $info = [
                            'source' => $user,
                            'target' => $patient->getOwner(),
                            'patient' => $patient->getPatientId(),
                            'type' => $typeOperation
                        ];
                        $NotificationService = $this->get('notification');
                        $NotificationService->notify($info);
                    }
                }

                if (isset($DataRegister)) {
                    array_push($data[$PersistantType], $DataRegister);
                }
            }
        }
    }




    
	/**
     * @Route("/editpatient",name="editpatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function editPatientAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Patient');
        $repositoryUser = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $repositorySpec = $this->getDoctrine()->getManager()->getRepository('AppBundle:Speciality');
        $user = $this->getUser();

        $patientId = $request->get('patientId');
        if (null === $patientId) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ces données");
            return $this->redirectToRoute('viewpatient');
        }

        $patient = $repository->findOneBy(array('patientId' => $patientId));
        if (null === $patient) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ces données");
            return $this->redirectToRoute('viewpatient');
        }
            
        $form = $this->createForm(PatientFormType::class, $patient);
        $form->handleRequest($request);

        //Savoir si on est propriétaire du dossier (si oui, permettre la modification)
        $edit = ($user == $patient->getOwner() or $user->getRoles()[0] == 'ROLE_SECRETARY');
        $evaluators = $this->getEvaluatorNames($patient);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->merge($patient);
            $em->flush();
            $this->addFlash('notice', "Le dossier a été modifié avec succès !");
            return $this->redirectToRoute('viewpatient', array('patientId' => $patientId));
        }

        return $this->render("patient/editPatient.html.twig", array(
            'edit_form' => $form->createView(),
            'evaluators' => $evaluators,
            'patient' => $patient,
            'user' => $user,
            'edit' => $edit,
        ));
    }





    /**
     * @Route("/validatepatient",name="validatepatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function validatePatientAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Patient');
        $repositoryUser = $em->getRepository('AppBundle:User');
        $repositoryACL = $em->getRepository('AppBundle:ACL');
        $repositoryNotif = $em->getRepository('AppBundle:Notification');

        $user = $this->getUser();

        $listPatient = array();
        if(in_array('ROLE_MEDICAL', $user->getRoles())) {
            $listPatient = $repository->findBy(['owner' => $user, 'archived' => false]);
        
        } else {

            foreach($repositoryACL->findByEvaluator($user) as $item){
                $patient = $repository->findOneByPatientId($item->getIdPatient());
                if(!$patient->isArchived()) {
                    array_push($listPatient, $patient);
                }
            }
            
        }

        // Boucle pour ne récupérer que les patients dont des informations sur le dossier médical sont à valider
        foreach ($listPatient as $patientNum => $patient) {
            // si aucun changements on été détecté dans les boucles on supprime le patient de la liste
            if (extraDataHasNoChanges($patient)) {
                unset($listPatient[$patientNum]);
            }
        }

        $patientId = $request->get('patientId');
        if (null === $patientId) {
            return $this->render("patient/validatePatient.html.twig", array(
                'form' => NULL,
                'listPatient' => $listPatient,
            ));
        }

        $patient = $repository->findOneByPatientId($patientId);
        if (null === $patient) {
            $this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
            return $this->redirectToRoute('validatepatient');
        }
        
        // Liste reliant les ids des évaluateurs qui ont inséré une donnée dans le dossier médical avec leur nom
        $evaluators = $this->getEvaluatorNames($patient);
        $form = $this->createForm(PatientFormType::class, $patient, array("allow_extra_fields" => true));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
 
            $data = $patient->getData();
            $acls = $repositoryACL->findById_patient($patient->getPatientId()); // Permet de récupérer les tuples de la table ACL dont l'Id du patient est $patient->getPatientId()

            /* création de l'objet notification */
            $info = [
                'source' => $user,
                'target' => null,
                'patient' => $patient->getPatientId(),
                'type' => "addInfos" /* Création d'un nouveau type de notifications : addInfos */
            ];
            $NotificationService = $this->get('notification');

            $accepted_pro = [];

            // Mise à jour du champ validation
            foreach ($form->getExtraData() as $key => $val) {
                if($val != 5) {
                    $ret = sscanf($key, "data %s validation %d");
                    $data[$ret[0]][$ret[1]]['validation'] = (int)$val;

                    $info['source'] = $repositoryUser->findOneBy(['id' => $data[$ret[0]][$ret[1]]['pro_id']]);
                    if (!in_array($info['source']->getId(), $accepted_pro)){
                        $accepted_pro[] = $info['source']->getId();

                        foreach($acls as $acl) {
                            $info['target'] = $acl->getEvaluator();
                            $NotificationService->notify($info);
                        }
                    }
                }
            }

            foreach (array_reverse($form->getExtraData()) as $key => $val) {

                if($val == 5) {
                    $ret = sscanf($key,"data %s validation %d");

                    if( $ret[0] !== "comments"){
                        $file='upload\\'.$ret[0].'\\'.$data[$ret[0]][$ret[1]]["path"];
                        unlink($file);
                    }

                    $data[$ret[0]] = array_merge(array_diff_key($data[$ret[0]], array("$ret[1]"=>"test")));
                }
                
            }

            $patient->setData($data);
            $em->merge($patient);
            $em->flush();
            $this->addFlash('notice', "Le dossier a été modifié avec succès !");
            
            // Après soumission, on vérifie s'il y a encore des données à valider ou pas
            if (extraDataHasNoChanges($patient)) {
                return $this->redirectToRoute("validatepatient");
            }
        }

        
        $deleteNotifications = $repositoryNotif->findBy(['target' => $user, 'idPatient' => $patient->getPatientId()]);

        foreach ($deleteNotifications as $notification) {
            $em->remove($notification);
            $em->flush();
        }

        return $this->render("patient/validatePatient.html.twig", array(
            'form' => $form->createView(),
            'listPatient' => $listPatient,
            'evalComments' => $evaluators["comments"],
            'evalImages' => $evaluators["images"],
            'evalVideos' => $evaluators["videos"],
            'evalFiles' => $evaluators["files"],
            'patient' => $patient,
            'user' => $user
        ));
        
    }



    
    /**
     * @Route("/viewpatient",name="viewpatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function viewPatientAction(Request $request) {
		$em = $this->getDoctrine()->getManager();
        $repositoryPatient = $em->getRepository('AppBundle:Patient');
        $repositoryUser = $em->getRepository('AppBundle:User');
        $notificationRepository = $em->getRepository('AppBundle:Notification');
		$repositorySpec = $em->getRepository('AppBundle:Speciality');
        $user = $this->getUser();

        $patientId = $request->get('patientId');
		if (null == $patientId) {

            if (in_array('ROLE_SECRETARY', $user->getRoles())) {
                $listPatient = $repositoryPatient->findByArchived(false);
            } else { // Pour les comptes ayant un rôle différent de secrétaire, n'afficher que les dossiers médiicaux leuur appartenant
                $listPatient = $repositoryPatient->findBy(['owner' => $user, 'archived' => false]);
            }
    
            return $this->render("patient/viewPatient.html.twig", [
                'patient' => null,
                'listPatient' => $listPatient,
            ]);

        }

        $patient = $repositoryPatient->findOneByPatientId($patientId);
        if (null === $patient) {
            $this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
            return $this->redirectToRoute('viewpatient');
        }

        $evaluators = $this->getEvaluatorNames($patient);

        //If patient shared delete share notification.
        $shareNotifications = $notificationRepository->findBy(['target' => $user, 'idPatient' => $patient->getPatientId(), 'type' => 'share']);
        $changeDocNotifications = $notificationRepository->findBy(['target' => $user, 'idPatient' => $patient->getPatientId(), 'type' => 'changeDoc']);
        $deleteNotifications = array_merge($shareNotifications, $changeDocNotifications);
        foreach ($deleteNotifications as $notification) {
            $em->remove($notification);
            $em->flush();
        }

        $defaultData = ['message' => 'Type your message here'];
        $form = $this->createFormBuilder($defaultData, array("allow_extra_fields" => true))->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $patient->getData();
            foreach ($form->getExtraData() as $key => $val) {
                if (strpos($key, "archive")) {
                    $ret = sscanf($key, "data %s archive %d");
                    $data[$ret[0]][$ret[1]]['validation'] = (int)$val;
                }
                if (strpos($key, "perm")) {
                    $ret = sscanf($key, "data %s perm %d");
                    // Si la ressource est privée, on la rend publique
                    if(isset($data[$ret[0]][$ret[1]]['perm'])){
                        if($data[$ret[0]][$ret[1]]['perm'] === 1){
                            $data[$ret[0]][$ret[1]]['perm'] = 0;
                        }
                        else { // Si la ressource est publique, on la rend privée
                            $data[$ret[0]][$ret[1]]['perm'] = 1;
                        }
                    }
                }
            }

            // Les extraData du formulaire contiennent les images/vidéos/fichiers à uploader
            $edit = ($user == $patient->getOwner() or in_array('ROLE_SECRETARY', $user->getRoles()));
            $time = (new \DateTime())->format('d-m-Y H:i:s');
            
            $extraData = $this->get('file.uploader')->upload($form->getExtraData());
            $nbr_extra_datas = $this->countExtraDatas($extraData);
            $this->addData($extraData, $validation, $data, $nbr_extra_datas["comments"], 'datacomment', 'datacommentperm', 'datacommentcat', 'addComment', 'comments', '', $edit, $time, $user, $patient);
            $this->addData($extraData, $validation, $data, $nbr_extra_datas["images"], 'dataimage', 'dataimageperm', 'dataimagecat', 'addImage', 'images', 'dataimageoriginalName', $edit, $time, $user, $patient);
            $this->addData($extraData, $validation, $data, $nbr_extra_datas["videos"], 'datavideo', 'datavideoperm', 'datavideocat', 'addVideo', 'videos', 'datavideooriginalName', $edit, $time, $user, $patient);
            $this->addData($extraData, $validation, $data, $nbr_extra_datas["files"], 'datafile', 'datafileperm', 'datafilecat', 'addFile', 'files', 'datafileoriginalName', $edit, $time, $user, $patient);
            $patient->setData($data);

            $em->merge($patient);
            $em->flush();

            if (in_array('ROLE_PARAMEDICAL', $user->getRoles())) {
                $this->addFlash('notice', "La demande de modification a été soumise avec succès !");
            } else {
                $this->addFlash('notice', "Le dossier a été modifié avec succès !");
            }

            return $this->redirectToRoute('viewpatient', array('patientId' => $patientId));
        }

        return $this->render("patient/viewPatient.html.twig", [
            'patient' => $patient,
            'evaluators' => $evaluators,
            'user' => $user,
            'form' => $form->createView()
        ]);
            
        
    }

    /**
     * @Route("/archivedpatient",name="archivedpatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function viewArchivedPatientAction(Request $request)
    {
        // On récupère le repository
        $em = $this->getDoctrine()->getManager();
        $repositoryPatient = $em->getRepository('AppBundle:Patient');
        $repositoryUser = $em->getRepository('AppBundle:User');
        $notificationRepository = $em->getRepository('AppBundle:Notification');

        $user = $this->getUser();
        // On récupère l'entité correspondante à l'patientId "patientId"
        $patientId = $request->get('patientId');
        if ($patientId) {

            $patient = $repositoryPatient->findOneByPatientId($patientId);
            if (null === $patient) {
                $this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
                return $this->redirectToRoute('archivedpatient');
            }

            $evaluators = $this->getEvaluatorNames($patient);
            //If doc chqnged delete notification after viewing the page.

            $changeDocNotifications = $notificationRepository->findBy([
                'target' => $user,
                'idPatient' => $patient->getPatientId(),
                'type' => 'changeDoc'
            ]);

            $shareNotifications = $notificationRepository->findBy([
                'target' => $user,
                'idPatient' => $patient->getPatientId(),
                'type' => 'share'
            ]);

            

            $deleteNotifications = array_merge($shareNotifications,$changeDocNotifications);

            foreach ($deleteNotifications as $notification) {
                $em->remove($notification);
                $em->flush();
            }

        } else {
            $patient = null;
            $evaluators["comments"] = null;
            $evaluators["images"] = null;
            $evaluators["videos"] = null;
            $evaluators["files"] = null;
        }


        if ($user->getRoles()[0] == 'ROLE_SECRETARY') {
            $listPatient = $repositoryPatient->findByArchived(true);

        } else {
            $listPatient = $repositoryPatient->findBy(['owner' => $user, 'archived' => true]);
        }

        return $this->render("patient/archivedPatient.html.twig", [
            'user' => $user,
            'patient' => $patient,
            'listPatient' => $listPatient,
            'evaluators' => $evaluators
        ]);
    }

    /**
     * @Route("/viewpublic",name="view_public_patient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function viewPublicPatientAction(Request $request)
    {
        // On récupère le repository
        $repositoryPatient = $this->getDoctrine()->getManager()->getRepository('AppBundle:Patient');
        $repositoryUser = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');

        $patientId = $request->get('patientId');
        if ($patientId) {

            $patient = $repositoryPatient->findOneByPatientId($patientId);
            if (null === $patient) {
                $this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
                return $this->redirectToRoute('view_public_patient');
            }
            $evaluators = $this->getEvaluatorNames($patient, true);

        } else {
            $patient = null;
            $evaluators["comments"] = null;
            $evaluators["images"] = null;
            $evaluators["videos"] = null;
            $evaluators["files"] = null;
        }

        $listPatient = $repositoryPatient->findBy([
            'archived' => 0,
        ]);

        return $this->render("patient/viewPublic.html.twig", [
            'user' => $this->getUser(),
            'patient' => $patient,
            'listPatient' => $listPatient,
            'evaluators' => $evaluators
        ]);
    }

	/**
     * @Route("/downloadpatient",name="downloadpatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function downloadPatientAction(Request $request)
    {
		$form = $this->createFormBuilder()
            ->add('Cledechiffrement', TextType::class, [
                'label'=>'Clé de chiffrement',
                'constraints' => array(new Length(array('min' => 6))),
            ])
            ->add('public', CheckboxType::class, array(
                'label'    => 'J\'ai conscience de l\'inaccessibilité du dossier si la clé de chiffrement est oubliée',
            ))
            ->add('Generer le dossier', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->getMethod() == 'POST') {

            //Récupération de la date actuelle
            date_default_timezone_set('Europe/Paris' );
            $date = date("d-m-y_H\h_i\m");

			$em = $this->getDoctrine()->getManager();
			$repositoryUser = $em->getRepository('AppBundle:User');
			$repositoryPatient = $em ->getRepository('AppBundle:Patient');
			$notificationRepository = $em->getRepository('AppBundle:Notification');

			$user = $this->getUser();

            $patientId = $request->get('patientId');
			if ($patientId) {

				$patient = $repositoryPatient->findOneByPatientId($patientId);
				if (null === $patient) {
					$this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
                    return $this->redirectToRoute('downloadpatient');
				}

				//Savoir si on est propriétaire du dossier (si oui, permettre la modification)
                if ($user == $patient->getOwner()) {
                    $edit = 1;
                } else {
                    $edit = 0;
                }

				

				$nom = "Dossier de ".$patient->getPrenom()." ".$patient->getNom();
				$etatCivil = "Sexe : ".$patient->getSexe()."\t\t Né le : ".$patient->getDateNaissance()->format("d-m-Y");
				$coordonnées = "Adresse : ".$patient->getAdresse()."\n Email : ".$patient->getEmail()."\t\t téléphone : ".$patient->getTel();
				$infosDossier = "ID dossier : ".$patient->getPatientId()."\t\t archivé : ".$patient->isArchived();

				$text = $nom."\n".$etatCivil."\n".$coordonnées."\n".$infosDossier;
				$path = "tmp";
				$filename = $path.'/'.$patient->getPrenom()."_".$patient->getNom()."_Dossier_medical.txt";

				if (!is_dir($path) && mkdir($path)) {
					$file = fopen($filename, "w");

					if ($file !== null) {
						fputs($file, $text);
						fclose($file);
					} else {
						$this->addFlash('alert',
							'Une erreur est survenue lors de la création du fichier.');
					}
				} else {
					//Suppression du dossier tmp
					/*$cmd = 'rmdir /s /q '.$path;
                    exec($cmd);*/
                    delTree($path);

					mkdir($path);
					$file = fopen($filename, "w");
					fputs($file, $data);
					fclose($file);
				}

				$archiveName = $patient->getPrenom().'_'.$patient->getNom().'_Dossier_medical_'.$date.'.7z';
				$passwd = $form['Cledechiffrement']->getData();

				$cmd = '"C:\Program Files (x86)\Text2PDF v1.5\txt2pdf.exe" '.$filename.' -epo:'.$passwd.' -epu:'.$passwd;
				exec($cmd);

                $cmd = 'powershell.exe -NonInteractive -NoProfile -ExecutionPolicy Bypass -Command "Compress-7Zip -Path '.$path.' -ArchiveFileName '.$archiveName.' -Format SevenZip -Password \''.$passwd.'\' -EncryptFilenames"';
                exec($cmd, $output, $ret);

                //Suppression du dossier tmp
                /*$cmd = 'rmdir /s /q '.$path;
                exec($cmd);*/
                delTree($path);

                //Si le zip s'est correctement effectué
                if($ret==0){

                    //Envoi du zip à l'utilisateur
                    $response = new Response();
                    $response->setStatusCode(200);
                    $response->headers->set('Content-Type', "application/x-7z-compressed");
                    $response->headers->set('Content-Disposition', 'attachment;filename='.$archiveName);
                    $response->setContent(file_get_contents($archiveName));
                    $response->send();

                    //Suppression du zip
                    unlink($archiveName);

                    $this->addFlash('notice',
                        'La génération du dossier s\'est effectuée avec succès.'
                    );

                } else {
                    $this->addFlash('alert',
                        'Une erreur est survenue lors de la génération du dossier.'
                    );
                }
            }

            return $this->render('patient/downloadPatient.html.twig', array(
                'form' => $form->createView()
            ));

        }

        return $this->render('patient/downloadPatient.html.twig', array(
            'form' => $form->createView()
        ));

	}

    /**
     * @Route("/archivepatient",name="archivepatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function archivePatientAction(Request $request)
    {
        // On récupère le repository
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Patient');

        // On récupère l'entité correspondante à l'patientId "patientId"
        $patientId = $request->get('patientId');
        if (null == $patientId) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ces données");
            return $this->redirectToRoute('viewpatient');
        }

        $patient = $repository->findOneByPatientId($patientId);
        if (null === $patient) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ces données");
            return $this->redirectToRoute('viewpatient');
        }

        //Suppression des notifications qui concernent ce patient
        $repositoryNotif = $em->getRepository('AppBundle:Notification');
        $notif = $repositoryNotif->findByIdPatient($patientId);
        foreach ($notif as $value) {
            $em->remove($value);
        }

        //Suppression des acl qui concernent ce patient
        //Pour eviter que le dossier soit partage sans qu'on le sache
        $repositoryACL = $em->getRepository('AppBundle:ACL');
        $acl = $repositoryACL->findById_patient($patientId);
        foreach ($acl as $value) {
            $em->remove($value);
        }

        $patient->setArchived(true);
        $em->merge($patient);
        $em->flush();

        $this->addFlash('notice', "Le dossier a été archivé avec succès !");
        return $this->redirectToRoute('viewpatient');
    }

    /**
     * @Route("/unarchivepatient",name="unarchivepatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function unarchivePatientAction(Request $request)
    {
        // On récupère le repository
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Patient');

        $patientId = $request->get('patientId');
        if (null == $patientId) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ces données");
            return $this->redirectToRoute('homepage');
        }
        
        $patient = $repository->findOneByPatientId($patientId);
        if (null === $patient) {
            $this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
            return $this->redirectToRoute('homepage');
        }

        $patient->setArchived(false);
        $em->merge($patient);
        $em->flush();
        $this->addFlash('notice', "Le dossier a été désarchivé avec succès !");
        return $this->redirectToRoute('archivedpatient');

    }

    /**
     * @Route("/archiveddata",name="archiveddata")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function archivedDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repositoryPatient = $em->getRepository('AppBundle:Patient');
        $repositoryUser = $em->getRepository('AppBundle:User');

        $user = $this->getUser();

        $patientId = $request->get('patientId');
        if ($patientId) {

            $patient = $repositoryPatient->findOneByPatientId($patientId);
            if (null === $patient) {
                $this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
                return $this->redirectToRoute('archiveddata');
            }
            

            $form = $this->createFormBuilder(array("allow_extra_fields" => true))
                ->add('Enregistrer', SubmitType::class)
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid() && isset($_POST['deleteitem'])) {

                $data = $patient->getData();

                foreach (array_reverse($_POST['deleteitem']) as $key => $val) {
                    $ret=sscanf($key,"data %s delete %d");

                    if( $ret[0] !== "comments"){
                        $file='upload\\'.$ret[0].'\\'.$data[$ret[0]][$ret[1]]["path"];
                        unlink($file);
                    }

                    $data[$ret[0]]=array_merge(array_diff_key($data[$ret[0]], array("$ret[1]"=>"test")));
                }

                $patient->setData($data);

                $em->merge($patient);
                $em->flush();
            }

            /*
             * Liste reliant les ids des évaluateurs qui ont inséré une donnée dans le dossier médical avec leur nom
             */
            $evaluators = $this->getEvaluatorNames($patient);

            return $this->render("patient/archivedData.html.twig", [
                'patient' => $patient,
                'evaluators' => $evaluators,
                'user' => $user,
                'form' => $form->createView()
            ]);

        } else {
            $patient = null;
            $evaluators["comments"] = null;
            $evaluators["images"] = null;
            $evaluators["videos"] = null;
            $evaluators["files"] = null;
            $form = null;
        }


        if ($user->getRoles()[0] == 'ROLE_MEDICAL') {
            $listPatient = $repositoryPatient->findBy(['owner' => $user, 'archived' => false]);
        }else {
            $listPatient = null;
        }


        return $this->render("patient/archivedData.html.twig", [
            'patient' => $patient,
            'listPatient' => $listPatient,
            'evalComments' => $evaluators["comments"],
            'evalImages' => $evaluators["images"],
            'evalVideos' => $evaluators["videos"],
            'evalFiles' => $evaluators["files"],
            'user' => $user,
            'form' => $form
        ]);
    }

    /**
     * @Route("/deletepatient",name="deletepatient")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function deletePatientAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Patient');
        $patientId = $request->get('patientId');
        
        if (null === $patientId) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ces données");
            return $this->redirectToRoute('homepage');
        }

        $patient = $repository->findOneByPatientId($patientId);
        if (null === $patient) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ces données");
            return $this->redirectToRoute('deletepatient');
        }

        //TODO : Suppression manuelle en attendant les annotations doctrine fonctionnelles
        //Suppression des notifications qui concernent ce patient
        $repositoryNotif = $em->getRepository('AppBundle:Notification');
        $notif = $repositoryNotif->findByIdPatient($patientId);
        foreach ($notif as $value) {
            $em->remove($value);
        }
        //Suppression des acl qui concernent ce patient
        $repositoryACL = $em->getRepository('AppBundle:ACL');
        $acl = $repositoryACL->findById_patient($patientId);
        foreach ($acl as $value) {
            $em->remove($value);
        }

        //Suppression des Fichiers du patient sur le système
        $data=$patient->getData();
        foreach($data as $key => $value){
            if($key !== "comments"){
                foreach($value as $cle => $valeur){
                        $file='upload\\'.$key.'\\'.$valeur["path"];
                        unlink($file);
                }
            }
        }

        $em->remove($patient);
        $em->flush();
        $this->addFlash('notice', "Le dossier a été supprimé avec succès !");
        return $this->redirectToRoute('viewpatient');
        
    }

    public $files = [];

    /*
     * Parcours récursif du dossier donné en paramètre et ajout des chemins absolus
     * des fichiers trouvés au tableau $this->files
     */
    public function recurseDir($dir)
    {
        header('Content-type: text/plain; charset=utf-8');
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        if (!is_dir($dir . '/' . $file)
                            && mime_content_type($dir . '/' . $file) != "application/octet-stream") {
                            $element = $dir. '/' .$file;

                            array_push($this->files, $element);
                        } else {
                            $this->recurseDir($dir. '/' . $file);
                        }
                    }
                }
            }
            closedir($dh);
        }
    }

    /**
     * @Route("/importExistent", name="importExistent")
     * @param Request $request
     * @return Response
     */
    public function importExistentAction(Request $request)
    {
        date_default_timezone_set('Europe/Paris');
        $time = (new \DateTime())->format('d-m-Y H:i:s');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:User');
        $listPro = $repository->findByRoles('ROLE_MEDICAL');

        /*
         * Tableau contenant les noms des catégories sous forme d'expressions régulières pour faire abstraction
         * de la façon dont c'est écrit dans le système considéré d'où on veut importer des dossiers médicaux
         */
        $categories = ["/^appareillage$/i","/^bilans? m[eéÉ]dicaux$/i","/^certificats$/i","/^consultations$/i","/^courriers \(dr.? labat\)+$/i", "/^m[eéÉ]decin$/i","/^divers$/i","/^ergoth[eéÉ]rapie$/i","/^infirmerie$/i","/^kin[eéÉ]sith[eéÉ]rapie$/i","/^musicoth[eéÉ]rapie$/i","/^neuropsychologue$/i","/^orthophonie$/i","/^psychologue$/i","/^psychomotricit[eéÉ]$/i","/^radios$/i"];

        $form = $this->createFormBuilder()
            ->add('archive', FileType::class, array('required' => true, 'mapped' => false))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->getMethod() == 'POST') {
            /*
             * On vérifie bien que l'archive soumise est de format .zip
             */
            if (preg_match("/(.zip)$/", $form['archive']->getData()->getClientOriginalName()) == 1) {
                /*
                 * On renomme le fichier uploadé et on le place dans le répertoire tmp/
                 */
                $form['archive']->getData()->move('tmp/', 'archive.zip');

                /*
                 * On décompresse l'archive uploadée
                 */
                //exec("unzip tmp/archive.zip -d arch");
                exec('powershell.exe -NonInteractive -NoProfile -ExecutionPolicy Bypass -Command "Expand-7Zip -ArchiveFileName tmp\archive.zip -TargetPath arch"');

                /*
                 * On fait appel à la fonction recurseDir pour avoir les chemins absolus
                 * de tous les fichiers contenus dans l'archive dans le tableau $this->files
                 */
                $this->recurseDir("arch");
                /*
                 * Tri du tableau $this->files
                 */
                sort($this->files);

                $filesByName = [];

                /*
                 * Initialisation du tableau associatif (Nom Patient => [])
                 */
                foreach($this->files as $file){
                    $filesByName[explode('/', $file)[2]] = [];
                }

                /*
                 * On établit le tableau associatif (Nom Patient => [fichiers])
                 */
                foreach ($this->files as $file) {
                    array_push($filesByName[explode('/', $file)[2]], $file);
                }

                /*
                 * Pour chaque dossier médical
                 */
                foreach ($filesByName as $key => $value) {
                    $fullName = $key;
                    $exploded = explode(' ', $fullName);
                    $prenom = $exploded[count($exploded) - 1];
                    array_pop($exploded);
                    $nom = implode(' ', $exploded);

                    $patient = new Patient();

                    /*
                     * Informations relatives au patient
                     */
                    $patient->setPatientId($this->generateIdAction());
                    $patient->setNom(utf8_encode($nom));
                    $patient->setPrenom(utf8_encode($prenom));
                    $patient->setSexe("Masculin");
                    $patient->setDateNaissance($now);
                    $patient->setPartage(false);
                    $patient->setPublic(false);
                    $patient->setOwner($repository->findOneById($_POST['proId']));
                    $patient->setArchived(false);
                    $data = [
                        'comments' => [],
                        'images' => [],
                        'videos' => [],
                        'files' => []
                    ];

                    $i = 0;

                    /*
                     * Fichiers relatif au patient
                     */
                    $filesArray = [];
                    foreach($value as $file){
                        $originalName = array_reverse(explode('/', $file))[0];
                        $myfile = new File($file);
                        $file = new UploadedFile($file, $originalName, null, $myfile->getSize(), null, true);

                        $filesArray["datafile".$i++] = $file;
                    }

                    $extraData = $this->get('file.uploader')->upload($filesArray);

                    /*
                     * Nombre de fichiers upploadés
                     */
                    $nbrOfFiles = count($extraData)/2;

                    /*
                     * Fichiers patient selon la catégorie
                     */
                    for($i = 0; $i < $nbrOfFiles ; $i++){
                        $catOfFile = explode('/' ,$filesArray['datafile'.$i]->getPath())[3];

                        $pathName = $filesArray['datafile'.$i]->getPathname();
                        $pathName = explode('/' ,$pathName);
                        $pathName = array_slice($pathName, 4);

                        $name = implode('_', $pathName);

                        $keyOfCat = -1;
                        foreach ($categories as $keyCat => $cat) {
                            if(preg_match_all($cat, $catOfFile)){
                                $keyOfCat = $keyCat;
                                break;
                            }
                        }
                        if($keyOfCat == -1){
                            $this->addFlash(
                                'alert',
                                "Erreur lors de l'importation du fichier : ".$filesArray['datafile'.$i]->getPathname()
                            );
                        } else if($keyOfCat == 4) {
                            $file = [
                                "path" => $extraData['datafile'.$i],
                                "name" => utf8_encode($name),
                                "perm" => 1,
                                "created_at" => $time,
                                "pro_id" => $_POST['proId'],
                                "validation" => 1,
                                "categorie" => $keyOfCat
                            ];
                            array_push($data['files'], $file);
                        } else {
                            $file = [
                                "path" => $extraData['datafile'.$i],
                                "name" => utf8_encode($name),
                                "perm" => 0,
                                "created_at" => $time,
                                "pro_id" => $_POST['proId'],
                                "validation" => 1,
                                "categorie" => $keyOfCat
                            ];
                            array_push($data['files'], $file);
                        }
                    }

                    $patient->setData($data);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($patient);
                    $em->flush();
                }

                //Notifications
                $user = $this->getUser();

                $info = [
                    'source' => $user,
                    'target' => $repositoryUser->findOneById($_POST['proId']),
                    'patient' => null,
                    'type' => "addPatients"
                ];

                $NotificationService = $this->get('notification');
                $NotificationService->notify($info);
                $em->flush();


                $this->addFlash(
                    'notice',
                    'Importation effectuée avec succès.'
                );

                /*
                 * On supprime l'archive uploadée du dossier tmp/
                 */
                //exec("rm -rf arch tmp/archive.zip");
                /*exec("rmdir /q /s arch ");
                exec("rmdir /q /s tmp");*/
                delTree("arch");
                delTree("tmp");

            } else {
                $this->addFlash(
                    'alert',
                    "L'importation n'a pas pu être effectuée. Le fichier choisi n'est pas du bon type"
                );
            }

            return $this->render('patient/importExistent.html.twig', array(
                'form' => $form->createView(),
                'listPro' => $listPro,
            ));
        } else {
            return $this->render('patient/importExistent.html.twig', array(
                'form' => $form->createView(),
                'listPro' => $listPro,
            ));
        }
    }

    /**
     * @Route("/changedoc",name="changedoc")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function changeDocAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repositoryPatient = $em->getRepository('AppBundle:Patient');
        $repositoryUser = $em->getRepository('AppBundle:User');
        $repositoryACL = $em->getRepository('AppBundle:ACL');

        $listPatient = $repositoryPatient->findAll();
        $listPro = $repositoryUser->findByRoles('ROLE_MEDICAL');

        $patientId = $request->get('patientId');
        $newDocId = $request->get('proId');
        if (null !== $newDocId && null !== $patientId){

            $patient = $repositoryPatient->findOneByPatientId($patientId);

            if (null === $patient) {
                $this->addFlash('alert', "Le patient n'existe pas dans la base de données !");
                return $this->redirectToRoute('changedoc');
            }

            $doc = $repositoryUser->findOneById($newDocId);
            if (null === $doc) {
                $this->addFlash('alert', "Le docteur n'existe pas dans la base de données !");
                return $this->redirectToRoute('changedoc');
            }

            if ($patient->getOwner() != $doc ){

                
                $patient->setOwner($doc);
                $em->persist($patient);

                //Notifications
                $user = $this->getUser();
                $newDoc = $repositoryUser->findOneById($newDocId);

                $info = [
                    'source' => $user,
                    'target' => $newDoc,
                    'patient' => $patient->getPatientId(),
                    'type' => "changeDoc"
                ];

                $NotificationService = $this->get('notification');
                $NotificationService->notify($info);

                /*
                 * Reset ACL Table
                 * Delete the acl to avoid sharing files with myself.
                 */
                $aclDoc = $repositoryACL->findOneBy([
                    'idPatient' => $patient->getPatientId(),
                    'evaluator' => $newDoc
                ]);
                if($aclDoc != null){
                    $em->remove($aclDoc);
                }

                
                // Change Doc in ACL with the new DocId.
                $listACL = $repositoryACL->findByIdPatient($patient->getPatientId());
                foreach ($listACL as $acl) {
                    $acl->setDoc($newDoc);
                    $em->merge($acl);
                }

                $em->flush();
                $this->addFlash('notice', 'La modification a été effectuée avec succès.');

            } else {

                $this->addFlash('alert', 'Le médecin que vous avez choisi est déjà le médecin soignant.');
            }

        }
        return $this->render('patient/changeDoc.html.twig', array(
            'listPatient' => $listPatient,
            'listPro' => $listPro,
        ));
    }
}
