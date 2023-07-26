@extends(backpack_view('blank'))

@php
    $widgets['before_content'][] = [
        'type' => 'jumbotron',
        'heading' => trans('backpack::base.welcome'),
        'content' => 'Welcome to vATC Suite Admin Panel. This panel provides administrative access to the vATC Suite, enabling you to manage ATIS and AWOS audio files, airports, and users.',
    ];
@endphp

@section('content')
@endsection
