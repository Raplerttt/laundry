-- TABEL USERS
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(128),
    email VARCHAR(128) UNIQUE,
    password VARCHAR(255),
    nomer_telepon VARCHAR(255)
);

-- TABEL PAKET
CREATE TABLE paket (
    id SERIAL PRIMARY KEY,
    nama_paket VARCHAR(30),
    harga VARCHAR(20)
);

-- TABEL ORDERS
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    nama_depan VARCHAR(128),
    nama_belakang VARCHAR(128),
    nomer_telepon VARCHAR(255),
    alamat_penjemputan VARCHAR(255),
    alamat_pengantaran VARCHAR(255),
    status_pemesanan VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- TABEL ORDER_PAKET (many-to-many: order <-> paket)
CREATE TABLE order_paket (
    id SERIAL PRIMARY KEY,
    id_order INT NOT NULL,
    id_paket INT NOT NULL,
    FOREIGN KEY (id_order) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (id_paket) REFERENCES paket(id) ON DELETE CASCADE
);

-- TABEL REVIEWS (user mereview paket tertentu)
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    paket_id INT NOT NULL,
    rating FLOAT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE CASCADE
);
