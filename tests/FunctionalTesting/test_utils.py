# coding: utf-8

import time

# De base on accepte 60 + 10 secondes, ce qui fait qu'on testera que les minutes
DEFAULT_DATE_THRESHOLD = 70

def hide_symfony_devbar(driver, wait_time=3):
    """ cache la barre de symfony dédié aux développeur si on est en app_dev """
    if "app_dev.php" in driver.current_url:
        time.sleep(wait_time)
        # nécessite JQuery sur le site
        driver.execute_script("$('[id^=sfwdt]').hide()")


def does_date_differ(found, expected, threshold=DEFAULT_DATE_THRESHOLD):
    """
        return True si les deux dates diffère trop, False si elles sont 
        quasiment identique.

        found (datetime.datetime): le datetime trouvé
        expected (datetime.datetime): le datetime qu'on aimerais dans le meilleur des mondes
        threshold (int): correspond à la différence (en secondes) maximum entre les deux datetime.
            descendre en dessous de 60 secondes peut etre problématique lorsque la granularité
            des datetime est de la minute (évidemment)
    """
    return abs((found - expected).total_seconds()) >= threshold