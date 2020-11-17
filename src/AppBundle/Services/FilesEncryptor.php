<?php

namespace AppBundle\Services;

class FilesEncryptor
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

    public function encryptAllFiles($encryptionKeyId)
    {
        $repositoryPatient = $this->doctrine->getManager()->getRepository('AppBundle:Patient');
        $repositoryCrypto = $this->doctrine->getManager()->getRepository('AppBundle:Crypto');
        $crypto = $repositoryCrypto->findOneBy(array(
            'id' => $encryptionKeyId,
        ));
        $encryptionKey = $crypto->getEncryptionKey();
        /*On parcours la liste de tous les patients*/
        $patientlist=$repositoryPatient->findAll();

        $count=0;
        foreach ($patientlist as $patient)
        {
            $data = $patient->getData();
            /* On récupère les données du patient et on les encrypte au cas par cas -> images, vidéos, fichiers */
            for ($i = 0; $i < sizeof($data['images']); $i++)
            {
                if (file_exists($this->imagesDir . '/' .$data['images'][$i]['path'])){
                  $this->encryptFile($encryptionKey, $encryptionKeyId, $data['images'][$i]['path'], 'image',$count);
                }

            }
            for ($i = 0; $i < sizeof($data['videos']); $i++)
            {
                if (file_exists($this->videosDir . '/' .$data['videos'][$i]['path'])){
                  $this->encryptFile($encryptionKey, $encryptionKeyId, $data['videos'][$i]['path'], 'video',$count);
                }
            }
            for ($i = 0; $i < sizeof($data['files']); $i++)
            {
              if (file_exists($this->filesDir . '/' .$data['files'][$i]['path'])){
                $this->encryptFile($encryptionKey, $encryptionKeyId, $data['files'][$i]['path'], 'file',$count);
              }
            }

            $patient->setData($data);
            $em = $this->doctrine->getManager();
            $em->persist($patient);
            $em->flush();
        }
        if ($count==0){
          return -1;
        }

        return $count;  
    }

    public function encryptFile($encryptionKey, $encryptionKeyId, &$filePath, $fileType,&$count)
    {
        /* Si le fichier n'est pas chiffré */
        if (strncmp($filePath, 'CRYPT_', 6) != 0)
        {

            if ($fileType == 'image')
            {
                $filePathSource =  $this->imagesDir . '/' . $filePath;
            }
            else if ($fileType == 'video')
            {
                $filePathSource =  $this->videosDir . '/' . $filePath;
            }
            else if ($fileType == 'file')
            {
                $filePathSource =  $this->filesDir . '/' . $filePath;
            }

            /* Récupération du contenu du fichier en clair et suppression de celui-ci */
            $fdSrc = fopen($filePathSource, 'rb');
            $fileContent = fread($fdSrc, filesize($filePathSource));
            fclose($fdSrc);

            /* Chiffrement des données à l'aide de l'algorithme AES-256-CBC et écriture dans le fichier */
            $encryptedData = openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, 0, "secretivsecreti\0");

            //sscanf($key, 'data' . $fileType . '%d', $num);

            $filePath = 'CRYPT_' . $encryptionKeyId . '_' . $filePath;

            if ($fileType == 'image')
            {
                $filePathDest =  $this->imagesDir . '/' . $filePath;
            }
            else if ($fileType == 'video')
            {
                $filePathDest =  $this->videosDir . '/' . $filePath;
            }
            else if ($fileType == 'file')
            {
                $filePathDest =  $this->filesDir . '/' . $filePath;
            }


            /* Ecriture dans le nouveau fichier */
            $fdDest = fopen($filePathDest, 'wb+');
            fwrite($fdDest, $encryptedData);
            fclose($fdDest);

            unlink($filePathSource);
            $count++;
        }
    }
}
