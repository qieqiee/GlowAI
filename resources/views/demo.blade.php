<h1>GlowAI Demo Flow</h1>

<h2>Admin</h2>
<ul>
    <li><a href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
    <li><a href="{{ route('admin.users.index') }}">Manage Users</a></li>
    <li><a href="{{ route('admin.muas.index') }}">Manage MUAs</a></li>
    <li><a href="{{ route('admin.booking.index') }}">Manage Bookings</a></li>
</ul>

<hr>

<h2>Customer</h2>
<p>Login as customer account first.</p>
<ul>
    <li><a href="{{ route('customer.mua.index') }}">Search MUA</a></li>
    <li><a href="{{ route('customer.bookings.index') }}">My Bookings</a></li>
</ul>

<hr>

<h2>MUA</h2>
<p>Login as MUA account first.</p>
<ul>
    <li><a href="{{ route('mua.bookings.index') }}">Manage Booking Requests</a></li>
</ul>