import React, { useEffect } from "react";

const Vouchers = ({ vouchers, voucherId, setVoucherId, setDiscount }) => {
    const handleVoucherChange = (e) => {
        const selectedVoucherId = e.target.value;
        setVoucherId(selectedVoucherId);

        // Find the selected voucher and apply its value as discount
        const selectedVoucher = vouchers.find((v) => v.id == selectedVoucherId);
        if (selectedVoucher) {
            setDiscount(selectedVoucher.value);
        } else {
            setDiscount(0); // Reset discount if no voucher selected
        }
    };

    return (
        <div className="form-group row">
            <div className="col-sm-8">
                <select
                    id="voucher"
                    className="form-control"
                    // value={voucherId}
                    onChange={handleVoucherChange}
                >
                    <option value="">Select a voucher</option>
                    {vouchers.map((voucher) => (
                        <option key={voucher.id} value={voucher.id}>
                            {voucher.name}, {voucher.value}
                        </option>
                    ))}
                </select>
            </div>
            <div className="col-sm-4">
                <a className="btn btn-success btn-sm" href="/voucher/create">
                    Tambah Voucher
                </a>
            </div>
        </div>
    );
};

export default Vouchers;
