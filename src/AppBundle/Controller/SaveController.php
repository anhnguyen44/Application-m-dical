<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\SecurityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use AppBundle\Services\SaveProcess;
use Symfony\Component\Process\Exception;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use AppBundle\Services\SaveConfig;


class SaveController extends Controller
{
    /**
     * @Route("/createBackup", name="createBackup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createBackup(Request $request){
        
        $form = $this->createFormBuilder()
            ->add('Cledechiffrement', TextType::class, [
                'label'=>'Clé de chiffrement',
                'constraints' => array(new Length(array('min' => 6))),
            ])
            ->add('public', CheckboxType::class, array(
                'label'    => 'J\'ai conscience de l\'inutilité de la sauvegarde si la clé de chiffrement est oubliée',
            ))
            ->add('Sauvegarder', SubmitType::class)
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->getMethod() == 'POST') {
            $saveProcess = $this->container->get('save.process');
            $saveConfig = $this->container->get('save.config');
            $saveConfig->checkConfigFile();
            $saveRotation = $saveConfig->getConfig()['save_rotation'];

            // On appelle le service SaveProcess afin d'executer le script avec les bons arguments
            $has_backup_process_started = $saveProcess->createBackup(
                $this->container->getParameter('database_name'),
                $form['Cledechiffrement']->getData(),
                $saveRotation
            );
            
            switch($has_backup_process_started) {
                case 0 :
                    $this->addFlash(
                        'notice',
                        'La sauvegarde a bien été lancée.'
                    );

                    break;
                case -1 :
                    $this->addFlash(
                        'alert',
                        "Erreur lors du lancement de la sauvegarde, voir 'create_backup.log' pour plus d'explications."
                    );
                    break;
                case -2 :
                    $this->addFlash(
                        'alert',
                        "Annulation de la sauvegarde, le script a été altéré"
                    );
                    break;
                case -3 :
                    $this->addFlash(
                        'alert',
                        "Annulation de la sauvegarde, une sauvegarde est déjà en cours"
                    );
                    break;
            }
        }

        # Dans tous les cas on finis avec une nouvelle page de création d'un backup
        return $this->render('admin/backup/createBackup.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    /**
     * @Route("/listBackup", name="listBackup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listBackup(Request $request) {
        $saveStorageService = $this->container->get("save.storage");
        $saveProcessService = $this->container->get('save.process');
        
        # on liste les sauvegards disponibles (mais aussi les initFile)
        $save_filenames = $saveStorageService->listAllBackups();

        # On forme le tableau de traduction
        $stepTranslations = array(
            "Dumping" => "Sauvegarde de la base de données",
            "Compressing" => "Compression de la sauvegarde",
            "Terminating" => "Finalisation de la sauvegarde",
            "Finished" => "Sauvegarde disponible"
        );

        $saves = array();
        foreach ($save_filenames as $save_filename) {
            $date = $saveStorageService->extractSaveDate($save_filename);
            if ($date != "") {
                // IMPROVE: Le step pourrait etre requeté en AJAX pour évoluer.
                array_push($saves, [
                    "date" => $date,
                    "status" => $saveProcessService->getStatus($date),
                    "step" => $stepTranslations[$saveProcessService->getProcessStep($date)],
                    "french_date" => "Le ".$saveStorageService->convertDateFromEnglishFormat("d/m/Y \à H\hi\m", $date)
                ]);
            }
        }

        return $this->render("admin/backup/listBackup.html.twig", array(
            'saves' => $saves
        ));
    }

    /**
     * @Route("/restoreBackup", name="restoreBackup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restoreBackup(Request $request){
        # on vérifie les pré-requis de la page
        if (!isset($_GET["backup_date"])) {
            $this->addFlash(
                "alert",
                "Aucune sauvegarde n'a été sélectionnée. Veuilez d'abord choisir la sauvegarde à restaurer"
            );
            return $this->redirectToRoute("listBackup");
        }

        # Pour l'instant la restauration d'une sauvegarde demande un mot de passe
        # C'est pour ça que le code ci dessous est nécessaire (pour demander ce password)
        $saveStorageService = $this->container->get('save.storage');
        $saveProcess = $this->container->get('save.process');
 
        $french_date = $saveStorageService->convertDateFromEnglishFormat("d/m/Y à H\hi\m", $_GET["backup_date"]);
        if ($french_date != "") {
            if ($request->getMethod() == 'GET') {
                // On ajoute, dans une nouvelle file de flash, le texte pour indiquer à l'utilisateur la sauvegarde
                // qu'il a sélectionné en venant de listBackup.
                # HACK: Pourrait etre fait avec une file normal ... (TODO ?)
                $this->addFlash(
                    "loaded-save",
                    "La sauvegarde à réappliquer est celle qui à été effectuée le ".$french_date
                );
            }
        } else {
            $this->addFlash(
                "alert",
                "la sauvegarde demandée n'est pas valide (erreur dans le format de la date)"
            );
            return $this->redirectToRoute("listBackup");
        }

        $form = $this->createFormBuilder()
            ->add('Cledechiffrement', TextType::class, [
                'label'=>'Clé de chiffrement',
                'constraints' => array(new Length(array('min' => 6))),
            ])
            ->add('backupSelection', HiddenType::class, array(
                'data' => $saveStorageService->saveFilenameFromDate($_GET["backup_date"])
            ))
            ->getForm();
            
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->getMethod() == 'POST') {
            $has_restore_process_started = $saveProcess->restoreBackup(
                $this->container->getParameter('database_name'),
                $form['Cledechiffrement']->getData(),
                $form['backupSelection']->getData()
            );

            switch($has_restore_process_started) {
                case 0 :
                    $this->addFlash(
                        'notice',
                        'La restauration a bien été lancée.'
                    );
                    break;
                case -1 :
                    $this->addFlash(
                        'alert',
                        "Erreur lors du lancement de la restauration, voir 'restore_backup.log' pour plus d'explications."
                    );
                    break;
                case -2 :
                    $this->addFlash(
                        'alert',
                        "Annulation de la sauvegarde, le script a été altéré"
                    );
                    break;
                case -3:
                    $this->addFlash(
                        'alert',
                        "Ce fichier ne correspond pas à un fichier de sauvegarde"
                    );
                    break;
            }
        }

        return $this->render('admin/backup/restoreBackup.html.twig', array(
            'form' => $form->createView()
        ));

    }

    /**
     * @Route("/configBackup", name="configBackup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function configBackup(Request $request) {

        $save_config_service = $this->container->get('save.config');
        switch ($save_config_service->checkConfigFile()) {
            case 1 :
                break;
            case 0 : 
                $this->addFlash('warning', 'Le fichier de configuration ne 
                contient pas une ou plusieurs clés, qui ont donc été 
                initialisées à zéro.');
                break;
            case -1 :
                $this->addFlash('warning', 'Le fichier de configuration n\'a 
                pas été trouvé, il sera donc créé avec la valeur de 
                l\'utilisateur.');
                break;
        }
        $save_config = $save_config_service->getConfig();

        # On initialise le formulaire où les options de configuration de la sauvegarde seront présents et modifiables par l'utilisateur
        $rotationForm = $this->get('form.factory')->createNamedBuilder('rotationForm')
            ->add('rotationenabled', CheckboxType::class, [
                'label'    => ' ',
                'required' => false,
            ])
            ->add('saverotation', IntegerType::class, [
                'label'=>'Save rotation',
                'data' => $save_config['save_rotation'],
                'constraints' => array(new GreaterThanOrEqual(array('value' => 1))),  # TODO : traduire ce message d'erreur? Il est en anglais.
                'attr' => array('min' => 1),
            ])
            ->add('Enregistrer', SubmitType::class)
            ->getForm();

        $schedulingForm = $this->get('form.factory')->createNamedBuilder('schedulingForm')
            ->add('schedulingenabled', CheckboxType::class, [
                'label'    => ' ',
                'required' => false,
            ])
            ->add('frequencyscheduling', IntegerType::class, [
                'label'=>'Frequency scheduling',
                'data' => $save_config['save_frequency_scheduling'],
                'constraints' => array(new GreaterThanOrEqual(array('value' => 1))),  # TODO : traduire ce message d'erreur? Il est en anglais.
                'attr' => array('min' => 1),
            ])
            ->add('hourscheduling', TimeType::class, [
                'label'=>'Hour scheduling',
                'data' => $save_config['save_hour_scheduling'],
                'input' => 'string',
            ])
            ->add('encryptionkey', TextType::class, [
                'label'=>'Encryption key',
                'constraints' => array(new Length(array('min' => 6))),
            ])
            ->add('windowsname', TextType::class, [
                'label'=>'Windows name',
            ])
            ->add('windowspassword', PasswordType::class, [
                'label'=>'Windows password',
            ])
            ->add('public', CheckboxType::class, [
                'label'    => 'J\'ai conscience de l\'inutilité de la sauvegarde si la clé de chiffrement est oubliée',
            ])
            ->add('Enregistrer', SubmitType::class)
            ->getForm();

        if($request->getMethod() == 'POST') {
            $rotationForm->handleRequest($request);
            $schedulingForm->handleRequest($request);
            
            if ($rotationForm->isSubmitted() && $rotationForm->isValid()) {
                $rotationData = $rotationForm->getData();
                if ($rotationData['rotationenabled']) {
                    $save_config['save_rotation'] = $rotationForm['saverotation']->getData();
                }
                else {
                    $save_config['save_rotation'] = 0;
                }
                $this->addFlash('notice', 'La rotation des 
                        sauvegardes a bien été configurée');
            }
     
            if($schedulingForm->isSubmitted() && $schedulingForm->isValid()) {
                
                $schedulingData = $schedulingForm->getData();
                $saveProcess = $this->container->get('save.process');                             

                if(!$schedulingData['schedulingenabled']) {
                    $schedulingData['frequencyscheduling'] = 0;
                    $schedulingData['hourscheduling'] = '00:00:00';
                    $schedulingData['encyptionkey'] = "notspecified";
                }
                switch($saveProcess->planBackup($schedulingData, $save_config['save_rotation'])){
                    case 0 :
                        $this->addFlash('notice', 'La planification des 
                        sauvegardes a bien été configurée');
                        break;
                    case -1 :
                        $this->addFlash(
                            'alert', "Erreur lors du lancement de la 
                            configuration de la planification des sauvegardes, 
                            voir 'plan_backup.log' pour plus d'explications.");
                        break;
                    case -2 :
                        $this->addFlash('alert',"Annulation de la sauvegarde, 
                        le script a été altéré");
                        break;
                }
                $save_config['save_frequency_scheduling'] = $schedulingData['frequencyscheduling'];
                $save_config['save_hour_scheduling'] = $schedulingData['hourscheduling'];
            }
            $save_config_service->setConfig($save_config);
        }
        
        # Enfin, on fait le rendu de la page
        return $this->render("admin/backup/configBackup.html.twig", array(
            'save_rotation' => $save_config['save_rotation'],
            'rotationForm' => $rotationForm->createView(),
            'schedulingForm' => $schedulingForm->createView()
        ));
    }

    /**
     * @Route("/downloadBackup", name="downloadBackup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadBackup(Request $request){
        if (isset($_POST['date'])){
            // On récupère le path de la sauvegarde à télécharger
            $saveStorageService = $this->container->get('save.storage');
            $save_path = $saveStorageService->saveFilenameFromDate($_POST['date']);

            // On copie le fichier et on l'envoi à l'utilisateur
            $file = new File($save_path);
            return $this->file($file);
        };

        //Si ça rate, on recharge la page
        return $this->redirectToRoute('listBackup');
    }

    /**
     * @Route("/uploadBackup", name="uploadBackup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadBackup(Request $request){
        // OPTIMIZE: On utilise des fonctions 'die' dans la partie AJAX, les page
        // qui contiennent ces die ne sont jamais visible (réponses au POST de AJAX)
        // Pourrait etre changer pour envoyer des Flash symfony à la premiere page 
        // uploadBackup ?? :o

        if (isset($_FILES['file'])) {
            /**
             * DISCLAIMER: Cette partie est une partie "script", ici on a perdu le lien
             * avec la premiere page uploadBackup généré (celle ou le client choisi le
             * fichier qu'il va envoyer). On ne peut donc pas utiliser de Flash ou ce genre
             * de joyeuseté de Symfony, si une meilleure est trouvée, elle peut largement
             * remplacer celle ci sans aucun soucis :)
             */
            $saveStorageService = $this->container->get('save.storage');
            $saveStorageService->handleNewFileChunck();
        }

        return $this->render("admin/backup/uploadBackup.html.twig");
    }
    
    /**
     * @Route("/deleteBackup", name="deleteBackup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteBackup(Request $request){
        $saveStorageService = $this->container->get('save.storage');

        //On recompose le path de la save
        if (isset($_POST['date'])){
            // On supprime la bonne sauvegarde
            if ($saveStorageService->deleteBackup($_POST['date'])){
                $this->addFlash(
                    "notice",
                    sprintf(
                        "La sauvegarde du %s a bien été supprimée !",
                        $saveStorageService->convertDateFromEnglishFormat("d/m/Y à H\hi\m", $_POST["date"])
                    )
                );
            } else {
                $this->addFlash(
                    'alert',
                    "Erreur de suppression"
                );
            }
        }

        return $this->redirectToRoute("listBackup");
    }
}
?>