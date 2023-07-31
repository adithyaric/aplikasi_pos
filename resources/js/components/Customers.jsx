import React from "react";
const Customers = ({ customers, customerId, setCustomerId }) => {
    return (
        <div className="form-group row">
            <div className="col-sm-8">
                <select
                    className="form-control"
                    value={customerId}
                    onChange={(e) => setCustomerId(e.target.value)}
                >
                    <option value="">Walking Customer</option>
                    {customers.map((cus) => (
                        <option key={cus.id} value={cus.id}>
                            {`${cus.name}, ${cus.alamat}`}
                        </option>
                    ))}
                </select>
            </div>
            <div className="col-sm-4">
                <a className="btn btn-success btn-sm" href="/customer/create">
                    Tambah Customer
                </a>
            </div>
        </div>
    );
};
export default Customers;
