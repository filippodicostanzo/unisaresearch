<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Template;
use App\Models\User;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        if (config('fdc.app_public') == true) {
            $this->app->bind('path.public', function() {
                return base_path().'/';
            });
        }


        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            $array = [
                [
                    'text' => 'templates',
                    'url' => 'admin/templates',
                    'icon' => 'far fa-fw fa-file',
                    'label' => Template::count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'categories',
                    'url' => 'admin/categories',
                    'icon' => 'fas fa-fw fa-tags',
                    'label' => Category::count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'authors',
                    'url' => 'admin/authors',
                    'icon' => 'fas fa-fw fa-user',
                     'label' => Category::count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'posts',
                    'url' => 'admin/posts',
                    'icon' => 'fas fa-fw fa-file',
                  //  'label' => Category::count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'users',
                    'url' => 'admin/users',
                    'icon' => 'fas fa-fw fa-users',
                    'label' => User::count(),
                    'label_color' => 'success',
                ],
            ];
            $event->menu->addAfter('admin_settings', ...$array);
        });


    }
}
