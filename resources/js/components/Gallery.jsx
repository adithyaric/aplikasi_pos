import React from "react";
import "../../css/Gallery.css";
import { formatRupiah } from "../utils";

const Gallery = ({ products, addProductToCart }) => {
    return (
        <div className="gallery">
            {products.map((p) => (
                <div
                    key={p.id}
                    className="gallery__item"
                    onClick={() => addProductToCart(p.barcode)}
                >
                    <img
                        className="gallery__img"
                        src={p.image_url}
                        alt={p.name}
                    />
                    <div className="gallery__caption">
                        <ul>
                            <li> Kode {p.barcode}</li>
                            <li> Nama {p.name}</li>
                            <li>Total Stock: {p.total_stock}</li>
                            <li>
                                Serials:{" "}
                                {p.stocks
                                    .filter(
                                        (s) => s.available && s.serial_number
                                    )
                                    .map((s) => s.serial_number)
                                    .join(", ") || "N/A"}
                            </li>
                            <li>{formatRupiah(p.harga_jual)}</li>
                        </ul>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default Gallery;
