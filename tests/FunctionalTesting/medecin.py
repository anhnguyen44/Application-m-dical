# -*- coding: utf-8 -*-
import time
import os

import test_utils

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.action_chains import ActionChains
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, re, run_tests

from selenium.webdriver.firefox.options import Options

options = Options()
options.headless = True
driver = webdriver.Firefox(options=options)
driver.implicitly_wait(7)
base_url = "http://webmedical/"
verificationErrors = []
accept_next_alert = True


class Login(unittest.TestCase):
    
    def test_login(self):
        driver.get(base_url)
        driver.find_element_by_id("_username").clear()
        driver.find_element_by_id("_username").send_keys("doc1")
        driver.find_element_by_id("_password").clear()
        driver.find_element_by_id("_password").send_keys("a")
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Password'])[1]/following::button[1]").click()
        self.assertEqual(u"Bienvenue sur la base de données locale de l'établissement.", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Dossier Medical'])[1]/following::p[1]").text)
    
    
class VerifierPresencePatients(unittest.TestCase):
    
    def test_verifier_presence_patients(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        patient_list = driver.find_element_by_id("patient_list").find_elements_by_tag_name("a")
        text_elements = [a.get_attribute("testvalue") for a in patient_list]
        self.assertEqual(text_elements, [
            "dd_Np5ZH3jh",
            "pp_ZxZ28G6c",
            "pp_12uDT6YI",
            "pp_hj8fMoUw",
            "pp_CfUHnJQM",
            "pp_ZR8JZdhQ",
            "pp_Qm16OsEi",
            "pp_PXWZuoDB",
            "sd_pJAxF8pb",
            "ta_ykr9L29F"
        ])


class AjouterPatientMin(unittest.TestCase):

    def test_ajouter_patient_min(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un dossier").click()
        driver.find_element_by_id("appbundle_member_nom").clear()
        driver.find_element_by_id("appbundle_member_nom").send_keys("katalon")
        driver.find_element_by_id("appbundle_member_prenom").clear()
        driver.find_element_by_id("appbundle_member_prenom").send_keys("minimum")
        
        element = driver.find_element_by_class_name("_test_sumit")
        driver.execute_script("arguments[0].click();", element)
        time.sleep(1)
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le dossier a été ajouté avec succès !")

class AjouterPatient(unittest.TestCase):
    
    def test_ajouter_patient(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un dossier").click()
        driver.find_element_by_id("appbundle_member_nom").clear()
        driver.find_element_by_id("appbundle_member_nom").send_keys("katalon")
        driver.find_element_by_id("appbundle_member_prenom").clear()
        driver.find_element_by_id("appbundle_member_prenom").send_keys("test")
        driver.find_element_by_id("appbundle_member_email").clear()
        driver.find_element_by_id("appbundle_member_email").send_keys("katalon.test@insa-cvl.fr")
        driver.find_element_by_id("appbundle_member_socialNumber").clear()
        driver.find_element_by_id("appbundle_member_socialNumber").send_keys("010203040506070")
        driver.find_element_by_id("appbundle_member_medecinTraitant").clear()
        driver.find_element_by_id("appbundle_member_medecinTraitant").send_keys("doc1")

        element = driver.find_element_by_class_name("_test_sumit")
        driver.execute_script("arguments[0].click();", element)
        time.sleep(1)
        self.assertEqual(u"Le dossier a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un patient'])[1]/following::div[1]").text)
    

class ConsulterUnDossier(unittest.TestCase):
    
    def test_consulter_un_dossier(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat1 pat1").click()

        self.assertEqual("pat1 pat1", driver.find_element_by_class_name("_test_nom").text)
        self.assertEqual("pat@1.com", driver.find_element_by_class_name("_test_email").text)
        self.assertEqual("010203040506071", driver.find_element_by_class_name("_test_secu").text)
        self.assertEqual("Non", driver.find_element_by_class_name("_test_visible").text)
    

class ConsulterEtModifierUnDossier(unittest.TestCase):
    
    def test_consulter_et_modifier_un_dossier(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat1 pat1").click()
        driver.find_element_by_class_name("_test_button_edit").click()
        driver.find_element_by_id("appbundle_member_tel").clear()
        driver.find_element_by_id("appbundle_member_tel").send_keys("0102030406")

        element = driver.find_element_by_class_name("_test_sumit")
        driver.execute_script("arguments[0].click();", element)
        time.sleep(1)
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le dossier a été modifié avec succès !"
        )


class ConsulterEtArchiverUnDossier(unittest.TestCase):

    def test_consulter_et_archiver_un_dossier(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat1 pat1").click()
        driver.find_element_by_class_name("_test_button_archive").click()
        self.assertRegex(self.close_alert_and_get_its_text(), r"^Êtes-vous sûr de vouloir archiver le dossier [\s\S]$")
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le dossier a été archivé avec succès !"
        )

    def close_alert_and_get_its_text(self):
        try:
            alert = driver.switch_to.alert
            alert_text = alert.text
            global accept_next_alert
            if accept_next_alert :
                alert.accept()
            else:
                alert.dismiss()
            return alert_text
        finally: accept_next_alert = True


class ConsulterEtSupprimerUnDossier(unittest.TestCase):
    
    def test_consulter_et_supprimer_un_dossier(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("doc delete").click()
        driver.find_element_by_class_name("_test_button_delete").click()
        self.assertRegex(self.close_alert_and_get_its_text(), r"^Êtes-vous sûr de vouloir supprimer définitivement le dossier [\s\S]$")
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le dossier a été supprimé avec succès !"
        )
            
    def close_alert_and_get_its_text(self):
        try:
            alert = driver.switch_to.alert
            alert_text = alert.text
            global accept_next_alert
            if accept_next_alert :
                alert.accept()
            else:
                alert.dismiss()
            return alert_text
        finally: accept_next_alert = True    

    

class AjouterCommentaireAuDossier(unittest.TestCase):
    
    def test_ajouter_commentaire_au_dossier(self):
        driver.get(base_url)
        
        # Sélection du patient
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat2 pat2").click()
        driver.find_element_by_link_text("Ajouter un commentaire").click()
        driver.find_element_by_id("form_data").clear()
        driver.find_element_by_id("form_data").send_keys("Katalon test comment")
        driver.find_element_by_class_name("_test_sumit").click()

        element = driver.find_element_by_class_name("_test_link_toggle_Appareillage")
        driver.execute_script("arguments[0].click();", element)
        time.sleep(1)
        self.assertEqual("Katalon test comment", driver.find_element_by_id("appbundle_member_data").text)
    
class AjouterUneImageAuDossier(unittest.TestCase):
    def test_ajouter_une_image_au_dossier(self):
        driver.get(base_url)
        test_utils.hide_symfony_devbar(driver)
        
        # Sélection du patient
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat2 pat2").click()
        
        # Ajout d'une image au dossier
        driver.find_element_by_link_text("Ajouter une photo").click()
        driver.find_element_by_id("form_data").send_keys(os.path.join(os.getcwd(), "..", "test_files", "test_picture.png"))
        driver.find_element_by_class_name("_test_sumit").click()
        
        # On vient d'envoyer le formulaire, on vérifie que tout est bien là 
        test_utils.hide_symfony_devbar(driver)
        driver.find_element_by_xpath(".//*[@href='#collapseAppareillage']/..").click()
        other_data_section = driver.find_element_by_id("collapseAppareillage")
        first_image_section = driver.find_element_by_xpath("//*[@name='appbundle_member[dataimage0]']")
        self.assertEqual(1, len(other_data_section.find_elements_by_tag_name("img")))
        self.assertIn("Par doc1", first_image_section.find_element_by_tag_name("label").text)


class AjouterUneVideoAuDossier(unittest.TestCase):
    def test_ajouter_une_video_au_dossier(self):
        driver.get(base_url)
        
        # Sélection du patient
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat2 pat2").click()

        # Ajout d'une vidéo au dossier
        test_utils.hide_symfony_devbar(driver)
        driver.find_element_by_link_text("Ajouter une vidéo").click()
        driver.find_element_by_id("form_data").send_keys(os.path.join(os.getcwd(), "..", "test_files", "test_video.mp4"))
        driver.find_element_by_class_name("_test_sumit").click()
        
        # On vient d'envoyer le formulaire, on vérifie que tout est bien là 
        test_utils.hide_symfony_devbar(driver)
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            "Le dossier a été modifié avec succès !"
        )
        driver.find_element_by_xpath(".//*[@href='#collapseAppareillage']/..").click()
        other_data_section = driver.find_element_by_id("collapseAppareillage")
        self.assertEqual(1, len(other_data_section.find_elements_by_tag_name("video")))


class AjouterUnFichierAuDossier(unittest.TestCase):
    def test_ajouter_un_fichier_au_dossier(self):
        driver.get(base_url)
        
        # Sélection du patient
        driver.find_element_by_link_text("Mes dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat2 pat2").click()

        # Ajout d'un fichier au dossier
        test_utils.hide_symfony_devbar(driver)
        driver.find_element_by_link_text("Ajouter un fichier").click()
        driver.find_element_by_id("form_data").send_keys(os.path.join(os.getcwd(), "..", "test_files", "test_file.pdf"))
        driver.find_element_by_class_name("_test_sumit").click()
        
        # On vient d'envoyer le formulaire, on vérifie que tout est bien là 
        test_utils.hide_symfony_devbar(driver)
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            "Le dossier a été modifié avec succès !"
        )
        driver.find_element_by_xpath(".//*[@href='#collapseAppareillage']/..").click()
        other_data_section = driver.find_element_by_id("collapseAppareillage")
        # self.assertEqual(1, len(other_data_section.find_elements_by_tag_name("a")))
        
        other_data_section.find_element_by_link_text("test_file.pdf")
        # self.assertEqual("test_file.pdf", other_data_section.find_element_by_tag_name("a").text)
        # Bon j'arrive pas à faire fonctionner la regex ..
        # self.assertRegexpMatches(
        #     r".*\/download\/file\?&filePath=.*&fileName=test_file\.pdf",
        #     other_data_section.find_element_by_tag_name("a").get_attribute("href")
        # )




class DossierArchiveInfo(unittest.TestCase):

    def test_dossier_archive_info(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Les archives").click()
        driver.find_element_by_link_text(u"Consulter un dossier archivé").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("archived a").click()

        self.assertEqual("a archived", driver.find_element_by_class_name("_test_nom").text)
        self.assertEqual("a@archived.com", driver.find_element_by_class_name("_test_email").text)
        self.assertEqual("1 rue des archives", driver.find_element_by_class_name("_test_adresse").text)
        self.assertEqual("Non", driver.find_element_by_class_name("_test_visible").text)
    

class DesarchiverDossier(unittest.TestCase):
    
    def test_desarchiver_dossier(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Les archives").click()
        driver.find_element_by_link_text(u"Consulter un dossier archivé").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("archived a").click()
        driver.find_element_by_class_name("_test_button_unarchive").click()
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le dossier a été désarchivé avec succès !"
        )

class DossierArchiveSuppression(unittest.TestCase):

    def test_dossier_archive_suppression(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Les archives").click()
        driver.find_element_by_link_text(u"Consulter un dossier archivé").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("archived b").click()
        driver.find_element_by_class_name("_test_button_delete").click()
        self.close_alert_and_get_its_text()
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le dossier a été supprimé avec succès !"
        )
    
    def close_alert_and_get_its_text(self):
        try:
            alert = driver.switch_to.alert
            alert_text = alert.text
            global accept_next_alert
            if accept_next_alert :
                alert.accept()
            else:
                alert.dismiss()
            return alert_text
        finally: accept_next_alert = True


class PartagerUnDossierUnEval(unittest.TestCase):

    def test_partager_un_dossier_un_eval(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Partager un dossier").click()
        Select(driver.find_element_by_id("evaluatorId")).select_by_visible_text("Ergotherapie : par1")
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("part1 part1 - pp_ZxZ28G6c")
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partage temporaire :'])[1]/following::input[3]").click()
        driver.find_element_by_id("appbundle_acl_Partager").click()
        self.assertEqual(u"Le partage a été effectué avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partager un dossier'])[2]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part1 part1 avec par1.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
    

class PartagerUnDossierPlusieursEval(unittest.TestCase):

    def test_partager_un_dossier_plusieurs_eval(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Partager un dossier").click()
        Select(driver.find_element_by_id("evaluatorId")).select_by_visible_text(u"Médecin : doc2")
        Select(driver.find_element_by_id("evaluatorId")).select_by_visible_text(u"Musicothérapie : par2")
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("part1 part1 - pp_ZxZ28G6c")
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partage temporaire :'])[1]/following::input[3]").click()
        driver.find_element_by_id("appbundle_acl_Partager").click()
        self.assertEqual(u"Le partage a été effectué avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partager un dossier'])[2]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part1 part1 avec doc2.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part1 part1 avec par2.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
    

class PartagerPlusieursDossiersUnEval(unittest.TestCase):

    def test_partager_plusieurs_dossiers_un_eval(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Partager un dossier").click()
        Select(driver.find_element_by_id("evaluatorId")).select_by_visible_text("Ergotherapie : par1")
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("part2 part2 - pp_12uDT6YI")
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("part3 part3 - pp_hj8fMoUw")
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partage temporaire :'])[1]/following::input[3]").click()
        driver.find_element_by_id("appbundle_acl_Partager").click()
        self.assertEqual(u"Le partage a été effectué avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partager un dossier'])[2]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part2 part2 avec par1.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part3 part3 avec par1.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
    

class PartagerPlusieursDossiersPlusieursEval(unittest.TestCase):

    def test_partager_plusieurs_dossiers_plusieurs_eval(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Partager un dossier").click()
        Select(driver.find_element_by_id("evaluatorId")).select_by_visible_text(u"Médecin : doc2")
        Select(driver.find_element_by_id("evaluatorId")).select_by_visible_text(u"Musicothérapie : par2")
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("part2 part2 - pp_12uDT6YI")
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("part3 part3 - pp_hj8fMoUw")
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partage temporaire :'])[1]/following::input[3]").click()
        driver.find_element_by_id("appbundle_acl_Partager").click()
        self.assertEqual(u"Le partage a été effectué avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Partager un dossier'])[2]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part2 part2 avec doc2.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part3 part3 avec doc2.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part2 part2 avec par2.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
        self.assertNotEqual(u"Vous avez déjà partagé le dossier de part3 part3 avec par2.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le partage a été effectué avec succès !'])[1]/following::div[1]").text)
    

class VerifierDossiersPublics(unittest.TestCase):

    def test_verifier_dossiers_publics(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Dossiers partagés").click()
        driver.find_element_by_link_text("Les dossiers publics").click()
        text_elements = list()
        all_options = driver.find_element_by_id("patientId").find_elements_by_tag_name("option")
        for option in all_options:
            text_elements.append(option.get_attribute("value"))
        self.assertEqual(text_elements, [
            "dd_Np5ZH3jh",
            "pp_ZxZ28G6c",
            "pp_12uDT6YI",
            "pp_hj8fMoUw",
            "pp_CfUHnJQM",
            "pp_ZR8JZdhQ",
            "pp_Qm16OsEi",
            "pp_PXWZuoDB",
            "sd_pJAxF8pb"
        ])
    

class ConsulterDossierPartage(unittest.TestCase):

    def test_consulter_dossier_partage(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Dossiers partagés").click()
        driver.find_element_by_link_text(u"Mes dossiers partagés").click()
        self.assertEqual("pp_ZxZ28G6c", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Annuler le partage'])[1]/following::td[1]").text)
        self.assertEqual("Ergotherapie", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='par1'])[1]/following::td[1]").text)


class ChangerPassword(unittest.TestCase):
    
    def test_changer_password(self):
        driver.get(base_url)
        driver.find_element_by_link_text("doc1").click()
        driver.find_element_by_link_text("Changer le mot de passe").click()
        driver.find_element_by_id("appbundle_user_plainPassword_first").clear()
        driver.find_element_by_id("appbundle_user_plainPassword_first").send_keys("bbbbbb")
        driver.find_element_by_id("appbundle_user_plainPassword_second").clear()
        driver.find_element_by_id("appbundle_user_plainPassword_second").send_keys("bbbbbb")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"Le mot de passe a été modifié avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Modifier votre mot de passe'])[1]/following::div[1]").text)
    

class SendBugReport(unittest.TestCase):
    
    def test_send_bug_report(self):
        driver.get(base_url)
        driver.find_element_by_link_text("doc1").click()
        self.assertEqual("Rapporter un bogue", driver.find_element_by_link_text("Rapporter un bogue").text)


class Logout(unittest.TestCase):
    
    def test_logout(self):
        driver.get(base_url)
        driver.find_element_by_link_text("doc1").click()
        driver.find_element_by_link_text(u"Déconnexion").click()
        self.assertEqual(u"MesureLocal (c) Pour toute utilisation, copie ou modification de ce programme vous devez obtenir préalablement l'autorisation auprès de christian.toinard@insa-cvl.fr", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Username'])[1]/preceding::h2[1]").text)
    
    def tearDown(self):
        driver.quit()
        self.assertEqual([], verificationErrors)


test_cases = [
    Login, VerifierPresencePatients, 
    AjouterPatientMin, AjouterPatient, ConsulterUnDossier, ConsulterEtModifierUnDossier, ConsulterEtArchiverUnDossier, ConsulterEtSupprimerUnDossier,
    AjouterCommentaireAuDossier, AjouterUneImageAuDossier, AjouterUneVideoAuDossier, AjouterUnFichierAuDossier,
    DossierArchiveInfo, DesarchiverDossier, DossierArchiveSuppression,
    PartagerUnDossierUnEval, PartagerUnDossierPlusieursEval, PartagerPlusieursDossiersUnEval, PartagerPlusieursDossiersPlusieursEval,
    ConsulterDossierPartage,
    ChangerPassword, SendBugReport, Logout
]

if __name__ == "__main__":
    run_tests.run_tests(test_cases)