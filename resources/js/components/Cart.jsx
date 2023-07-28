import React, { useState, useEffect } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import { sum } from "lodash";
import Swal from "sweetalert2";
import Barcodes from "./Barcodes.jsx";
import Customers from "./Customers";
import CartTable from "./CartTable";
import Gallery from "./Gallery";

const Cart = () => {
    const [cart, setCart] = useState([]);
    const [products, setProducts] = useState([]);
    const [customers, setCustomers] = useState([]);
    const [barcode, setBarcode] = useState("");
    const [customerId, setCustomerId] = useState("");
    const [search, setSearch] = useState("");

    useEffect(() => {
        loadCart();
        loadProducts();
        loadCustomers();
    }, []);

    //Data Products
    const loadProducts = (search = "") => {
        const query = !!search ? `?search=${search}` : "";
        axios.get(`/product${query}`).then((res) => {
            const products = res.data.data;
            setProducts(products);
        });
    };

    const handleChangeSearch = (event) => {
        setSearch(event.target.value);
    };

    const handleSeach = (event) => {
        if (event.keyCode === 13) {
            loadProducts(event.target.value);
        }
    };

    //Data Cart
    const loadCart = () => {
        axios.get("/cart").then((res) => {
            const cart = res.data;
            setCart(cart);
        });
    };

    const addToCart = (barcode) => {
        let product = products.find((p) => p.barcode === barcode);
        if (!!product) {
            let cartItem = cart.find((c) => c.id === product.id);
            if (!!cartItem) {
                setCart(
                    cart.map((c) => {
                        if (c.id === product.id && product.qty > c.pivot.qty) {
                            c.pivot.qty = c.pivot.qty + 1;
                        }
                        return c;
                    })
                );
            } else {
                if (product.qty > 0) {
                    product = {
                        ...product,
                        pivot: { qty: 1, product_id: product.id, user_id: 1 },
                    };
                    setCart([...cart, product]);
                }
            }
            axios
                .post("/cart", { barcode })
                .then((res) => {
                    loadCart();
                    console.log(res);
                })
                .catch((err) => {
                    console.log("Error!", err.response.data.message, "error");
                    Swal.fire(
                        "Error!",
                        err.response.data.message,
                        "error"
                    ).then(() => {
                        setState((prevState) => ({
                            ...prevState,
                            error: true,
                        }));
                    });
                });
        }
    };

    const updateCart = (product_id, newQty) => {
        const updatedCart = cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.qty = newQty;
            }
            return c;
        });
        setCart(updatedCart);
        axios
            .post("/cart/change-qty", { product_id, qty: newQty })
            .then((res) => {})
            .catch((err) => {
                console.log("Error!", err.response.data.message, "error");
                Swal.fire("Error!", err.response.data.message, "error").then(
                    () => {
                        location.reload();
                        setState((prevState) => ({
                            ...prevState,
                            error: true,
                        }));
                    }
                );
            });
    };

    const addProductToCart = (barcode) => {
        addToCart(barcode);
    };

    const handleClickIncrease = (product_id) => {
        const currentQty = cart.find((c) => c.id === product_id).pivot.qty;
        updateCart(product_id, currentQty + 1);
    };

    //Increase 1 qty
    const handleClickDecrease = (product_id) => {
        const currentQty = cart.find((c) => c.id === product_id).pivot.qty;
        if (currentQty > 1) {
            updateCart(product_id, currentQty - 1);
        }
    };

    //Decrease 1 qty
    const handleChangeQty = (product_id, qty) => {
        const parsedQty = parseInt(qty, 10);
        if (!isNaN(parsedQty) && parsedQty >= 1) {
            updateCart(product_id, parsedQty);
        }
    };

    //Delete 1 item
    const handleClickDelete = (product_id) => {
        axios
            .post("/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const updatedCart = cart.filter((c) => c.id !== product_id);
                setCart(updatedCart);
            });
    };

    //Delete All item
    const handleEmptyCart = () => {
        axios.post("/cart/empty", { _method: "DELETE" }).then((res) => {
            setCart([]);
        });
    };

    const getTotal = (cart) => {
        const total = cart.map((c) => c.pivot.qty * c.harga_jual);
        return sum(total);
    };

    //Data Customers
    const loadCustomers = () => {
        axios.get("/customer").then((res) => {
            const customers = res.data;
            setCustomers(customers);
        });
    };

    const handleOnChangeBarcode = (event) => {
        setBarcode(event.target.value);
    };

    const handleScanBarcode = (event) => {
        event.preventDefault();
        addToCart(barcode);
    };

    return (
        <div className="row">
            <div className="col-md-6 col-lg-4">
                <Barcodes
                    barcode={barcode}
                    handleScanBarcode={handleScanBarcode}
                    handleOnChangeBarcode={handleOnChangeBarcode}
                />
                <Customers
                    customers={customers}
                    setCustomerId={setCustomerId}
                />
                <CartTable
                    cart={cart}
                    handleChangeQty={handleChangeQty}
                    handleClickIncrease={handleClickIncrease}
                    handleClickDecrease={handleClickDecrease}
                    handleClickDelete={handleClickDelete}
                    handleEmptyCart={handleEmptyCart}
                    getTotal={getTotal}
                />
            </div>
            <div className="col-md-6 col-lg-8">
                <div className="form-group">
                    <input
                        type="text"
                        className="form-control"
                        placeholder="Cari Product..."
                        onChange={handleChangeSearch}
                        onKeyDown={handleSeach}
                    />
                    <br />
                    <Gallery
                        products={products}
                        addProductToCart={addProductToCart}
                    />
                </div>
            </div>
        </div>
    );
};

export default Cart;

if (document.getElementById("cart")) {
    createRoot(document.getElementById("cart")).render(<Cart />);
}
