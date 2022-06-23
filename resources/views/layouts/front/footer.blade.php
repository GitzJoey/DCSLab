<footer id="footer" class="footer-area">
    <div class="footer-widget">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer-logo-support d-md-flex align-items-end justify-content-between">
                        <div class="footer-logo d-flex align-items-end">
                            <a class="mt-30" href=""><img src="{{ asset('images/g_logo.png') }}" alt="Logo" width="35px" height="35px"></a>
                            <ul class="social mt-30">
                                <li><a href=""><i class="icon icon-social-facebook" /></a></li>
                                <li><a href=""><i class="icon icon-social-twitter" /></a></li>
                                <li><a href=""><i class="icon icon-social-instagram" /></a></li>
                                <li><a href=""><i class="icon icon-social-linkedin" /></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="footer-link">
                        <h6 class="footer-title">{{ __('front.footer.company.title') }}</h6>
                        <ul>
                            <li><a href="">{{ __('front.footer.company.about') }}</a></li>
                            <li><a href="">{{ __('front.footer.company.contact') }}</a></li>
                            <li><a href="">{{ __('front.footer.company.career') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="footer-link">
                        <h6 class="footer-title">{{ __('front.footer.product_service.title') }}</h6>
                        <ul>
                            <li><a href="">{{ __('front.footer.product_service.products') }}</a></li>
                            <li><a href="">{{ __('front.footer.product_service.business') }}</a></li>
                            <li><a href="">{{ __('front.footer.product_service.developer') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-5">
                    <div class="footer-link">
                        <h6 class="footer-title">{{ __('front.footer.help_support.title') }}</h6>
                        <ul>
                            <li><a href="">{{ __('front.footer.help_support.support') }}</a></li>
                            <li><a href="">{{ __('front.footer.help_support.faq') }}</a></li>
                            <li><a href="">{{ __('front.footer.help_support.terms') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-7">
                    <div class="footer-newsletter">
                        <h6 class="footer-title">{{ __('front.footer.subscribe.sub_news') }}</h6>
                        <div class="newsletter">
                            <form action="#">
                                <input type="text" placeholder="Your Email">
                                <button type="submit"><i class="icon icon-arrow-right-circle"></i></button>
                            </form>
                        </div>
                        <p class="text">{{ __('front.footer.subscribe.sub_news_desc') }}</p>
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

<a class="back-to-top hide" href=""><i class="icon icon-arrow-up"></i></a>
