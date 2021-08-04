<nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">Doctor Accounting</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ ($title === "Home") ? 'active' : '' }}" href="/doctoraccounting/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($title === "Dokumentasi") ? 'active' : '' }}" href="/doctoraccounting/dokumentasi">Dokumentasi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($title === "FAQ") ? 'active' : '' }}" href="/doctoraccounting/faq">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($title === "Download") ? 'active' : '' }}" href="/doctoraccounting/download">Download</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($title === "Harga") ? 'active' : '' }}" href="/doctoraccounting/harga">Harga</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($title === "Client") ? 'active' : '' }}" href="/doctoraccounting/client">Client</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($title === "Tentang Kami") ? 'active' : '' }}" href="/doctoraccounting/tentangkami">Tentang Kami</a>
                </li>
            </ul>
        </div> 
    </div>
</nav>
