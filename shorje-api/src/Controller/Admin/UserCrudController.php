<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('مستخدم')
            ->setEntityLabelInPlural('المستخدمين')
            ->setPageTitle('index', 'إدارة المستخدمين')
            ->setPageTitle('new', 'إضافة مستخدم جديد')
            ->setPageTitle('edit', 'تعديل المستخدم')
            ->setPageTitle('detail', 'تفاصيل المستخدم')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['email', 'firstName', 'lastName']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('إضافة مستخدم جديد');
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
            
            EmailField::new('email', 'البريد الإلكتروني')
                ->setRequired(true)
                ->setHelp('البريد الإلكتروني للمستخدم'),
            
            TextField::new('firstName', 'الاسم الأول')
                ->setHelp('الاسم الأول للمستخدم'),
            
            TextField::new('lastName', 'الاسم الأخير')
                ->setHelp('الاسم الأخير للمستخدم'),
            
            ChoiceField::new('roles', 'الأدوار')
                ->setChoices([
                    'مستخدم عادي' => 'ROLE_USER',
                    'مدير' => 'ROLE_ADMIN',
                    'مدير عام' => 'ROLE_SUPER_ADMIN'
                ])
                ->allowMultipleChoices()
                ->setHelp('أدوار المستخدم في النظام'),
            
            BooleanField::new('isVerified', 'مفعل')
                ->setHelp('هل تم تفعيل الحساب؟'),
            
            TextField::new('phoneNumber', 'رقم الهاتف')
                ->setHelp('رقم هاتف المستخدم')
                ->hideOnIndex(),
            
            IntegerField::new('age', 'العمر')
                ->setHelp('عمر المستخدم')
                ->hideOnIndex(),
            
            TextField::new('location', 'الموقع')
                ->setHelp('موقع المستخدم')
                ->hideOnIndex(),
            
            ChoiceField::new('gender', 'الجنس')
                ->setChoices([
                    'ذكر' => 'male',
                    'أنثى' => 'female'
                ])
                ->hideOnIndex(),
            
            TextareaField::new('bio', 'السيرة الذاتية')
                ->setHelp('نبذة عن المستخدم')
                ->hideOnIndex(),
            
            BooleanField::new('isOnline', 'متصل')
                ->setHelp('هل المستخدم متصل حالياً؟'),
            
            DateTimeField::new('lastSeenAt', 'آخر ظهور')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
        ];
    }
}
