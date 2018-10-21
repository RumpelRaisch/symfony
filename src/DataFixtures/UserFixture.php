<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user
            ->setEmail('rainer@bitshifting.de')
            ->setPassword(
                $this->encoder->encodePassword($user, 'test')
            )
            ->setName('Rainer')
            ->setSurname('Schulz')
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setAvatar(file_get_contents('public/img/rumpel.jpg'));

        $manager->persist($user);

        $user = new User();
        $user
            ->setEmail('keiner@bitshifting.de')
            ->setPassword(
                $this->encoder->encodePassword($user, 'test')
            )
            ->setName('Keiner')
            ->setSurname('Schulz')
            ->setRoles(['ROLE_USER'])
            ->setAvatar(file_get_contents('public/img/avatar.male.png'));

        $manager->persist($user);

        $manager->flush();
    }
}
