<?php

namespace App\DataFixtures;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        {
            $question = new Question();
            $question->setTitle('Worest question');
            $question ->setDescription('boom');
            $question ->setScore(0);
            $manager->persist($question);
            $manager->flush();
        }
    }
}
