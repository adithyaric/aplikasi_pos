import React from "react";

const CartTableBody = ({ cart, handleChangeQty, handleClickDelete }) => {
    return (
        <tbody>
            {cart.map((c) => (
                <tr key={c.id}>
                    <td>{c.name}</td>
                    <td>
                        <input
                            type="number"
                            className="form-control form-control-sm qty"
                            value={c.pivot.qty}
                            onChange={(event) =>
                                handleChangeQty(c.id, event.target.value)
                            }
                        />
                    </td>
                    <td>
                        <button
                            className="btn btn-danger btn-sm"
                            onClick={() => handleClickDelete(c.id)}
                        >
                            <i className="fa fa-trash"></i>
                        </button>
                    </td>
                    <td className="text-right">
                        {(c.harga_jual * c.pivot.qty).toFixed(2)}
                    </td>
                </tr>
            ))}
        </tbody>
    );
};

export default CartTableBody;
