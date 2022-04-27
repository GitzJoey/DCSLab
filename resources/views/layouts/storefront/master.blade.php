<!DOCTYPE html>
<html lang="en" data-footer="true">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Acorn Admin Template | Product Home Page</title>
    <meta name="description" content="Ecommerce Product Home Page" />
    <!-- Favicon Tags Start -->
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="img/favicon/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/favicon/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/favicon/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/favicon/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="img/favicon/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="img/favicon/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="img/favicon/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="img/favicon/apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-196x196.png" sizes="196x196" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="img/favicon/favicon-128.png" sizes="128x128" />
    <meta name="application-name" content="&nbsp;" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content="img/favicon/mstile-144x144.png" />
    <meta name="msapplication-square70x70logo" content="img/favicon/mstile-70x70.png" />
    <meta name="msapplication-square150x150logo" content="img/favicon/mstile-150x150.png" />
    <meta name="msapplication-wide310x150logo" content="img/favicon/mstile-310x150.png" />
    <meta name="msapplication-square310x310logo" content="img/favicon/mstile-310x310.png" />
    <!-- Favicon Tags End -->
    <!-- Font Tags Start -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="font/CS-Interface/style.css" />
    <!-- Font Tags End -->
    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/OverlayScrollbars.min.css" />

    <!-- Vendor Styles End -->
    <!-- Template Base Styles Start -->
    <link rel="stylesheet" href="css/styles.css" />
    <!-- Template Base Styles End -->

    <link rel="stylesheet" href="css/main.css" />
    <script src="js/base/loader.js"></script>
  </head>

  <body>
    <div id="root">
      <div id="nav" class="nav-container d-flex">
        <div class="nav-content d-flex">
          <!-- Logo Start -->
          <div class="logo position-relative">
            <a href="Dashboard.html">
              <!-- Logo can be added directly -->
              <!-- <img src="img/logo/logo-white.svg" alt="logo" /> -->

              <!-- Or added via css to provide different ones for different color themes -->
              <div class="img"></div>
            </a>
          </div>
          <!-- Logo End -->

          <!-- User Menu Start -->
          <div class="user-container d-flex">
            <a href="#" class="d-flex user position-relative" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img class="profile" alt="profile" src="img/profile/profile-1.webp" />
              <div class="name">Zayn Hartley</div>
            </a>
            <div class="dropdown-menu dropdown-menu-end user-menu wide">
              <div class="row mb-3 ms-0 me-0">
                <div class="col-12 ps-1 mb-2">
                  <div class="text-extra-small text-primary">ACCOUNT</div>
                </div>
                <div class="col-6 ps-1 pe-1">
                  <ul class="list-unstyled">
                    <li>
                      <a href="#">User Info</a>
                    </li>
                    <li>
                      <a href="#">Preferences</a>
                    </li>
                    <li>
                      <a href="#">Calendar</a>
                    </li>
                  </ul>
                </div>
                <div class="col-6 pe-1 ps-1">
                  <ul class="list-unstyled">
                    <li>
                      <a href="#">Security</a>
                    </li>
                    <li>
                      <a href="#">Billing</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="row mb-1 ms-0 me-0">
                <div class="col-12 p-1 mb-2 pt-2">
                  <div class="text-extra-small text-primary">APPLICATION</div>
                </div>
                <div class="col-6 ps-1 pe-1">
                  <ul class="list-unstyled">
                    <li>
                      <a href="#">Themes</a>
                    </li>
                    <li>
                      <a href="#">Language</a>
                    </li>
                  </ul>
                </div>
                <div class="col-6 pe-1 ps-1">
                  <ul class="list-unstyled">
                    <li>
                      <a href="#">Devices</a>
                    </li>
                    <li>
                      <a href="#">Storage</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="row mb-1 ms-0 me-0">
                <div class="col-12 p-1 mb-3 pt-3">
                  <div class="separator-light"></div>
                </div>
                <div class="col-6 ps-1 pe-1">
                  <ul class="list-unstyled">
                    <li>
                      <a href="#">
                        <i data-acorn-icon="help" class="me-2" data-acorn-size="17"></i>
                        <span class="align-middle">Help</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <i data-acorn-icon="file-text" class="me-2" data-acorn-size="17"></i>
                        <span class="align-middle">Docs</span>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="col-6 pe-1 ps-1">
                  <ul class="list-unstyled">
                    <li>
                      <a href="#">
                        <i data-acorn-icon="gear" class="me-2" data-acorn-size="17"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <i data-acorn-icon="logout" class="me-2" data-acorn-size="17"></i>
                        <span class="align-middle">Logout</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <!-- User Menu End -->

          <!-- Icons Menu Start -->
          <ul class="list-unstyled list-inline text-center menu-icons">
            <li class="list-inline-item">
              <a href="#" data-bs-toggle="modal" data-bs-target="#searchPagesModal">
                <i data-acorn-icon="search" data-acorn-size="18"></i>
              </a>
            </li>
            <li class="list-inline-item">
              <a href="#" id="pinButton" class="pin-button">
                <i data-acorn-icon="lock-on" class="unpin" data-acorn-size="18"></i>
                <i data-acorn-icon="lock-off" class="pin" data-acorn-size="18"></i>
              </a>
            </li>
            <li class="list-inline-item">
              <a href="#" id="colorButton">
                <i data-acorn-icon="light-on" class="light" data-acorn-size="18"></i>
                <i data-acorn-icon="light-off" class="dark" data-acorn-size="18"></i>
              </a>
            </li>
            <li class="list-inline-item">
              <a href="#" data-bs-toggle="dropdown" data-bs-target="#notifications" aria-haspopup="true" aria-expanded="false" class="notification-button">
                <div class="position-relative d-inline-flex">
                  <i data-acorn-icon="bell" data-acorn-size="18"></i>
                  <span class="position-absolute notification-dot rounded-xl"></span>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end wide notification-dropdown scroll-out" id="notifications">
                <div class="scroll">
                  <ul class="list-unstyled border-last-none">
                    <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                      <img src="img/profile/profile-1.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                      <div class="align-self-center">
                        <a href="#">Joisse Kaycee just sent a new comment!</a>
                      </div>
                    </li>
                    <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                      <img src="img/profile/profile-2.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                      <div class="align-self-center">
                        <a href="#">New order received! It is total $147,20.</a>
                      </div>
                    </li>
                    <li class="mb-3 pb-3 border-bottom border-separator-light d-flex">
                      <img src="img/profile/profile-3.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                      <div class="align-self-center">
                        <a href="#">3 items just added to wish list by a user!</a>
                      </div>
                    </li>
                    <li class="pb-3 pb-3 border-bottom border-separator-light d-flex">
                      <img src="img/profile/profile-6.webp" class="me-3 sw-4 sh-4 rounded-xl align-self-center" alt="..." />
                      <div class="align-self-center">
                        <a href="#">Kirby Peters just sent a new message!</a>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </li>
          </ul>
          <!-- Icons Menu End -->

          <!-- Menu Start -->
          <div class="menu-container flex-grow-1">
            <ul id="menu" class="menu">
              <li>
                <a href="Dashboard.html">
                  <i data-acorn-icon="shop" class="icon" data-acorn-size="18"></i>
                  <span class="label">Dashboard</span>
                </a>
              </li>
              <li>
                <a href="#products" data-href="Products.html">
                  <i data-acorn-icon="cupcake" class="icon" data-acorn-size="18"></i>
                  <span class="label">Products</span>
                </a>
                <ul id="products">
                  <li>
                    <a href="Products.List.html">
                      <span class="label">List</span>
                    </a>
                  </li>
                  <li>
                    <a href="Products.Detail.html">
                      <span class="label">Detail</span>
                    </a>
                  </li>
                </ul>
              </li>
              <li>
                <a href="#orders" data-href="Orders.html">
                  <i data-acorn-icon="cart" class="icon" data-acorn-size="18"></i>
                  <span class="label">Orders</span>
                </a>
                <ul id="orders">
                  <li>
                    <a href="Orders.List.html">
                      <span class="label">List</span>
                    </a>
                  </li>
                  <li>
                    <a href="Orders.Detail.html">
                      <span class="label">Detail</span>
                    </a>
                  </li>
                </ul>
              </li>
              <li>
                <a href="#customers" data-href="Customers.html">
                  <i data-acorn-icon="user" class="icon" data-acorn-size="18"></i>
                  <span class="label">Customers</span>
                </a>
                <ul id="customers">
                  <li>
                    <a href="Customers.List.html">
                      <span class="label">List</span>
                    </a>
                  </li>
                  <li>
                    <a href="Customers.Detail.html">
                      <span class="label">Detail</span>
                    </a>
                  </li>
                </ul>
              </li>
              <li>
                <a href="#storefront" data-href="Storefront.html">
                  <i data-acorn-icon="screen" class="icon" data-acorn-size="18"></i>
                  <span class="label">Storefront</span>
                </a>
                <ul id="storefront">
                  <li>
                    <a href="Storefront.Home.html">
                      <span class="label">Home</span>
                    </a>
                  </li>
                  <li>
                    <a href="Storefront.Filters.html">
                      <span class="label">Filters</span>
                    </a>
                  </li>
                  <li>
                    <a href="Storefront.Categories.html">
                      <span class="label">Categories</span>
                    </a>
                  </li>
                  <li>
                    <a href="Storefront.Detail.html">
                      <span class="label">Detail</span>
                    </a>
                  </li>
                  <li>
                    <a href="Storefront.Cart.html">
                      <span class="label">Cart</span>
                    </a>
                  </li>
                  <li>
                    <a href="Storefront.Checkout.html">
                      <span class="label">Checkout</span>
                    </a>
                  </li>
                  <li>
                    <a href="Storefront.Invoice.html">
                      <span class="label">Invoice</span>
                    </a>
                  </li>
                </ul>
              </li>
              <li>
                <a href="Shipping.html">
                  <i data-acorn-icon="shipping" class="icon" data-acorn-size="18"></i>
                  <span class="label">Shipping</span>
                </a>
              </li>
              <li>
                <a href="Discount.html">
                  <i data-acorn-icon="tag" class="icon" data-acorn-size="18"></i>
                  <span class="label">Discount</span>
                </a>
              </li>
              <li>
                <a href="Settings.html">
                  <i data-acorn-icon="gear" class="icon" data-acorn-size="18"></i>
                  <span class="label">Settings</span>
                </a>
              </li>
            </ul>
          </div>
          <!-- Menu End -->

          <!-- Mobile Buttons Start -->
          <div class="mobile-buttons-container">
            <!-- Menu Button Start -->
            <a href="#" id="mobileMenuButton" class="menu-button">
              <i data-acorn-icon="menu"></i>
            </a>
            <!-- Menu Button End -->
          </div>
          <!-- Mobile Buttons End -->
        </div>
        <div class="nav-shadow"></div>
      </div>

      <main>
        <div class="container">
          <!-- Title and Top Buttons Start -->
          <div class="page-title-container">
            <div class="row">
              <!-- Title Start -->
              <div class="col-auto mb-3 mb-md-0 me-auto">
                <div class="w-auto sw-md-30">
                  <a href="#" class="muted-link pb-1 d-inline-block breadcrumb-back">
                    <i data-acorn-icon="chevron-left" data-acorn-size="13"></i>
                    <span class="text-small align-middle">Home</span>
                  </a>
                  <h1 class="mb-0 pb-0 display-4" id="title">Storefront</h1>
                </div>
              </div>
              <!-- Title End -->

              <!-- Top Buttons Start -->
              <div class="col-12 col-md-5 d-flex align-items-center justify-content-end">
                <!-- Categories Button For Small Screens Start -->
                <button
                  type="button"
                  class="btn btn-icon btn-outline-primary d-inline-block d-xl-none w-100 w-md-auto"
                  data-bs-toggle="modal"
                  data-bs-target="#menuModal"
                >
                  <span>Categories</span>
                  <i data-acorn-icon="more-horizontal"></i>
                </button>
                <!-- Categories Button For Small Screens End -->
              </div>
              <!-- Top Buttons End -->
            </div>
          </div>
          <!-- Title and Top Buttons End -->

          <div class="row">
            <!-- Left Side Start -->
            <div class="col-12 col-xl-3 d-none d-xl-block mb-5">
              <div class="card">
                <div class="card-body d-flex flex-column justify-content-between" id="menuColumn">
                  <!-- Content of this will be moved from #categoryMenuMoveContent div based on the responsive breakpoint.  -->
                </div>
              </div>
            </div>
            <!-- Left Side End -->

            <!-- Right Side Cta Banners Start -->
            <div class="col-12 col-xl-9 mb-5">
              <div class="row g-2 mb-2">
                <div class="col-12 col-sm-6 col-md-8">
                  <div class="card sh-30 sh-sm-45 hover-img-scale-up">
                    <img src="img/banner/cta-standard-1.webp" class="card-img h-100 scale" alt="card image" />
                    <div class="card-img-overlay d-flex flex-column justify-content-between bg-transparent">
                      <div>
                        <div class="cta-1 mb-3 text-black w-md-100 w-75">Wall Shelf Ideas to Transform Your Space</div>
                        <div class="w-50 text-black d-none d-md-block">
                          Lollipop chocolate marzipan marshmallow gummi bears. Tootsie roll liquorice cake jelly beans.
                        </div>
                      </div>
                      <div>
                        <a href="Storefront.Filters.html" class="btn btn-icon btn-icon-start btn-outline-primary mt-3 stretched-link">
                          <i data-acorn-icon="chevron-right"></i>
                          <span>View</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <div class="card sh-30 sh-sm-45 hover-img-scale-up">
                    <img src="img/banner/cta-vertical-3.webp" class="card-img h-100 scale" alt="card image" />
                    <div class="card-img-overlay d-flex flex-column justify-content-between bg-transparent">
                      <div>
                        <div class="cta-1 mb-5 text-black w-md-100 w-75">Sale on Notebooks and Sketchbooks</div>
                      </div>
                      <div>
                        <a href="Storefront.Filters.html" class="btn btn-icon btn-icon-start btn-outline-primary mt-3 stretched-link">
                          <i data-acorn-icon="chevron-right"></i>
                          <span>View</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row g-2">
                <div class="col-12 col-sm-6">
                  <div class="card sh-22 sh-xl-19 hover-img-scale-up">
                    <span class="badge rounded-pill bg-primary me-1 position-absolute e-2 t-2 z-index-1">SALE</span>
                    <img src="img/banner/cta-horizontal-short-1.webp" class="card-img h-100 scale" alt="card image" />
                    <div class="card-img-overlay d-flex flex-column justify-content-between bg-transparent">
                      <div>
                        <div class="cta-3 mb-2 text-black w-75">10% Discount for Canned Products</div>
                      </div>
                      <div>
                        <a href="Storefront.Filters.html" class="btn btn-icon btn-icon-start btn-outline-primary stretched-link">
                          <i data-acorn-icon="chevron-right"></i>
                          <span>Buy Now</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <div class="card sh-22 sh-xl-19 hover-img-scale-up">
                    <span class="badge rounded-pill bg-primary me-1 position-absolute e-2 t-2 z-index-1">SALE</span>
                    <img src="img/banner/cta-horizontal-short-2.webp" class="card-img h-100 scale" alt="card image" />
                    <div class="card-img-overlay d-flex flex-column justify-content-between bg-transparent">
                      <div>
                        <div class="cta-3 mb-2 text-black w-75">20% Discount for Bagged Products</div>
                      </div>
                      <div>
                        <a href="Storefront.Filters.html" class="btn btn-icon btn-icon-start btn-outline-primary stretched-link">
                          <i data-acorn-icon="chevron-right"></i>
                          <span>Buy Now</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Right Side Cta Banners End -->
          </div>

          <!-- Trending Start -->
          <h2 class="small-title">Trending</h2>
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-4 g-2 mb-5">
            <div class="col">
              <div class="card h-100">
                <span class="badge rounded-pill bg-primary me-1 position-absolute s-n2 t-2 z-index-1">SALE</span>
                <img src="img/product/small/product-1.webp" class="card-img-top sh-22" alt="card image" />
                <div class="card-body pb-2">
                  <div class="h6 mb-0 d-flex">
                    <a href="Storefront.Detail.html" class="body-link d-block lh-1-25 stretched-link">
                      <span class="clamp-line sh-4" data-line="2">Wooden Animal Toys</span>
                    </a>
                  </div>
                </div>
                <div class="card-footer border-0 pt-0">
                  <div class="mb-2">
                    <div class="br-wrapper br-theme-cs-icon d-inline-block">
                      <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                      </select>
                    </div>
                    <div class="text-muted d-inline-block text-small align-text-top">(5)</div>
                  </div>
                  <div class="card-text mb-0">
                    <div class="text-muted text-overline text-small sh-2">
                      <del>$ 14.25</del>
                    </div>
                    <div>$ 8.50</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card h-100">
                <img src="img/product/small/product-2.webp" class="card-img-top sh-22" alt="card image" />
                <div class="card-body pb-2">
                  <div class="h6 mb-0 d-flex">
                    <a href="Storefront.Detail.html" class="body-link d-block lh-1-25 stretched-link">
                      <span class="clamp-line sh-4" data-line="2">Aromatic Green Candle</span>
                    </a>
                  </div>
                </div>
                <div class="card-footer border-0 pt-0">
                  <div class="mb-2">
                    <div class="br-wrapper br-theme-cs-icon d-inline-block">
                      <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                      </select>
                    </div>
                    <div class="text-muted d-inline-block text-small align-text-top">(44)</div>
                  </div>
                  <div class="card-text mb-0">
                    <div class="text-muted text-overline text-small sh-2"></div>
                    <div>$ 4.25</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card h-100">
                <span class="badge rounded-pill bg-primary me-1 position-absolute s-n2 t-2 z-index-1">SALE</span>
                <img src="img/product/small/product-3.webp" class="card-img-top sh-22" alt="card image" />
                <div class="card-body pb-2">
                  <div class="h6 mb-0 d-flex">
                    <a href="Storefront.Detail.html" class="body-link d-block lh-1-25 stretched-link">
                      <span class="clamp-line sh-4" data-line="2">Good Glass Teapot</span>
                    </a>
                  </div>
                </div>
                <div class="card-footer border-0 pt-0">
                  <div class="mb-2">
                    <div class="br-wrapper br-theme-cs-icon d-inline-block">
                      <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                      </select>
                    </div>
                    <div class="text-muted d-inline-block text-small align-text-top">(2)</div>
                  </div>
                  <div class="card-text mb-0">
                    <div class="text-muted text-overline text-small sh-2">
                      <del>$ 12.25</del>
                    </div>
                    <div>$ 9.50</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card h-100">
                <img src="img/product/small/product-4.webp" class="card-img-top sh-22" alt="card image" />
                <div class="card-body pb-3">
                  <h5 class="heading mb-0 d-flex">
                    <a href="Storefront.Detail.html" class="body-link d-block lh-1-5 stretched-link">
                      <span class="clamp-line sh-4" data-line="2">Modern Dark Pot</span>
                    </a>
                  </h5>
                </div>
                <div class="card-footer border-0 pt-0">
                  <div class="mb-2">
                    <div class="br-wrapper br-theme-cs-icon d-inline-block">
                      <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                      </select>
                    </div>
                    <div class="text-muted d-inline-block text-small align-text-top">(412)</div>
                  </div>
                  <div class="card-text mb-0">
                    <div class="text-muted text-overline text-small sh-2"></div>
                    <div>$ 9.50</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Trending End -->

          <!-- Popular Categories Start -->
          <h2 class="small-title">Popular Categories</h2>
          <div class="row g-2 row-cols-2 row-cols-md-3 row-cols-xl-6 mb-5">
            <div class="col sh-19">
              <div class="card h-100 hover-border-primary">
                <a class="card-body text-center" href="Storefront.Categories.html">
                  <i data-acorn-icon="office" class="text-primary"></i>
                  <p class="heading mt-3 text-body">Home Decorations</p>
                  <div class="text-extra-small fw-medium text-muted">14 PRODUCTS</div>
                </a>
              </div>
            </div>
            <div class="col sh-19">
              <div class="card h-100 hover-border-primary">
                <a class="card-body text-center" href="Storefront.Categories.html">
                  <i data-acorn-icon="screen" class="text-primary"></i>
                  <p class="heading mt-3 text-body">Computers</p>
                  <div class="text-extra-small fw-medium text-muted">3 PRODUCTS</div>
                </a>
              </div>
            </div>
            <div class="col sh-19">
              <div class="card h-100 hover-border-primary">
                <a class="card-body text-center" href="Storefront.Categories.html">
                  <i data-acorn-icon="gift" class="text-primary"></i>
                  <p class="heading mt-3 text-body">Arts & Gifts</p>
                  <div class="text-extra-small fw-medium text-muted">8 PRODUCTS</div>
                </a>
              </div>
            </div>
            <div class="col sh-19">
              <div class="card h-100 hover-border-primary">
                <a class="card-body text-center" href="Storefront.Categories.html">
                  <i data-acorn-icon="crown" class="text-primary"></i>
                  <p class="heading mt-3 text-body">Fashion</p>
                  <div class="text-extra-small fw-medium text-muted">9 PRODUCTS</div>
                </a>
              </div>
            </div>
            <div class="col sh-19">
              <div class="card h-100 hover-border-primary">
                <a class="card-body text-center" href="Storefront.Categories.html">
                  <i data-acorn-icon="prize" class="text-primary"></i>
                  <p class="heading mt-3 text-body">Sports</p>
                  <div class="text-extra-small fw-medium text-muted">3 PRODUCTS</div>
                </a>
              </div>
            </div>
            <div class="col sh-19">
              <div class="card h-100 hover-border-primary">
                <a class="card-body text-center" href="Storefront.Categories.html">
                  <i data-acorn-icon="pocket-knife" class="text-primary"></i>
                  <p class="heading mt-3 text-body">Tools</p>
                  <div class="text-extra-small fw-medium text-muted">4 PRODUCTS</div>
                </a>
              </div>
            </div>
          </div>
          <!-- Popular Categories Start -->

          <!-- Discover Start -->
          <h2 class="small-title">Discover</h2>
          <div class="row g-2 row-cols-1 row-cols-md-2 row-cols-xl-2 row-cols-xxl-3 mb-5">
            <div class="col">
              <div class="card">
                <div class="row g-0 sh-16 sh-sm-17">
                  <div class="col-auto h-100 position-relative">
                    <span class="badge rounded-pill bg-primary me-1 position-absolute e-n2 t-2 z-index-1">SALE</span>
                    <img src="img/product/small/product-9.webp" alt="alternate text" class="card-img card-img-horizontal h-100 sw-11 sw-sm-16 sw-lg-20" />
                  </div>
                  <div class="col p-0">
                    <div class="card-body d-flex align-items-center h-100 py-3">
                      <div class="mb-0 h6">
                        <a href="Storefront.Detail.html" class="body-link stretched-link">
                          <div class="clamp-line sh-3 lh-1-5" data-line="1">XBox Controller</div>
                        </a>
                        <div class="card-text mb-2">
                          <div class="text-muted text-overline text-small sh-2">
                            <del>$ 12.25</del>
                          </div>
                          <div>$ 8.50</div>
                        </div>
                        <div>
                          <div class="br-wrapper br-theme-cs-icon d-inline-block">
                            <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                              <option value="5">5</option>
                            </select>
                          </div>
                          <div class="text-muted d-inline-block text-small align-text-top">(5)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card">
                <div class="row g-0 sh-16 sh-sm-17">
                  <div class="col-auto h-100">
                    <img src="img/product/small/product-2.webp" alt="alternate text" class="card-img card-img-horizontal h-100 sw-11 sw-sm-16 sw-lg-20" />
                  </div>
                  <div class="col p-0">
                    <div class="card-body d-flex align-items-center h-100 py-3">
                      <div class="mb-0 h6">
                        <a href="Storefront.Detail.html" class="body-link stretched-link">
                          <div class="clamp-line sh-3 lh-1-5" data-line="1">Aromatic Green Candle</div>
                        </a>
                        <div class="card-text mb-2">
                          <div class="text-muted text-overline text-small sh-2"></div>
                          <div>$ 7.50</div>
                        </div>
                        <div>
                          <div class="br-wrapper br-theme-cs-icon d-inline-block">
                            <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                              <option value="5">5</option>
                            </select>
                          </div>
                          <div class="text-muted d-inline-block text-small align-text-top">(2)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card">
                <div class="row g-0 sh-16 sh-sm-17">
                  <div class="col-auto h-100">
                    <img src="img/product/small/product-10.webp" alt="alternate text" class="card-img card-img-horizontal h-100 sw-11 sw-sm-16 sw-lg-20" />
                  </div>
                  <div class="col p-0">
                    <div class="card-body d-flex align-items-center h-100 py-3">
                      <div class="mb-0 h6">
                        <a href="Storefront.Detail.html" class="body-link stretched-link">
                          <div class="clamp-line sh-3 lh-1-5" data-line="1">Health and Fitness Smartwatch</div>
                        </a>
                        <div class="card-text mb-2">
                          <div class="text-muted text-overline text-small sh-2"></div>
                          <div>$ 4.25</div>
                        </div>
                        <div>
                          <div class="br-wrapper br-theme-cs-icon d-inline-block">
                            <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                              <option value="5">5</option>
                            </select>
                          </div>
                          <div class="text-muted d-inline-block text-small align-text-top">(4)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card">
                <div class="row g-0 sh-16 sh-sm-17">
                  <div class="col-auto h-100">
                    <img src="img/product/small/product-8.webp" alt="alternate text" class="card-img card-img-horizontal h-100 sw-11 sw-sm-16 sw-lg-20" />
                  </div>
                  <div class="col p-0">
                    <div class="card-body d-flex align-items-center h-100 py-3">
                      <div class="mb-0 h6">
                        <a href="Storefront.Detail.html" class="body-link stretched-link">
                          <div class="clamp-line sh-3 lh-1-5" data-line="1">Geometric Chandelier</div>
                        </a>
                        <div class="card-text mb-2">
                          <div class="text-muted text-overline text-small sh-2"></div>
                          <div>$ 12.25</div>
                        </div>
                        <div>
                          <div class="br-wrapper br-theme-cs-icon d-inline-block">
                            <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                              <option value="5">5</option>
                            </select>
                          </div>
                          <div class="text-muted d-inline-block text-small align-text-top">(12)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card">
                <div class="row g-0 sh-16 sh-sm-17">
                  <div class="col-auto h-100 position-relative">
                    <span class="badge rounded-pill bg-primary me-1 position-absolute e-n2 t-2 z-index-1">SALE</span>
                    <img src="img/product/small/product-4.webp" alt="alternate text" class="card-img card-img-horizontal h-100 sw-11 sw-sm-16 sw-lg-20" />
                  </div>
                  <div class="col p-0">
                    <div class="card-body d-flex align-items-center h-100 py-3">
                      <div class="mb-0 h6">
                        <a href="Storefront.Detail.html" class="body-link stretched-link">
                          <div class="clamp-line sh-3 lh-1-5" data-line="1">Modern Dark Pot</div>
                        </a>
                        <div class="card-text mb-2">
                          <div class="text-muted text-overline text-small sh-2">
                            <del>$ 3.25</del>
                          </div>
                          <div>$ 2.50</div>
                        </div>
                        <div>
                          <div class="br-wrapper br-theme-cs-icon d-inline-block">
                            <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                              <option value="5">5</option>
                            </select>
                          </div>
                          <div class="text-muted d-inline-block text-small align-text-top">(9)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card">
                <div class="row g-0 sh-16 sh-sm-17">
                  <div class="col-auto h-100 position-relative">
                    <span class="badge rounded-pill bg-primary me-1 position-absolute e-n2 t-2 z-index-1">SALE</span>
                    <img src="img/product/small/product-5.webp" alt="alternate text" class="card-img card-img-horizontal h-100 sw-11 sw-sm-16 sw-lg-20" />
                  </div>
                  <div class="col p-0">
                    <div class="card-body d-flex align-items-center h-100 py-3">
                      <div class="mb-0 h6">
                        <a href="Storefront.Detail.html" class="body-link stretched-link">
                          <div class="clamp-line sh-3 lh-1-5" data-line="1">Wood Handle Hunter Knife</div>
                        </a>
                        <div class="card-text mb-2">
                          <div class="text-muted text-overline text-small sh-2">
                            <del>$ 5.25</del>
                          </div>
                          <div>$ 2.85</div>
                        </div>
                        <div>
                          <div class="br-wrapper br-theme-cs-icon d-inline-block">
                            <select class="rating" name="rating" autocomplete="off" data-readonly="true" data-initial-rating="5">
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                              <option value="5">5</option>
                            </select>
                          </div>
                          <div class="text-muted d-inline-block text-small align-text-top">(3)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Discover End -->
        </div>

        <!-- Category Modal Start -->
        <div class="modal modal-right fade" id="menuModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <!-- Content of below will be moved to #menuColumn or back in here based on the data-move-breakpoint attribute below -->
                <div id="categoryMenuMoveContent" data-move-target="#menuColumn" data-move-breakpoint="xl">
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Electronics</span>
                    <span class="align-middle">(41)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Computers</span>
                    <span class="align-middle">(16)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Smart Home</span>
                    <span class="align-middle">(13)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Arts & Gifts</span>
                    <span class="align-middle">(21)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Baby</span>
                    <span class="align-middle">(15)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Personal Care</span>
                    <span class="align-middle">(12)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Fashion</span>
                    <span class="align-middle">(13)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Heath & Household</span>
                    <span class="align-middle">(15)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Industrial & Scientific</span>
                    <span class="align-middle">(5)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Luggage</span>
                    <span class="align-middle">(7)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Pet Supplies</span>
                    <span class="align-middle">(9)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Software</span>
                    <span class="align-middle">(17)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Sports</span>
                    <span class="align-middle">(9)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                  <a class="nav-link body-link px-0 py-2" href="Storefront.Categories.html">
                    <span class="align-middle">Tools</span>
                    <span class="align-middle">(8)</span>
                    <i data-acorn-icon="chevron-right" class="align-middle float-end mb-1" data-acorn-size="13"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Category Modal End -->
      </main>

      <!-- Layout Footer Start -->
      <footer>
        <div class="footer-content">
          <div class="container">
            <div class="row">
              <div class="col-12 col-sm-6">
                <p class="mb-0 text-muted text-medium">Colored Strategies 2021</p>
              </div>
              <div class="col-sm-6 d-none d-sm-block">
                <ul class="breadcrumb pt-0 pe-0 mb-0 float-end">
                  <li class="breadcrumb-item mb-0 text-medium">
                    <a href="https://1.envato.market/BX5oGy" target="_blank" class="btn-link">Review</a>
                  </li>
                  <li class="breadcrumb-item mb-0 text-medium">
                    <a href="https://1.envato.market/BX5oGy" target="_blank" class="btn-link">Purchase</a>
                  </li>
                  <li class="breadcrumb-item mb-0 text-medium">
                    <a href="https://acorn-html-docs.coloredstrategies.com/" target="_blank" class="btn-link">Docs</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </footer>
      <!-- Layout Footer End -->
    </div>

    <!-- Theme Settings Modal Start -->
    <div
      class="modal fade modal-right scroll-out-negative"
      id="settings"
      data-bs-backdrop="true"
      tabindex="-1"
      role="dialog"
      aria-labelledby="settings"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-scrollable full" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Theme Settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="scroll-track-visible">
              <div class="mb-5" id="color">
                <label class="mb-3 d-inline-block form-label">Color</label>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-blue" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="blue-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT BLUE</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-blue" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="blue-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK BLUE</span>
                    </div>
                  </a>
                </div>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-teal" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="teal-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT TEAL</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-teal" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="teal-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK TEAL</span>
                    </div>
                  </a>
                </div>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-sky" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="sky-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT SKY</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-sky" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="sky-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK SKY</span>
                    </div>
                  </a>
                </div>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-red" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="red-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT RED</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-red" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="red-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK RED</span>
                    </div>
                  </a>
                </div>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-green" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="green-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT GREEN</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-green" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="green-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK GREEN</span>
                    </div>
                  </a>
                </div>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-lime" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="lime-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT LIME</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-lime" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="lime-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK LIME</span>
                    </div>
                  </a>
                </div>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-pink" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="pink-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT PINK</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-pink" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="pink-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK PINK</span>
                    </div>
                  </a>
                </div>
                <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="light-purple" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="purple-light"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT PURPLE</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-purple" data-parent="color">
                    <div class="card rounded-md p-3 mb-1 no-shadow color">
                      <div class="purple-dark"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK PURPLE</span>
                    </div>
                  </a>
                </div>
              </div>
              <div class="mb-5" id="navcolor">
                <label class="mb-3 d-inline-block form-label">Override Nav Palette</label>
                <div class="row d-flex g-3 justify-content-between flex-wrap">
                  <a href="#" class="flex-grow-1 w-33 option col" data-value="default" data-parent="navcolor">
                    <div class="card rounded-md p-3 mb-1 no-shadow">
                      <div class="figure figure-primary top"></div>
                      <div class="figure figure-secondary bottom"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DEFAULT</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-33 option col" data-value="light" data-parent="navcolor">
                    <div class="card rounded-md p-3 mb-1 no-shadow">
                      <div class="figure figure-secondary figure-light top"></div>
                      <div class="figure figure-secondary bottom"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">LIGHT</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-33 option col" data-value="dark" data-parent="navcolor">
                    <div class="card rounded-md p-3 mb-1 no-shadow">
                      <div class="figure figure-muted figure-dark top"></div>
                      <div class="figure figure-secondary bottom"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">DARK</span>
                    </div>
                  </a>
                </div>
              </div>

              <div class="mb-5" id="behaviour">
                <label class="mb-3 d-inline-block form-label">Menu Behaviour</label>
                <div class="row d-flex g-3 justify-content-between flex-wrap">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="pinned" data-parent="behaviour">
                    <div class="card rounded-md p-3 mb-1 no-shadow">
                      <div class="figure figure-primary left large"></div>
                      <div class="figure figure-secondary right small"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">PINNED</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="unpinned" data-parent="behaviour">
                    <div class="card rounded-md p-3 mb-1 no-shadow">
                      <div class="figure figure-primary left"></div>
                      <div class="figure figure-secondary right"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">UNPINNED</span>
                    </div>
                  </a>
                </div>
              </div>

              <div class="mb-5" id="layout">
                <label class="mb-3 d-inline-block form-label">Layout</label>
                <div class="row d-flex g-3 justify-content-between flex-wrap">
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="fluid" data-parent="layout">
                    <div class="card rounded-md p-3 mb-1 no-shadow">
                      <div class="figure figure-primary top"></div>
                      <div class="figure figure-secondary bottom"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">FLUID</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-50 option col" data-value="boxed" data-parent="layout">
                    <div class="card rounded-md p-3 mb-1 no-shadow">
                      <div class="figure figure-primary top"></div>
                      <div class="figure figure-secondary bottom small"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">BOXED</span>
                    </div>
                  </a>
                </div>
              </div>

              <div class="mb-5" id="radius">
                <label class="mb-3 d-inline-block form-label">Radius</label>
                <div class="row d-flex g-3 justify-content-between flex-wrap">
                  <a href="#" class="flex-grow-1 w-33 option col" data-value="rounded" data-parent="radius">
                    <div class="card rounded-md radius-rounded p-3 mb-1 no-shadow">
                      <div class="figure figure-primary top"></div>
                      <div class="figure figure-secondary bottom"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">ROUNDED</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-33 option col" data-value="standard" data-parent="radius">
                    <div class="card rounded-md radius-regular p-3 mb-1 no-shadow">
                      <div class="figure figure-primary top"></div>
                      <div class="figure figure-secondary bottom"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">STANDARD</span>
                    </div>
                  </a>
                  <a href="#" class="flex-grow-1 w-33 option col" data-value="flat" data-parent="radius">
                    <div class="card rounded-md radius-flat p-3 mb-1 no-shadow">
                      <div class="figure figure-primary top"></div>
                      <div class="figure figure-secondary bottom"></div>
                    </div>
                    <div class="text-muted text-part">
                      <span class="text-extra-small align-middle">FLAT</span>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Theme Settings Modal End -->

    <!-- Niches Modal Start -->
    <div
      class="modal fade modal-right scroll-out-negative"
      id="niches"
      data-bs-backdrop="true"
      tabindex="-1"
      role="dialog"
      aria-labelledby="niches"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-scrollable full" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Niches</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="scroll-track-visible">
              <div class="mb-5">
                <label class="mb-2 d-inline-block form-label">Classic Dashboard</label>
                <div class="hover-reveal-buttons position-relative hover-reveal cursor-default">
                  <div class="position-relative mb-3 mb-lg-5 rounded-sm">
                    <img
                      src="https://acorn.coloredstrategies.com/img/page/classic-dashboard.webp"
                      class="img-fluid rounded-sm lower-opacity border border-separator-light"
                      alt="card image"
                    />
                    <div class="position-absolute reveal-content rounded-sm absolute-center-vertical text-center w-100">
                      <a
                        target="_blank"
                        href="https://acorn-html-classic-dashboard.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Html
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-laravel-classic-dashboard.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Laravel
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-dotnet-classic-dashboard.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        .Net5
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-5">
                <label class="mb-2 d-inline-block form-label">Medical Assistant</label>
                <div class="hover-reveal-buttons position-relative hover-reveal cursor-default">
                  <div class="position-relative mb-3 mb-lg-5 rounded-sm">
                    <img
                      src="https://acorn.coloredstrategies.com/img/page/medical-assistant.webp"
                      class="img-fluid rounded-sm lower-opacity border border-separator-light"
                      alt="card image"
                    />
                    <div class="position-absolute reveal-content rounded-sm absolute-center-vertical text-center w-100">
                      <a
                        target="_blank"
                        href="https://acorn-html-medical-assistant.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Html
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-laravel-medical-assistant.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Laravel
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-dotnet-medical-assistant.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        .Net5
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-5">
                <label class="mb-2 d-inline-block form-label">Service Provider</label>
                <div class="hover-reveal-buttons position-relative hover-reveal cursor-default">
                  <div class="position-relative mb-3 mb-lg-5 rounded-sm">
                    <img
                      src="https://acorn.coloredstrategies.com/img/page/service-provider.webp"
                      class="img-fluid rounded-sm lower-opacity border border-separator-light"
                      alt="card image"
                    />
                    <div class="position-absolute reveal-content rounded-sm absolute-center-vertical text-center w-100">
                      <a
                        target="_blank"
                        href="https://acorn-html-service-provider.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Html
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-laravel-service-provider.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Laravel
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-dotnet-service-provider.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        .Net5
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-5">
                <label class="mb-2 d-inline-block form-label">Elearning Portal</label>
                <div class="hover-reveal-buttons position-relative hover-reveal cursor-default">
                  <div class="position-relative mb-3 mb-lg-5 rounded-sm">
                    <img
                      src="https://acorn.coloredstrategies.com/img/page/elearning-portal.webp"
                      class="img-fluid rounded-sm lower-opacity border border-separator-light"
                      alt="card image"
                    />
                    <div class="position-absolute reveal-content rounded-sm absolute-center-vertical text-center w-100">
                      <a
                        target="_blank"
                        href="https://acorn-html-elearning-portal.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Html
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-laravel-elearning-portal.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Laravel
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-dotnet-elearning-portal.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        .Net5
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-5">
                <label class="mb-2 d-inline-block form-label">Ecommerce Platform</label>
                <div class="hover-reveal-buttons position-relative hover-reveal cursor-default">
                  <div class="position-relative mb-3 mb-lg-5 rounded-sm">
                    <img
                      src="https://acorn.coloredstrategies.com/img/page/ecommerce-platform.webp"
                      class="img-fluid rounded-sm lower-opacity border border-separator-light"
                      alt="card image"
                    />
                    <div class="position-absolute reveal-content rounded-sm absolute-center-vertical text-center w-100">
                      <a
                        target="_blank"
                        href="https://acorn-html-ecommerce-platform.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Html
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-laravel-ecommerce-platform.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Laravel
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-dotnet-ecommerce-platform.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        .Net5
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-5">
                <label class="mb-2 d-inline-block form-label">Starter Project</label>
                <div class="hover-reveal-buttons position-relative hover-reveal cursor-default">
                  <div class="position-relative mb-3 mb-lg-5 rounded-sm">
                    <img
                      src="https://acorn.coloredstrategies.com/img/page/starter-project.webp"
                      class="img-fluid rounded-sm lower-opacity border border-separator-light"
                      alt="card image"
                    />
                    <div class="position-absolute reveal-content rounded-sm absolute-center-vertical text-center w-100">
                      <a
                        target="_blank"
                        href="https://acorn-html-starter-project.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Html
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-laravel-starter-project.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        Laravel
                      </a>
                      <a
                        target="_blank"
                        href="https://acorn-dotnet-starter-project.coloredstrategies.com/"
                        class="btn btn-primary btn-sm sw-10 sw-lg-12 d-block mx-auto my-1"
                      >
                        .Net5
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Niches Modal End -->

    <!-- Theme Settings & Niches Buttons Start -->
    <div class="settings-buttons-container">
      <button type="button" class="btn settings-button btn-primary p-0" data-bs-toggle="modal" data-bs-target="#settings" id="settingsButton">
        <span class="d-inline-block no-delay" data-bs-delay="0" data-bs-offset="0,3" data-bs-toggle="tooltip" data-bs-placement="left" title="Settings">
          <i data-acorn-icon="paint-roller" class="position-relative"></i>
        </span>
      </button>
      <button type="button" class="btn settings-button btn-primary p-0" data-bs-toggle="modal" data-bs-target="#niches" id="nichesButton">
        <span class="d-inline-block no-delay" data-bs-delay="0" data-bs-offset="0,3" data-bs-toggle="tooltip" data-bs-placement="left" title="Niches">
          <i data-acorn-icon="toy" class="position-relative"></i>
        </span>
      </button>
    </div>
    <!-- Theme Settings & Niches Buttons End -->

    <!-- Search Modal Start -->
    <div class="modal fade modal-under-nav modal-search modal-close-out" id="searchPagesModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close btn btn-icon btn-icon-only btn-foreground" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body ps-5 pe-5 pb-0 border-0">
            <input id="searchPagesInput" class="form-control form-control-xl borderless ps-0 pe-0 mb-1 auto-complete" type="text" autocomplete="off" />
          </div>
          <div class="modal-footer border-top justify-content-start ps-5 pe-5 pb-3 pt-3 border-0">
            <span class="text-alternate d-inline-block m-0 me-3">
              <i data-acorn-icon="arrow-bottom" data-acorn-size="15" class="text-alternate align-middle me-1"></i>
              <span class="align-middle text-medium">Navigate</span>
            </span>
            <span class="text-alternate d-inline-block m-0 me-3">
              <i data-acorn-icon="arrow-bottom-left" data-acorn-size="15" class="text-alternate align-middle me-1"></i>
              <span class="align-middle text-medium">Select</span>
            </span>
          </div>
        </div>
      </div>
    </div>
    <!-- Search Modal End -->

    <!-- Vendor Scripts Start -->
    <script src="js/vendor/jquery-3.5.1.min.js"></script>
    <script src="js/vendor/bootstrap.bundle.min.js"></script>
    <script src="js/vendor/OverlayScrollbars.min.js"></script>
    <script src="js/vendor/autoComplete.min.js"></script>
    <script src="js/vendor/clamp.min.js"></script>
    <script src="icon/acorn-icons.js"></script>
    <script src="icon/acorn-icons-interface.js"></script>
    <script src="icon/acorn-icons-commerce.js"></script>

    <script src="js/vendor/jquery.barrating.min.js"></script>

    <script src="js/vendor/movecontent.js"></script>

    <!-- Vendor Scripts End -->

    <!-- Template Base Scripts Start -->
    <script src="js/base/helpers.js"></script>
    <script src="js/base/globals.js"></script>
    <script src="js/base/nav.js"></script>
    <script src="js/base/search.js"></script>
    <script src="js/base/settings.js"></script>
    <!-- Template Base Scripts End -->
    <!-- Page Specific Scripts Start -->

    <script src="js/pages/storefront.home.js"></script>

    <script src="js/common.js"></script>
    <script src="js/scripts.js"></script>
    <!-- Page Specific Scripts End -->
  </body>
</html>
