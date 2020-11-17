<?php

namespace AppBundle\Services;

class FileUploader
{
    private $imagesDir;
    private $videosDir;
    private $filesDir;
    private $doctrine;

    public function __construct($imagesDir, $videosDir, $filesDir, $doctrine)
    {
        $this->imagesDir = $imagesDir;
        $this->videosDir = $videosDir;
        $this->filesDir = $filesDir;
        $this->doctrine = $doctrine;
    }

    public function upload($data)
    {
        foreach ($data as $key => $val) {
            if (is_file($val)) {
                if (strpos($key, "image")) {
                    $dir = $this->imagesDir;
                }
                else if (strpos($key, "video")) {
                    $dir = $this->videosDir;
                }
                else if (strpos($key, "file")) {
                    $dir = $this->filesDir;
                }
                $fileName = uniqid() . '.' . $val->guessExtension();
                $val->move($dir, $fileName);

                /* Récupération de la clé de chiffrement et de son id */
                $repositoryCrypto = $this->doctrine->getManager()->getRepository('AppBundle:Crypto');
                $crypto = $repositoryCrypto->findOneBy(array(
                    'id' => 1,
                ));
                $encryptionKey = $crypto->getEncryptionKey();
                $encryptionKeyId = $crypto->getId();

                /* Récupération du contenu du fichier en clair et suppression de celui-ci */
                $destinationFileName = $dir . '/' . $fileName;
                $fd = fopen($destinationFileName, 'rb') or die('Could not open file ' . $destinationFileName . ' for reading...');
                $fileContent = fread($fd, filesize($destinationFileName));
                fclose($fd);
                unlink($destinationFileName);

                /* Chiffrement des données à l'aide de l'algorithme AES-256-CBC et écriture dans le fichier */
                $encryptedData = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, 0, "secretivsecreti\0");

                if (strpos($key, "image")) {
                    sscanf($key, "dataimage%d", $num);
                    $data["dataimageoriginalName$num"] = $data[$key]->getClientOriginalName();
                }
                else if (strpos($key, "video")) {
                    sscanf($key, "datavideo%d", $num);
                    $data["datavideooriginalName$num"] = $data[$key]->getClientOriginalName();
                }
                else if (strpos($key, "file")) {
                    sscanf($key, "datafile%d", $num);
                    $data["datafileoriginalName$num"] = $data[$key]->getClientOriginalName();
                }

                $data[$key] = 'CRYPT_' . $encryptionKeyId . '_' . $fileName;
                $newDestinationFileName = $dir . '/' . $data[$key];
                $fd = fopen($newDestinationFileName, 'wb+') or die('Could not open file ' . $newDestinationFileName . ' for writing...');
                fwrite($fd, $encryptedData) or die('Could not write to file ' . $newDestinationFileName . '...');
                fclose($fd);

            }
        }

        foreach ($data as $key => $val) {
            if ($val === null) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
