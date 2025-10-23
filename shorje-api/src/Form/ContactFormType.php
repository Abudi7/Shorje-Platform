<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'الاسم',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white',
                    'placeholder' => 'أدخل اسمك الكامل'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'الاسم مطلوب'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'الاسم يجب أن يكون على الأقل {{ limit }} أحرف',
                        'maxMessage' => 'الاسم يجب أن يكون أقل من {{ limit }} حرف'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'البريد الإلكتروني',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white',
                    'placeholder' => 'أدخل بريدك الإلكتروني'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'البريد الإلكتروني مطلوب'
                    ]),
                    new Assert\Email([
                        'message' => 'البريد الإلكتروني غير صحيح'
                    ])
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'الموضوع',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white',
                    'placeholder' => 'أدخل موضوع الرسالة'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'الموضوع مطلوب'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 200,
                        'minMessage' => 'الموضوع يجب أن يكون على الأقل {{ limit }} أحرف',
                        'maxMessage' => 'الموضوع يجب أن يكون أقل من {{ limit }} حرف'
                    ])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'الرسالة',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white',
                    'placeholder' => 'أدخل رسالتك هنا...',
                    'rows' => 5
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'الرسالة مطلوبة'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'الرسالة يجب أن تكون على الأقل {{ limit }} أحرف',
                        'maxMessage' => 'الرسالة يجب أن تكون أقل من {{ limit }} حرف'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'إرسال الرسالة',
                'attr' => [
                    'class' => 'bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'contact_form',
        ]);
    }
}
