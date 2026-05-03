@if(auth()->user()->role === 'seller')
    @include('profile.edit-seller')
@elseif(auth()->user()->role === 'buyer')
    @include('profile.edit-buyer')
@else
    @include('profile.edit-admin')
@endif
