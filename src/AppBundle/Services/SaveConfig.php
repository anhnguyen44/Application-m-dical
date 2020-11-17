<?php

namespace AppBundle\Services;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/* Ce code pourra être extrait si d'autres services ont besoin de gérer des fichiers de configuration en YAML */
class SaveConfig {
    
    const SAVE_CONFIG = "../app/config/save_config.yml";

    public function checkConfigFile() 
    {
        # On initialise nos parser et dumper yaml, qui vont respectivement lire et écrire dans le fichier de configuration des sauvegardes
        $yamlParser = new Parser();
        $yamlDumper = new Dumper();
        # On initialise notre code de retour : 1 = valide, 0 = valeur manquante, -1 = fichier manquant
        $config_file_valid = 1;
        # On vérifie que le fichier de configuration des sauvegardes existe
        if (file_exists(self::SAVE_CONFIG)){
            # On vérifie chaque clé du fichier de configuration, et on l'initialise à 0 si elle n'existe pas.
            $save_config = $this->getConfig();
        }
        else{
            # Si le fichier n'est pas trouvé, on initialise le tableau
            $save_config = [];
        }

        if (!$save_config['save_rotation']){
            $save_config['save_rotation'] = 0;
        }
        if (!$save_config['save_frequency_scheduling']){
            $save_config['save_frequency_scheduling'] = 0;
        }
        if (!$save_config['save_hour_scheduling']){
            $save_config['save_hour_scheduling'] = "00:00:00";
        }
        # Enfin, on réécrit le fichier
        $this->setConfig($save_config);
    }
    
    /* Penser à appeler checkConfigFile() avant ce getter pour vérifier/initialiser le fichier de config */
    public function getConfig()
    {
        $yamlParser = new Parser();
        return $yamlParser->parse(file_get_contents(self::SAVE_CONFIG));
    }
    public function setConfig($save_config)
    {
        $yamlDumper = new Dumper();
        file_put_contents(self::SAVE_CONFIG, $yamlDumper->dump($save_config, 2));
    }
}