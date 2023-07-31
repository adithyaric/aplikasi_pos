import React from "react";
import { formatRupiah } from "../utils";
import CartTableBody from "./CartTableBody";

const CartTable = ({
    cart,
    discount,
    handleChangeQty,
    handleClickIncrease,
    handleClickDecrease,
    handleClickDelete,
    handleEmptyCart,
    getTotal,
    handleDiscountChange,
    handleClickWishlist,
    handleClickSubmit,
}) => {
    return (
        <>
            <div className="table-responsive">
                <table className="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Per Item</th>
                            <th>Aksi</th>
                            <th className="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <CartTableBody
                            cart={cart}
                            handleChangeQty={handleChangeQty}
                            handleClickIncrease={handleClickIncrease}
                            handleClickDecrease={handleClickDecrease}
                            handleClickDelete={handleClickDelete}
                        />
                        <tr>
                            <td colSpan="4">Total</td>
                            <td className="text-right">
                                {formatRupiah(getTotal(cart))}
                            </td>
                        </tr>
                        <tr>
                            <td colSpan="4">Discount</td>
                            <td>
                                <input
                                    type="number"
                                    className="form-control form-control-sm"
                                    placeholder="Discount..."
                                    value={discount}
                                    onChange={handleDiscountChange}
                                />
                            </td>
                        </tr>
                        <tr>
                            <th colSpan="4">Grand Total</th>
                            <th className="text-right">
                                {formatRupiah(getTotal(cart) - discount)}
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div className="row">
                <div className="col-2 col-sm-2">
                    <button
                        type="button"
                        className="btn btn-danger btn-block"
                        onClick={handleEmptyCart}
                        disabled={!cart.length}
                    >
                        Empty
                    </button>
                </div>
                <div className="col-4 col-sm-4">
                    <button
                        type="button"
                        className="btn btn-warning btn-block"
                        onClick={handleClickWishlist}
                        disabled={!cart.length}
                    >
                        Hold
                    </button>
                </div>
                <div className="col-6 col-sm-6">
                    <button
                        type="button"
                        className="btn btn-success btn-block"
                        onClick={handleClickSubmit}
                        disabled={!cart.length}
                    >
                        Submit
                    </button>
                </div>
            </div>
        </>
    );
};

export default CartTable;
