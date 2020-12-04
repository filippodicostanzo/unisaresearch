<?php

namespace App\Providers;

use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\Status;
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
                    'text' => 'statuses',
                    'url' => 'admin/statuses',
                    'icon' => 'fas fa-fw fa-eye',
                    'label' => Status::count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'authors',
                    'url' => 'admin/authors',
                    'icon' => 'fas fa-fw fa-user',
                     'label' => Author::count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'posts',
                    'url' => 'admin/posts',
                    'icon' => 'fas fa-fw fa-file',
                    'label' => Post::count(),
                    'label_color' => 'success',
                ],
            ];
            $event->menu->addAfter('admin_settings', ...$array);
        });


        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            $administrators = User::whereRoleIs('superadministrator')->orwhereRoleIs('administrator')->get();
            $supervisors  = User::whereRoleIs('supervisor')->get();
            $researchers = User::whereRoleIs('researcher')->get();
            $users  = User::whereRoleIs('user')->get();

            $array = [
                [
                    'text' => 'administrators',
                    'url' => 'admin/users?type=administrator',
                    'icon' => 'fas fa-fw fa-user-cog',
                    'label' => count($administrators),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'supervisors',
                    'url' => 'admin/users?type=supervisor',
                    'icon' => 'fas fa-fw fa-user-tag',
                    'label' => count($supervisors),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'researchers',
                    'url' => 'admin/users?type=researcher',
                    'icon' => 'fas fa-fw fa-user-edit',
                    'label' => count($researchers),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'users',
                    'url' => 'admin/users?type=user',
                    'icon' => 'fas fa-fw fa-user-clock',
                    'label' => count($users),
                    'label_color' => 'success',
                ],
            ];
            $event->menu->addAfter('users_settings', ...$array);
        });

    }
}
