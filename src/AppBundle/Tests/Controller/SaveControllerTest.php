<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SaveControllerTest extends WebTestCase
{
    public function testCreatebackup()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/createBackup');
    }

    public function testRestorebackup()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/restoreBackup');
    }

}