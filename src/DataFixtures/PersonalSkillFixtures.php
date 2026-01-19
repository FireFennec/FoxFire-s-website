<?php

namespace App\DataFixtures;

use App\Entity\PersonalSkill;
use App\Model\PersonalSkillLevelEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonalSkillFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $personalSkill1 = new PersonalSkill();
        $personalSkill1->setSkillName('PHP (Hypertext Preprocessor)');
        $personalSkill1->setSkillDescription('I can create full-fledged OOP code. I focus more on creating code in the PHP framework,
        but I also have experience in classic PHP without a framework, where I created a few of my own projects and it is relatively
        easy for me to find and quickly understand the missing information about what I would be working on.');
        $personalSkill1->setLevelOfSkill(PersonalSkillLevelEnum::EXPERIENCED);

        $personalSkill2 = new PersonalSkill();
        $personalSkill2->setSkillName('Symfony (PHP framework)');
        $personalSkill2->setSkillDescription('The Symfony framework is currently my focus and I am working on
        improving this skill every day. I can create fairly clean basic code but I sometimes struggle with reading other people\'s
        code if it contains a lot of specific packages or is complexly extended. This means that it takes me some time to understand
        it and sometimes I have to ask questions but I am still mostly able to work with it and extend it, as well as create something
        of my own in an ongoing project.');
        $personalSkill2->setLevelOfSkill(PersonalSkillLevelEnum::ADVANCED);

        $personalSkill3 = new PersonalSkill();
        $personalSkill3->setSkillName('HTML (Hypertext Markup Language)');
        $personalSkill3->setSkillDescription('Since I\'m more of a back-end programmer, I\'ve never paid much attention to HTML,
        but I know the basics. I can easily understand the HTML part of the code and possibly even create something.
        I often use AI for some more complex modifications, because if I were to create it manually, it would take me a long time,
        however, I always check what I put there and it\'s crucial for me to understand every part of such code. So I could say that
        I can read HTML code well, but creating it myself without the help of AI is difficult for me.');
        $personalSkill3->setLevelOfSkill(PersonalSkillLevelEnum::ADVANCED);

        $personalSkill4 = new PersonalSkill();
        $personalSkill4->setSkillName('CSS (Cascading Style Sheets)');
        $personalSkill4->setSkillDescription('Honestly, I don\'t like CSS editing and I\'m only partially familiar with writing
        such code, since I\'m a back-end programmer. I can read such code, but I\'m not very good at writing it myself.
        So I can make a few edits, but if I had to do something like that, I\'d rely more on AI to check it than on creating it myself.');
        $personalSkill4->setLevelOfSkill(PersonalSkillLevelEnum::BEGINNER);

        $personalSkill5 = new PersonalSkill();
        $personalSkill5->setSkillName('English');
        $personalSkill5->setSkillDescription('I would also like to mention my English, which is very important in the IT field.
        My English is at a level where I can communicate both in writing and speaking, although I make occasional grammatical errors.
        In other words, it is more than sufficient for a normally functioning Czech company and I would probably be able to get by
        with it even in a company that communicates purely in English.');
        $personalSkill5->setLevelOfSkill(PersonalSkillLevelEnum::PROFESSIONAL);

        $personalSkill6 = new PersonalSkill();
        $personalSkill6->setSkillName('Doctrine');
        $personalSkill6->setSkillDescription('I am able to work with Doctrine in the framework quite reliably.
        I can handle migrations, repositories, entities, both creation and editing. I know the basic SQL commands by heart and
        I can quickly find what I don\'t remember. There is still room to learn how to work with doctrine better,
        but for now it is enough for basic use and maybe more.');
        $personalSkill6->setLevelOfSkill(PersonalSkillLevelEnum::EXPERIENCED);

        $personalSkill7 = new PersonalSkill();
        $personalSkill7->setSkillName('Git');
        $personalSkill7->setSkillDescription('I already have some experience working with git, both on my own personal projects
        and in a fully functional company. I can handle the basics like checking adds, committing, and pushing data.
        I can also figure out how to branch. However, from time to time I get stuck on something I did wrong and then have a
        hard time fixing the mistake.');
        $personalSkill7->setLevelOfSkill(PersonalSkillLevelEnum::ADVANCED);

        $personalSkill8 = new PersonalSkill();
        $personalSkill8->setSkillName('Twig');
        $personalSkill8->setSkillDescription('I\'m used to working with twig in the Symfony framework and I can do some basics
        in it and integrate it properly. It\'s also not a problem to deal with translations for switching on pages.
        However, when it comes to design, I\'m not that good at this part, but I help myself with IA, which generates what
        I need and then I go through it all to be sure of what I\'m adding.');
        $personalSkill8->setLevelOfSkill(PersonalSkillLevelEnum::ADVANCED);

        $personalSkill9 = new PersonalSkill();
        $personalSkill9->setSkillName('Unit, Integration and Application tests');
        $personalSkill9->setSkillDescription('I already have some experience with testing. I can distinguish between the three
        tests mentioned, which ones are suitable for what and write a test if necessary. However, when I write tests for code
        that is not mine, it sometimes takes me a while to write such a test and I also often miss the options that I should have tested.');
        $personalSkill9->setLevelOfSkill(PersonalSkillLevelEnum::BEGINNER);

        $personalSkill10 = new PersonalSkill();
        $personalSkill10->setSkillName('Javascript');
        $personalSkill10->setSkillDescription('Honestly, I\'ve only come across Javascript a few times while coding, but
        I\'ve never really learned it and it takes me a while to read what the code does. I probably wouldn\'t be able to
        write good quality clean Javascript code myself and my knowledge is more of an amateur.');
        $personalSkill10->setLevelOfSkill(PersonalSkillLevelEnum::AMATEUR);

        $manager->persist($personalSkill1);
        $manager->persist($personalSkill2);
        $manager->persist($personalSkill3);
        $manager->persist($personalSkill4);
        $manager->persist($personalSkill5);
        $manager->persist($personalSkill6);
        $manager->persist($personalSkill7);
        $manager->persist($personalSkill8);
        $manager->persist($personalSkill9);
        $manager->persist($personalSkill10);

        $manager->flush();
    }
}
