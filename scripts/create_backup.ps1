# Script de sauvegarde du site mesure online
# version: 1.0
# Auteur originel: Antoine Meynard
#
# IMPORTANT: Ce script doit ABSOLUMENT être rétrocompatible
# afin de permettre l'application d'une sauvegarde pour n'importe
# qu'elle version du site. Il faut ainsi qu'entre deux version 
# majeur du système de sauvegarde ce dernier puisse distinguer plusieurs
# type de sauvegardes.

# On passe en paramètre la clé de chiffrement
Param(
    [string]$cryptkey,
    [string]$dbname,
    [switch]$check_integrity,
    [int]$rotation # 0 pour désactiver, un entier naturel positif sui dit combien de sauvegardes on garde
    
);

######## usage ########
# .\create_backup.ps1 -dbname ma_base -cryptkey cle
# dbname: la base de donnée à dump
# cryptkey: la clé de chiffrement du fichier de sauvegarde
# check_integrity: permet de vérifier l'intégrité de l'archive par le script (relance 
# la commande si l'archive est corrompu)
#######################

# GLOBAL_VARIABLES
$SAVES_PATH = "../web/saves"
$UPLOAD_PATH = "../web/upload"
$DUMP_PATH = "$UPLOAD_PATH/dump_bdd.sql"
$MYSQL_CLIENT_ENCODING = "utf8"

$NB_MAX_ITERATION = 5

function QuitOnError {
    if (-not $?) {
        Write-Output "On quitte le script de creation de backup"
        exit
    }    
}

function GetSavesSortedByName{
    Get-ChildItem -Path $SAVES_PATH -Filter sauvegarde_mesureonline_*.7z | Sort-Object Name
}

# on forme le nom de la sauvegarde
$date = Get-Date -format yyyy-MM-dd-HH-mm
$save_name = "sauvegarde_mesureonline_$date.7z"
New-Item ../web/saves/"$save_name.init"
$pid >> ../web/saves/"$save_name.init"

# vérification des pré-requis à la sauvegarde
if ( -not (Test-Path $UPLOAD_PATH -PathType Container)) {
    # création du dossier upload si non présent pour continuer la sauvegarde
    mkdir $UPLOAD_PATH
}

$archive_is_valid = $false
$time_to_loop = $NB_MAX_ITERATION
while ( -not $archive_is_valid -and $time_to_loop -ne 0) {
    # on dump la bdd en la plaçant dans le dossier qu'on compressera ensuite
    # il faut bien utiliser l'encoding cp850 car la base n'est malheureusement pas encodé en utf-8
    mysqldump.exe --defaults-extra-file=../app/config/config.cnf --default-character-set=$MYSQL_CLIENT_ENCODING $dbname -r $DUMP_PATH
    QuitOnError
    New-Item -ItemType file ../web/saves/done-mysqldump
    Compress-7Zip -Path ../web/upload -ArchiveFileName $save_name -Format SevenZip -Password $cryptkey -EncryptFilenames
    QuitOnError
    New-Item -ItemType file ../web/saves/done-compress
    if ($check_integrity) {
        Get-7Zip -ArchiveFileName $save_name -Password $cryptkey
        # Une erreur sera levée si l'archive est corrompu (illisible)
        $archive_is_valid = $?
        $time_to_loop -= 1
    } else {
        # On bypass la vérification d'intégrité de l'archive
        $archive_is_valid = $true;
    }
}


# On affiche qu'on est sorti de la boucle car on a dépassé son nombre d'itération maximum
if ($time_to_loop -eq 0) {
    Write-Output "La commande n'a pas pu faire son travail à cause d'une erreur interne"
    # On quitte pour ne pas mettre une fausse sauvegarde
    exit
}

# on met à disposition la nouvelle sauvegarde
if ( -not (Test-Path $SAVES_PATH -PathType Container)) {
    mkdir $SAVES_PATH
}
Move-Item $save_name $SAVES_PATH/$save_name

# on clean apres le passage
Remove-Item $dump_path

## ROTATION

# si la rotation est activée
if ($rotation -gt 0) {
    # on compte le nombre de sauvegardes dans le dossier de sauvegardes
    $number_of_saves = (GetSavesSortedByName | Measure-Object).Count
    # on ne garde que le nombre de sauvegardes nécessaire, en supprimant les anciennes
    if ($number_of_saves -gt $rotation){
        GetSavesSortedByName | Select-Object -First ($number_of_saves - $rotation) | Remove-Item
    }
    # Remove-Item -Path ../web/upload/done-mysqldump
    # Remove-Item -Path ../web/upload/done-compress
}

Remove-Item -Path ../web/saves/done-mysqldump
Remove-Item -Path ../web/saves/done-compress
Remove-Item -Path ../web/saves/"$save_name.init"