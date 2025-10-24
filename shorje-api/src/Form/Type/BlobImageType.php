<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BlobImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $uploadedFile = $event->getData();
            $form = $event->getForm();
            $entity = $form->getParent()->getData();
            
            if ($uploadedFile instanceof UploadedFile && $entity instanceof \App\Entity\SliderImage) {
                // Read the file content
                $fileContent = file_get_contents($uploadedFile->getPathname());
                
                // Set the blob data and MIME type
                $entity->setImage($fileContent);
                $entity->setImageMimeType($uploadedFile->getMimeType());
                
                // Clear the form data to prevent Doctrine from trying to persist the UploadedFile
                $event->setData(null);
            }
        });
    }

    public function getParent(): string
    {
        return FileType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                    ],
                    'mimeTypesMessage' => 'يرجى رفع صورة صالحة (JPG, PNG, GIF, WebP)',
                ])
            ],
            'attr' => [
                'accept' => 'image/*'
            ]
        ]);
    }
}
