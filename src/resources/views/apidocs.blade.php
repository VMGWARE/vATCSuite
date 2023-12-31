<!DOCTYPE html>
<html>

<head>
    <title>vATC Suite - API Documentation</title>

    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">

    <!-- Favicon -->
    @include('components.favicon')

    <!-- Meta Tags -->
    <meta name="description"
        content="The offical API documentation for vATC Suite, a web application that provides virtual air traffic controllers with essential tools like ATIS and AWOS generation to enhance realism in online flying networks.">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="https://atisgenerator.com/docs">
    <meta property="og:type" content="website">
    <meta property="og:title" content="vATC Suite - API Documentation">
    <meta property="og:description"
        content="The offical API documentation for vATC Suite, a web application that provides virtual air traffic controllers with essential tools like ATIS and AWOS generation to enhance realism in online flying networks.">
    <meta property="og:image" content="https://atisgenerator.com/lib/images/vatcsuite_logo_small.png">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:domain" content="atisgenerator.com">
    <meta property="twitter:url" content="https://atisgenerator.com/docs">
    <meta name="twitter:title" content="vATC Suite - API Documentation">
    <meta name="twitter:description"
        content="The offical API documentation for vATC Suite, a web application that provides virtual air traffic controllers with essential tools like ATIS and AWOS generation to enhance realism in online flying networks.">
    <meta name="twitter:image" content="https://atisgenerator.com/lib/images/vatcsuite_logo_small.png">


    <style>
        body {
            margin: 0;
        }
    </style>

    {{-- Matomo --}}
    @include('components.matomo')

    {{-- Google Analytics --}}
    @include('components.gtag')
</head>

<body>
    <script id="api-reference" data-url="/openapi"></script>
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
</body>

</html>
