# -*- coding: utf-8 -*-
import time

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, time, re, run_tests

from selenium.webdriver.firefox.options import Options

options = Options()
options.headless = False
driver = webdriver.Firefox(options=options)
driver.implicitly_wait(7)
base_url = "http://webmedical/"
verificationErrors = []
accept_next_alert = True


class Login(unittest.TestCase):
    
    def test_login(self):
        driver.get(base_url)
        driver.find_element_by_id("_username").clear()
        driver.find_element_by_id("_username").send_keys("sec1")
        driver.find_element_by_id("_password").clear()
        driver.find_element_by_id("_password").send_keys("a")
        driver.find_element_by_xpath("//button[@type='submit']").click()
        self.assertEqual(u"Bienvenue sur la base de données locale de l'établissement.", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Dossier Medical'])[1]/following::p[1]").text)


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


class AjouterDossier(unittest.TestCase):
    
    def test_ajouter_dossier(self):
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
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le dossier a été ajouté avec succès !")


class ConsulterUnDossier(unittest.TestCase):
    
    def test_consulter_un_dossier(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Gestion des dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat1 pat1").click()

        self.assertEqual("pat1 pat1", driver.find_element_by_class_name("_test_nom").text)
        self.assertEqual("pat@1.com", driver.find_element_by_class_name("_test_email").text)
        self.assertEqual("010203040506071", driver.find_element_by_class_name("_test_secu").text)
        self.assertEqual("Non", driver.find_element_by_class_name("_test_visible").text)

class ConsulterEtModifierUnDossier(unittest.TestCase):
    
    def test_consulter_et_modifier_un_dossier(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Gestion des dossiers").click()
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
        driver.find_element_by_link_text("Gestion des dossiers").click()
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
        driver.find_element_by_link_text("Gestion des dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("doc delete").click()
        driver.find_element_by_class_name("_test_button_delete").click()
        self.assertRegex(self.close_alert_and_get_its_text(),
                         r"^Êtes-vous sûr de vouloir supprimer définitivement le dossier [\s\S]$")
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
        driver.find_element_by_link_text("Gestion des dossiers").click()
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


class ChangerLeMedecinSoignant(unittest.TestCase):
    
    def test_changer_le_medecin_soignant(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Gestion des dossiers").click()
        driver.find_element_by_link_text(u"Changer le médecin soignant").click()
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("pat6 pat6")
        Select(driver.find_element_by_id("proId")).select_by_visible_text("doc1")
        driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Changer le médecin soignant'])[2]/following::input[1]").click()
        self.assertEqual(u"La modification a été effectuée avec succès.", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Changer le médecin soignant'])[2]/following::div[1]").text)



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


class ChangerPassword(unittest.TestCase):
    
    def test_changer_password(self):
        driver.get(base_url)
        driver.find_element_by_link_text("sec1").click()
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
        driver.find_element_by_link_text("sec1").click()
        self.assertEqual("Rapporter un bogue", driver.find_element_by_link_text("Rapporter un bogue").text)


class Logout(unittest.TestCase):
    
    def test_logout(self):
        driver.get(base_url)
        driver.find_element_by_link_text("sec1").click()
        driver.find_element_by_link_text(u"Déconnexion").click()
        self.assertEqual(u"MesureLocal (c) Pour toute utilisation, copie ou modification de ce programme vous devez obtenir préalablement l'autorisation auprès de christian.toinard@insa-cvl.fr", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Username'])[1]/preceding::h2[1]").text)
    
    def tearDown(self):
        driver.quit()
        self.assertEqual([], verificationErrors)


test_cases = [
    Login, AjouterPatientMin, AjouterDossier, ConsulterUnDossier, ConsulterEtModifierUnDossier,
    ConsulterEtArchiverUnDossier, ConsulterEtSupprimerUnDossier,
    AjouterCommentaireAuDossier, ChangerLeMedecinSoignant, DossierArchiveInfo,
    DesarchiverDossier, DossierArchiveSuppression,
    ChangerPassword, SendBugReport, Logout
]

if __name__ == "__main__":
    run_tests.run_tests(test_cases)