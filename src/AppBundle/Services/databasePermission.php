<?php

namespace AppBundle\Services;

class databasePermission {

    private $em;
    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }


    public function patient($user, $patient) {

        if ($patient->isPublic()) {
            return true;
        }

        if(in_array('ROLE_SECRETARY', $user->getRoles())) {
            return true;
        }

        if(in_array('ROLE_MEDICAL', $user->getRoles())) {
            if($user == $patient->getOwner()) {
                return true;
            }
        }

        if (in_array('ROLE_MEDICAL', $user->getRoles()) || in_array('ROLE_PARAMEDICAL', $user->getRoles())) {

            $acl = $this->em->getRepository('AppBundle:ACL')->findOneBy(['evaluator' => $user, 'idPatient' => $patient->getPatientId()]);
            if ($acl !== null) {
                return true;
            }
        }

        return false;

    }



    public function healthcare($user, $healthcare) {
    
        if ($this->patient($user, $healthcare->getPatient())) {

            if (in_array('ROLE_PARAMEDICAL', $user->getRoles())) {
                
                if ($healthcare->getSpeciality() == $user->getSpeciality()) {
                    return true;
                }

            } else {
                return true;
            }

        }

        return false;

    }

}
