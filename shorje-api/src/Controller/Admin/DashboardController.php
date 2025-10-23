<?php

namespace App\Controller\Admin;

use App\Entity\SliderImage;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Message;
use App\Entity\Follow;
use App\Entity\Notification;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('لوحة تحكم شورجي')
            ->setFaviconPath('favicon.ico')
            ->setTextDirection('rtl');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('الرئيسية', 'fa fa-home');
        
        // إدارة المحتوى
        yield MenuItem::section('إدارة المحتوى', 'fa fa-cogs');
        yield MenuItem::linkToCrud('صور السلايدر', 'fa fa-images', SliderImage::class)
            ->setController(SliderImageCrudController::class);
        yield MenuItem::linkToCrud('المنتجات', 'fa fa-box', Product::class)
            ->setController(ProductCrudController::class);
        
        // إدارة المستخدمين
        yield MenuItem::section('إدارة المستخدمين', 'fa fa-users');
        yield MenuItem::linkToCrud('المستخدمين', 'fa fa-user', User::class)
            ->setController(UserCrudController::class);
        yield MenuItem::linkToCrud('المتابعات', 'fa fa-heart', Follow::class)
            ->setController(FollowCrudController::class);
        
        // إدارة التواصل
        yield MenuItem::section('إدارة التواصل', 'fa fa-comments');
        yield MenuItem::linkToCrud('الرسائل', 'fa fa-envelope', Message::class)
            ->setController(MessageCrudController::class);
        yield MenuItem::linkToCrud('الإشعارات', 'fa fa-bell', Notification::class)
            ->setController(NotificationCrudController::class);
    }
}
