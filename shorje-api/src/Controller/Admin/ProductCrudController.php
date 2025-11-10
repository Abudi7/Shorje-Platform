<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('منتج')
            ->setEntityLabelInPlural('المنتجات')
            ->setPageTitle('index', 'إدارة المنتجات')
            ->setPageTitle('new', 'إضافة منتج جديد')
            ->setPageTitle('edit', 'تعديل المنتج')
            ->setPageTitle('detail', 'تفاصيل المنتج')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['title', 'description', 'category']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('إضافة منتج جديد');
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
            
            TextField::new('title', 'اسم المنتج')
                ->setRequired(true)
                ->setHelp('اسم المنتج'),
            
            TextareaField::new('description', 'الوصف')
                ->setHelp('وصف المنتج')
                ->hideOnIndex(),
            
            MoneyField::new('price', 'السعر')
                ->setCurrency('IQD')
                ->setRequired(true)
                ->setHelp('سعر المنتج بالدينار العراقي'),
            
            TextField::new('category', 'الفئة')
                ->setHelp('فئة المنتج')
                ->hideOnIndex(),
            
            TextField::new('location', 'الموقع')
                ->setHelp('موقع المنتج')
                ->hideOnIndex(),
            
            ChoiceField::new('status', 'الحالة')
                ->setChoices([
                    'متاح' => 'available',
                    'مباع' => 'sold',
                    'محجوز' => 'reserved'
                ])
                ->setHelp('حالة المنتج'),
            
            AssociationField::new('seller', 'البائع')
                ->setRequired(true)
                ->setHelp('البائع الذي يملك المنتج'),
            
            // الحقول التالية غير موجودة في الكيان Product حالياً (تسببت بخطأ):
            // viewCount, isFeatured
            // عند إضافتها للكيان يمكن إعادة تفعيلها هنا.
            
            DateTimeField::new('createdAt', 'تاريخ الإنشاء')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
            
            DateTimeField::new('updatedAt', 'تاريخ التحديث')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(),
        ];
    }
}
