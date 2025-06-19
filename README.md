## APP INTERNAL DECAA STORE

**User**
- [x] Superadmin
- [ ] Admin Warehouse
- [ ] Admin Outlet
- [x] Kasir Outlet (sales)

**⁠WAREHOUSE**

- [x] Product
- [x] Category
- [x] Stock -> Purchase Order -> Penerimaan Barang (Baru Masuk Stock)
- [x] Order Outlet
    - [ ] List Pesan Order Dari Outlet
    - [ ] Acc -> Pilih Product & QTY -> `Send Stock (Auto stock berkurang / pindah)`
- Return Outlet
    - [ ] List Return Outlet (Refund)
    - [ ] Acc -> Check Product & QTY -> `Submit (Auto Stock Bertambah)`

**⁠POS**

- Adopsi POS yang sudah ada (newpos.demoo.net)
    Hal Yang Berbeda dengan newpo.demoo.net
    - [x] POS Multi Outlet **Tapi Penjualan Hanya 1 Outlet**
    - [x] Stock Barang Sendiri - Sendiri
    - [x] Stock Barang Request Ke Warehouse (Acc Warehouse)
        - Contoh : Kak aku minta diisi stock Laptop Asus
    - [ ] Return Ke Warehouse (Acc Warehouse)
        - Contoh : Pilih Barang & QTY -> Send Stock (Waiting Acc Warehouse) -> Setelah Acc Stock Berkurang
    - [x] Saat POS pilih `SALES` yang menjualkan (Berfungsi untuk poin)
    - [x] Voucher (Diskon Tambahan)
      - kasir bisa kasih max diskon jika di set oleh admin
      - contoh : kasir 1 bisa memasukkan nominal diskon sebesar 50k, jika ingin kasih potogan tambahan maka pakai voucher
    - [x] Search Stock
        - Group By Name (Biar Tugas Admin Menyamakan Namanya)
            - Stock Harus Kelihatan Dari All Outlet - Warehouse & Statusnya
    - [x] Outlet Bisa Memberi Status Pada Product (Free & On Keep) -> wishlist (hanya sebagai status saja)
