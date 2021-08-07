<!-- Header Section Start -->
<header id="home" class="hero-area-2">    
    <div class="overlay"></div>
    <nav class="navbar navbar-expand-md bg-inverse fixed-top scrolling-navbar">
        <div class="container">
            <a href="index.html" class="navbar-brand"><img src="../images/doctoraccounting/logo.png" alt=""></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <i class="lni-menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto w-100 justify-content-end">
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

    @if ($title === "Home")
        <div class="container">      
            <div class="row space-100">
                <div class="col-lg-7 col-md-12 col-xs-12">
                    <div class="contents">
                        <h2 class="head-title">Get Your App Landing Page <br> With Proton Template</h2>
                        <p>lorem ipsum dolor sit amet, consectetur adipisicing elit. Esse unde blanditiis nostrum mollitia aliquam sed. Numquam ipsum unde repellendus similique autem non ab quibusdam enim provident distinctio! Fugit tenetur, iusto.</p>
                        <div class="header-button">
                            <a href="#" class="btn btn-border-filled">Learn More</a>
                            <a href="#" class="btn btn-border">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 col-xs-12">
                    <div class="intro-img">
                        <img src="../images/doctoraccounting/intro-mobile.png" alt="">
                    </div>            
                </div>
            </div> 
        </div>
    @endif

</header>
<!-- Header Section End --> 