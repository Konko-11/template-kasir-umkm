// Database Produk Sederhana
let products = []; // Sekarang menjadi array kosong, akan diisi dari API
let cart = {}; 

// script.js

// ... (Bagian array products dan cart) ...

// Fungsi utilitas untuk menghilangkan format (menghapus titik) dan mengembalikan angka INT
function unformatRupiah(value) {
    // Hapus semua titik (separator ribuan)
    const cleaned = value.replace(/\./g, '');
    // Kembalikan sebagai integer, atau 0 jika gagal
    return parseInt(cleaned) || 0; 
}

// Fungsi untuk otomatis memformat saat input diketik
function formatInput(inputElement) {
    let value = inputElement.value.replace(/\./g, ''); // Hapus semua titik saat ini
    if (value) {
        value = parseInt(value);
        // Tampilkan kembali dengan format ribuan (titik)
        inputElement.value = value.toLocaleString('id-ID'); 
    }
}

// ... (Sisa fungsi lain seperti updateCart, addToCart, checkout, dll.) ...

// Fungsi untuk memuat daftar produk ke halaman
function loadProducts() {
    // Mengambil data dari API PHP
    fetch('get_products.php')
        .then(response => response.json())
        .then(data => {
            products = data; // Simpan data dari database ke array products
            renderProductsUI(); // Panggil fungsi baru untuk menampilkan UI
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            alert('Gagal mengambil data produk dari server. Cek koneksi database.');
        });
}
function renderProductsUI() {
    const productList = document.getElementById('product-list');
    productList.innerHTML = '';
    
    products.forEach(product => {
        const div = document.createElement('div');
        div.className = 'product-item';
        // Tambahkan atribut data-id
        div.setAttribute('data-id', product.id); 
        div.innerHTML = `
            ${product.name}
            <div style="font-size: 0.9em; margin-top: 5px;">${formatRupiah(product.price)}</div>
            <div style="font-size: 0.8em; opacity: 0.7;">Stok: ${product.stock}</div>
        `;
        div.onclick = () => addToCart(product);
        productList.appendChild(div);
    });
}

// Fungsi untuk menambah produk ke keranjang
function addToCart(product) {
    if (cart[product.id]) {
        cart[product.id].quantity += 1;
    } else {
        cart[product.id] = { ...product, quantity: 1 };
    }
    updateCart();
}

// Fungsi untuk memperbarui tampilan keranjang dan total
// DI DALAM script.js, GANTI FUNGSI updateCart() DENGAN YANG INI:
function updateCart() {
    const cartList = document.getElementById('cart-list');
    cartList.innerHTML = '';
    
    // 1. Hitung Subtotal Awal (sebelum diskon)
    let subtotal = 0;

    for (const id in cart) {
        // ... (Loop item keranjang tetap sama, hitung subtotal) ...
        const item = cart[id];
        const itemSubtotal = item.price * item.quantity;
        subtotal += itemSubtotal;
        
        // ... (Tambahkan li ke cartList) ...
    }

    // 2. Ambil Input Diskon
    const discountInput = document.getElementById('discount-input');
    let discountAmount = unformatRupiah(discountInput.value);

    // Pastikan diskon tidak melebihi subtotal
    if (discountAmount > subtotal) {
        discountAmount = subtotal; 
        // Secara opsional, Anda bisa mereset nilai input jika diskon terlalu besar
        discountInput.value = formatRupiah(discountAmount); 
    }

    // 3. Hitung Total Akhir
    const finalTotal = subtotal - discountAmount;
    
    // 4. Update Tampilan Diskon
    const discountDisplay = document.getElementById('discount-display');
    if (discountAmount > 0) {
        document.getElementById('current-discount-amount').textContent = formatRupiah(discountAmount);
        discountDisplay.style.display = 'block';
    } else {
        discountDisplay.style.display = 'none';
    }

    // 5. Update Total Akhir
    document.getElementById('grand-total').textContent = formatRupiah(finalTotal);
}

// Fungsi untuk menghapus item dari keranjang (mengurangi kuantitas)
function removeItem(id) {
    if (cart[id]) {
        cart[id].quantity -= 1;
        if (cart[id].quantity <= 0) {
            delete cart[id];
        }
    }
    updateCart();
}

// Fungsi untuk menyelesaikan transaksi
// DI DALAM script.js

// ... (Fungsi utility: formatRupiah, unformatRupiah, formatInput) ...
// ... (Fungsi updateCart, removeItem, dll.) ...

// GANTI FUNGSI checkout() YANG LAMA
function checkout() {
    if (Object.keys(cart).length === 0) {
        alert("Keranjang belanja masih kosong! Silakan pilih produk terlebih dahulu.");
        return;
    }

    const modal = document.getElementById('payment-modal');
    const totalAmountSpan = document.getElementById('modal-total-amount');
    
    // Ambil Total Bayar dari tampilan keranjang
    const finalTotalText = document.getElementById('grand-total').textContent;
    totalAmountSpan.textContent = finalTotalText;

    // Reset input dan tampilan modal
    document.getElementById('paid-amount-input').value = '';
    document.getElementById('change-amount').textContent = formatRupiah(0);
    document.getElementById('payment-method-select').value = 'Cash';
    togglePaymentInputs(); // Atur tampilan awal ke Cash
    
    // Tampilkan Modal
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// Fungsi untuk menyembunyikan/menampilkan input Uang Bayar berdasarkan metode
function togglePaymentInputs() {
    const method = document.getElementById('payment-method-select').value;
    const cashInputs = document.getElementById('cash-inputs');
    const completeButton = document.getElementById('complete-button');
    
    if (method === 'Cash') {
        cashInputs.style.display = 'block';
        // Atur agar tombol tidak bisa diklik sampai kembalian >= 0
        calculateChange(); 
    } else {
        cashInputs.style.display = 'none';
        // Non-tunai selalu dianggap lunas (langsung enable tombol)
        completeButton.disabled = false;
        completeButton.style.cursor = 'pointer';
        completeButton.style.backgroundColor = '#007bff';
    }
}

// Fungsi untuk menghitung kembalian dan mengaktifkan tombol Selesaikan Transaksi
function calculateChange() {
    const finalTotalText = document.getElementById('modal-total-amount').textContent;
    const finalTotal = unformatRupiah(finalTotalText);

    const paidInput = document.getElementById('paid-amount-input').value;
    const paidAmount = unformatRupiah(paidInput);

    const change = paidAmount - finalTotal;
    const changeSpan = document.getElementById('change-amount');
    const completeButton = document.getElementById('complete-button');

    changeSpan.textContent = formatRupiah(change);
    
    if (paidAmount >= finalTotal) {
        completeButton.disabled = false;
        completeButton.style.cursor = 'pointer';
        completeButton.style.backgroundColor = '#007bff';
    } else {
        completeButton.disabled = true;
        completeButton.style.cursor = 'not-allowed';
        completeButton.style.backgroundColor = '#a0a0a0'; // Warna abu-abu saat disabled
    }
}

// Fungsi untuk menyelesaikan transaksi (sebelum integrasi database)
function completeTransaction() {
    const totalAmount = document.getElementById('modal-total-amount').textContent;
    const paymentMethod = document.getElementById('payment-method-select').value;
    const changeAmount = document.getElementById('change-amount').textContent;

    // Di sini data transaksi akan dikirim ke backend
    
    alert(`Transaksi Selesai!\nTotal: ${totalAmount}\nMetode: ${paymentMethod}\nKembalian: ${changeAmount}\n\n(DATA INI PERLU DISIMPAN KE DATABASE!)`);
    
    // Reset dan Tutup
    cart = {};
    updateCart(); // Kosongkan keranjang
    closeModal();
}

// Jalankan saat halaman dimuat
window.onload = loadProducts;