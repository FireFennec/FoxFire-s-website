<?php

namespace App\DataFixtures;

use App\Entity\WebsiteFeature;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WebsiteFeatureFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $websiteFeature1 = new WebsiteFeature();
        $websiteFeature1->setName('Features list creating');
        $websiteFeature1->setDescription('List of the website features is created by hands.
        There is not a page for adding or editing those features so this will have to change.');
        $websiteFeature1->setPriority(FeaturePriorityEnum::ASAP);
        $websiteFeature1->setStatus(FeaturesStatusEnum::UPDATE_FEATURE);

        $websiteFeature2 = new WebsiteFeature();
        $websiteFeature2->setName('Basic texting minigame');
        $websiteFeature2->setDescription('A small text minigame where player will have a character. It will be RPG game with
        levels, items, health points, damage and a few locations with some monsters.
        Every location will have a different monsters and after a fight there will be a cooldown for a few minutes.');
        $websiteFeature2->setPriority(FeaturePriorityEnum::ANYTIME);
        $websiteFeature2->setStatus(FeaturesStatusEnum::NEW_FEATURE);

        $websiteFeature3 = new WebsiteFeature();
        $websiteFeature3->setName('Personal skills');
        $websiteFeature3->setDescription('A page with my own skills around programming for some information about me and what can I do.');
        $websiteFeature3->setPriority(FeaturePriorityEnum::SOON);
        $websiteFeature3->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);

        $websiteFeature4 = new WebsiteFeature();
        $websiteFeature4->setName('User interface');
        $websiteFeature4->setDescription('Login, registration and logout for users. It will be important for many of my features.');
        $websiteFeature4->setPriority(FeaturePriorityEnum::HIGH_PRIORITY);
        $websiteFeature4->setStatus(FeaturesStatusEnum::NEW_FEATURE,);

        $websiteFeature5 = new WebsiteFeature();
        $websiteFeature5->setName('Better visual for the interface');
        $websiteFeature5->setDescription('Add some colors and backgrounds design for the website.
        Another improvements are possible in the future.');
        $websiteFeature5->setPriority(FeaturePriorityEnum::HIGH_PRIORITY);
        $websiteFeature5->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature6 = new WebsiteFeature();
        $websiteFeature6->setName('Navigation bar');
        $websiteFeature6->setDescription('Add a navigation bar for the website for better access to pages on my website.');
        $websiteFeature6->setPriority(FeaturePriorityEnum::ASAP);
        $websiteFeature6->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature7 = new WebsiteFeature();
        $websiteFeature7->setName('About me - links');
        $websiteFeature7->setDescription('Improvement for information about me with links for better access to my social media.');
        $websiteFeature7->setPriority(FeaturePriorityEnum::ANYTIME);
        $websiteFeature7->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature8 = new WebsiteFeature();
        $websiteFeature8->setName('About me');
        $websiteFeature8->setDescription('A page with information about me like contact and social media.');
        $websiteFeature8->setPriority(FeaturePriorityEnum::SOON);
        $websiteFeature8->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature9 = new WebsiteFeature();
        $websiteFeature9->setName('Feature detail list');
        $websiteFeature9->setDescription('A page with chosen feature and every information about that feature.');
        $websiteFeature9->setPriority(FeaturePriorityEnum::HIGH_PRIORITY);
        $websiteFeature9->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature10 = new WebsiteFeature();
        $websiteFeature10->setName('Features list of priority');
        $websiteFeature10->setDescription('Features will have another attribute and it will be a priority. There will be five priorities.
                Nice to have (grey), Anytime (white), Soon (Yellow), High priority (Orange), ASAP (Red).
                The bigger priority means the bigger chance that this will be a next update for this website.');
        $websiteFeature10->setPriority(FeaturePriorityEnum::ANYTIME);
        $websiteFeature10->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature11 = new WebsiteFeature();
        $websiteFeature11->setName('About me - visual improvement');
        $websiteFeature11->setDescription('Borders and a text visual improvement for a page About me.
        Also improve link so it can be recognized it is link.');
        $websiteFeature11->setPriority(FeaturePriorityEnum::NICE_TO_HAVE);
        $websiteFeature11->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature12 = new WebsiteFeature();
        $websiteFeature12->setName('Features - add deadlines');
        $websiteFeature12->setDescription('With this update I can add deadlines so you can check how long much time I have until
        I will have to finish every feature.');
        $websiteFeature12->setPriority(FeaturePriorityEnum::ANYTIME);
        $websiteFeature12->setStatus(FeaturesStatusEnum::UPDATE_FEATURE,);

        $websiteFeature13 = new WebsiteFeature();
        $websiteFeature13->setName('Improve website with database');
        $websiteFeature13->setDescription('Add a database to store important data and improve possibilities I can do on my website.
        This improvement will store data in Fixtures for now and it will have another updates.');
        $websiteFeature13->setPriority(FeaturePriorityEnum::ASAP);
        $websiteFeature13->setStatus(FeaturesStatusEnum::NEW_FEATURE,);

        $websiteFeature14 = new WebsiteFeature();
        $websiteFeature14->setName('Add a cooking recipes');
        $websiteFeature14->setDescription('There will be a page with my recipes that I tried and want to save for myself
        and at the same time share with others. For now it will be only names of recipes. Later I will add more.');
        $websiteFeature14->setPriority(FeaturePriorityEnum::ANYTIME);
        $websiteFeature14->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature15 = new WebsiteFeature();
        $websiteFeature15->setName('Better navigation menu');
        $websiteFeature15->setDescription('Add some light effects and better positioning for a navigation menu.');
        $websiteFeature15->setPriority(FeaturePriorityEnum::SOON);
        $websiteFeature15->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature16 = new WebsiteFeature();
        $websiteFeature16->setName('Rename Tasks on Web features');
        $websiteFeature16->setDescription('Tasks is not very good name for those small updates on this website because in the future
        I would like to add tasks as a daily tasks, so it will be a new feature and that is the reason for renaming.');
        $websiteFeature16->setPriority(FeaturePriorityEnum::HIGH_PRIORITY);
        $websiteFeature16->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature17 = new WebsiteFeature();
        $websiteFeature17->setName('Rename Tasks on Web features');
        $websiteFeature17->setDescription('Tasks is not very good name for those small updates on this website because in the future
        I would like to add tasks as a daily tasks, so it will be a new feature and that is the reason for renaming.');
        $websiteFeature17->setPriority(FeaturePriorityEnum::HIGH_PRIORITY);
        $websiteFeature17->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE,);

        $websiteFeature18 = new WebsiteFeature();
        $websiteFeature18->setName('Personal skills - full list');
        $websiteFeature18->setDescription('Add more information about my skills and better description how good I am with those skills.');
        $websiteFeature18->setPriority(FeaturePriorityEnum::ANYTIME);
        $websiteFeature18->setStatus(FeaturesStatusEnum::UPDATE_FEATURE,);

        $manager->persist($websiteFeature1);
        $manager->persist($websiteFeature2);
        $manager->persist($websiteFeature3);
        $manager->persist($websiteFeature4);
        $manager->persist($websiteFeature5);
        $manager->persist($websiteFeature6);
        $manager->persist($websiteFeature7);
        $manager->persist($websiteFeature8);
        $manager->persist($websiteFeature9);
        $manager->persist($websiteFeature10);
        $manager->persist($websiteFeature11);
        $manager->persist($websiteFeature12);
        $manager->persist($websiteFeature13);
        $manager->persist($websiteFeature14);
        $manager->persist($websiteFeature15);
        $manager->persist($websiteFeature16);
        $manager->persist($websiteFeature17);

        $manager->flush();
    }
}
