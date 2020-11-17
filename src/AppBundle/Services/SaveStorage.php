<?php

namespace AppBundle\Services;

use \Datetime;


class SaveStorage {
    const SAVE_BASENAME = "sauvegarde_mesureonline_";
    const SAVE_DIRECTORY = "saves/";
    const SAVE_FIND_REGEX = "/".self::SAVE_BASENAME."(.*)\.7z(\.init)?/";

    public function saveFilenameFromDate($date_string) {
        return self::SAVE_DIRECTORY.self::SAVE_BASENAME.$date_string.".7z";
    }

    public function convertDateFromEnglishFormat($new_format, $date) {
        $date = DateTime::createFromFormat("Y-m-d-H-i", $date);
        if ($date) {
            return $date->format($new_format);
        }
        return "";
    }

    public function listAllBackups() {
        # On vérifie que le dossier SAVE_DIRECTORY existe bien
        if (is_dir(self::SAVE_DIRECTORY) && is_readable(self::SAVE_DIRECTORY)) {
            $cdir = scandir(self::SAVE_DIRECTORY, 1);
        } else {
            # Dans le cas où il n'existe pas on présentera une table vide
            $cdir = array();
        }
    
        # On ne garde que les noms de fichiers qui correspondent à des fichiers de backup
        # bien comprendre comment les callback php (surtout avec les méthodes d'une classe) sont fait
        # pour lire ce array_filter ! array($this, "isSaveFile") == $this->isSaveFile
        return array_filter(array_values($cdir), array($this, "isSaveFile"));
    }

    public function isSaveFile($filename) {
        return preg_match(self::SAVE_FIND_REGEX, pathinfo($filename)["basename"]);
    }

    /**
    * Extrait la date depuis un nom de fichier de sauvegarde
    * i.e. finissant par ".7z" ou ".7z.init" et commençant par "sauvegarde_mesureonline"
    */
    public function extractSaveDate($filename) {
        $matches = array();
        if (preg_match(self::SAVE_FIND_REGEX, $filename, $matches) != 0) {
            return $matches[1];
        } else {
            return "";
        }
    }

    public function deleteBackup($date) {
        return unlink($this->saveFilenameFromDate($date));
    }

    # ======================================================================== #
    #          Partie qui gère l'envoi d'une sauvegarde en morceaux            #
    # ======================================================================== #

    /**
     * Gère l'upload (de la part d'un client) d'un fichier de sauvegarde en morceaux.
     */
    public function handleNewFileChunck() {
        // TODO: Encore améliorer la fonction pour que les exceptions soit passées (et donc gérées)
        //       au controller PHP, le message permettant la différenciation des erreurs.
        // QuUESTION: die c'est bien ? on peut pas faire autrement ?

        if (empty($_FILES) || $_FILES['file']['error']) {
            die('{"OK": 0, "info": "Failed to move uploaded file."}');
        }

        # on récupère le fichier de sortie temporaire
        try {
            $out = $this->getUploadingPartFile();
        } catch (Exception $error) {
            die('{"OK": 0, "info": "'.$error->getMessage().'"}');
        }

        if ($out) {
            # On ajoute les données fraichement reçus au fichier .part
            try {
                $this->appendIncomingDataToPartFile($out);
            } catch (Exception $error) {
                die('{"OK": 0, "info": "'.$error->getMessage().'"}');
            }
            fclose($out);
        } else {
            die('{"OK": 0, "info": "Impossible d\'ouvrir le flux (fichier) de sortie."}');
        }
        
        if ($this->isLastChunk()) {
            # supprime le .part et met a disposition le fichier de sauvegarde complet
            rename("{$this->getUploadingFilePath()}.part", $this->getUploadingFilePath());
        }
        
        die('{"OK": 1, "info": "Upload terminé."}');
    }

    /**
     * Retourne un descripteur sur le fichier dans lequel la sauvegarde envoyée par les requetes
     * AJAX devra etre sauvegardée.
     * Le fichier .part est un fichier temporaire dans lequel on 'append' au fur et à mesure le fichier 
     * de la sauvegarde. On le renommera en .7z seulement à la fin de la réception de la sauvegarde.
     */
    public function getUploadingPartFile() {
        if ($this->getChunckNumber()) {
            if (!is_dir(self::SAVE_DIRECTORY)) {
                mkdir(self::SAVE_DIRECTORY);
            } elseif (!is_writable(self::SAVE_DIRECTORY)) {
                // TODO: Faire une exception plus explicite
                throw new Exception("Dossier de sauvegardes non accessible en écriture.");
            }
            return fopen("{$this->getUploadingFilePath()}.part", "wb");
        } else {
            return fopen("{$this->getUploadingFilePath()}.part", "ab");
        }
    }

    /**
     * @param resource $partFile la resource du fichier temporaire (.part) où il faut stocker la sauvegarde
     */
    public function appendIncomingDataToPartFile($partFile) {
        // Read binary input stream and append it to temp file
        $in = fopen($_FILES['file']['tmp_name'], "rb");
        if ($in) {
            while ($buff = fread($in, 4096))
                fwrite($partFile, $buff);
        } else {
            throw new Exception("Impossible d\'ouvrir le flux (fichier) d\'entrée.");
        }
        
        # on ferme tout et on s'en va sans laisser de traces.
        fclose($in);
        unlink($_FILES['file']['tmp_name']);
    }

    /**
     * @return Integer Le numéro du chunck en cours de traitement.
     */
    public function getChunckNumber() {
        return isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    }

    /**
     * @return Integer Le nombre de chunk total (depuis le début) à traiter pour finir
     *                 l'enregistrement de la sauvegarde.
     */
    public function getChuncksAmount() {
        return isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    }

    /**
     * @return String Le chemin vers le fichier final où la sauvegarde se trouvera 
     */
    public function getUploadingFilePath() {
        if(isset($_REQUEST["name"])) {
            return self::SAVE_DIRECTORY.$_REQUEST["name"];
        }

        return self::SAVE_DIRECTORY.$_FILES["file"]["name"];
    }

    /**
     * @return Boolean vrai si on est au dernier chunck d'envoi d'une sauvegarde
     *                 (ou bien si il n'y avais aucun chunk).
     */
    public function isLastChunk() {
        return !$this->getChuncksAmount() || $this->getChunckNumber() == $this->getChuncksAmount() - 1;
    }
}