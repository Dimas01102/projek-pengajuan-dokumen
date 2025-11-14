/* ========================================
   Berisi fungsi-fungsi helper yang digunakan di semua dashboard
========================================= */

// ============================================
// SWEETALERT2 HELPERS
// ============================================

/**
 * Konfirmasi hapus dengan SweetAlert2
 * @param {string} message - Pesan konfirmasi
 * @returns {Promise} - Promise dari SweetAlert2
 */
function confirmDelete(message) {
    return Swal.fire({
        title: 'Konfirmasi',
        text: message || 'Apakah Anda yakin ingin menghapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });
}

/**
 * Tampilkan loading indicator
 * @param {string} message - Pesan loading (default: "Memproses...")
 */
function showLoading(message = 'Memproses...') {
    Swal.fire({
        title: message,
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Tutup loading indicator
 */
function hideLoading() {
    Swal.close();
}

/**
 * Tampilkan pesan sukses
 * @param {string} message - Pesan sukses
 * @param {number} timer - Durasi tampil (ms), default 2000
 */
function showSuccess(message, timer = 2000) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        timer: timer,
        showConfirmButton: false
    });
}

/**
 * Tampilkan pesan error
 * @param {string} message - Pesan error
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message
    });
}

/**
 * Tampilkan pesan info
 * @param {string} message - Pesan info
 */
function showInfo(message) {
    Swal.fire({
        icon: 'info',
        title: 'Informasi',
        text: message
    });
}

// ============================================
// REAL-TIME CLOCK
// ============================================

/**
 * Update jam real-time di element dengan id="realtime-clock"
 */
function updateClock() {
    const clockElement = document.getElementById('realtime-clock');
    if (!clockElement) return;
    
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    clockElement.textContent = `${hours}:${minutes}:${seconds}`;
}

/**
 * Inisialisasi real-time clock
 * Otomatis berjalan saat DOM ready
 */
function initClock() {
    updateClock(); // Update pertama kali
    setInterval(updateClock, 1000); // Update setiap detik
}

// ============================================
// FILE HANDLING
// ============================================

/**
 * Validasi file upload
 * @param {HTMLInputElement} input - Input file element
 * @param {number} maxSize - Ukuran maksimal dalam bytes (default: 5MB)
 * @param {array} allowedTypes - Array tipe file yang diizinkan (optional)
 * @returns {boolean} - true jika valid
 */
function validateFile(input, maxSize = 5242880, allowedTypes = null) {
    const file = input.files[0];
    
    if (!file) {
        return true; // Tidak ada file dipilih = valid
    }
    
    // Cek ukuran file
    if (file.size > maxSize) {
        const maxSizeMB = (maxSize / 1048576).toFixed(2);
        showError(`Ukuran file terlalu besar. Maksimal ${maxSizeMB} MB`);
        input.value = '';
        return false;
    }
    
    // Cek tipe file jika ada batasan
    if (allowedTypes && allowedTypes.length > 0) {
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExt)) {
            showError(`Tipe file tidak diizinkan. Hanya: ${allowedTypes.join(', ')}`);
            input.value = '';
            return false;
        }
    }
    
    return true;
}

/**
 * Format ukuran file ke format readable (KB, MB)
 * @param {number} bytes - Ukuran dalam bytes
 * @returns {string} - String format readable
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' bytes';
}

/**
 * Preview file (PDF atau Image)
 * @param {string} path - Path file
 * @param {string} type - Tipe file (pdf, jpg, jpeg, png)
 */
function previewFile(path, type) {
    let content = '';
    
    if (type === 'pdf') {
        content = `<iframe src="${path}" width="100%" height="600px" style="border:none;"></iframe>`;
    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(type.toLowerCase())) {
        content = `<img src="${path}" class="img-fluid" alt="Preview" style="max-width:100%;">`;
    } else {
        showError('Tipe file tidak dapat di-preview');
        return;
    }
    
    Swal.fire({
        title: 'Preview Berkas',
        html: content,
        width: '85%',
        showCloseButton: true,
        showConfirmButton: false
    });
}

// ============================================
// FORMAT HELPERS
// ============================================

/**
 * Format angka ke format Rupiah
 * @param {number} angka - Angka yang akan diformat
 * @returns {string} - Format Rupiah
 */
function formatRupiah(angka) {
    if (!angka) return 'Rp 0';
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Format tanggal Indonesia
 * @param {string} dateString - String tanggal
 * @returns {string} - Format Indonesia
 */
function formatTanggalIndonesia(dateString) {
    const bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const date = new Date(dateString);
    const day = date.getDate();
    const month = bulan[date.getMonth()];
    const year = date.getFullYear();
    
    return `${day} ${month} ${year}`;
}

// ============================================
// MODAL HELPERS
// ============================================

/**
 * Tampilkan modal detail pengajuan
 * @param {object} data - Data pengajuan
 */
function viewDetail(data) {
    let keterangan = '';
    
    // Parse keterangan jika dalam format JSON
    try {
        const ket = JSON.parse(data.keterangan);
        keterangan = Object.entries(ket)
            .map(([k, v]) => `<strong>${k.replace(/_/g, ' ')}:</strong> ${v}`)
            .join('<br>');
    } catch (e) {
        keterangan = data.keterangan || '-';
    }

    // Fetch data berkas
    fetch('../includes/get_berkas.php?id_pengajuan=' + data.id_pengajuan)
        .then(response => response.json())
        .then(berkas => {
            let berkasHTML = '<p class="text-muted"><em>Tidak ada berkas diupload</em></p>';
            
            if (berkas.status === 'success' && berkas.data) {
                const b = berkas.data;
                const fileExt = b.tipe_file.toLowerCase();
                
                berkasHTML = `
                    <div class="alert alert-info mb-0">
                        <strong><i class="fas fa-file"></i> ${b.nama_file}</strong><br>
                        <small>Ukuran: ${formatFileSize(b.ukuran_file)} | Tipe: ${b.tipe_file.toUpperCase()}</small><br>
                        <a href="../uploads/${b.path_file}" target="_blank" class="btn btn-sm btn-primary mt-2">
                            <i class="fas fa-download"></i> Download Berkas
                        </a>
                        ${['pdf','jpg','jpeg','png'].includes(fileExt) ? 
                            `<button onclick="previewFile('../uploads/${b.path_file}', '${fileExt}')" class="btn btn-sm btn-success mt-2">
                                <i class="fas fa-eye"></i> Preview
                            </button>` : ''}
                    </div>
                `;
            }

            const htmlContent = `
                <table class="table text-start">
                    <tr><td><strong>Nomor Pengajuan</strong></td><td>${data.nomor_pengajuan}</td></tr>
                    <tr><td><strong>Nama Warga</strong></td><td>${data.nama_warga || data.nama_lengkap || '-'}</td></tr>
                    <tr><td><strong>Jenis Dokumen</strong></td><td>${data.nama_dokumen}</td></tr>
                    <tr><td><strong>Keperluan</strong></td><td>${data.keperluan || '-'}</td></tr>
                    <tr><td><strong>Status</strong></td><td><span class="badge bg-${data.warna_badge}">${data.nama_status}</span></td></tr>
                    <tr><td><strong>Tanggal Pengajuan</strong></td><td>${data.tanggal_pengajuan}</td></tr>
                    <tr><td colspan="2"><hr><strong>Keterangan:</strong><br>${keterangan}</td></tr>
                    ${data.catatan_validasi ? `<tr><td colspan="2"><hr><strong>Catatan Validasi:</strong><br>${data.catatan_validasi}</td></tr>` : ''}
                </table>
                <hr>
                <h6><i class="fas fa-paperclip"></i> Berkas Pendukung:</h6>
                ${berkasHTML}
            `;

            Swal.fire({
                title: 'Detail Pengajuan',
                html: htmlContent,
                width: 700,
                showCloseButton: true,
                confirmButtonText: 'Tutup'
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Gagal memuat detail berkas');
        });
}

// ============================================
// TABLE HELPERS
// ============================================

/**
 * Filter table berdasarkan input search
 * @param {string} inputId - ID input search
 * @param {string} tableId - ID table
 */
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    const filter = input.value.toUpperCase();
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) { // Skip header
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                const txtValue = cell.textContent || cell.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// ============================================
// FORM HELPERS
// ============================================

/**
 * Reset form dengan konfirmasi
 * @param {string} formId - ID form
 */
function resetForm(formId) {
    Swal.fire({
        title: 'Reset Form?',
        text: 'Semua data yang sudah diisi akan hilang',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).reset();
            showSuccess('Form berhasil direset');
        }
    });
}

/**
 * Validasi form sebelum submit
 * @param {HTMLFormElement} form - Form element
 * @returns {boolean} - true jika valid
 */
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        showError('Mohon lengkapi semua field yang wajib diisi');
    }
    
    return isValid;
}

// ============================================
// AUTO DISMISS ALERTS
// ============================================

/**
 * Auto dismiss Bootstrap alerts setelah beberapa detik
 */
function initAutoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// ============================================
// SECTION NAVIGATION
// ============================================

/**
 * Show/Hide content sections
 * @param {string} sectionId - ID section yang akan ditampilkan
 */
function showSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.style.display = 'block';
    }
    
    // Update active menu
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-section') === sectionId) {
            link.classList.add('active');
        }
    });
}

// ============================================
// INITIALIZATION
// ============================================

/**
 * Inisialisasi fungsi-fungsi saat DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Init clock jika ada element
    if (document.getElementById('realtime-clock')) {
        initClock();
    }
    
    // Init auto dismiss alerts
    initAutoDismissAlerts();
    
    // Handle section navigation via URL
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    if (section) {
        showSection(section);
    }
});

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Copy text ke clipboard
 * @param {string} text - Text yang akan dicopy
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('Berhasil disalin ke clipboard', 1000);
    }).catch(() => {
        showError('Gagal menyalin ke clipboard');
    });
}

/**
 * Print element tertentu
 * @param {string} elementId - ID element yang akan diprint
 */
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        showError('Element tidak ditemukan');
        return;
    }
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    .no-print { display: none !important; }
                    body { padding: 20px; }
                }
            </style>
        </head>
        <body>
            ${element.innerHTML}
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                };
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

/**
 * Debounce function untuk optimasi
 * @param {Function} func - Function yang akan di-debounce
 * @param {number} wait - Waktu tunggu dalam ms
 * @returns {Function} - Debounced function
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions untuk module (jika diperlukan)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        confirmDelete,
        showLoading,
        hideLoading,
        showSuccess,
        showError,
        showInfo,
        validateFile,
        formatFileSize,
        previewFile,
        formatRupiah,
        viewDetail,
        updateClock,
        initClock
    };
}