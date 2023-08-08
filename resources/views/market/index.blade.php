@extends('layouts.base')

@section('title', 'Marketplace')

@section('container')
    <!-- Men-Clothing -->
    <section class="section-maker">
        <div class="container">
            <div class="text-center sec-maker-header">
                <h3 class="sec-maker-h3">MEN'S CLOTHING</h3>
                <ul class="nav tab-nav-style-1-a justify-content-center">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#men-latest-products">Latest Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#men-best-selling-products">Best Selling</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#men-top-rating-products">Top Rating</a>
                    </li>
                </ul>
            </div>
            <div class="wrapper-content">
                <div class="outer-area-tab">
                    <div class="tab-content">
                        <div class="tab-pane active show fade" id="men-latest-products">
                            <div class="slider-fouc">
                                <div class="products-slider owl-carousel" data-item="2">
                                    @foreach ($sliders as $slider)
                                        <div class="item">
                                            <div class="image-container">
                                                <a class="item-img-wrapper-link" href="single-product.html">
                                                    <img class="img-fluid"
                                                        src="{{ asset($slider->pic) }}"
                                                        alt="Product">
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="men-best-selling-products">
                            <!-- Product Not Found -->
                            <div class="product-not-found">
                                <div class="not-found">
                                    <h2>SORRY!</h2>
                                    <h6>There is not any product in specific catalogue.</h6>
                                </div>
                            </div>
                            <!-- Product Not Found /- -->
                        </div>
                        <div class="tab-pane fade" id="men-top-rating-products">
                            <!-- Product Not Found -->
                            <div class="product-not-found">
                                <div class="not-found">
                                    <h2>SORRY!</h2>
                                    <h6>There is not any product in specific catalogue.</h6>
                                </div>
                            </div>
                            <!-- Product Not Found /- -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Men-Clothing-Timing-Section -->
    <section class="section-maker">
        <div class="container">
            <!-- Carousel -->
            <div class="slider-fouc">
                <div class="products-slider owl-carousel" data-item="3">
                    @foreach ($products as $product)
                        <div class="item">
                            <div class="image-container">
                                <a class="item-img-wrapper-link" href="single-product.html">
                                    <img class="img-fluid" src="{{ asset($product->pic) }}">
                                </a>
                                <div class="item-action-behaviors">
                                    <a class="item-quick-look" data-toggle="modal" href="#quick-view">Quick
                                        Look</a>
                                    <a class="item-mail" href="javascript:void(0)">Mail</a>
                                    <a class="item-addwishlist" href="javascript:void(0)">Add to Wishlist</a>
                                    <a class="item-addCart" href="javascript:void(0)">Add to Cart</a>
                                </div>
                            </div>
                            <div class="item-content">
                                <div class="what-product-is">
                                    <ul class="bread-crumb">
                                        <li class="">
                                            <a href="shop-v1-root-category.html">{{ $product->category->name }}</a>
                                        </li>
                                    </ul>
                                    <h6 class="item-title">
                                        <a href="single-product.html">{{ $product->name }}</a>
                                    </h6>
                                </div>
                                <div class="price-template">
                                    <div class="item-new-price">
                                        @currency($product->harga_jual)
                                    </div>
                                </div>
                            </div>
                            <div class="tag hot">
                                <span>HOT</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Carousel /- -->
        </div>
    </section>
    <!-- Men-Clothing-Timing-Section /- -->
    <!-- Men-Clothing /- -->
    <!-- Continue-Link -->
    <div class="continue-link-wrapper u-s-p-b-80">
        <a class="continue-link" href="store-directory.html" title="View all products on site">
            <i class="ion ion-ios-more"></i>
        </a>
    </div>
    <!-- Continue-Link /- -->
@endsection
