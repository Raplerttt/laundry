document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('editModal');
    const closeModalBtn = document.getElementById('closeModalBtn') || document.getElementById('closeModal');
  
    // Fungsi buka modal dan isi data
    window.openEditModal = (id, berat, hargaOngkir) => {
      document.getElementById('trxId').value = id;
      document.getElementById('berat').value = berat;
      document.getElementById('harga_ongkir').value = hargaOngkir;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    };
  
    // Tutup modal
    const closeModal = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };
  
    if (closeModalBtn) {
      closeModalBtn.addEventListener('click', closeModal);
    }
  
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });
  
    // Pasang event listener ke tombol edit
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const berat = button.getAttribute('data-berat');
        const hargaOngkir = button.getAttribute('data-harga_ongkir');
  
        console.log('Edit modal data:', { id, berat, hargaOngkir });
  
        openEditModal(id, berat, hargaOngkir);
      });
    });
  });
  
  (function() {
    const userEmailInput = document.getElementById('userEmail');
    const templateSelect = document.getElementById('templateSelect');
    const customMessage = document.getElementById('customMessage');
    const sendBtn = document.getElementById('sendNotificationBtn');
    const statusMessage = document.getElementById('statusMessage');

    // When admin picks a template, fill the textarea
    templateSelect.addEventListener('change', () => {
      const templates = {
        promo: 'Diskon 20% untuk pembelian paket bulan ini!',
        reminder: 'Jangan lupa bayar tagihan sebelum tanggal jatuh tempo.',
        thanks: 'Terima kasih telah menggunakan layanan kami.'
      };
      const selected = templateSelect.value;
      customMessage.value = templates[selected] || '';
    });

    sendBtn.addEventListener('click', () => {
      const email = userEmailInput.value.trim();
      const message = customMessage.value.trim();

      if (!email) {
        alert('Please enter user email.');
        return;
      }
      if (!message) {
        alert('Please enter a message.');
        return;
      }

      sendBtn.disabled = true;
      statusMessage.textContent = 'Sending notification...';

      fetch('send_notification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ user_email: email, message: message })
      })
      .then(res => res.json())
      .then(data => {
        statusMessage.style.color = data.success ? 'green' : 'red';
        statusMessage.textContent = data.message || 'Done';
        sendBtn.disabled = false;

        if (data.success) {
          userEmailInput.value = '';
          templateSelect.value = '';
          customMessage.value = '';
        }
      })
      .catch(err => {
        statusMessage.style.color = 'red';
        statusMessage.textContent = 'Failed to send notification.';
        sendBtn.disabled = false;
        console.error(err);
      });
    });
  })();

  async function fetchNotifications() {
    try {
      const res = await fetch('get_notifications.php');
      if (!res.ok) throw new Error('Failed to load notifications.');

      const data = await res.json();
      const container = document.getElementById('notifications-container');

      if (!data.notifications || data.notifications.length === 0) {
        container.innerHTML = `<p class="text-white dark:text-black text-center">No notifications.</p>`;
        return;
      }

      container.innerHTML = ''; // kosongkan dulu

      data.notifications.forEach(notif => {
        const notifDiv = document.createElement('div');
        notifDiv.className = "bg-gray-700 dark:bg-gray-300 p-4 rounded-md flex items-center gap-4";

        notifDiv.innerHTML = `
          <div class="w-20 h-20 flex items-center justify-center rounded-full bg-blue-400 dark:bg-teal-400">
            <i class="fas fa-user-circle text-white text-4xl"></i>
          </div>
          <div class="flex-1 flex flex-col gap-1">
            <div class="text-lg font-semibold font-serif text-white dark:text-black">${notif.user_email}</div>
            <p class="text-sm text-gray-300 dark:text-gray-600">${notif.message}</p>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-700 text-right">${notif.time_ago}</p>
          </div>
        `;

        container.appendChild(notifDiv);
      });
    } catch (err) {
      const container = document.getElementById('notifications-container');
      container.innerHTML = `<p class="text-red-500 text-center">${err.message}</p>`;
    }
  }

  document.addEventListener('DOMContentLoaded', fetchNotifications);

  function laporanKeuanganApp() {
    return {
        startDate: '',
        endDate: '',
        laporanKeuangan: [],

        async fetchLaporan() {
            if (!this.startDate || !this.endDate) {
                alert("Silakan isi kedua tanggal.");
                return;
            }

            try {
                const res = await fetch(`laporan_keuangan.php?start=${this.startDate}&end=${this.endDate}`);
                const data = await res.json();
                this.laporanKeuangan = data;
            } catch (error) {
                console.error("Gagal memuat laporan:", error);
                alert("Gagal mengambil data laporan.");
            }
        },

        formatRupiah(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }
    }
}