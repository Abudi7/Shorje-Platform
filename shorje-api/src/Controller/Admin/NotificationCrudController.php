<?php

namespace App\Controller\Admin;

use App\Entity\Notification;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class NotificationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Notification::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('إشعار')
            ->setEntityLabelInPlural('الإشعارات')
            ->setPageTitle('index', 'إدارة الإشعارات')
            ->setPageTitle('new', 'إضافة إشعار جديد')
            ->setPageTitle('edit', 'تعديل الإشعار')
            ->setPageTitle('detail', 'تفاصيل الإشعار')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['title', 'message']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('إضافة إشعار جديد');
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
            
            AssociationField::new('user', 'المستخدم')
                ->setRequired(true)
                ->setHelp('المستخدم الذي سيستقبل الإشعار'),
            
            TextField::new('title', 'عنوان الإشعار')
                ->setRequired(true)
                ->setHelp('عنوان الإشعار'),
            
            TextareaField::new('message', 'رسالة الإشعار')
                ->setRequired(true)
                ->setHelp('محتوى الإشعار'),
            
            ChoiceField::new('type', 'نوع الإشعار')
                ->setChoices([
                    'رسالة جديدة' => 'message',
                    'منتج جديد' => 'product',
                    'متابعة جديدة' => 'follow',
                    'عام' => 'general'
                ])
                ->setHelp('نوع الإشعار'),
            
            BooleanField::new('isRead', 'مقروء')
                ->setHelp('هل تم قراءة الإشعار؟'),
            
            AssociationField::new('product', 'المنتج')
                ->setHelp('المنتج المرتبط بالإشعار (اختياري)')
                ->hideOnIndex(),
            
            DateTimeField::new('createdAt', 'تاريخ الإنشاء')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
        ];
    }
}
