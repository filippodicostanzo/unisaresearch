<?php

namespace App\Providers;

use App\Models\Author;
use App\Models\Category;
use App\Models\Edition;
use App\Models\Event;
use App\Models\Post;
use App\Models\Room;
use App\Models\Status;
use App\Models\Template;
use App\Models\User;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Auth;
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
            $this->app->bind('path.public', function () {
                return base_path() . '/';
            });
        }


        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            $edition = Edition::where('active', 1)->first();


            $array = [
                [
                    'text' => 'editions',
                    'url' => 'admin/editions',
                    'icon' => 'far fa-fw fa-dot-circle',
                    'label' => Edition::count(),
                    'label_color' => 'success',
                ],
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
                    'text' => 'coauthors',
                    'url' => 'admin/authors/all',
                    'icon' => 'fas fa-fw fa-user',
                    'label' => Author::count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'papers',
                    'url' => 'admin/posts/all',
                    'icon' => 'fas fa-fw fa-file',
                    'label' => Post::where('edition', $edition->id)->where('state', '!=', 1)->count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'supervisors',
                    'url' => 'admin/reviewers/count',
                    'icon' => 'fas fa-fw fa-at',
                    'label' => User::whereRoleIs('superadministrator')->orWhereRoleIs('supervisor')->count(),
                    'label_color' => 'success',
                ],
            ];
            $event->menu->addAfter('admin_settings', ...$array);
        });


        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            $edition = Edition::where('active', 1)->first();

            $array = [
                [
                    'text' => 'rooms',
                    'url' => 'admin/rooms',
                    'icon' => 'far fa-fw fa-building',
                    'label' => Room::where('edition', $edition['id'])->count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'events',
                    'url' => 'admin/events',
                    'icon' => 'far fa-fw fa-calendar-plus',
                    'label' => Event::where('edition', $edition['id'])->where('active', 1)->count(),
                    'label_color' => 'success',
                ],

                [
                    'text' => 'calendar',
                    'url' => 'admin/calendar',
                    'icon' => 'far fa-fw fa-calendar',
                    'label' => Event::where('edition', $edition['id'])->where('active', 1)->count(),
                    'label_color' => 'success',
                ]

            ];

            $event->menu->addAfter('calendar_settings', ...$array);
        });


        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            $administrators = User::whereRoleIs('superadministrator')->orwhereRoleIs('administrator')->get();
            $supervisors = User::whereRoleIs('supervisor')->get();
            $researchers = User::whereRoleIs('researcher')->get();
            $users = User::whereRoleIs('user')->get();

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
                    'text' => 'authors',
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


        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            $edition = Edition::where('active', 1)->first();

            $postcount = Post::where('edition', $edition['id'])->whereHas('users', function ($q) {
                $q->where('users.id', Auth::id());
            })->count();


            $array = [
                [
                    'text' => 'assigned_papers',
                    'url' => 'admin/posts/review',
                    'icon' => 'fas fa-fw fa-user-cog',
                    'label' => $postcount,
                    'label_color' => 'success',
                ],
            ];
            $event->menu->addAfter('reviewers_settings', ...$array);
        });


        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            $edition = Edition::where('active', 1)->first();
            $postcount = Post::where('created', Auth::id())->where('edition', $edition['id'])->count();


            $array = [

                [
                    'text' => 'my_authors',
                    'url' => 'admin/authors',
                    'icon' => 'fas fa-fw fa-user',
                    'label' => Author::whereHas('users', function ($q) {
                        $q->where('users.id', Auth::id());
                    })->count(),
                    'label_color' => 'success',
                ],
                [
                    'text' => 'my_papers',
                    'url' => 'admin/posts',
                    'icon' => 'fas fa-fw fa-file',
                    'label' => $postcount,
                    'label_color' => 'success',
                ],
            ];
            $event->menu->addAfter('authors_settings', ...$array);
        });


    }
}
