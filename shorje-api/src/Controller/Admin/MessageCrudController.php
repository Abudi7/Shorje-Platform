<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class MessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Message::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('رسالة')
            ->setEntityLabelInPlural('الرسائل')
            ->setPageTitle('index', 'إدارة الرسائل')
            ->setPageTitle('new', 'إضافة رسالة جديدة')
            ->setPageTitle('edit', 'تعديل الرسالة')
            ->setPageTitle('detail', 'تفاصيل الرسالة')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['content']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('إضافة رسالة جديدة');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('تعديل');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('حذف');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'المعرف')->hideOnForm(),
            
            AssociationField::new('sender', 'المرسل')
                ->setRequired(true)
                ->setHelp('المستخدم الذي أرسل الرسالة'),
            
            AssociationField::new('receiver', 'المستقبل')
                ->setRequired(true)
                ->setHelp('المستخدم الذي استقبل الرسالة'),
            
            TextareaField::new('content', 'محتوى الرسالة')
                ->setRequired(true)
                ->setHelp('نص الرسالة'),
            
            BooleanField::new('isRead', 'مقروءة')
                ->setHelp('هل تم قراءة الرسالة؟'),
            
            BooleanField::new('isDelivered', 'تم التسليم')
                ->setHelp('هل تم تسليم الرسالة؟'),
            
            DateTimeField::new('createdAt', 'تاريخ الإرسال')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
            
            DateTimeField::new('seenAt', 'تاريخ المشاهدة')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
            
            DateTimeField::new('readAt', 'تاريخ القراءة')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
        ];
    }
}
