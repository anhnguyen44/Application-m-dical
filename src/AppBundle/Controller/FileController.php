<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Asset\Exception\InvalidArgumentException;



class FileController extends Controller
{
    /**
     * @Route("/download/{type}", name="decrypt", requirements={"type"="video|image|file"})
     * @param $type
     * @param Request $request
     */
    public function decryptAction($type, Request $request)
    {
        //attention ne vérifie pas si l'utilisateur est autorisé à voir le fichier
        
        $encFilePath = $request->query->get('filePath');
        $realFileName = $request->query->get('fileName');
        $projDir = $this->get('kernel')->getProjectDir();

        $fileSource = $projDir . \DIRECTORY_SEPARATOR . 'web' . \DIRECTORY_SEPARATOR . 'upload' . \DIRECTORY_SEPARATOR;
        $fileSource .= $type . 's' . \DIRECTORY_SEPARATOR;
        $fileSource .= $encFilePath;

        $fileDest = $projDir . \DIRECTORY_SEPARATOR . 'var' . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR;
        $fileDest .= $encFilePath;

        // Si le fichier est chiffré, on le déchiffre
        if (strncmp($encFilePath, 'CRYPT_', 6) === 0) {

            $encryptionKeyId = explode('_', $encFilePath)[1];
            $em = $this->getDoctrine()->getManager();
            $keyFound = $em->getRepository('AppBundle:Crypto')->find($encryptionKeyId);
            if (!$keyFound) {
                throw new InvalidArgumentException('Impossible to find a key corresponding to file ' . $encFilePath);
            }

            $key = $keyFound->getEncryptionKey();
            $fdest = fopen($fileDest, 'wb');
            $data = file_get_contents($fileSource);
            $decryptedData = openssl_decrypt($data, 'AES-256-CBC', $key, 0, "secretivsecreti\0");
            fwrite($fdest, $decryptedData);
            fclose($fdest);
        
        } else {
            $fileDest = $fileSource;
        }

        $fileContent = file_get_contents($fileDest);
        $response = new Response($fileContent);
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $realFileName);
        $response->headers->set('Content-Disposition', $disposition);

        if ($type === 'image') {
            $response->headers->set('Content-type', 'image/jpeg');
        } elseif ($type === 'video') {
            $response->headers->set('Content-type', 'video/mp4');
        }

        unlink($fileDest);
        return $response;
    }

}
