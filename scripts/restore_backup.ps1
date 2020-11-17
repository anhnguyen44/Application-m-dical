# Script de sauvegarde du site mesure online
# version: 1.0
# Auteur originel: Antoine Meynard
#
# IMPORTANT: Lire la note dans create_backup.ps1

# On passe en paramètre la clé de chiffrement
Param(
    [string]$cryptkey,
    [string]$dbname,
    [string]$bakfile
);

######## usage ########
# .\restore_backup.ps1 -dbname ma_base -cryptkey cle
# dbname: la base de donnée à restaurer
# cryptkey: la clé de chiffrement du fichier de sauvegarde
# bakfile: le fichier contenant la sauvegarde. Correspond à un fichier du dossier rassemblant les sauvegardes
#######################

# GLOBAL_VARIABLES
$SAVES_PATH = "../web/saves"
$UPLOAD_PATH = "../web/upload"
$TMP_UPLOAD_PATH = "../web/upload_tmp"
$DUMP_PATH = "$TMP_UPLOAD_PATH/dump_bdd.sql"
$MYSQL_CLIENT_ENCODING = "utf8"

# vérification des pré-requis à la sauvegarde
if ( -not (Test-Path $SAVES_PATH -PathType Container)) {
    # création du dossier upload si non présent pour continuer la sauvegarde
    return "Il n'y a aucune sauvegarde, impossible de restaurer le site"
}

Write-Output $bakfile | Out-File -FilePath test.txt


# Le script lance son homologue pour effectuer une sauvegarde du site 
# avant de rétablir une ancienne version.
# ou pas

# Décompression du dossier d'upload temporaire
Expand-7Zip -ArchiveFileName $SAVES_PATH/$bakfile -Password $cryptkey -TargetPath $TMP_UPLOAD_PATH
Write-Output $bakfile | Out-File -FilePath test.txt

# Restauration de la base de données
mysql --defaults-extra-file=../app/config/config.cnf --default-character-set=$MYSQL_CLIENT_ENCODING $dbname -e "source $DUMP_PATH;" 
Remove-Item $DUMP_PATH

# Déplacement restauration du dossier d'upload
# le force permet de supprimer les fichiers en lecture seule et les fichier cachés type lock docx
Remove-Item -r -force $UPLOAD_PATH  
Move-Item $TMP_UPLOAD_PATH $UPLOAD_PATH
