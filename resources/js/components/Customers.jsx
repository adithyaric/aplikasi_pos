import React, { useEffect, useRef } from "react";

const Customers = ({ customers, customerId, setCustomerId }) => {
    const selectRef = useRef(null);
    const [select2Initialized, setSelect2Initialized] = React.useState(false);

    useEffect(() => {
        if (selectRef.current && !select2Initialized) {
            // Initialize Select2
            $(selectRef.current).select2({
                placeholder: "Select a customer",
                width: "100%",
                allowClear: true,
            });

            // Handle change event
            $(selectRef.current).on("change", (e) => {
                setCustomerId(e.target.value);
            });

            setSelect2Initialized(true);
        }

        // Set initial value if customerId changes
        if (selectRef.current && select2Initialized && customerId) {
            $(selectRef.current).val(customerId).trigger("change");
        }

        return () => {
            if (selectRef.current && select2Initialized) {
                try {
                    $(selectRef.current).off("change");
                    $(selectRef.current).select2("destroy");
                } catch (e) {
                    console.warn("Select2 cleanup error:", e);
                }
                setSelect2Initialized(false);
            }
        };
    }, [customerId, select2Initialized, setCustomerId]);

    return (
        <div className="form-group row">
            <div className="col-sm-8">
                <select
                    ref={selectRef}
                    className="form-control"
                    defaultValue={customerId}
                >
                    <option value="">Select a customer</option>
                    {customers.map((cus) => (
                        <option key={cus.id} value={cus.id}>
                            {cus.name}, {cus.alamat}
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
