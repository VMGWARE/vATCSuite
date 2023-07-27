<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>vATC Suite</title>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="{{ asset('/lib/css/overrides.css') }}" rel="stylesheet">

    <script src="https://kit.fontawesome.com/6a94b5c85f.js" crossorigin="anonymous"></script>

    <!-- HTML Meta Tags -->
    @include('components.meta-tags')

    <!-- Favicon -->
    @include('components.favicon')

    {{-- Matomo --}}
    @include('components.matomo')

    {{-- Google Analytics --}}
    @include('components.gtag')
</head>

<body>
    {{-- Content --}}
    <div class="content">
        @yield('content')
    </div>

    {{-- Footer --}}
    @yield('footer')

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="{{ asset('/lib/js/redbeard.js') }}"></script>
</body>

</html>
