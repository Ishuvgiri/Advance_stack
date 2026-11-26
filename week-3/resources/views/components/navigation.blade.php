<aside class="sidebar">
    <div class="logo">
        Task Manager
    </div>
    <nav>
        @auth
        <a href="{{ route('tasks.index') }}"
            class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
            Tasks
        </a>

        <a href="{{ route('tasks.create') }}"
            class="nav-link {{ request()->routeIs('tasks.create') ? 'active' : '' }}">
            Create Task
        </a>

        <div class="nav-link">{{ auth()->user()->name }}</div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="nav-link" type="submit">Log Out</button>
        </form>
        @endauth

        @guest
        <a href="{{ route('login') }}" class="nav-link">Log In</a>
        <a href="{{ route('register') }}" class="nav-link">Register</a>
        @endguest
    </nav>

</aside>