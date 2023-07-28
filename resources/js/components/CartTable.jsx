// CartTable.jsx
import React from "react";
import { formatRupiah } from "../utils";
import CartTableBody from "./CartTableBody";

const CartTable = ({
    cart,
    handleChangeQty,
    handleClickIncrease,
    handleClickDecrease,
    handleClickDelete,
    handleEmptyCart,
    getTotal,
}) => {
    return (
        <div className="table-responsive">
            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Per Item</th>
                        <th>Aksi</th>
                        <th className="text-right">Subtotal</th>
                    </tr>
                </thead>
                <CartTableBody
                    cart={cart}
                    handleChangeQty={handleChangeQty}
                    handleClickIncrease={handleClickIncrease}
                    handleClickDecrease={handleClickDecrease}
                    handleClickDelete={handleClickDelete}
                />
            </table>
            <button
                type="button"
                className="btn btn-danger btn-block"
                onClick={handleEmptyCart}
                disabled={!cart.length}
            >
                Empty
            </button>
            <div className="col">Total:</div>
            <div className="text-right col">{formatRupiah(getTotal(cart))}</div>
        </div>
    );
};

export default CartTable;
