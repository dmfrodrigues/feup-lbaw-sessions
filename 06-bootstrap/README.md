# \#6. Bootstrap

Bootstrap is a CSS framework for front-end development, focused on responsiveness.

It provides many CSS classes which are roughly divided into two types:
- CSS shortcuts
- Animating elements with custom Bootstrap JS

## Getting started

To get started, add this line to all your pages' `<head>` element to ensure proper rendering and touch zooming for all devices (mobile and desktop):
```html
<meta name="viewport" content="width=device-width, initial-scale=1">
```

Add the following line somewhere in the `<head>` element to include the CSS part of Bootstrap:
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
```

Then, add the following line at the very end of the `<body>` element to include custom Bootstrap JS:
```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
```

You should alter the main Blade template you're using to include these lines, so you end up with a main template `layouts.app` similar to the following:

```html
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'lbaw21gg') }}</title>

  <!-- Styles -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
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

And a `layouts.scripts` file like the following:

```html
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

@yield('scripts')
```

## How to use Bootstrap

Bootstrap has so many classes and shortcuts that you won't have to write about 90% of the CSS you'd need if starting from scratch. As such, I won't be mentioning much about its endless classes, but you should always bear in mind that, if you think you need some custom CSS, Bootstrap likely already has it:

- Need an element to have no top margin? Use [CSS utility class `mt-0`](https://getbootstrap.com/docs/5.1/utilities/spacing/).
- Need a button signifying danger? Use [CSS utility class `text-danger`](https://getbootstrap.com/docs/5.1/utilities/colors/) to make it red.
- Need some notifications but `alert` is too ugly? Use the [*toasts* component](https://getbootstrap.com/docs/5.1/components/toasts/).
- Need some slides to show your images? Use the [*carousel* component](https://getbootstrap.com/docs/5.1/components/carousel/)
- Want to attract some birds? Use the [*breadcrumbs* component](https://getbootstrap.com/docs/5.1/components/breadcrumb/).

Add these CSS classes directly to your HTML elements in the views.
