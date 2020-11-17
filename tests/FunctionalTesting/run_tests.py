import unittest, sys

import time
import argparse

def run_tests(test_cases, test_names=[], verbosity=1):
    testsuite = unittest.TestSuite()
    test_loader = unittest.TestLoader()

    # A minima on demande des test_case à laner
    for test_case in test_cases:
        tests = test_loader.loadTestsFromTestCase(test_case)
        testsuite.addTests(tests)

    # On peut aussi charger des tests via leur nom complet
    if test_names:
        testsuite.addTest(test_loader.loadTestsFromNames(test_names))
    
    results = unittest.TextTestRunner(verbosity=verbosity).run(testsuite)
    if not results.failures and not results.errors:
        sys.exit(0)
    else : 
        sys.exit(1)


# Partie du code pour lancer une fonction de test spécifique

TESTCASE_MODULE = None


def define_parser():
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "test_cases", metavar="TestCase", nargs="+",
        help="Une liste des class TestCase à lancer"
    )
    parser.add_argument(
        "-m", "--module", required=True,
        help="Module à partir duquel charger le ou les TestCase"
    )
    parser.add_argument(
        "--base-url",
        help="L'url à partir duquel les tests se lanceront. Doit absolument se finir par un '/'"
    )
    parser.add_argument(
        "-d", "--dev", action="store_true",
        help="Pour lancer les tests avec le base-url à 'http://webmedical/app_dev.php/'"
    )
    parser.add_argument(
        "-u", "--username", default="admin",
        help="Le nom de l'utilisateur pour lancer les tests [default: admin]"
    )
    parser.add_argument(
        "-p", "--password", default="admin",
        help="Le mot de passe de l'utilisateur pour lancer les tests [default: admin]"
    )

    return parser


def error_exit(message):
    define_parser().print_help()
    print("="*80 + "\n\n" + message + "\n")
    sys.exit(127)


def load_module(module_name):
    global TESTCASE_MODULE
    try:
        TESTCASE_MODULE = __import__(module_name)
    except ModuleNotFoundError:
        error_exit("Le module que vous avez demandé n'existe pas, vérifier bien votre ligne de commande")


def load_class(class_name):
    global TESTCASE_MODULE
    try:
        return getattr(TESTCASE_MODULE, class_name)
    except:
        TESTCASE_MODULE.driver.quit()
        error_exit("La classe de tests que vous avez demandé n'existe pas, vérifier bien votre ligne de commande")
    

def change_base_url(new_url):
    global TESTCASE_MODULE
    TESTCASE_MODULE.base_url = new_url


def connect_as(username, password):
    global TESTCASE_MODULE
    TESTCASE_MODULE.driver.get(TESTCASE_MODULE.base_url)

    # rempli le login de login
    login_form = TESTCASE_MODULE.driver.find_element_by_xpath("//form[@class='form-signin']")
    login_form.find_element_by_name("_username").send_keys(username)
    login_form.find_element_by_name("_password").send_keys(password)
    login_form.submit()
    # On attend que la page ai finis de charger avec un sleep à la con
    time.sleep(2)


def run_as(username, password):
    def connect_and_close(function):
        def wrapper(*args, **kwargs):
            global TESTCASE_MODULE
            try:
                connect_as(username, password)
                result = function(*args, **kwargs)
            finally:
                # Dans toues les cas on veut fermer le driver
                TESTCASE_MODULE.driver.quit()
            return result
        return wrapper
    return connect_and_close


if __name__ == "__main__":
    arg_parser = define_parser()
    args = arg_parser.parse_args()
    run_tests = run_as(args.username, args.password)(run_tests)


    if not args.module:
        error_exit("Vous devez spécifier le nom du module à partir duquel seront chargé les tests")
    
    load_module(args.module)

    if args.base_url:
        if not args.base_url.endswith("/"):
            error_exit("Vous devez fournir une url de base qui se termine par un '/'")
        change_base_url(args.base_url)

    # C'est le dernier changement de base_url à faire car il permet de forcer sa valeur
    if args.dev:
        change_base_url("http://webmedical/app_dev.php/")


    if all(["." in test_name for test_name in args.test_cases]):
        run_tests([], args.test_cases, verbosity=2)
    elif any(["." in test_name for test_name in args.test_cases]):
        error_exit("Vous ne pouvez pas mélanger le lancement de TestCase entier et de fonction spécifique")
    else:
        run_tests([load_class(test_case) for test_case in args.test_cases], verbosity=2)