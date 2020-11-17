<?php
// src/DataFixtures/AppFixtures.php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\Speciality;
use AppBundle\Entity\Crypto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture {

    public function load(ObjectManager $manager) {
        $specialities = array(
            array('Médecin',            'ROLE_MEDICAL',         'medical'),
            array('Secrétaire',         'ROLE_SECRETARY',       'medical'),
            array('Ergotherapie',       'ROLE_PARAMEDICAL',     'paramedical'),
            array('Kinésithérapie',     'ROLE_PARAMEDICAL',     'paramedical'),
            array('Psychologue',        'ROLE_PARAMEDICAL',     'paramedical'),
            array('Psychomotricité',    'ROLE_PARAMEDICAL',     'paramedical'),
            array('Neuropsychologue',   'ROLE_PARAMEDICAL',     'paramedical'),
            array('Musicothérapie',     'ROLE_PARAMEDICAL',     'paramedical'),
            array('Orthophonie',        'ROLE_PARAMEDICAL',     'paramedical')
        );
        
        foreach ($specialities as $value) {
            $obj = new Speciality();
            $obj->setSpeciality($value[0]);
            $obj->setRole($value[1]);
            $obj->setOccupation($value[2]);
            $manager->persist($obj);
        }

        
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 16; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $obj = new Crypto();
        $obj->setEncryptionKey($randomString);
        $manager->persist($obj);

        $manager->flush();
    }
}