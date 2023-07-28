import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import { sum } from "lodash";
import CartTable from "./CartTable";
import Gallery from "./Gallery";

class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            barcode: "",
            search: "",
        };

        //Products & Search
        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);

        //Cart
        this.loadCart = this.loadCart.bind(this);
        this.addProductToCart = this.addProductToCart.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleClickDelete = this.handleClickDelete.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);
    }

    componentDidMount() {
        this.loadCart();
        this.loadProducts();
    }

    //Products & Search
    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        axios.get(`/product${query}`).then((res) => {
            const products = res.data.data;
            this.setState({ products });
        });
    }
    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }
    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    //Cart
    loadCart() {
        axios.get("/cart").then((res) => {
            const cart = res.data;
            this.setState({ cart });
        });
    }

    getTotal(cart) {
        const total = cart.map((c) => c.pivot.qty * c.harga_jual);
        return sum(total).toFixed(2);
    }

    addProductToCart(barcode) {
        let product = this.state.products.find((p) => p.barcode === barcode);
        if (!!product) {
            // if product is already in cart
            let cart = this.state.cart.find((c) => c.id === product.id);
            if (!!cart) {
                // update qty
                this.setState({
                    cart: this.state.cart.map((c) => {
                        if (c.id === product.id && product.qty > c.pivot.qty) {
                            c.pivot.qty = c.pivot.qty + 1;
                        }
                        return c;
                    }),
                });
            } else {
                if (product.qty > 0) {
                    product = {
                        ...product,
                        pivot: {
                            qty: 1,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };

                    this.setState({ cart: [...this.state.cart, product] });
                }
            }

            axios
                .post("/cart", { barcode })
                .then((res) => {
                    this.loadCart();
                    console.log(res);
                })
                .catch((err) => {
                    console.log("Error!", err.response.data.message, "error");
                });
        }
    }

    handleChangeQty(product_id, qty) {
        const parsedQty = parseInt(qty, 10);
        if (!isNaN(parsedQty) && parsedQty >= 1) {
            const cart = this.state.cart.map((c) => {
                if (c.id === product_id) {
                    c.pivot.qty = parsedQty;
                }
                return c;
            });
            this.setState({ cart });
            axios
                .post("/cart/change-qty", { product_id, qty: parsedQty })
                .then((res) => {})
                .catch((err) => {
                    console.log("Error!", err.response.data.message, "error");
                });
        }
    }

    handleClickDelete(product_id) {
        axios
            .post("/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            });
    }

    handleEmptyCart() {
        axios.post("/cart/empty", { _method: "DELETE" }).then((res) => {
            this.setState({ cart: [] });
        });
    }

    render() {
        const { cart, products, barcode } = this.state;
        return (
            <div className="row">
                <div className="col-md-4">
                    <CartTable
                        cart={cart}
                        handleChangeQty={this.handleChangeQty}
                        handleClickDelete={this.handleClickDelete}
                        handleEmptyCart={this.handleEmptyCart}
                        getTotal={this.getTotal}
                    />
                </div>
                <div className="col-md-8">
                    <div className="form-group">
                        <input
                            type="text"
                            className="form-control"
                            placeholder="Search Product..."
                            onChange={this.handleChangeSearch}
                            onKeyDown={this.handleSeach}
                        />
                        <br />
                        <Gallery
                            products={products}
                            addProductToCart={this.addProductToCart}
                        />
                    </div>
                </div>
            </div>
        );
    }
}

export default Cart;

if (document.getElementById("cart")) {
    createRoot(document.getElementById("cart")).render(<Cart />);
}
