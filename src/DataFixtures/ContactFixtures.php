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
        $myContact->setSteam('FoxFire');
        $myContact->setSteamLink('https://steamcommunity.com/profiles/76561198063270997/');
        $myContact->setBattleNet('FoxFire#21881');

        $manager->persist($myContact);

        $manager->flush();
    }
}
