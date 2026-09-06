<div class="nav-label">Main</div>
<a href="{{ route('client.dashboard') }}" class="{{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</a>

<div class="nav-label">Events</div>
<a href="{{ route('client.packages.index') }}" class="{{ request()->routeIs('client.packages.*') ? 'active' : '' }}">
    <i class="fas fa-box-open"></i> Event Packages
</a>
<a href="{{ route('client.bookings.index') }}" class="{{ request()->routeIs('client.bookings.index') || request()->routeIs('client.bookings.show') ? 'active' : '' }}">
    <i class="fas fa-book"></i> My Bookings
</a>
<a href="{{ route('client.reviews.index') }}" class="{{ request()->routeIs('client.reviews.*') ? 'active' : '' }}">
    <i class="fas fa-star"></i> My Reviews
</a>

<div class="nav-label">Account</div>
<a href="{{ route('client.notifications.index') }}" class="{{ request()->routeIs('client.notifications.*') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> Notifications
</a>
<a href="{{ route('client.profile.edit') }}" class="{{ request()->routeIs('client.profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
</a>