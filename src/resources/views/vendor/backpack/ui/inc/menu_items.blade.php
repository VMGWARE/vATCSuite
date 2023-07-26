{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="ATIS Audio Files" icon="la la-file-audio" :link="backpack_url('atis-audio-file')" />
<x-backpack::menu-item title="Airports" icon="la la-plane" :link="backpack_url('airport')" />
<x-backpack::menu-item title="Users" icon="la la-user" :link="backpack_url('user')" />
