# -*- coding: utf-8 -*-
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, time, re, run_tests

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
        driver.find_element_by_id("_username").send_keys("par1")
        driver.find_element_by_id("_password").clear()
        driver.find_element_by_id("_password").send_keys("a")
        driver.find_element_by_xpath("//button[@type='submit']").click()
        self.assertEqual(u"Bienvenue sur la base de données locale de l'établissement.", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Dossier Medical'])[1]/following::p[1]").text)


class ViewSharedFile(unittest.TestCase):
    
    def test_view_shared_file(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Dossiers partagés avec moi").click()
        driver.find_element_by_css_selector(".btn.btn-primary._test_consult_patient").click()
        self.assertEqual("pat3 pat3", driver.find_element_by_class_name("_test_nom").text)
    

class AddCommentToSharedFile(unittest.TestCase):

    def test_add_comment_to_shared_file(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Dossiers partagés avec moi").click()
        driver.find_element_by_css_selector(".btn.btn-primary._test_consult_patient").click()
        driver.find_element_by_link_text("Ajouter un commentaire").click()
        driver.find_element_by_id("form_data").clear()
        driver.find_element_by_id("form_data").send_keys("Katalon test comment")
        driver.find_element_by_class_name("_test_sumit").click()
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            "La demande de modification a été soumise avec succès !"
        )


class SendBugReport(unittest.TestCase):
    
    def test_send_bug_report(self):
        driver.get(base_url)
        driver.find_element_by_link_text("par1").click()
        self.assertEqual("Rapporter un bogue", driver.find_element_by_link_text("Rapporter un bogue").text)
    

class ChangerMotDePasse(unittest.TestCase):
    
    def test_changer_mot_de_passe(self):
        driver.get(base_url)
        driver.find_element_by_link_text("par1").click()
        driver.find_element_by_link_text("Changer le mot de passe").click()
        driver.find_element_by_id("appbundle_user_plainPassword_first").clear()
        driver.find_element_by_id("appbundle_user_plainPassword_first").send_keys("bbbbbb")
        driver.find_element_by_id("appbundle_user_plainPassword_second").clear()
        driver.find_element_by_id("appbundle_user_plainPassword_second").send_keys("bbbbbb")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"Le mot de passe a été modifié avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Modifier votre mot de passe'])[1]/following::div[1]").text)
    
class VoirNotification(unittest.TestCase):
    
    def test_voir_notification(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Notifications").click()
        self.assertEqual(u"Dr. doc1 a partagé le dossier du patient pat1 pat1 avec vous.", driver.find_element_by_class_name("_test_share_notification").text)
    

class Logout(unittest.TestCase):
    
    def test_logout(self):
        driver.get(base_url)
        driver.find_element_by_link_text("par1").click()
        driver.find_element_by_link_text(u"Déconnexion").click()
        self.assertEqual(u"MesureLocal (c) Pour toute utilisation, copie ou modification de ce programme vous devez obtenir préalablement l'autorisation auprès de christian.toinard@insa-cvl.fr", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Username'])[1]/preceding::h2[1]").text)

    def tearDown(self):
        driver.quit()
        self.assertEqual([], verificationErrors)

test_cases = [
    Login, ViewSharedFile, AddCommentToSharedFile, 
    SendBugReport, ChangerMotDePasse, VoirNotification, Logout 
]

if __name__ == "__main__":
    run_tests.run_tests(test_cases)