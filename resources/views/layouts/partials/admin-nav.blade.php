<div class="nav-label">Main</div>
<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</a>
<a href="{{ route('admin.calendar.index') }}" class="{{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-alt"></i> Calendar
</a>

<div class="nav-label">Management</div>
<a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
    <i class="fas fa-book"></i> Bookings
</a>
<a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
    <i class="fas fa-box-open"></i> Event Packages
</a>
<a href="{{ route('admin.appointments.index') }}" class="{{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
    <i class="fas fa-handshake"></i> Appointments
</a>
<a href="{{ route('admin.inventory.index') }}" class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
    <i class="fas fa-boxes-stacked"></i> Inventory
</a>

<div class="nav-label">Team</div>
<a href="{{ route('admin.admins.index') }}" class="{{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
    <i class="fas fa-user-shield"></i> Admin Accounts
</a>

<div class="nav-label">Clients</div>
<a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> Clients
</a>
<a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
    <i class="fas fa-star"></i> Reviews
</a>

<div class="nav-label">System</div>
<a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> Notifications
</a>
<a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> Reports
</a>
<a href="{{ route('admin.profile.edit') }}" class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
    <i class="fas fa-cog"></i> Settings
</a>