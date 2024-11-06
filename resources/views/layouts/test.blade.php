

<a href="{{ route('logout') }}"
 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
 <div class="text">Logout</div>
</a>
<form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
    @csrf
</form>