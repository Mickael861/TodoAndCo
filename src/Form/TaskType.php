<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $is_author = $options['data']->getAuthor() === null;

        $builder
            ->add('title')
            ->add('content', TextareaType::class)
            ->add('author', TextType::class, [
                'disabled' => $is_author ? false : true,
                'help' => $is_author ? '' : "L'auteur ne peut-être modifié lors d'une édition"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
