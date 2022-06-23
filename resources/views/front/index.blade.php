@extends('layouts.front.master')

@section('title')
    {{ __('front.title') }}
@endsection

@section('header_content')
    <div id="home" class="header-hero bg_cover background">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="header-content text-center">
                        <h3 class="header-title">{{ __('front.header.header_2') }}</h3>
                        <p class="text">{{ __('front.header.header_3') }}</p>
                        <ul class="header-btn">
                            <li><a class="main-btn btn-one" href="#">{{ __('front.buttons.get_in_touch') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-shape">
            <img src="{{ asset('images/header-shape.svg') }}" alt="shape">
        </div>
    </div>
@endsection

@section('content')
    <section id="service" class="services-area">
        <div class="container">
            <div class="row my-5">
                <div class="col-lg-6">
                    <div class="section-title pb-10">
                        <h4 class="title">{{ __('front.services.title') }}</h4>
                        <p class="text">{{ __('front.services.title_desc') }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <div class="services-content mt-40 d-sm-flex">
                                <div class="services-icon">
                                    <i class="icon icon-check" />
                                </div>
                                <div class="services-content media-body">
                                    <h4 class="services-title">{{ __('front.services.service_1_header') }}</h4>
                                    <p class="text">{{ __('front.services.service_1_description') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="services-content mt-40 d-sm-flex">
                                <div class="services-icon">
                                    <i class="icon icon-anchor" />
                                </div>
                                <div class="services-content media-body">
                                    <h4 class="services-title">{{ __('front.services.service_2_header') }}</h4>
                                    <p class="text">{{ __('front.services.service_2_description') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="services-content mt-40 d-sm-flex">
                                <div class="services-icon">
                                    <i class="icon icon-briefcase" />
                                </div>
                                <div class="services-content media-body">
                                    <h4 class="services-title">{{ __('front.services.service_3_header') }}</h4>
                                    <p class="text">{{ __('front.services.service_3_description') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="services-content mt-40 d-sm-flex">
                                <div class="services-icon">
                                    <i class="icon icon-bulb" />
                                </div>
                                <div class="services-content media-body">
                                    <h4 class="services-title">{{ __('front.services.service_4_header') }}</h4>
                                    <p class="text">{{ __('front.services.service_4_description') }}</p>
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
            <div class="row justify-content-center mb-3">
                <div class="col-lg-6">
                    <div class="section-title text-center pb-10">
                        <h4 class="title">{{ __('front.pricing.title') }}</h4>
                        <p class="text">{{ __('front.pricing.title_desc') }}</p>
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
                                <li><i class="lni-check-mark-circle" /> Carefully crafted components</li>
                                <li><i class="lni-check-mark-circle" /> Amazing page examples</li>
                                <li><i class="lni-check-mark-circle" /> Super friendly support team</li>
                                <li><i class="lni-check-mark-circle" /> Awesome Support</li>
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
                                <li><i class="lni-check-mark-circle" /> Carefully crafted components</li>
                                <li><i class="lni-check-mark-circle" /> Amazing page examples</li>
                                <li><i class="lni-check-mark-circle" /> Super friendly support team</li>
                                <li><i class="lni-check-mark-circle" /> Awesome Support</li>
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
                        <div class="pricing-header text-end">
                            <h5 class="sub-title">Enterprise</h5>
                            <span class="price">$ 799</span>
                            <p class="year">per year</p>
                        </div>
                        <div class="pricing-list">
                            <ul>
                                <li><i class="lni-check-mark-circle" /> Carefully crafted components</li>
                                <li><i class="lni-check-mark-circle" /> Amazing page examples</li>
                                <li><i class="lni-check-mark-circle" /> Super friendly support team</li>
                                <li><i class="lni-check-mark-circle" /> Awesome Support</li>
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
                        <h2 class="call-title">{{ __('front.call-to-action.title') }}</h2>
                        <p class="text">{{ __('front.call-to-action.description') }}</p>
                        <div class="call-newsletter">
                            <i class="lni-envelope" />
                            <input type="text" placeholder="john@email.com">
                            <button type="submit">{{ __('front.buttons.subscribe') }}</button>
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
                        <h4 class="title">{{ __('front.contact.title') }}</h4>
                        <p class="text">{{ __('front.contact.description') }}</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form">
                        <form id="contact-form" action="" method="post" data-toggle="validator">
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
@endsection
