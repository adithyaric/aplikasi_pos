// CartTable.jsx
import React from "react";
import CartTableBody from "./CartTableBody";

const CartTable = ({
    cart,
    handleChangeQty,
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
                        <th>Aksi</th>
                        <th className="text-right">Price</th>
                    </tr>
                </thead>
                <CartTableBody
                    cart={cart}
                    handleChangeQty={handleChangeQty}
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
            <div className="text-right col">{getTotal(cart)}</div>
        </div>
    );
};

export default CartTable;
