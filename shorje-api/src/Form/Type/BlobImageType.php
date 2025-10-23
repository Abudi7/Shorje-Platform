<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BlobImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            
            if ($data instanceof \App\Entity\SliderImage && $data->getImage() instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $uploadedFile = $data->getImage();
                
                // Read the file content
                $fileContent = file_get_contents($uploadedFile->getPathname());
                
                // Set the blob data
                $data->setImage($fileContent);
                
                // Set the MIME type
                $data->setImageMimeType($uploadedFile->getMimeType());
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
