@extends('layouts.base')

@section('title', 'Marketplace')

@section('container')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>Checkout</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="home.html">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="checkout.html">Checkout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->
    <!-- Checkout-Page -->
    <div class="page-checkout u-s-p-t-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <!-- Second Accordion -->
                    @if ((count(Cart::session(auth()->id())->getConditions()) == 0) && $cartItems->count() > 0)
                        <form action="{{ route('market.coupon') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="message-open u-s-m-b-24">
                                Have a coupon?
                                <strong>
                                    <a class="u-c-brand" data-toggle="collapse" href="#showcoupon">Click here to enter your code</a>
                                </strong>
                            </div>
                            <div class="collapse u-s-m-b-24" id="showcoupon">
                                <h6 class="collapse-h6">
                                    Enter your coupon code if you have one.
                                </h6>
                                <div class="coupon-field">
                                    <label class="sr-only" for="coupon-code">Apply Coupon</label>
                                    <input id="coupon-code" name="code" type="text" class="text-field" placeholder="Coupon Code">
                                    <button type="submit" class="button">Apply Coupon</button>
                                </div>
                            </div>
                        </form>
                    @endif
                    <!-- Second Accordion /- -->
                    <form action="{{ route('market.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <!-- Billing-&-Shipping-Details -->
                            <div class="col-lg-6">
                                <h4 class="section-h4">Billing Details</h4>
                                <!-- Form-Fields -->
                                <div class="u-s-m-b-13">
                                    <label for="name">Name
                                        <span class="astk">*</span>
                                    </label>
                                    <input type="text" name="name" class="text-field"
                                        value="{{ auth()->user()->name }}">
                                </div>
                                <div class="u-s-m-b-13">
                                    <label for="address">Address
                                        <span class="astk">*</span>
                                    </label>
                                    <textarea class="text-area" name="address">{{ auth()->user()->alamat }}</textarea>
                                </div>
                                <div class="group-inline u-s-m-b-13">
                                    <div class="group-1 u-s-p-r-16">
                                        <label for="email">Email address
                                            <span class="astk">*</span>
                                        </label>
                                        <input type="text" name="email" class="text-field"
                                            value="{{ auth()->user()->email }}">
                                    </div>
                                    <div class="group-2">
                                        <label for="phone">Phone
                                            <span class="astk">*</span>
                                        </label>
                                        <input type="text" name="phone" class="text-field"
                                            value="{{ auth()->user()->no_telp }}">
                                    </div>
                                </div>
                                <!-- Form-Fields /- -->
                                <div class="u-s-m-b-13">
                                    <label for="order_notes">Order Notes</label>
                                    <textarea class="text-area" name="order_notes" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                                </div>
                            </div>
                            <!-- Billing-&-Shipping-Details /- -->
                            <!-- Checkout -->
                            <div class="col-lg-6">
                                <h4 class="section-h4">Your Order</h4>
                                <div class="order-table">
                                    <table class="u-s-m-b-13">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($cartItems->sortBy('name') as $item)
                                        <tr>
                                            <td>
                                                <h6 class="order-h6">{{$item->name}}</h6>
                                                <span class="order-span-quantity">x{{$item->quantity}}</span>
                                                @foreach($item->conditions as $condition)
                                                <span class="order-span-voucher">{{$condition->getName()}}: {{$condition->getValue()}}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if ($item->getPriceSumWithConditions() != $item->price * $item->quantity)
                                                    <h6 class="order-h6"><s>@currency($item->price * $item->quantity)</s></h6>
                                                @endif
                                                <h6 class="order-h6">@currency($item->getPriceSumWithConditions())</h6>
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td>
                                                <h3 class="order-h3">Subtotal</h3>
                                            </td>
                                            <td>
                                                <h3 class="order-h3">Rp.{{number_format(Cart::session(auth()->id())->getSubTotal(),0,',','.')}}</h3>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h3 class="order-h3">Shipping</h3>
                                            </td>
                                            <td>
                                                <h3 class="order-h3">$0.00</h3>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h3 class="order-h3">Tax</h3>
                                            </td>
                                            <td>
                                                <h3 class="order-h3">$0.00</h3>
                                            </td>
                                        </tr>
                                        @foreach(Cart::session(auth()->id())->getConditions() as $condition)
                                        <tr>
                                            <td>
                                                <h3 class="order-h3">{{$condition->getName()}}</h3>
                                            </td>
                                            <td>
                                                <h3 class="order-h3">{{(strpos($value = $condition->getValue(), '%') !== false) ? $value : "Rp. " . number_format($value, 0, ',', '.')}}</h3>
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td>
                                                <h3 class="order-h3">Total</h3>
                                            </td>
                                            <td>
                                                <h3 class="order-h3">Rp.{{number_format(Cart::session(auth()->id())->getTotal(),0,',','.')}}</h3>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    @foreach ($payments as $item)
                                        <div class="u-s-m-b-13">
                                            <input type="radio" class="radio-box" name="payment_method" id="{{ $item->id }}" value="{{ $item->name }}" @checked($loop->first)>
                                            <label class="label-text" for="{{ $item->id }}">{{ $item->name }}</label>
                                            <label class="label-text" for="{{ $item->id }}">{{ $item->bank_number }}</label>
                                            <label class="label-text" for="{{ $item->id }}">{{ $item->desc }}</label>
                                        </div>
                                    @endforeach
                                    <button type="submit" class="button button-outline-secondary">Place Order</button>
                                </div>
                            </div>
                            <!-- Checkout /- -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Checkout-Page /- -->
@endsection
