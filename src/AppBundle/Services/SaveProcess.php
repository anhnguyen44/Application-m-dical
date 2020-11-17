<?php

namespace AppBundle\Services;

use AppBundle\Form\Type\SecurityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Config\FileLocator;

Class SaveProcess {

    const CREATE_BACKUP_SCRIPT = "..\scripts\create_backup.ps1";
    const RESTORE_BACKUP_SCRIPT = "..\scripts\\restore_backup.ps1";
    const PLANIFICATION_SCRIPT = "..\scripts\plan_backup.ps1";
    const UPLOAD_CONFIG = "upload/";
    /**
     * Le service de gestion des processus de sauvegardes à besoin du service de stockage des sauvegardes.
     * Cela permet de faire fonctionner le service de gestion de processus comme une couche plus haute que la gestion
     * du système de stockage des sauvegardes (qui pourrait etre en réseau, en base de données, ...)
     * Il a aussi besoin de l'accès à certains paramètres de l'application, d'où l'yuilsation du container
     * @param SaveStorage $saveStorageService Le service de stockage des sauvegardes
     * @param ContainerInterface $container Les paramètres de la base de données
     */
    public function __construct(SaveStorage $saveStorageService, $database_name, $database_user, $database_password) {
        $this->saveStorage = $saveStorageService;
        $this->dbname = $database_name;
        $this->dbuser = $database_user;
        $this->dbpassword = $database_password;
    }


    /**
     * Permet de lancer le processus de création d'une sauvegarde.
     * Wrapper de la fonction createScriptProcess. Retourne la valeur retourné par cette derniere
     * 
     * @return Integer une valeur décrivant le bon déroulement ou une erreur:
     *                     * 0: tout s'est bien passé
     *                     * -1: problème de démarrage du processus
     *                     * -2: la vérification du hash du script à échoué
     * @param String $script Le chemin vers le script à lancer dans le processus
     * @param String $hash Le hash sha256 du script passé en premier paramètre
     * @param Array $arguments Un tableau associatif des arguments à passer à la ligne de commande.
     */
    public function createScriptProcess($script, $hash, $arguments) {
        # checking script hash
        if (hash_file("sha256", $script) != $hash)  {
            return -2;
        }

        # on reforme le tableau des paramètres de la ligne de commande
        $parameters = array();
        foreach ($arguments as $param_name => $value) {
            array_push($parameters, '-'.$param_name);
            array_push($parameters, $value);
        }
        
        # création de la ligne de commande, on y ajoute les paramètres qui ne bougeront pas
        $command = sprintf(
            'powershell.exe -ExecutionPolicy Unrestricted -windowstyle hidden -file %s %s',
            $script,
            implode(' ', $parameters)
        );
        
        # On lance le script en arrière-plan pour pouvoir rendre la main
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", pathinfo($script, PATHINFO_FILENAME).'.log', "a"),
            2 => array("file", pathinfo($script, PATHINFO_FILENAME).'_err.log', "a")
        );
        $background_save_process = proc_open($command, $descriptorspec, $pipes);

        # On test si le processus est bien vivant
        if (proc_get_status($background_save_process)['running'])
            return 0; //On vérifie qu 'il a bien été lancé pour le signaler à l'utilisateur
        else 
            return -1;
    }

    /**
     * @return -3 si une sauvegarde est déja lancé ou la valeur de retour de createScriptProcess
     * 
     * @param String $dbname le nom de la base de données à sauvegarder (l'utilisateur doit avoir les droits dessus)
     * @param String $cryptkey le mot de passe utilisé pour chiffrer la sauvegarde
     * @param String $rotation le nombre de sauvegarde à garder dans le dossier des sauvegardes
     */
    public function createBackup($dbname, $cryptkey, $rotation) {
        if (!$this->canISave())
            return -3;

        return $this->createScriptProcess(
            self::CREATE_BACKUP_SCRIPT,
            "06aab233909c031f21dd4913c34009b5b3e4edc3a1afbc962d703a734c791978",
            array(
                "dbname" => $dbname,
                "cryptkey" => $cryptkey,
                "rotation" => $rotation
            )
        );
    }

    /**
     * Permet de lancer le processus de réapplication d'une sauvegarde.
     * Wrapper de la fonction createScriptProcess. Retourne la valeur retourné par cette derniere
     * 
     * @param String $dbname le nom de la base de données à restaurer (l'utilisateur doit avoir les droits dessus)
     * @param String $cryptkey le mot de passe utilisé pour chiffrer la sauvegarde
     * @param String $backup_file le nom de la sauvegarde à réappliquer
     */
    public function restoreBackup($dbname, $cryptkey, $backup_file) {
        if (!$this->saveStorage->isSaveFile($backup_file)) {
            return -3;
        }

        return $this->createScriptProcess(
            self::RESTORE_BACKUP_SCRIPT,
            "8ecd555f69e74991cf95de38e10db23acf3b5686a8c6947620112d776ea88639",
            array(
                "dbname" => $dbname,
                "cryptkey" => $cryptkey,
                "bakfile" => basename($backup_file)
            )
        );
    }

    /**
     * Permet de lancer le processus planifiant une sauvegarde régulière.
     * Wrapper de la fonction createScriptProcess. Retourne la valeur retourné par cette derniere
     * 
     * @param String $dbname le nom de la base de données à restaurer (l'utilisateur doit avoir les droits dessus)
     * @param String $cryptkey le mot de passe utilisé pour chiffrer la sauvegarde
     * @param String $backup_file le nom de la sauvegarde à réappliquer
     */
    public function planBackup($schedulingOptions, $save_rotation){
        return $this->createScriptProcess(
            self::PLANIFICATION_SCRIPT,
            "c2a45defeda2f006f1dbd945e74a261d64f40063c927b61160a74c9fddd3e97c",
            array(
                "dbuser" => $this->dbuser,
                "dbpassword" => $this->dbpassword,
                "dbname" => $this->dbname,
                "cryptkey" => $schedulingOptions['encryptionkey'],
                "rotation" => $save_rotation,
                "frequency" => $schedulingOptions['frequencyscheduling'],
                "hour" => $schedulingOptions['hourscheduling'],
                "name" => $schedulingOptions['windowsname'],
                "password" => $schedulingOptions['windowspassword']
            )
        );
    }

    public function canISave(){
        // TODO: Ajouter la détetction d'une restauration en cours

        # on liste toute les sauvegardes et on filtre pour ne garder que les initFile
        $save_filenames = array_filter($this->saveStorage->listAllBackups(), function ($filename) {
            return pathinfo($filename)['extension'] == "init";
        });

        foreach ($save_filenames as $save_filename) {
            $date = $this->saveStorage->extractSaveDate($save_filename);
            if($date != "" && $this->isRunning($date))
                return false;
        }

        # on arrive là si: le dossier est vide ou si aucun initFile ne possède un pid existant
        return true;
    }

    //====== Fonctions pour traiter le statut du processus de createBackup ======

    public function isRunning(String $date){ // Verifie si le processus est en train de tourner
        if (file_exists($this->saveStorage->saveFilenameFromDate($date).'.init')){
            $pid_string = file_get_contents($this->saveStorage->saveFilenameFromDate($date).'.init');
            preg_match_all('!\d+!', $pid_string, $matches);
            $pid = implode("", $matches[0]);
            
            exec('powershell.exe -command "Get-Process -Id '.$pid.' 2>&1 | Out-Null; $?"', $output);
            return filter_var($output[0], FILTER_VALIDATE_BOOLEAN);
        }
        else {
            $this->cleanFiles();
            return false;
        }
        
    }

    /**
     * Retourne:
     *  - "En cours"   : la sauvegarde a démarrée (init file) et continue toujours (processus vivant)
     *  - "Echec"      : la sauvegarde a démarrée (init file) mais s'est arretée (processus mort)
     *  - "Disponible" : la sauvegarde est présente (processus terminé et bien fini)
     *  - "UnknownBug" : hmmm.... 
     */
    public function getStatus(String $date){
        if (file_exists($this->saveStorage->saveFilenameFromDate($date).'.init')) {
            # le processus de sauvegarde pour la date demandée à été démarré
            if ($this->isRunning($date)) {
                # le processus tourne normalement
                return "En cours";
            } else {
                # le processus s'est arreté avant de terminer
                return "Echec";
            }
        } elseif (file_exists($this->saveStorage->saveFilenameFromDate($date))) {
            # la sauvegarde à cette date est dispo
            return "Disponible";
        } else {
            # on devrait pas arriver là, sauf en demandant une fausse date
            return "UnknownBug";
        }
    }

    /**
     * Retourne:
     *  - "Dumping":     le processus de sauvegarde fait actuellement le dump mysql
     *  - "Compressing": le processus de sauvegarde à fait le dump mysql et fait actuellement la compressions
     *  - "Terminating": le processus a fait le dump mysql et la compressions du dossier upload
     *  - "Finished":    le processus de sauvegarde est complètement terminé
     */
    public function getProcessStep(String $date) {
        // récupère les différents états du processus de sauvegarde
        // grâce aux fichiers présents dans le dossier de sauvegarde
        // QUESTION: Ne serait-il pas mieux de laisser à SaveStorage le soin de compter ce genre de fichier ?
        $states = array(
            "init" => count(glob($this->saveStorage->saveFilenameFromDate($date).'.init')) != 0,
            "mysql" => count(glob($this->saveStorage::SAVE_DIRECTORY.'done-mysqldump')) != 0,
            "compress" => count(glob($this->saveStorage::SAVE_DIRECTORY.'done-compress')) != 0
        );

        // On retourne la bonne étape
        // NOTE: Si le fichier n'est pas présent c'est qu'on est en train
        // d'exécuter l'étapes qu'il représente. exemple: si done-mysqldump manque c'est
        // que l'étape en cours est le dump mysql mysqldump
        switch ($states) {
            case ['init' => true, 'mysql' => true, 'compress' => true]:
                return "Terminating";
            case ['init' => true, 'mysql' => true, 'compress' => false]:
                return "Compressing";
            case ['init' => true, 'mysql' => false, 'compress' => false]:
                return "Dumping";
            default:
                return "Finished";
        } 
    }

    public function cleanFiles(){
        // QUESTION: Comme pour getProcessStep: Laisser à SaveStorage le soin de supprimer ce genre de fichier ?
        unlink($this->saveStorage::SAVE_DIRECTORY.'done-mysqldump');
        unlink($this->saveStorage::SAVE_DIRECTORY.'done-compress');
    }
}