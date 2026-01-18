<?php

namespace App\Providers;

use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Eloquent\DiscountRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Repositories\Interfaces\DiscountRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\CloudinaryService;
use App\Services\Implementations\BrandService;
use App\Services\Implementations\CategoryService;
use App\Services\Implementations\DashboardService;
use App\Services\Implementations\DiscountService;
use App\Services\Implementations\OrderService;
use App\Services\Implementations\ProductService;
use App\Services\Implementations\ReviewService;
use App\Services\Implementations\UserService;
use App\Services\Interfaces\BrandServiceInterface;
use App\Services\Interfaces\CategoryServiceInterface;
use App\Services\Interfaces\DashboardServiceInterface;
use App\Services\Interfaces\DiscountServiceInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\ProductServiceInterface;
use App\Services\Interfaces\ReviewServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\OtpService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Service
        $this->app->bind(
            ProductServiceInterface::class,
            ProductService::class
        );

        $this->app->bind(
            ReviewServiceInterface::class,
            ReviewService::class
        );

        $this->app->singleton(CloudinaryService::class, function ($app) {
            return new CloudinaryService();
        });

        $this->app->bind(
            DiscountServiceInterface::class,
            DiscountService::class
        );

        $this->app->bind(
            UserServiceInterface::class,
            UserService::class
        );
        $this->app->bind(
            OrderServiceInterface::class,
            OrderService::class
        );
        $this->app->bind(
            BrandServiceInterface::class,
            BrandService::class
        );
        $this->app->bind(
            CategoryServiceInterface::class,
            CategoryService::class
        );

        $this->app->bind(
            DashboardServiceInterface::class,
            DashboardService::class
        );


        // Bind Repository
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        $this->app->bind(
            DiscountRepositoryInterface::class,
            DiscountRepository::class
        );

        $this->app->bind(
            ReviewRepositoryInterface::class,
            ReviewRepository::class
        );
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
        $this->app->bind(
            BrandRepositoryInterface::class,
            BrandRepository::class
        );
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );


        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );

        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}