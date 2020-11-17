# -*- coding: utf-8 -*-
import time
import datetime
import shutil
import os
import test_utils

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
from selenium.common.exceptions import TimeoutException
import unittest, time, re, run_tests

from selenium.webdriver.firefox.options import Options


def define_firefoxe_profile():
    fp = webdriver.FirefoxProfile()

    # Pour que le téléchargement des fichiers se fasse sans fenetre supplémentaire
    fp.set_preference("browser.download.folderList", 2)
    fp.set_preference("browser.download.manager.showWhenStarting", False)
    fp.set_preference("browser.download.dir", os.getcwd())
    fp.set_preference("browser.helperApps.neverAsk.saveToDisk", "application/x-7z-compressed")

    return fp


options = Options()
options.headless = False
driver = webdriver.Firefox(options=options, firefox_profile=define_firefoxe_profile())
driver.implicitly_wait(7)
base_url = "http://webmedical/app_dev.php"
verificationErrors = []
accept_next_alert = True

# on ne devrais pas changer ça souvent
BASIC_WAIT_TIMEOUT = 7


class Login(unittest.TestCase):
    
    def test_login(self):
        driver.get(base_url)
        driver.find_element_by_id("_username").clear()
        driver.find_element_by_id("_username").send_keys("admin")
        driver.find_element_by_id("_password").clear()
        driver.find_element_by_id("_password").send_keys("admin")
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Password'])[1]/following::button[1]").click()
        self.assertEqual(u"Bienvenue sur la base de données locale de l'établissement.", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Dossier Medical'])[1]/following::p[1]").text)
    
class AddDoc(unittest.TestCase):
    
    def test_add_doc(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestdoc")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddSec(unittest.TestCase):
    
    def test_add_sec(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestsec")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text(u"Secrétaire")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddErg(unittest.TestCase):
    
    def test_add_erg(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontesterg")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text("Ergotherapie")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddKin(unittest.TestCase):
    
    def test_add_kin(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestkin")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text(u"Kinésithérapie")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddPsyl(unittest.TestCase):
    
    def test_add_psyl(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestpsyl")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text("Psychologue")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddPsym(unittest.TestCase):
    
    def test_add_psym(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestpsym")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text(u"Psychomotricité")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddNeu(unittest.TestCase):
    
    def test_add_neu(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestneu")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text("Neuropsychologue")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddOrt(unittest.TestCase):
    
    def test_add_ort(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestort")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text("Orthophonie")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddWithEval(unittest.TestCase):
    
    def test_add_with_eval(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontesteval")
        Select(driver.find_element_by_id("appbundle_user_notificationType")).select_by_visible_text("Evaluateur particulier")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class AddWithDate(unittest.TestCase):
    
    def test_add_with_date(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Ajouter un utilisateur").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestdate")
        Select(driver.find_element_by_id("appbundle_user_notificationType")).select_by_visible_text("Date")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"L'utilisateur a été ajouté avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Ajouter un nouvel utilisateur'])[1]/following::p[1]").text)

class DelAccount(unittest.TestCase):
    
    def test_del_account(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Voir les comptes").click()
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Non'])[7]/following::input[4]").click()
        self.assertRegex(self.close_alert_and_get_its_text(), r"^Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur [\s\S]$")
        time.sleep(1)
        self.assertEqual(u"L'utilisateur a été supprimé avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Consulter la liste des comptes'])[1]/following::div[1]").text)

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

class ModAccount(unittest.TestCase):
    
    def test_mod_account(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Voir les comptes").click()
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Non'])[8]/following::input[2]").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("katalontestort2")
        Select(driver.find_element_by_id("appbundle_user_speciality")).select_by_visible_text("Orthophonie")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"Le compte a été modifié avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Consulter la liste des comptes'])[1]/following::div[1]").text)

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


class BlockAccount(unittest.TestCase):
    
    def test_block_account(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Voir les comptes").click()
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Non'])[8]/following::input[2]").click()
        driver.find_element_by_id("appbundle_user_nonlocked_0").click()
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"Le compte a été modifié avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Consulter la liste des comptes'])[1]/following::div[1]").text)

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

class AddSpecDoc(unittest.TestCase):
    
    def test_add_spec_doc(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Gérer les spécialités").click()
        driver.find_element_by_link_text(u"Ajouter une spécialité").click()
        driver.find_element_by_id("appbundle_member_speciality").clear()
        driver.find_element_by_id("appbundle_member_speciality").send_keys("katalontestdoc")
        driver.find_element_by_id("appbundle_member_Enregistrer").click()
        self.assertEqual(u"La création de la spécialité a été effectué avec succès !", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Ajouter une nouvelle spécialité'])[1]/following::div[1]").text)

class AddSpeciPara(unittest.TestCase):
    
    def test_add_speci_para(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Gérer les spécialités").click()
        driver.find_element_by_link_text(u"Ajouter une spécialité").click()
        Select(driver.find_element_by_id("appbundle_member_occupation")).select_by_visible_text(u"Paramédical")
        Select(driver.find_element_by_id("appbundle_member_role")).select_by_visible_text("ROLE_PARAMEDICAL")
        driver.find_element_by_id("appbundle_member_speciality").clear()
        driver.find_element_by_id("appbundle_member_speciality").send_keys("katalontestpara")
        driver.find_element_by_id("appbundle_member_Enregistrer").click()
        self.assertEqual(u"La création de la spécialité a été effectué avec succès !", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Ajouter une nouvelle spécialité'])[1]/following::div[1]").text)

class AddSpeciParaSec(unittest.TestCase):
    
    def test_add_speci_para_sec(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Gérer les spécialités").click()
        driver.find_element_by_link_text(u"Ajouter une spécialité").click()
        Select(driver.find_element_by_id("appbundle_member_occupation")).select_by_visible_text(u"Paramédical")
        Select(driver.find_element_by_id("appbundle_member_role")).select_by_visible_text("ROLE_SECRETARY")
        driver.find_element_by_id("appbundle_member_speciality").clear()
        driver.find_element_by_id("appbundle_member_speciality").send_keys("katalontestparasec")
        driver.find_element_by_id("appbundle_member_Enregistrer").click()
        self.assertEqual(u"La création de la spécialité a été effectué avec succès !", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Ajouter une nouvelle spécialité'])[1]/following::div[1]").text)

class ViewExistingSpecs(unittest.TestCase):
    
    def test_view_existing_specs(self):
        driver.get(base_url)
        driver.find_element_by_link_text(u"Gérer les spécialités").click()
        driver.find_element_by_link_text(u"Lister les spécialités existantes").click()
        self.assertEqual("katalontestpara", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Paramédical'])[8]/following::td[1]").text)
        self.assertEqual("katalontestdoc", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Médical'])[3]/following::td[1]").text)
        self.assertEqual("katalontestparasec", driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Paramédical'])[9]/following::td[1]").text)

class ChangePassword(unittest.TestCase):
    
    def test_change_password(self):
        driver.get(base_url)
        driver.find_element_by_link_text("Modifier un mot de passe").click()
        Select(driver.find_element_by_id("patientId")).select_by_visible_text("Orthophonie - katalontestort2")
        driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)=concat('Modifier le mot de passe d', \"'\", 'un compte')])[1]/following::input[1]").click()
        driver.find_element_by_id("appbundle_user_plainPassword_first").clear()
        driver.find_element_by_id("appbundle_user_plainPassword_first").send_keys("katalontestpassword")
        driver.find_element_by_id("appbundle_user_plainPassword_second").clear()
        driver.find_element_by_id("appbundle_user_plainPassword_second").send_keys("katalontestpassword")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()
        self.assertEqual(u"Le mot de passe a été modifié avec succès !", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)=concat('Modifier le mot de passe d', \"'\", 'un compte')])[1]/following::div[1]").text)

class SendBugReport(unittest.TestCase):
    
    def test_send_bug_report(self):
        driver.get(base_url)
        driver.find_element_by_link_text("admin").click()
        self.assertEqual("Rapporter un bogue", driver.find_element_by_link_text("Rapporter un bogue").text)

class Logout(unittest.TestCase):
    
    def test_logout(self):
        driver.get(base_url)
        driver.find_element_by_link_text("admin").click()
        driver.find_element_by_link_text(u"Déconnexion").click()
        self.assertEqual(u"MesureLocal (c) Pour toute utilisation, copie ou modification de ce programme vous devez obtenir préalablement l'autorisation auprès de christian.toinard@insa-cvl.fr", driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Username'])[1]/preceding::h2[1]").text)

    def tearDown(self):
        driver.quit()
        self.assertEqual([], verificationErrors)

class SaveSystem(unittest.TestCase):
    TEST_FILES_PATH = "../test_files/saves/"
    WEB_SAVES_PATH = "../../web/saves/"
    
    @classmethod
    def copy_to_production(cls, filename, _depth_call=False):
        try:
            shutil.copyfile(cls.TEST_FILES_PATH + filename, cls.WEB_SAVES_PATH + filename)
        except FileNotFoundError:
            os.mkdir(cls.WEB_SAVES_PATH)  # Dans le cas où on a supprimer le dossier saves
            if not _depth_call:
                # Evite une boucle infinie
                cls.copy_to_production(filename, True)

    @classmethod
    def remove_from_production(cls, filename):
        try:
            os.remove(cls.WEB_SAVES_PATH + filename)
        except FileNotFoundError:
            pass

    @classmethod
    def setUpClass(self):
        print("Setting up TestCase's class: SaveSystem")
        pass

    @classmethod
    def tearDownClass(cls):
        print("Tearing down TestCase's class: SaveSystem")
        # On oblige la suppression des fichiers temporaires
        # pour etre sur de ne pas emmerder les autres test_suite
        cls.remove_from_production("done-mysqldump")
        cls.remove_from_production("done-compress")
        cls.remove_from_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")

        cls.remove_from_production("done-compress")
        cls.remove_from_production("done-mysqldump")
        cls.remove_from_production("sauvegarde_mesureonline_2019-02-01-10-10.7z.init")
        cls.remove_from_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")
        cls.remove_from_production("sauvegarde_mesureonline_2019-01-18-10-56.7z")

    def setUp(self):
        self.wait = WebDriverWait(driver, BASIC_WAIT_TIMEOUT)

    def assert_alert_is_visible(self, text=None):
        """
            Vérifie qu'une alert de confirmation s'ouvre, vérifie que sont texte est valide et accepte
            la demande (clique sur OK).
            La méthode ne vérifie pas le text si il n'est pas spécifié.
            
            NOTE: L'objet doit posséder un WebDriverWait(driver, BASIC_WAIT_TIMEOUT) en attribut pour
            que la méthode fonctionne.
        """
        try:
            # ici se passe "l'assert" qui vérifie si l'alert de confirmation est présente
            self.wait.until(expected_conditions.alert_is_present())
        except TimeoutException:
            self.fail("Il n'y a pas d'alert qui demandant une confirmation de la part de l'utilisateur")

        alert = driver.switch_to_alert()
        alert_box_text = alert.text
        if text:
            self.assertEqual(text, alert_box_text)
        alert.accept()

    def assert_text_appears(self, text, where, error_message):
        """
            Attend que le texte 'text' soit présent au XPATH spécifié.
        """
        try:
            self.wait.until(expected_conditions.text_to_be_present_in_element((By.XPATH, where), text))
        except TimeoutException:
            self.fail(error_message)

    def assert_route_is(self, route_name):
        self.assertEqual(route_name, driver.current_url.replace(base_url, ""))

    def test_make_backup(self):
        driver.get(base_url + "createBackup")


        # On effectue le coeur de la fonctionnalité
        driver.find_element_by_id("form_Cledechiffrement").click()
        driver.find_element_by_id("form_Cledechiffrement").clear()
        driver.find_element_by_id("form_Cledechiffrement").send_keys("azerty")
        driver.find_element_by_id("form_public").click()
        driver.find_element_by_id("form_Sauvegarder").click()
        # on sauvegarde la date courante pour l'utiliser lors de la vérification
        backup_date = datetime.datetime.now()
        self.assert_text_appears(
            u"La sauvegarde a bien été lancée.",
            "(.//*[normalize-space(text()) and normalize-space(.)='Réaliser une sauvegarde'])[1]/following::div[1]",
            "Le flash notice indiquant le bon déroulement d'une action n'a pas été reçu"
        )
        # NOTE: On attend (un peu) que le backup soit réappliquer (vu qu'il est dans un autre processus)
        # TODO: Lorsque les notifications de terminaison de sauvegardes seront ajoutés, mettre un wait.until ici
        time.sleep(BASIC_WAIT_TIMEOUT)

        # On vérifie qu'elle a bien les effets voulu
        driver.get(base_url + "listBackup")
        self.assertFalse(
            test_utils.does_date_differ(
                datetime.datetime.strptime(
                    driver.find_element_by_xpath("//table/tbody/tr[1]/td[1]").text,
                    "Le %d/%m/%Y à %Hh%Mm"
                ),
                backup_date
            ), 
            "La sauvegarde qu'on vient d'effectuer n'est pas en première position dans le tableaux des sauvegardes"
        )
        self.assertEqual(
            "Disponible",
            driver.find_element_by_xpath("//table/tbody/tr[1]/td[5]/h4/input").get_attribute("title"),
            "La sauvegarde n'est pas marqué comme disponible, a-t-elle échouée?"
        )

    def test_restore_backup(self):
        self.copy_to_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")

        # Ajout d'un faux user qui sera supprimer lors de la réapplication du backup
        driver.get("http://webmedical/app_dev.php/register")
        driver.find_element_by_id("appbundle_user_username").click()
        driver.find_element_by_id("appbundle_user_username").clear()
        driver.find_element_by_id("appbundle_user_username").send_keys("test_to_delete")
        driver.find_element_by_id("appbundle_user_Enregistrer").click()

        # On applique la sauvegarde (coeur de la fonctionnalité)
        driver.get(base_url + "listBackup")
        # TODO: Faire attention à prendre la bonne sauvegarde
        driver.find_element_by_xpath(u"(.//*[normalize-space(text()) and normalize-space(.)='Le 17/01/2019 à 16h03m'])[1]/following::input[6]").click()
        self.assertEqual(
            u"La sauvegarde à réappliquer est celle qui à été effectuée le 17/01/2019 à 16h03m",
            driver.find_element_by_xpath("(.//*[normalize-space(text()) and normalize-space(.)='Restaurer une sauvegarde'])[1]/following::div[1]").text
        )
        driver.find_element_by_id("form_Cledechiffrement").click()
        driver.find_element_by_id("form_Cledechiffrement").clear()
        driver.find_element_by_id("form_Cledechiffrement").send_keys("azerty")
        driver.find_element_by_id("send").click()
        self.assert_alert_is_visible(
            "Cette opération écrasera vos données actuelles. Etes-vous certain de vouloir continuer ?"
        )
        # On utilise assert_text_appears pour faire attendre selenium, sinon il va sélectionner
        # le flash d'information qui contient la date de la sauvegarde qui sera réappliquée.
        self.assert_text_appears(
            u"La restauration a bien été lancée.",
            "(.//*[normalize-space(text()) and normalize-space(.)='Restaurer une sauvegarde'])[1]/following::div[1]",
            "Le flash notice indiquant le bon déroulement d'une action n'a pas été reçu"
        )

        # NOTE: On attend (un peu) que le backup soit réappliquer (vu qu'il est dans un autre processus)
        # TODO: Lorsque les notifications de terminaison de sauvegardes seront ajoutés, mettre un wait.until ici
        time.sleep(3)

        # Récupération de la liste des utilisateurs après application de la sauvegarde
        driver.get("http://webmedical/app_dev.php/viewusers")
        user_table = driver.find_element_by_css_selector("body .container .page-body table")
        user_lines = user_table.find_elements_by_css_selector("tbody tr")
        # On filtre car une ligne sur deux on trouve un nom vide (ça doit etre le tableau dans "Action")
        users = filter(None, [line.find_elements_by_css_selector("td")[1].text for line in user_lines])

        # Cette liste doit correspondre à l'état dans lequel se trouve le site
        # lors de la sauvegarde que ce test va réappliquer
        self.assertListEqual(list(users), ["doc1", "doc2", "par1", "par2", "sec1", "sec2"]) # TODO: Entrer les utilisateurs contenu dans cette sauvegarde.

        self.remove_from_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")

    def test_list_backup(self):
        driver.get(base_url + "listBackup")

    def test_download_backup(self):
        self.copy_to_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")
        driver.get(base_url + "listBackup")
        
        web_backup_date = driver.find_element_by_xpath("//table/tbody/tr[1]/td[1]").text
        backup_date = datetime.datetime.strptime(web_backup_date, "Le %d/%m/%Y à %Hh%Mm")
        
        download_button = driver.find_element_by_xpath("//table/tbody/tr[1]/td[3]/form/input[2]")
        self.assertEqual("Télécharger", download_button.get_attribute("value"))
        download_button.click()

        # On vérifie que le fichier est bien là ...
        backup_file = "sauvegarde_mesureonline_" + backup_date.strftime("%Y-%m-%d-%H-%M") + ".7z"
        self.assertTrue(os.path.isfile(backup_file))
        os.remove(backup_file)

        # TODO: Vérifier que le fichier est lisible (que c'est bien un 7z)
        # TODO: Vérifier que le fichier est désarchivable ?
        
        self.remove_from_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")

    def test_upload_backup(self):
        driver.get(base_url + "uploadBackup")
        # TODO: Rendrez le test fonctionnel, pour l'instant le send_keys ne fonctionne pas bien
        return

        upload_button = driver.find_element_by_id("browse_backup")
        # On force l'affichage de l'input pour que selenium puisse faire un send_keys dessus
        driver.execute_script("$('#browse_backup').show()")
        upload_button.send_keys(r"C:\wamp64\www\secproj\tests\test_files\sauvegarde_mesureonline_2019-01-18-10-56.7z")
        self.assert_text_appears(
            "sauvegarde_mesureonline_2019-01-18-10-56.7z",
            driver.find_element_by_id("filelist").text,
            "L'utilisateur n'est pas informé de la sauvegarde qui sera envoyée sur le serveur"
        )
        time.sleep(2)
        driver.find_element_by_id("start-upload").click()
        self.assert_text_appears(
            "Le fichier à bien été envoyé",
            driver.find_element_by_id("filelist").text,
            "Aucun retour d'information n'est reçu sur la sauvegarde envoyé"
        )

        # On vérifie que l'upload a bien été effectué
        driver.get(base_url + "listBackup")
        backup_table = driver.find_element_by_xpath("//table/tbody")
        backups = backup_table.find_elements_by_css_selector("tr")
        self.assertIn(
            "Le 18/01/2019 à 10h56m",
            [backup.find_elements_by_css_selector("td")[1].text for backup in backups],
            "La sauvegarde qu'on vient d'uploader ne se trouve pas dans la liste des backups"
        )

    def test_delete_backup(self):
        self.copy_to_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")
        driver.get(base_url + "listBackup")

        # On test qu'on peut supprimer une sauvegarde avec un bouton
        backup_date = datetime.datetime.strptime(
            driver.find_element_by_xpath("//table/tbody/tr[1]/td[1]").text,
            "Le %d/%m/%Y à %Hh%Mm"
        )
        delete_button = driver.find_element_by_xpath("//table/tbody/tr[1]/td[2]/form/input[2]")
        self.assertEqual("Supprimer", delete_button.get_attribute("value"))
        delete_button.click()
        self.assert_alert_is_visible("Êtes-vous sûr de vouloir supprimer définitivement la sauvegarde ?")

        # On vérifie qu'on reste bien sur l'affichage de liste des sauvegarde après la suppression
        self.assert_route_is("listBackup")

        # On vérifie le message de confirmation de suppression
        self.assert_text_appears(
            "La sauvegarde du {} a bien été supprimée !".format(backup_date.strftime("%d/%m/%Y à %Hh%Mm")),
            "(.//*[normalize-space(text()) and normalize-space(.)='Réaliser une sauvegarde'])[1]/following::div[1]",
            "L'utilisateur n'a pas eu de message confirmant la suppression de la sauvegarde"
        )

        # On vérifie que la sauvegarde n'apparait plus dans le tableau des sauvegardes
        backup_table = driver.find_element_by_xpath("//table/tbody")
        backups = backup_table.find_elements_by_css_selector("tr")
        self.assertNotIn(
            backup_date.strftime("Le %d/%m/%Y à %Hh%Mm"),
            [backup.find_elements_by_css_selector("td")[1].text for backup in backups],
            "La sauvegarde n'a pas été réellement supprimée."
        )

        self.remove_from_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")

    def test_status(self):
        running_backup_xpath = u"(.//*[normalize-space(text()) and normalize-space(.)='Le 01/02/2019 à 10h10m'])[1]/.."

        # Test du status "En cours" d'un processus de sauvegarde
        with open(self.TEST_FILES_PATH + "sauvegarde_mesureonline_2019-02-01-10-10.7z.init", "w") as f:
            f.write(str(os.getpid()))
        self.copy_to_production("sauvegarde_mesureonline_2019-02-01-10-10.7z.init")

        driver.get(base_url + "listBackup")
        running_backup_status = driver.find_element_by_xpath(running_backup_xpath + "/td[5]/h4/input")
        self.assertEqual("Sauvegarde de la base de données", running_backup_status.get_attribute("title"))

        open(self.WEB_SAVES_PATH + "done-mysqldump", "w").close()
        driver.get(base_url + "listBackup")
        running_backup_status = driver.find_element_by_xpath(running_backup_xpath + "/td[5]/h4/input")
        self.assertEqual("Compression de la sauvegarde", running_backup_status.get_attribute("title"))

        open(self.WEB_SAVES_PATH + "done-compress", "w").close()
        driver.get(base_url + "listBackup")
        running_backup_status = driver.find_element_by_xpath(running_backup_xpath + "/td[5]/h4/input")
        self.assertEqual("Finalisation de la sauvegarde", running_backup_status.get_attribute("title"))

        self.remove_from_production("done-compress")
        self.remove_from_production("done-mysqldump")
        self.remove_from_production("sauvegarde_mesureonline_2019-02-01-10-10.7z.init")

        # Test du status "Echec" d'un processus de sauvegarde
        open(self.WEB_SAVES_PATH + "sauvegarde_mesureonline_2019-02-01-10-10.7z.init", "w").close()

        driver.get(base_url + "listBackup")
        running_backup_status = driver.find_element_by_xpath(running_backup_xpath + "/td[3]/h4/input")
        self.assertEqual("Echec", running_backup_status.get_attribute("title"))
        running_backup_actions = driver.find_element_by_xpath(running_backup_xpath + "/td[2]")
        self.assertEqual("Sauvegarde non disponible", running_backup_actions.text)
        
        self.remove_from_production("sauvegarde_mesureonline_2019-02-01-10-10.7z.init")

        # Test du status "Disponible" d'un processus de sauvegarde
        self.copy_to_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")
        
        driver.get(base_url + "listBackup")
        available_backup_xpath = "(.//*[normalize-space(text()) and normalize-space(.)='Le 17/01/2019 à 16h03m'])[1]/.."
        running_backup_status = driver.find_element_by_xpath(available_backup_xpath + "/td[5]/h4/input")
        self.assertEqual("Disponible", running_backup_status.get_attribute("title"))

        self.remove_from_production("sauvegarde_mesureonline_2019-01-17-16-03.7z")

    def test_rotate(self):
        driver.get(base_url + "configBackup")


test_cases = [
    Login, AddDoc, AddSec, AddErg, AddKin, 
    AddPsyl, AddPsym, AddNeu, AddOrt, AddWithEval,
    AddWithDate, DelAccount, ModAccount, BlockAccount, AddSpecDoc,
    AddSpeciPara, AddSpeciParaSec, ViewExistingSpecs, ChangePassword, 
    SendBugReport, Logout 
]

if __name__ == "__main__":
    run_tests.run_tests(test_cases)
