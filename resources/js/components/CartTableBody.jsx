import React from "react";
import { formatRupiah } from "../utils";
const CartTableBody = ({
    cart,
    handleChangeQty,
    handleClickIncrease,
    handleClickDecrease,
    handleClickDelete,
}) => {
    return (
        <>
            {cart.map((c, index) => (
                <tr key={index}>
                    <td>{c.name}</td>
                    <td className="col-2 col-sm-2">
                        <input
                            type="number"
                            className="form-control form-control-sm qty"
                            value={c.pivot.qty}
                            onChange={(event) =>
                                handleChangeQty(c.id, event.target.value)
                            }
                        />
                    </td>
                    <td className="col-2 col-sm-2">
                        {formatRupiah(c.harga_jual)}
                    </td>
                    <td className="col-3 col-sm-3">
                        <button
                            className="btn btn-sm"
                            onClick={() => handleClickIncrease(c.id)}
                        >
                            <i className="fa fa-plus"></i>
                        </button>
                        <button
                            className="btn btn-sm"
                            onClick={() => handleClickDecrease(c.id)}
                        >
                            <i className="fa fa-minus"></i>
                        </button>
                        <button
                            className="btn btn-danger btn-sm"
                            onClick={() => handleClickDelete(c.id)}
                        >
                            <i className="fa fa-trash"></i>
                        </button>
                    </td>
                    <td className="text-right">
                        {formatRupiah(c.harga_jual * c.pivot.qty)}
                    </td>
                </tr>
            ))}
        </>
    );
};
export default CartTableBody;
