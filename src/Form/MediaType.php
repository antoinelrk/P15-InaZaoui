<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['is_admin']) {
            $builder
                ->add('user', EntityType::class, [
                    'label' => 'Utilisateur',
                    'required' => false,
                    'class' => User::class,
                    'choice_label' => 'name',
                    'query_builder' => function (UserRepository $userRepository) {
                        return $userRepository->createQueryBuilder('u')
                            ->where('u.active = :active')
                            ->setParameter('active', true)
                            ->orderBy('u.name', 'ASC');
                    },
                ])
                ->add('album', EntityType::class, [
                    'label' => 'Album',
                    'required' => false,
                    'class' => Album::class,
                    'choice_label' => 'name',
                ])
            ;
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('file', FileType::class, [
                'label' => 'Image',
                'constraints'=> [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/webp',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => "Le format de l'image n'est pas valide, seules les images WebP, JPEG et PNG sont acceptées",
                        'maxSizeMessage' => "L'image est trop volumineuse, la taille maximale autorisée est de 2 Mo",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'is_admin' => false,
        ]);
    }
}
