<?php
namespace App\Form;

use App\Entity\User;
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

/**
 * Class UserType
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ContainerInterface $container */
        $container = $options['container'];

        $builder
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('name', TextType::class, [
                'label'    => 'First Name',
                'required' => false,
            ])
            ->add('surname', TextType::class, [
                'label'    => 'Last Name',
                'required' => false,
            ])
            ->add('github_user', TextType::class, [
                'label'    => 'GitHub Username',
                'required' => false,
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (png or jpeg file)',
            ])
            ->add('roles', ChoiceType::class, [
                'expanded' => false,
                'multiple' => true,
                'choices'  => $container
                    ->get('raisch.user.hierarchy')
                    ->getAssignableRoles(),
            ]);
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['container']);
        $resolver->setDefaults([
            'data_class' => User::class,
            'container'  => ContainerInterface::class,
        ]);
    }
}
