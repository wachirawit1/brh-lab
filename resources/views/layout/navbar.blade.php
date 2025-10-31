<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('index') }}"><img src="{{ asset('assets/img/logo-brh.png') }}" alt=""
                width="45"></a> โรงพยาบาลบุรีรัมย์
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('index') ? 'active' : '' }}" href="{{ route('index') }}">หน้าแรก</a>
                </li>
                <li class="nav-item dropdown">

                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        @if (session()->has('user'))
                            {{ session('user.fullname') }}
                        @endif
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.notifySettings') }}">จัดการแจ้งเตือน</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.management') }}">จัดการผู้ใช้</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}">ออกจากระบบ</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
