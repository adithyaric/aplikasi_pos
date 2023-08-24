import React, { useState, useEffect } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import { sum } from "lodash";
import Swal from "sweetalert2";
import Barcodes from "./Barcodes.jsx";
import Customers from "./Customers";
// import Kas from "./Kas";
import CartTable from "./CartTable";
import Gallery from "./Gallery";
import Wishlist from "./Wishlist";

const Cart = () => {
    const [cart, setCart] = useState([]);
    const [products, setProducts] = useState([]);
    const [customers, setCustomers] = useState([]);
    const [customerId, setCustomerId] = useState("");
    const [kas, setKas] = useState([]);
    const [kasId, setKasId] = useState("");
    const [barcode, setBarcode] = useState("");
    const [search, setSearch] = useState("");
    const [discount, setDiscount] = useState("");
    const [total, setTotal] = useState(0);
    const [wishlist, setWishlist] = useState([]);
    const [errorMessage, setErrorMessage] = useState("");
    const outlet = window.outlet;

    useEffect(() => {
        setTotal(getTotal(cart) - discount);
    }, [cart, discount]);

    useEffect(() => {
        loadCart();
        loadProducts();
        loadCustomers();
        loadKas();
        loadWishlist();
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
            .post("/cart-change-qty", { product_id, qty: newQty })
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
        updateCart(product_id, Number(currentQty) + 1);
    };

    const handleClickDecrease = (product_id) => {
        const currentQty = cart.find((c) => c.id === product_id).pivot.qty;
        if (currentQty > 1) {
            updateCart(product_id, currentQty - 1);
        }
    };

    const handleChangeQty = (product_id, qty) => {
        const parsedQty = parseInt(qty, 10);
        if (!isNaN(parsedQty) && parsedQty >= 1) {
            updateCart(product_id, parsedQty);
        }
    };

    //Delete 1 item
    const handleClickDelete = (product_id) => {
        axios
            .post("/cart/destroy", { product_id, _method: "DELETE" })
            .then((res) => {
                const updatedCart = cart.filter((c) => c.id !== product_id);
                setCart(updatedCart);
            });
    };

    //Delete All item
    const handleEmptyCart = () => {
        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to clear your cart?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, clear it!",
            cancelButtonText: "No, keep it",
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post("/cart-empty", { _method: "DELETE" }).then((res) => {
                    setCart([]);
                });
            }
        });
    };

    const getTotal = (cart) => {
        const total = cart.map((c) => c.pivot.qty * c.harga_jual);
        return sum(total);
    };

    const handleChangeTotal = (event) => {
        setTotal(event.target.value);
    };

    //Data Customers
    const loadCustomers = () => {
        axios.get("/customer").then((res) => {
            const customers = res.data;
            setCustomers(customers);
            setCustomerId(customers[0].id);
        });
    };

    //Data Kas
    const loadKas = () => {
        axios.get(`/kas?outlet_id=${outlet.id}`).then((res) => {
            const kas = res.data;
            setKas(kas);
        });
    };

    const handleOnChangeBarcode = (event) => {
        setBarcode(event.target.value);
    };

    const handleScanBarcode = (event) => {
        event.preventDefault();
        addToCart(barcode);
    };

    const handleDiscountChange = (event) => {
        const value = parseInt(event.target.value, 10);
        if (!isNaN(value) && value >= 0) {
            setDiscount(value);
        }
    };

    //Hold
    const loadWishlist = () => {
        axios.get("/wishlist-pos").then((res) => {
            setWishlist(res.data);
        });
    };

    const handleClickWishlist = () => {
        Swal.fire({
            title: "Wishlist Name",
            input: "text",
            showCancelButton: true,
            confirmButtonText: "Send",
            showLoaderOnConfirm: true,
            preConfirm: (name) => {
                return axios
                    .post("/wishlist-pos", {
                        cart,
                        customer_id: customerId,
                        name: name,
                    })
                    .then((res) => {
                        loadWishlist();
                        loadCart();
                        loadWishlist();
                        loadProducts();
                        setBarcode("");
                        setCustomerId("");
                        setSearch("");
                        setDiscount(0);
                        Swal.fire(
                            "Success!",
                            "Items have been added to your wishlist",
                            "success"
                        );
                    })
                    .catch((err) => {
                        console.log(
                            "Error!",
                            err.response.data.message,
                            "error"
                        );
                        Swal.fire("Error!", err.response.data.message, "error");
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        });
    };

    const handleMoveToCart = (name, customer_id) => {
        axios
            .post("/wishlist/move-to-cart", { name, customer_id })
            .then((res) => {
                loadCart();
                loadWishlist();
                loadProducts();
                setBarcode("");
                setCustomerId(customer_id);
                setSearch("");
                setDiscount(0);
            });
    };

    //Submit
    // const handleClickSubmit = (event) => {
    //     Swal.fire({
    //         title: "Received Amount",
    //         input: "text",
    //         inputValue: getTotal(cart) - discount,
    //         showCancelButton: true,
    //         confirmButtonText: "Send",
    //         showLoaderOnConfirm: true,
    //         preConfirm: (amount) => {
    //             return axios
    //                 .post("/penjualan", {
    //                     customer_id: customerId,
    //                     outlet_id: outlet.id,
    //                     kas_id: kasId,
    //                     total: amount,
    //                     discount: discount,
    //                     cart: cart,
    //                 })
    //                 .then((res) => {
    //                     loadCart();
    //                     loadWishlist();
    //                     loadProducts();
    //                     setBarcode("");
    //                     setCustomerId("");
    //                     setSearch("");
    //                     setDiscount(0);
    //                     Swal.fire(
    //                         "Success!",
    //                         "Pesanan berhasil dibuat",
    //                         "success"
    //                     );
    //                 })
    //                 .catch((err) => {
    //                     Swal.showValidationMessage(err.response.data.message);
    //                 });
    //         },
    //         allowOutsideClick: () => !Swal.isLoading(),
    //     });
    // };

    // Handle form submission
    const handleSubmit = (event) => {
        // Get the total amount from the input field
        const totalAmount = $(".total").val();

        // Send the POST request with the data
        axios
            .post("/penjualan", {
                customer_id: customerId,
                outlet_id: outlet.id,
                kas_id: kasId,
                total: totalAmount,
                discount: discount,
                cart: cart,
            })
            .then((res) => {
                loadCart();
                loadProducts();
                loadCustomers();
                loadKas();
                loadWishlist();
                Swal.fire("Success!", "Pesanan berhasil dibuat", "success").then(() => {
                    window.location.reload(false); // <-- added this line to refresh the page
                });
            })
            .catch((err) => {
                // console.log(err.response.data.message);
                // Swal.showValidationMessage(err.response.data.message);
                setErrorMessage(err.response.data.message);
            });
    };

    // Add an onClick event handler to the "OK" button in the modal footer
    $(".modal-footer .btn-primary").on("click", handleSubmit);

    return (
        <div className="row">
            <div className="col-12 col-sm-12">
                <Wishlist
                    wishlist={wishlist}
                    handleMoveToCart={handleMoveToCart}
                />
            </div>
            <div className="col-md-6 col-lg-4">
                <Barcodes
                    barcode={barcode}
                    handleScanBarcode={handleScanBarcode}
                    handleOnChangeBarcode={handleOnChangeBarcode}
                />
                <Customers
                    key={customerId}
                    customers={customers}
                    customerId={customerId}
                    setCustomerId={setCustomerId}
                />
                <CartTable
                    cart={cart}
                    discount={discount}
                    handleChangeQty={handleChangeQty}
                    handleClickIncrease={handleClickIncrease}
                    handleClickDecrease={handleClickDecrease}
                    handleClickDelete={handleClickDelete}
                    handleDiscountChange={handleDiscountChange}
                    getTotal={getTotal}
                    handleEmptyCart={handleEmptyCart}
                    handleClickWishlist={handleClickWishlist}
                    // handleClickSubmit={handleClickSubmit}
                    handleChangeTotal={handleChangeTotal}
                    total={total}
                    errorMessage={errorMessage}
                    kas={kas}
                    kasId={kasId}
                    setKasId={setKasId}
                />
                <hr />
                {/* <Kas kas={kas} kasId={kasId} setKasId={setKasId} /> */}
            </div>
            <hr />
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
