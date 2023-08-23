import React from "react";
import Select from "react-select";

const Customers = ({ customers, customerId, setCustomerId }) => {
    const options = customers.map((cus) => ({
        value: cus.id,
        label: `${cus.name}, ${cus.alamat}`,
    }));
    const selectedCustomer = customers.find((cus) => cus.id == customerId);
    const placeholder = selectedCustomer
        ? selectedCustomer.name
        : "Select a customer";

    return (
        <div className="form-group row">
            <div className="col-sm-8">
                <Select
                    options={options}
                    value={customerId}
                    onChange={(e) => setCustomerId(e.value)}
                    placeholder={placeholder}
                />
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
