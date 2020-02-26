<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 13/06/2018
 * Time: 12:19
 */

namespace App\DataFixtures\ORM;


use ScyLabs\NeptuneBundle\Entity\PageType;
use ScyLabs\NeptuneBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;


class LoadUserData extends Fixture implements OrderedFixtureInterface,ContainerAwareInterface
{

    private $container;

    public function load(ObjectManager $manager){


        $userManager = $this->container->get('fos_user.user_manager');
        $templating = $this->container->get('templating');

        $mailer = $this->container->get('mailer');

        $users = array();
        /*
         * Alex
         */
        $users[] = $userManager->createUser()
            ->setUsername('alex@e-corses.com')
            ->setEmail('alex@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_SUPER_ADMIN'));
        $users[] = $userManager->createUser()
            ->setUsername('youssefs@e-corses.com')
            ->setEmail('youssef@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_SUPER_ADMIN'));

        $users[] = $userManager->createUser()
            ->setUsername('pierre-louis@e-corses.com')
            ->setEmail('pierre-louis@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_SUPER_ADMIN'));


        /*
         * FA
         */
        $users[] = $userManager->createUser()
            ->setUsername('fa@e-corses.com')
            ->setEmail('fa@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_SUPER_ADMIN'));
        /*
         * Quentin
         */
        $users[] = $userManager->createUser()
            ->setUsername('quentin@e-corses.com')
            ->setEmail('quentin@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_SUPER_ADMIN'));

        /*
         * Ayrton
         */
        $users[] = $userManager->createUser()
            ->setUsername('assistance@e-corses.com')
            ->setEmail('assistance@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_SUPER_ADMIN'));
        /*
         * MARINE
         */
        $users[] = $userManager->createUser()
            ->setUsername('m.savreux@e-corses.com')
            ->setEmail('m.savreux@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_ADMIN'));

        /*
         * Web
         */
        $users[] = $userManager->createUser()
            ->setUsername('web@e-corses.com')
            ->setEmail('web@e-corses.com')
            ->setFirstConnexion(true)
            ->setRoles(array('ROLE_SUPER_ADMIN'));

        foreach ($users as $user){
            $pass = substr(hash('sha256',random_bytes(10)),0,10);


            $user->setPlainPassword($pass);
            var_dump($pass);

            $message = (new \Swift_Message('Création de compte'))
                ->setFrom('web@e-corses.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $templating->render(
                        '@ScyLabsNeptune/mail/mail_account.html.twig',
                        array(
                            'login' =>  $user->getEmail(),
                            'pass'  =>  $pass,
                        )
                    )
                    ,'text/html');
            $mailer->send($message);
            $userManager->updateUser($user);


        }

    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getOrder(){
        return 1;
    }
}