<?php
namespace App\Form;

use App\Entity\UserAssert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAssertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'First Name'])
            ->add('surname', TextType::class, ['label' => 'Last Name'])
            ->add('github_user', TextType::class, ['label' => 'GitHub Username']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserAssert::class,
        ]);
    }
}
