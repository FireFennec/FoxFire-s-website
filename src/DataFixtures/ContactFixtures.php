<?php

namespace App\DataFixtures;

use App\Entity\ContactInformation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ContactFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $myContact = new ContactInformation();
        $myContact->setPhoneNumber('+420 739950157');
        $myContact->setEmail('DanVraspir@protonmail.com');
        $myContact->setDiscord('foxfire7445');
        $myContact->setTelegram('FoxFireCZ');
        $myContact->setX('FoxFireCZ');
        $myContact->setXLink('https://x.com/FoxFireCZ');
        $myContact->setBarq('FoxFireCZ');
        $myContact->setBarqLink('https://barq.app/@FoxFireCZ');

        $manager->persist($myContact);

        $manager->flush();
    }
}
