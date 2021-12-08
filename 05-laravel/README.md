# \#5. Laravel

**TL;DR** Carefully read the guide *LBAW A8: Putting it all together* (made available by lecturers), and after that see the video by Prof. Tiago Boldt Sousa from last semester about the Laravel template ([here](https://uporto.cloud.panopto.eu/Panopto/Pages/Viewer.aspx?id=d4c6fa63-33b8-4214-9e25-adf201166694)) to understand what the guide says from a practical perspective.

## Intro

There are many useful features that Laravel provides; I'll be focusing on the most important ones. Laravel is mostly divided into sub-packages with their own names and functionalities.

## Models

You have PHP code, and you have your database (DB). You can use the PHP PDO interface to get data from the DB, which is fine for simple applications without much data. But when things start getting complicated, you're tempted to create PHP classes that mirror what you have in your DB: for each table storing instances of an entity, you create a PHP class to represent an instance of that entity. This is called object-relational mapping (ORM), because you're mapping a DB relation into a PHP object.

This is very useful in theory, but very hard in practice because you have to carefully code everything from scratch and take special care when you want to synchronize objects and relations. That's what Laravel package **Eloquent** provides: each DB table has a corresponding *Model* that is used to interact with that table.

```php
<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    use Notifiable;
    public $table = 'user';
    public $timestamps  = false;
    public $fillable = [
        'email',
        'name',
        'obs',
        'password',
        'img',
        'is_admin'
    ];

    // The attributes that should be casted to native types.
    protected $casts = [
        'id' => 'integer',
        'email' => 'string',
        'name' => 'string',
        'obs' => 'string',
        'password' => 'string',
        'img' => 'string',
        'is_admin' => 'boolean'
    ];

    // The attributes that should be hidden for arrays.
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Validation rules
    public static $rules = [

    ];

    public function reviews (){ return $this->belongsToMany(\App\Models\Work::class, 'review'); }
    public function works   (){ return $this->hasMany(\App\Models\Work::class); }
    public function loans   (){ return $this->hasMany(\App\Models\Loan::class); }
    public function wishList(){ return $this->belongsToMany(\App\Models\Work::class, 'wish_list'); }
    public function isAdmin (){ return $this->is_admin; }
}
```

There's also a very interesting but somewhat tricky feature of Eloquent that you can use: **traits**. Traits is a PHP feature, but Eloquent makes intensive use of it to share logic among models. Think of a trait as a characteristic of a model. Say that operations on some models should trigger notifications (e.g., when a user is edited you want to send an email notification); those models will share some code, because the same code can be used to send email notifications. As such, all those models have a common trait: that they can trigger notifications. As such, all those models can use the `Notifiable` trait, which is used as per the above example.

Models are placed in Laravel folder `app/Models`.

## Blade

Remember last session that we used the `include` construct to reuse the same footer across all pages. Now, Laravel has a special package for this: **Blade**. It is a very useful templating library, which allows you to define very flexible templates (e.g. a template for your whole site, keeping the same header/footer and changing the HTML body only) and reusable components (headers, footers, cards, comments, etc.).

**layouts/app.blade.php**
```html
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <!-- Stuff belonging to the head element... -->
</head>
<body>
@include('layouts.navbar')

<section id="content">
  @yield('content')
</section>

@include('layouts.footer')
@include('layouts.scripts')
</body>
</html>
```

**pages/about.blade.php**
```html
@extends('layouts.app')

@section('content')
  <div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mt-3">
        <li class="breadcrumb-item"><a href="{{ URL::to('/') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">About</li>
      </ol>
    </nav>
    <h1 class="mt-3">About</h1>
    <hr>
    [...]
  </div>
@endsection
```

The most important instructions are `@include` and `@yield`/`@extends`/`@section`/`@endsection`.

Blade templates and views are in Laravel folder `resources/views`.

When referring to a blade file from another blade file, you need to use the name of the file (i.e., if you have a blade file called `footer.blade.php` with a component, you can refer to it from other blade files as `footer`). Additionally, if your views are organized in folders (which they should be), you need to add the path relative to `resources/views`, but replace `/` (slash) with `.` (dot), so a blade file `app.blade.php` inside folder `layouts` (i.e., its path is `layouts/app.blade.php`) must be referred in other blade files as `layouts.app`.

**Aside:** Blade gives you a new way to include PHP in the middle of HTML: using `{{ <your PHP code> }}`, as you can see in the above example.

## Routes and controllers

**What is a route?** A route is one path that you can place in front of your domain name. For instance, if you browse URL <https://localhost:8000/user/login.php>, you're using route `/user/login.php` of your server. Pretty simple of a concept actually.

**How do routes usually work?** If you use the PHP development server, Apache or Nginx, you know there is a *document root* (in the case of the PHP server, it is the current directory; in the case of Apache and Nginx, the root is `/var/www/html` or similar). You'll notice that, for a request to a certain route, those servers will convert said route into a filesystem path, and try to serve whatever is at that path (e.g., for Apache to serve route `/user/login.php`, it will try to serve a file from the filesystem at path `/var/www/html/user/login.php`).

**What problems does that have?**
1. You are somewhat limited to the routes you can use: you always need to use extensions `.php` or `.html` (unless you call all your files `index.html` and `index.php` and place all those files in folders to match the routes you want).
2. It is cumbersome to define routes, because you have to create folders and place files in the right place.
3. You may want to define a route with format `/user/{user ID}`, to display data about a user with a certain ID. This looks very neat and all (e.g., for user ID 12 you'd have route `/user/12`), but it has two problems:
   1. You can't enumerate all possible routes and create a PHP file for each possible route.
   2. Avoiding the above problem using only pure PHP and Apache/Nginx requires extensive use of the rewrite engine, which is error-prone. This is made even more complicated if you'd want to create a route `/user/login`, because you'd have to make exceptions for some routes.
   3. Your rewritten URL would probably be `/user/?id={user ID}` and in script `/user/index.php` you could get the user ID, but it would be far more useful and semantic to actually get an object that represents the user instead of only its ID.

### Laravel routing

Under Laravel, all you have to do is implement a simple and standardized rewrite rule, so all routes are rewritten and redirected to a single file (called `index.php` at the server root), and have that file handle all the routing. This is exactly what Laravel does:

```php
Route::get('/'        , function () { return view('pages.index'   ) });
Route::get('/about'   , function () { return view('pages.about'   ) });
Route::get('/services', function () { return view('pages.services') });
Route::get('/faq'     , function () { return view('pages.faq'     ) });
Route::get('/contact' , function () { return view('pages.contact' ) });

Route::get ('/user/login', function () { return view('pages.user.login'); });
Route::post('/user/login', 'Auth\LoginController@login');

Route::delete('/item/{id}/comments/{comment_id}', 'CommentController@delete');
```

There are several functions you can use to create routes:
```php
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
```

`get`, `post`, `put` and `delete` (and sometimes `options`) are the most common ones, and correspond directly to the possible types of HTTP requests, so in the above example a GET request will return a view containing the login page in HTML, but a POST request to the same page will be handled by member function `login` of class `LoginController`.

Routes are defined in the PHP files of `routes` folder. Although you can define different routes for the API and the website by using files `routes/api.php` and `routes/web.php`, I advise you to design your website as an API of itself, and (when it makes sense) allow your routes to return different data formats according to the `Content-Type` header of the request. This allows you to be as organized as possible, as well as making your HTTP requests very semantic, because you use the request type to describe what you want to do (get a page with GET, change the DB with POST/PUT, delete an element with DELETE) and in the case of GET you use the `Content-Type` header to specify the format you want your data in.

### Controllers

Controllers are special classes that you can implement to handle requests that require more complex operations. To reply to a GET request you generally only need to return the view corresponding to that page, but to actually login a user, or editing the DB, is quite more complex than serving a login page.

```php
<?php

namespace App\Http\Controllers;

// Include models and convenient packages...

class CommentController extends Controller {
    // Some functions ...
    public function delete(Request $request, int $id, int $comment_id) {
        $comment = Comment::findOrFail($comment_id);

        if ((!Gate::allows('commentOwner', $comment)) && (!User::find($comment->user_id)->moderator())) {
            return redirect()->back();
        }

        $comment->delete();
    }
    // Some other functions ...
}
```

Controllers are defined in `app/Http/Controllers`.

## Putting it all together

To put it all together, see the *Putting it all together* guide.

## References

- Eloquent: https://laravel.com/docs/8.x/eloquent
- Blade: https://laravel.com/docs/8.x/blade
- Routing: https://laravel.com/docs/8.x/routing
