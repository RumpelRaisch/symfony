<?php
namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class UserType
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ContainerInterface $container */
        $container = $options['container'];

        /** @var AuthorizationChecker $authChecker */
        $authChecker = $container->get('security.authorization_checker');

        /** @var RoleRepository $roleRepo */
        $roleRepo = $options['repository_role'];

        /** @var Role[] $roles */
        $roles = $roleRepo
            ->createQueryBuilder('r')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->execute();

        $choises = [];

        foreach ($roles as $role) {
            if ($authChecker->isGranted($role->getIsGranted())) {
                $choises[$role->getName()] = $role->getName();
            }
        }

        $builder
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('name', TextType::class, ['label' => 'First Name', 'required' => false])
            ->add('surname', TextType::class, ['label' => 'Last Name', 'required' => false])
            ->add('github_user', TextType::class, ['label' => 'GitHub Username', 'required' => false])
            ->add('avatar', FileType::class, array('label' => 'Avatar (png or jpeg file)'))
            ->add('roles', ChoiceType::class, [
                'expanded' => false,
                'multiple' => true,
                'choices'  => $choises,
            ]);
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['container', 'repository_role']);
        $resolver->setDefaults([
            'data_class'      => User::class,
            'container'       => ContainerInterface::class,
            'repository_role' => RoleRepository::class,
        ]);
    }
}
