<!DOCTYPE html>
<html>

<head>
    <title>vATC Suite - API Documentation</title>

    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">

    <!-- HTML Meta Tags -->
    @include('components.meta-tags')

    <!-- Favicon -->
    @include('components.favicon')

    <!--
    Redoc doesn't change outer page styles
    -->
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <redoc spec-url='/openapi'></redoc>
    <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
</body>

</html>
