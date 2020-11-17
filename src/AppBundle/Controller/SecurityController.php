<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\SecurityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

//Efface récursivement le dossier passé en paramètre
function delTree($dir){
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file){
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

class SecurityController extends Controller
{
    /**
     * @Route ("/login",name="login")
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->render('security/login.html.twig', [
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout")
     * @throws \RuntimeException
     */
    public function logoutAction()
    {
        throw new \RuntimeException("");
    }


    /**
     * Cette fonction permet à l'utilisateur de modifier son mot de passe.
     * @Route("/changepassword", name="changepassword")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePasswordAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(SecurityType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /*
             * Le mot de passe n'est pas stoqué en clair, il est encodé/chiffrer d'abord.
             * L'algorithme utilisé est précisé dans app/config/security.yml
             *
             * security:
             *       encoders:
             *          AppBundle\Entity\User:
             *              algorithm: bcrypt
             *
             */
            $password = $this
                ->get('security.password_encoder')
                ->encodePassword(
                    $user,
                    $user->getPlainPassword()
                );
            $user->setPassword($password);

            // Modifier la bdd puis enregistrer les modifications.
            $em->merge($user);
            $em->flush();
            $this->addFlash('notice', "Le mot de passe a été modifié avec succès !");
            return $this->redirectToRoute('changepassword');
        }

        return $this->render("security/changePassword.html.twig", array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/encryptAllFiles", name="encryptAllFiles")
     * @return Response
     */
    public function encryptAllFilesAction(Request $request)
    {
        
        $encryptionKeyId = 1; // Il n'y a qu'une clé pour l'instant, donc on fixe $encryptionKeyId à 1.
        if ($request->get('encrypt')) //Si on a un paramètre GET/POST (peu importe sa valeur)
        {
            $fileCount = $this->get('files.encryptor')->encryptAllFiles($encryptionKeyId); //On appelle le service FilesEncryptor
            if ($fileCount < 0) {
                $this->addFlash('alert', "Tous les fichiers sont déjà cryptés !");
            } elseif ($fileCount === 1) {
                $this->addFlash('notice', $fileCount . " fichier a été crypté avec succès !");
            } else {
                $this->addFlash('notice', $fileCount . " fichiers ont été cryptés avec succès !");
            }
            return $this->redirectToRoute('encryptAllFiles');
        }

        return $this->render('admin/encryptAllFiles.html.twig');
    }
}
