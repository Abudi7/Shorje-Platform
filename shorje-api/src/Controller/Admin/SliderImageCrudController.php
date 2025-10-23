<?php

namespace App\Controller\Admin;

use App\Entity\SliderImage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use App\Form\Type\BlobImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class SliderImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SliderImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('صورة السلايدر')
            ->setEntityLabelInPlural('صور السلايدر')
            ->setPageTitle('index', 'إدارة صور السلايدر')
            ->setPageTitle('new', 'إضافة صورة سلايدر جديدة')
            ->setPageTitle('edit', 'تعديل صورة السلايدر')
            ->setPageTitle('detail', 'تفاصيل صورة السلايدر')
            ->setDefaultSort(['sortOrder' => 'ASC'])
            ->setSearchFields(['title', 'description']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('إضافة صورة جديدة');
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
            
            TextField::new('title', 'العنوان')
                ->setRequired(true)
                ->setHelp('عنوان الصورة الذي سيظهر في السلايدر'),
            
            TextareaField::new('description', 'الوصف')
                ->setHelp('وصف مختصر للصورة')
                ->hideOnIndex(),
            
            Field::new('image', 'الصورة')
                ->setRequired(true)
                ->setHelp('اختر صورة للسلايدر (JPG, PNG, GIF)')
                ->hideOnIndex()
                ->hideOnDetail()
                ->setFormType(BlobImageType::class)
                ->setFormTypeOption('mapped', true)
                ->setTemplatePath('admin/fields/blob_image.html.twig'),
            
            Field::new('imagePreview', 'معاينة الصورة')
                ->hideOnForm()
                ->hideOnIndex()
                ->setTemplatePath('admin/fields/image_preview.html.twig'),
            
            TextField::new('buttonText', 'نص الزر الأول')
                ->setHelp('النص الذي سيظهر على الزر الأول')
                ->hideOnIndex(),
            
            TextField::new('buttonUrl', 'رابط الزر الأول')
                ->setHelp('الرابط الذي سيتم الانتقال إليه عند الضغط على الزر الأول')
                ->hideOnIndex(),
            
            TextField::new('buttonText2', 'نص الزر الثاني')
                ->setHelp('النص الذي سيظهر على الزر الثاني')
                ->hideOnIndex(),
            
            TextField::new('buttonUrl2', 'رابط الزر الثاني')
                ->setHelp('الرابط الذي سيتم الانتقال إليه عند الضغط على الزر الثاني')
                ->hideOnIndex(),
            
            BooleanField::new('isActive', 'نشط')
                ->setHelp('هل تريد عرض هذه الصورة في السلايدر؟'),
            
            IntegerField::new('sortOrder', 'ترتيب العرض')
                ->setHelp('ترتيب عرض الصورة في السلايدر (الأقل = الأول)'),
            
            DateTimeField::new('createdAt', 'تاريخ الإنشاء')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm'),
            
            DateTimeField::new('updatedAt', 'تاريخ التحديث')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm'),
        ];
    }
}
