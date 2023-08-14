<!-- Header -->
<header>
    <!-- Top-Header -->
    <div class="full-layer-outer-header">
        <div class="container clearfix">
            <nav>
                <ul class="primary-nav g-nav">
                    <li>
                        <a href="tel:+111444989">
                            <i class="fas fa-phone u-c-brand u-s-m-r-9"></i>
                            Telephone:+111-444-989</a>
                    </li>
                    <li>
                        <a href="mailto:contact@domain.com">
                            <i class="fas fa-envelope u-c-brand u-s-m-r-9"></i>
                            E-mail: contact@domain.com
                        </a>
                    </li>
                </ul>
            </nav>
            <nav>
                <ul class="secondary-nav g-nav">
                    <li>
                        <a>My Account
                            <i class="fas fa-chevron-down u-s-m-l-9"></i>
                        </a>
                        <ul class="g-dropdown" style="width:200px">
                            <li><a href="{{ route('marketcart.index') }}"><i class="fas fa-cog u-s-m-r-9"></i>My Cart</a></li>
                            <li><a href="{{ route('wishlist.index') }}"><i class="far fa-heart u-s-m-r-9"></i>My Wishlist</a></li>
                            <li><a href="{{ route('market.checkout') }}"><i class="far fa-check-circle u-s-m-r-9"></i>Checkout</a></li>
                            @auth
                            <li><a href="{{ url('profile') }}"><i class="fas fa-user u-s-m-r-9"></i>Profile</a></li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-default btn-flat">Logout</button>
                            </form>
                            @else
                            <li><a href="{{ route('login') }}"><i class="fas fa-sign-in-alt u-s-m-r-9"></i>Login / Signup</a></li>
                            @endauth
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <!-- Top-Header /- -->
    <!-- Mid-Header -->
    <div class="full-layer-mid-header">
        <div class="container">
            <div class="clearfix row align-items-center">
                <div class="col-lg-3 col-md-9 col-sm-6">
                    <div class="brand-logo text-lg-center">
                        <a href="{{ route('market.index') }}">
                            <img src="{{ asset('assets/marketplace/images/main-logo/groover-branding-1.png') }}"
                                alt="Groover Brand Logo" class="app-brand-logo">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 u-d-none-lg">
                    <form class="form-searchbox">
                        <label class="sr-only" for="search-landscape">Search</label>
                        <input id="search-landscape" type="text" class="text-field" placeholder="Search everything">
                        <div class="select-box-position">
                            <div class="select-box-wrapper select-hide">
                                <label class="sr-only" for="select-category">Choose category for search</label>
                                <select class="select-box" id="select-category">
                                    <option selected="selected" value="">
                                        All
                                    </option>
                                    <option value="">Men's Clothing</option>
                                    <option value="">Women's Clothing</option>
                                    <option value="">Toys Hobbies & Robots</option>
                                    <option value="">Mobiles & Tablets</option>
                                    <option value="">Consumer Electronics</option>
                                    <option value="">Books & Audible</option>
                                    <option value="">Beauty & Health</option>
                                    <option value="">Furniture Home & Office</option>
                                </select>
                            </div>
                        </div>
                        <button id="btn-search" type="submit" class="button button-primary fas fa-search"></button>
                    </form>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <nav>
                        <ul class="mid-nav g-nav">
                            <li class="u-d-none-lg">
                                <a href="{{ route('market.index') }}">
                                    <i class="ion ion-md-home u-c-brand"></i>
                                </a>
                            </li>
                            <li class="u-d-none-lg">
                                <a href="{{ route('wishlist.index') }}">
                                    <span class="item-counter">{{ app('wishlist')->getContent()->count() }}</span>
                                    <i class="far fa-heart"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('marketcart.index') }}">
                                    <i class="ion ion-md-basket"></i>
                                    <span class="item-counter">{{ Cart::session(auth()->id())->getContent()->count() }}</span>
                                    <span class="item-price">Rp. {{ number_format(Cart::session(auth()->id())->getTotal(), 0, ',', '.') }}</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Mid-Header /- -->
    <!-- Responsive-Buttons -->
    <div class="fixed-responsive-container">
        <div class="fixed-responsive-wrapper">
            <button type="button" class="button fas fa-search" id="responsive-search"></button>
        </div>
        <div class="fixed-responsive-wrapper">
            <a href="#">
                <i class="far fa-heart"></i>
                <span class="fixed-item-counter">4</span>
            </a>
        </div>
    </div>
    <!-- Responsive-Buttons /- -->
    <!-- Bottom-Header -->
    <div class="full-layer-bottom-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3">
                    <div class="v-menu v-close">
                        <span class="v-title">
                            <i class="ion ion-md-menu"></i>
                            All Categories
                            <i class="fas fa-angle-down"></i>
                        </span>
                        <nav>
                            <div class="v-wrapper">
                                <ul class="v-list animated fadeIn">
                                    <li class="js-backdrop">
                                        <a href="#">
                                            <i class="ion ion-md-shirt"></i>
                                            Men's Clothing
                                            <i class="ion ion-ios-arrow-forward"></i>
                                        </a>
                                        <button class="v-button ion ion-md-add"></button>
                                        <div class="v-drop-right" style="width: 700px;">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <ul class="v-level-2">
                                                        <li>
                                                            <a href="shop-v2-sub-#">Tops</a>
                                                            <ul>
                                                                <li><a href="#">T-Shirts</a></li>
                                                                <li><a href="#">Hoodies</a></li>
                                                                <li><a href="#">Suits</a></li>
                                                                <li><a href="#">Black Bean T-Shirt</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-4">
                                                    <ul class="v-level-2">
                                                        <li>
                                                            <a href="shop-v2-sub-#">Outwear</a>
                                                            <ul>
                                                                <li><a href="#">Jackets</a></li>
                                                                <li><a href="#">Trench</a></li>
                                                                <li><a href="#">Parkas</a></li>
                                                                <li><a href="#">Sweaters</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-4">
                                                    <ul class="v-level-2">
                                                        <li>
                                                            <a href="#">Accessories</a>
                                                            <ul>
                                                                <li><a href="#">Watches</a></li>
                                                                <li><a href="#">Ties</a></li>
                                                                <li><a href="#">Scarves</a></li>
                                                                <li><a href="#">Belts</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <ul class="v-level-2">
                                                        <li>
                                                            <a href="shop-v2-sub-#">Bottoms</a>
                                                            <ul>
                                                                <li><a href="#">Casual Pants</a></li>
                                                                <li><a href="#">Shoes</a></li>
                                                                <li><a href="#">Jeans</a></li>
                                                                <li><a href="#">Shorts</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-4">
                                                    <ul class="v-level-2">
                                                        <li>
                                                            <a href="shop-v2-sub-#">Underwear</a>
                                                            <ul>
                                                                <li><a href="#">Boxers</a></li>
                                                                <li><a href="#">Briefs</a></li>
                                                                <li><a href="#">Robes</a></li>
                                                                <li><a href="#">Socks</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-4">
                                                    <ul class="v-level-2">
                                                        <li>
                                                            <a href="shop-v2-sub-#">Sunglasses</a>
                                                            <ul>
                                                                <li><a href="#">Pilot</a></li>
                                                                <li><a href="#">Wayfarer</a></li>
                                                                <li><a href="#">Square</a></li>
                                                                <li><a href="#">Round</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="ion ion-md-phone-portrait"></i>
                                            Mobiles & Tablets
                                        </a>
                                    </li>
                                    <li>
                                        <a class="v-more">
                                            <i class="ion ion-md-add"></i>
                                            <span>View More</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-9">
                    <ul class="bottom-nav g-nav u-d-none-lg">
                        <li>
                            <a href="#">New Arrivals
                                <span class="superscript-label-new">NEW</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">Exclusive Deals
                                <span class="superscript-label-hot">HOT</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">Flash Deals</a>
                        </li>
                        <li>
                            <a href="#">Super Sale
                                <span class="superscript-label-discount">-15%</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Bottom-Header /- -->
</header>
<!-- Header /- -->
