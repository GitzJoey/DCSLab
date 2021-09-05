<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="description" content="DCSLab">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>{{ __('front.title') }}</title>

        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/ico">

        <link rel="stylesheet" href="{{ mix('css/start/main.css') }}">
    </head>

    <body>
        <header class="header-area">
            <div class="navgition navgition-transparent">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <nav class="navbar navbar-expand-lg">
                                <a class="navbar-brand" href="">
                                    <img src="{{ asset('images/g_logo.png') }}" alt="Logo" width="50px" height="50px">
                                </a>

                                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarOne" aria-controls="navbarOne" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="toggler-icon"></span>
                                    <span class="toggler-icon"></span>
                                    <span class="toggler-icon"></span>
                                </button>

                                <div class="collapse navbar-collapse sub-menu-bar" id="navbarOne">
                                    <ul class="navbar-nav m-auto">
                                        <li class="nav-item active">
                                            <a class="page-scroll" href="#home">HOME</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="page-scroll" href="#service">SERVICES</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="page-scroll" href="#pricing">PRICING</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="page-scroll" href="#contact">CONTACT</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('login') }}">DASHBOARD</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="navbar-social d-none d-sm-flex align-items-center">
                                    <span>FOLLOW US</span>
                                    <ul>
                                        <li><a href="#"><i class="lni-facebook-filled"></i></a></li>
                                        <li><a href="#"><i class="lni-twitter-original"></i></a></li>
                                        <li><a href="#"><i class="lni-instagram-original"></i></a></li>
                                        <li><a href="#"><i class="lni-linkedin-original"></i></a></li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div id="home" class="header-hero bg_cover" style="background-image: url('{{ asset('images/bg1.jpg') }}')">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-10">
                            <div class="header-content text-center">
                                <h3 class="header-title">DCSLab</h3>
                                <p class="text">One Stop Business Solutions. Crafted With Latest Technology In Mind</p>
                                <ul class="header-btn">
                                    <li><a class="main-btn btn-one" href="">GET IN TOUCH</a></li>
                                    <li><a class="main-btn btn-two video-popup" href="">WATCH THE VIDEO <i class="lni-play"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header-shape">
                    <img src="{{ asset('images/header-shape.svg') }}" alt="shape">
                </div>
            </div>
        </header>

        <section id="service" class="services-area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-title pb-10">
                            <h4 class="title">Services</h4>
                            <p class="text">Stop wasting time and money that doesn’t get results<br/>Happiness guaranteed!</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="services-content mt-40 d-sm-flex">
                                    <div class="services-icon">
                                        <i class="lni-bolt"></i>
                                    </div>
                                    <div class="services-content media-body">
                                        <h4 class="services-title">Startup</h4>
                                        <p class="text">Company profile, landing page, startup page or just start something cool.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="services-content mt-40 d-sm-flex">
                                    <div class="services-icon">
                                        <i class="lni-bar-chart"></i>
                                    </div>
                                    <div class="services-content media-body">
                                        <h4 class="services-title">Business Solutions</h4>
                                        <p class="text">Point of Sales, Warehouse, CRM, Point System or any solutions to support your business</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="services-content mt-40 d-sm-flex">
                                    <div class="services-icon">
                                        <i class="lni-code-alt"></i>
                                    </div>
                                    <div class="services-content media-body">
                                        <h4 class="services-title">Custom Services</h4>
                                        <p class="text">Fully customizeable framework to handle any services or business requirement</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="services-content mt-40 d-sm-flex">
                                    <div class="services-icon">
                                        <i class="lni-bulb"></i>
                                    </div>
                                    <div class="services-content media-body">
                                        <h4 class="services-title">Consultancy</h4>
                                        <p class="text">Our team with 15 more year experiences in IT world, ready to help.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="services-image d-lg-flex align-items-center">
                <div class="image">
                    <img src="{{ asset('images/comp.jpeg') }}" alt="Services">
                </div>
            </div>
        </section>

        <section id="pricing" class="pricing-area">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="section-title text-center pb-10">
                            <h4 class="title">Pricing</h4>
                            <p class="text">Please use our Contact page or email to our support for more flexible pricing</p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-7 col-sm-9">
                        <div class="single-pricing mt-40">
                            <div class="pricing-header text-center">
                                <h5 class="sub-title">Basic</h5>
                                <span class="price">$ 199</span>
                                <p class="year">per year</p>
                            </div>
                            <div class="pricing-list">
                                <ul>
                                    <li><i class="lni-check-mark-circle"></i> Carefully crafted components</li>
                                    <li><i class="lni-check-mark-circle"></i> Amazing page examples</li>
                                    <li><i class="lni-check-mark-circle"></i> Super friendly support team</li>
                                    <li><i class="lni-check-mark-circle"></i> Awesome Support</li>
                                </ul>
                            </div>
                            <div class="pricing-btn text-center">
                                <a class="main-btn" href="#">GET STARTED</a>
                            </div>
                            <div class="buttom-shape">
                                <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 350 112.35"><defs><style>.color-1{fill:#2bdbdc;isolation:isolate;}.cls-1{opacity:0.1;}.cls-2{opacity:0.2;}.cls-3{opacity:0.4;}.cls-4{opacity:0.6;}</style></defs><title>bottom-part1</title><g id="bottom-part"><g id="Group_747" data-name="Group 747"><path id="Path_294" data-name="Path 294" class="cls-1 color-1" d="M0,24.21c120-55.74,214.32,2.57,267,0S349.18,7.4,349.18,7.4V82.35H0Z" transform="translate(0 0)"/><path id="Path_297" data-name="Path 297" class="cls-2 color-1" d="M350,34.21c-120-55.74-214.32,2.57-267,0S.82,17.4.82,17.4V92.35H350Z" transform="translate(0 0)"/><path id="Path_296" data-name="Path 296" class="cls-3 color-1" d="M0,44.21c120-55.74,214.32,2.57,267,0S349.18,27.4,349.18,27.4v74.95H0Z" transform="translate(0 0)"/><path id="Path_295" data-name="Path 295" class="cls-4 color-1" d="M349.17,54.21c-120-55.74-214.32,2.57-267,0S0,37.4,0,37.4v74.95H349.17Z" transform="translate(0 0)"/></g></g></svg>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-7 col-sm-9">
                        <div class="single-pricing pro mt-40">
                            <div class="pricing-baloon">
                                <img src="{{ asset('images/baloon.svg') }}" alt="baloon">
                            </div>
                            <div class="pricing-header">
                                <h5 class="sub-title">Pro</h5>
                                <span class="price">$ 399</span>
                                <p class="year">per year</p>
                            </div>
                            <div class="pricing-list">
                                <ul>
                                    <li><i class="lni-check-mark-circle"></i> Carefully crafted components</li>
                                    <li><i class="lni-check-mark-circle"></i> Amazing page examples</li>
                                    <li><i class="lni-check-mark-circle"></i> Super friendly support team</li>
                                    <li><i class="lni-check-mark-circle"></i> Awesome Support</li>
                                </ul>
                            </div>
                            <div class="pricing-btn text-center">
                                <a class="main-btn" href="#">GET STARTED</a>
                            </div>
                            <div class="buttom-shape">
                                <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 350 112.35"><defs><style>.color-2{fill:#0067f4;isolation:isolate;}.cls-1{opacity:0.1;}.cls-2{opacity:0.2;}.cls-3{opacity:0.4;}.cls-4{opacity:0.6;}</style></defs><title>bottom-part1</title><g id="bottom-part"><g id="Group_747" data-name="Group 747"><path id="Path_294" data-name="Path 294" class="cls-1 color-2" d="M0,24.21c120-55.74,214.32,2.57,267,0S349.18,7.4,349.18,7.4V82.35H0Z" transform="translate(0 0)"/><path id="Path_297" data-name="Path 297" class="cls-2 color-2" d="M350,34.21c-120-55.74-214.32,2.57-267,0S.82,17.4.82,17.4V92.35H350Z" transform="translate(0 0)"/><path id="Path_296" data-name="Path 296" class="cls-3 color-2" d="M0,44.21c120-55.74,214.32,2.57,267,0S349.18,27.4,349.18,27.4v74.95H0Z" transform="translate(0 0)"/><path id="Path_295" data-name="Path 295" class="cls-4 color-2" d="M349.17,54.21c-120-55.74-214.32,2.57-267,0S0,37.4,0,37.4v74.95H349.17Z" transform="translate(0 0)"/></g></g></svg>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-7 col-sm-9">
                        <div class="single-pricing enterprise mt-40">
                            <div class="pricing-flower">
                                <img src="{{ asset('images/flower.svg') }}" alt="flower">
                            </div>
                            <div class="pricing-header text-right">
                                <h5 class="sub-title">Enterprise</h5>
                                <span class="price">$ 799</span>
                                <p class="year">per year</p>
                            </div>
                            <div class="pricing-list">
                                <ul>
                                    <li><i class="lni-check-mark-circle"></i> Carefully crafted components</li>
                                    <li><i class="lni-check-mark-circle"></i> Amazing page examples</li>
                                    <li><i class="lni-check-mark-circle"></i> Super friendly support team</li>
                                    <li><i class="lni-check-mark-circle"></i> Awesome Support</li>
                                </ul>
                            </div>
                            <div class="pricing-btn text-center">
                                <a class="main-btn" href="#">GET STARTED</a>
                            </div>
                            <div class="buttom-shape">
                                <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 350 112.35"><defs><style>.color-3{fill:#4da422;isolation:isolate;}.cls-1{opacity:0.1;}.cls-2{opacity:0.2;}.cls-3{opacity:0.4;}.cls-4{opacity:0.6;}</style></defs><title>bottom-part1</title><g id="bottom-part"><g id="Group_747" data-name="Group 747"><path id="Path_294" data-name="Path 294" class="cls-1 color-3" d="M0,24.21c120-55.74,214.32,2.57,267,0S349.18,7.4,349.18,7.4V82.35H0Z" transform="translate(0 0)"/><path id="Path_297" data-name="Path 297" class="cls-2 color-3" d="M350,34.21c-120-55.74-214.32,2.57-267,0S.82,17.4.82,17.4V92.35H350Z" transform="translate(0 0)"/><path id="Path_296" data-name="Path 296" class="cls-3 color-3" d="M0,44.21c120-55.74,214.32,2.57,267,0S349.18,27.4,349.18,27.4v74.95H0Z" transform="translate(0 0)"/><path id="Path_295" data-name="Path 295" class="cls-4 color-3" d="M349.17,54.21c-120-55.74-214.32,2.57-267,0S0,37.4,0,37.4v74.95H349.17Z" transform="translate(0 0)"/></g></g></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="call-to-action" class="call-to-action">
            <div class="call-action-image">
                <img src="{{ asset('images/bg6.jpg') }}" alt="call-to-action">
            </div>

            <div class="container-fluid">
                <div class="row justify-content-end">
                    <div class="col-lg-6">
                        <div class="call-action-content text-center">
                            <h2 class="call-title">Curious to Learn More? Stay Tuned</h2>
                            <p class="text">You let us know whenever you want us to update anything or think something can be optimised.</p>
                            <div class="call-newsletter">
                                <i class="lni-envelope"></i>
                                <input type="text" placeholder="john@email.com">
                                <button type="submit">SUBSCRIBE</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="contact-area">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="section-title text-center pb-10">
                            <h4 class="title">Get In touch</h4>
                            <p class="text">Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed!</p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="contact-form">
                            <form id="contact-form" action="assets/contact.php" method="post" data-toggle="validator">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="single-form form-group">
                                            <input type="text" name="name" placeholder="Your Name" data-error="Name is required." required="required">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-form form-group">
                                            <input type="email" name="email" placeholder="Your Email" data-error="Valid email is required." required="required">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-form form-group">
                                            <input type="text" name="subject" placeholder="Subject" data-error="Subject is required." required="required">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-form form-group">
                                            <input type="text" name="phone" placeholder="Phone" data-error="Phone is required." required="required">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="single-form form-group">
                                            <textarea placeholder="Your Mesaage" name="message" data-error="Please, leave us a message." required="required"></textarea>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <p class="form-message"></p>
                                    <div class="col-md-12">
                                        <div class="single-form form-group text-center">
                                            <button type="submit" class="main-btn">send message</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer id="footer" class="footer-area">
            <div class="footer-widget">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer-logo-support d-md-flex align-items-end justify-content-between">
                                <div class="footer-logo d-flex align-items-end">
                                    <a class="mt-30" href="index.html"><img src="{{ asset('images/g_logo.png') }}" alt="Logo" width="35px" height="35px"></a>
                                    <ul class="social mt-30">
                                        <li><a href="#"><i class="lni-facebook-filled"></i></a></li>
                                        <li><a href="#"><i class="lni-twitter-original"></i></a></li>
                                        <li><a href="#"><i class="lni-instagram-original"></i></a></li>
                                        <li><a href="#"><i class="lni-linkedin-original"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="footer-link">
                                <h6 class="footer-title">Company</h6>
                                <ul>
                                    <li><a href="#">About</a></li>
                                    <li><a href="#">Contact</a></li>
                                    <li><a href="#">Career</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="footer-link">
                                <h6 class="footer-title">Product & Services</h6>
                                <ul>
                                    <li><a href="#">Products</a></li>
                                    <li><a href="#">Business</a></li>
                                    <li><a href="#">Developer</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-5">
                            <div class="footer-link">
                                <h6 class="footer-title">Help & Suuport</h6>
                                <ul>
                                    <li><a href="#">Support Center</a></li>
                                    <li><a href="#">FAQ</a></li>
                                    <li><a href="#">Terms & Conditions</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-7">
                            <div class="footer-newsletter">
                                <h6 class="footer-title">Subscribe Newsletter</h6>
                                <div class="newsletter">
                                    <form action="#">
                                        <input type="text" placeholder="Your Email">
                                        <button type="submit"><i class="lni-angle-double-right"></i></button>
                                    </form>
                                </div>
                                <p class="text">Subscribe weekly newsletter to stay upto date. We don’t send spam.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-copyright">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="copyright text-center">
                                <p class="text">
                                    <img class="img-thumbnail" src="{{ asset('images/g.jpg') }}" alt="G">
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <a class="back-to-top" href="#"><i class="lni-chevron-up"></i></a>

        <script src="{{ asset('js/start/app.js') }}"></script>
    </body>
</html>
