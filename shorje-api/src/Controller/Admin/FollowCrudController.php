<?php

namespace App\Controller\Admin;

use App\Entity\Follow;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class FollowCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Follow::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('متابعة')
            ->setEntityLabelInPlural('المتابعات')
            ->setPageTitle('index', 'إدارة المتابعات')
            ->setPageTitle('new', 'إضافة متابعة جديدة')
            ->setPageTitle('edit', 'تعديل المتابعة')
            ->setPageTitle('detail', 'تفاصيل المتابعة')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['follower.email', 'following.email']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('إضافة متابعة جديدة');
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
            
            AssociationField::new('follower', 'المتابع')
                ->setRequired(true)
                ->setHelp('المستخدم الذي يتابع'),
            
            AssociationField::new('following', 'المتابع له')
                ->setRequired(true)
                ->setHelp('المستخدم الذي يتم متابعته'),
            
            DateTimeField::new('createdAt', 'تاريخ المتابعة')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
        ];
    }
}
