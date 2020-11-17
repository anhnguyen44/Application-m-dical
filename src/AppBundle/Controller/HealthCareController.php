<?php
     
namespace AppBundle\Controller;

use AppBundle\Entity\HealthCare;
use AppBundle\Entity\HealthSession;
use AppBundle\Form\Type\HealthCareFormType;
use AppBundle\Form\Type\HealthSessionFormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class HealthCareController extends Controller {
    

    /**
    * @Route("{patientId}/healthcare/", name="viewcare", requirements={"patientId"="\d+"})
    * @return \Symfony\Component\HttpFoundation\Response
    * @throws \LogicException
    */
    public function viewHealthCareAction($patientId, Request $request) {
        date_default_timezone_set('Europe/Paris');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $patient = $em->getRepository('AppBundle:Patient')->findOneById($patientId);
        
        if (!$patient) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }

        $permission = $this->container->get('database.permission');
        if (!$permission->patient($user, $patient)) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }

        $healthcares = $patient->getHealthcares();
        if (in_array('PARAMEDICAL', $user->getRoles())) {
            $spe = $user->getSpeciality();
            $healthcares = $healthcares->filter(function($var) use ($spe) { return($var->getSpeciality() == $spe); });
            return $this->render("healthcare/view/secretary.html.twig", array('patient' => $patient, 'healthcares' => $patient->getHealthcares()));
        } 
            
        return $this->render("healthcare/view.html.twig", array('patient' => $patient, 'healthcares' => $healthcares));
    

    }
    



    /**
    * @Route("{patientId}/healthcare/add", name="addcare", requirements={"patientId"="\d+"})
    * @return \Symfony\Component\HttpFoundation\Response
    * @throws \LogicException
    */
    public function addCareAction($patientId, Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $patient = $em->getRepository('AppBundle:Patient')->findOneById($patientId);
        
        if (!$patient) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }

        $permission = $this->container->get('database.permission');
        if (!$permission->patient($user, $patient)) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }
        
        $healthcare = new HealthCare();
        $healthcare->setPatient($patient);

        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->neq('role', 'ROLE_MEDICAL'));
        $criteria->andWhere(Criteria::expr()->neq('role', 'ROLE_SECRETARY'));
        $specialities = $em->getRepository('AppBundle:Speciality')->matching($criteria);
        
        $form = $this->createForm(HealthCareFormType::class, $healthcare, array('specialities'=>$specialities));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($healthcare);
            $em->flush();
            $this->addFlash('notice', "Le soin a été ajouté avec succès !");
            return $this->redirectToRoute('viewcare', array('patientId' => $patientId));
        }
        return $this->render("healthcare/forms/healthcare.html.twig", array(
            'title' => 'Ajouter un soin',
            'form' => $form->createView(),
        ));
    }




    /**
    * @Route("{patientId}/healthcare/{healthcareId}/close", name="closecare", requirements={"healthcareId"="\d+"})
    * @return \Symfony\Component\HttpFoundation\Response
    * @throws \LogicException
    */
    public function closeCareAction($healthcareId, Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $healthcare = $em->getRepository('AppBundle:HealthCare')->findOneById($healthcareId);
        
        if (!$healthcare) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }

        $permission = $this->container->get('database.permission');
        if (!$permission->healthcare($user, $healthcare)) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }
        
        $healthcare->setClosed(true);
        $em->persist($healthcare);
        $em->flush();
        $this->addFlash('notice', "Le soin a été fermé avec succès !");
        return $this->redirectToRoute('viewcare', array('patientId' => $healthcare->getPatient()->getId()));
    }




    /**
    * @Route("{patientId}/healthcare/{healthcareId}/addsession", name="addsession", requirements={"healthcareId"="\d+"})
    * @return \Symfony\Component\HttpFoundation\Response
    * @throws \LogicException
    */
    public function addSessionAction($healthcareId, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $healthcare = $em->getRepository('AppBundle:HealthCare')->findOneById($healthcareId);
        
        if (!$healthcare) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }

        $permission = $this->container->get('database.permission');
        if (!$permission->healthcare($user, $healthcare)) {
            $this->addFlash('alert', "Vous ne pouvez pas accéder à ce dossier");
            return $this->redirectToRoute('homepage');
        }

        $healthsession = new HealthSession();
        $healthsession->setHealthcare($healthcare);
        $healthsession->setCaregiver($user);

        $form = $this->createForm(HealthSessionFormType::class, $healthsession);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            //Véfication sur le nombre de séance par mois
            // $count = $healthcare->countDate($healthsession->getDate());
            // if ($count+1 > $healthcare->getSessionCount()) {
            //     $this->addFlash('alert', "Le compte de séance est atteinte pour ce mois !");
            //     return $this->render("healthcare/forms/healthsession.html.twig", array(
            //         'title' => 'Valider une séance pour le soin ' . $healthcare->getName(),
            //         'form' => $form->createView(),
            //     ));
            // }

            $em->persist($healthsession);
            $em->flush();
            $this->addFlash('notice', "La séance a été valider avec succès !");
            return $this->redirectToRoute('viewcare', array('patientId' => $healthcare->getPatient()->getId()));
        }


        return $this->render("healthcare/forms/healthsession.html.twig", array(
            'title' => 'Valider une séance pour le soin ' . $healthcare->getName(),
            'form' => $form->createView(),
        ));

    }




    /**
    * @Route("{patientId}/healthcare/{healthcareId}/deletesession/{sessionId}", name="deletesession", requirements={"sessionId"="\d+"})
    * @return \Symfony\Component\HttpFoundation\Response
    * @throws \LogicException
    */
    public function deletesessionAction($sessionId, Request $request) {
    }
    
    
    
}