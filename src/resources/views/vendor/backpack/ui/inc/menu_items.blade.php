{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="ATIS Audio Files" icon="la la-file-audio" :link="backpack_url('atis-audio-file')" />
<x-backpack::menu-item title="Airports" icon="la la-plane" :link="backpack_url('airport')" />

<!-- Users, Roles Permissions -->
<x-backpack::menu-dropdown title="Authentication" icon="la la-lock">
    <x-backpack::menu-dropdown-item title="Users" icon="la la-user" :link="backpack_url('user')" />
    <x-backpack::menu-dropdown-item title="Roles" icon="la la-group" :link="backpack_url('role')" />
    <x-backpack::menu-dropdown-item title="Permissions" icon="la la-key" :link="backpack_url('permission')" />
</x-backpack::menu-dropdown>

<!-- Advanced -->
<x-backpack::menu-dropdown title="Advanced" icon="la la-cog">
    <x-backpack::menu-dropdown-item title="Logs" icon="la la-list" :link="backpack_url('log')" />
    <x-backpack::menu-dropdown-item title="Settings" icon="la la-cog" :link="backpack_url('setting')" />
</x-backpack::menu-dropdown>

<!-- Resources -->
<x-backpack::menu-dropdown title="Resources" icon="la la-book">
    <x-backpack::menu-dropdown-item title="API Documentation" icon="la la-book" :link="route('docs')" />
    <x-backpack::menu-dropdown-item title="GitHub" icon="la la-github" :link="'https://github.com/VMGWARE/vATCSuite'" target="_blank" />
</x-backpack::menu-dropdown>