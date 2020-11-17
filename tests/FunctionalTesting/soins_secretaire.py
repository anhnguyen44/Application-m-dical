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
        driver.find_element_by_id("_username").send_keys("sec1")
        driver.find_element_by_id("_password").clear()
        driver.find_element_by_id("_password").send_keys("a")
        driver.find_element_by_xpath(
            "(.//*[normalize-space(text()) and normalize-space(.)='Password'])[1]/following::button[1]").click()
        self.assertEqual(u"Bienvenue sur la base de données locale de l'établissement.", driver.find_element_by_xpath(
            "(.//*[normalize-space(text()) and normalize-space(.)='Dossier Medical'])[1]/following::p[1]").text)

class AjouterSoinPatient(unittest.TestCase):

    def test_ajouter_soin_patient(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Gestion des dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat1 pat1").click()
        driver.find_element_by_class_name("_test_button_healthcare").click()
        driver.find_element_by_class_name("_test_ajout_soin").click()

        driver.find_element_by_id("health_care_form_name").clear()
        driver.find_element_by_id("health_care_form_name").send_keys("Mon nouveau soin")
        driver.find_element_by_id("health_care_form_description").clear()
        driver.find_element_by_id("health_care_form_description").send_keys("Test ajout soin")
        driver.find_element_by_id("health_care_form_sessionCount").clear()
        driver.find_element_by_id("health_care_form_sessionCount").send_keys("2")

        element = driver.find_element_by_class_name("_test_sumit")
        driver.execute_script("arguments[0].click();", element)
        time.sleep(1)
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le soin a été ajouté avec succès !")


class ValiderSeance(unittest.TestCase):

    def test_valider_seance(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Gestion des dossiers").click()
        driver.find_element_by_link_text("Consulter un dossier").click()
        driver.find_element_by_id("patient_list").find_element_by_link_text("pat1 pat1").click()
        driver.find_element_by_class_name("_test_button_healthcare").click()
        driver.find_element_by_class_name("_test_button_see_more").click()

        self.assertEqual("Mon soin", driver.find_element_by_class_name("_test_nom_du_soin").text)
        self.assertEqual("Test description soin", driver.find_element_by_class_name("_test_description_soin").text)
        self.assertEqual("Ergotherapie", driver.find_element_by_class_name("_test_specialite_soin").text)
        self.assertEqual("doc1", driver.find_element_by_class_name("_test_valide_medical_seance").text)
        self.assertEqual("Test commentaire séance", driver.find_element_by_class_name("_test_valide_commentaire").text)

        driver.find_element_by_class_name("_test_fermer_soin").click()
        time.sleep(1)
        self.assertEqual(
            driver.find_element_by_css_selector("body .page-body .container .flash-notice.alert.alert-success").text,
            u"Le soin a été fermé avec succès !")

test_cases = [
    Login, ValiderSeance, AjouterSoinPatient,
]

if __name__ == "__main__":
    run_tests.run_tests(test_cases)