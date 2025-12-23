/**
 * Admin Dashboard JavaScript
 * Handles all client-side interactions for admin panel
 */

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Show success notification
 */
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

/**
 * Show error notification
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

/**
 * Confirm delete action
 */
function confirmDelete(message) {
    return Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });
}

/**
 * View detail pengajuan
 */
function viewDetail(data) {
    Swal.fire({
        title: 'Detail Pengajuan',
        html: `
            <table class="table text-start">
                <tr><td><strong>No. Pengajuan</strong></td><td>${data.nomor_pengajuan}</td></tr>
                <tr><td><strong>Nama Warga</strong></td><td>${data.nama_warga}</td></tr>
                <tr><td><strong>Jenis Dokumen</strong></td><td>${data.nama_dokumen}</td></tr>
                <tr><td><strong>Tanggal</strong></td><td>${data.tanggal_pengajuan}</td></tr>
                <tr><td><strong>Status</strong></td><td>${data.nama_status}</td></tr>
            </table>
        `,
        width: 600,
        showCloseButton: true
    });
}

// ============================================
// PETUGAS MANAGEMENT
// ============================================

/**
 * Edit petugas modal
 */
function editPetugas(data) {
    const fields = ['id_pengguna', 'username', 'nama_lengkap', 'nik', 'email', 'no_hp'];
    fields.forEach(field => {
        const element = document.getElementById('edit_' + field);
        if (element) {
            element.value = data[field] || '';
        }
    });
    
    const modal = new bootstrap.Modal(document.getElementById('editPetugasModal'));
    modal.show();
}

/**
 * Reset password modal
 */
function resetPassword(id, nama) {
    document.getElementById('reset_id_pengguna').value = id;
    document.getElementById('reset_nama').textContent = nama;
    
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}

/**
 * Delete petugas
 */
function hapusPetugas(id, nama) {
    confirmDelete('Yakin ingin menghapus petugas ' + nama + '? Data yang sudah terhapus tidak dapat dikembalikan.')
        .then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="id_pengguna" value="${id}">
                    <input type="hidden" name="delete_petugas" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
}

// ============================================
// WARGA MANAGEMENT
// ============================================

/**
 * View warga detail
 */
function viewWargaDetail(data) {
    Swal.fire({
        title: 'Detail Warga',
        html: `
            <table class="table text-start">
                <tr><td><strong>Username</strong></td><td>${data.username}</td></tr>
                <tr><td><strong>Nama Lengkap</strong></td><td>${data.nama_lengkap}</td></tr>
                <tr><td><strong>NIK</strong></td><td>${data.nik || '-'}</td></tr>
                <tr><td><strong>Email</strong></td><td>${data.email || '-'}</td></tr>
                <tr><td><strong>No. HP</strong></td><td>${data.no_hp || '-'}</td></tr>
                <tr><td><strong>Tanggal Daftar</strong></td><td>${data.tanggal_daftar}</td></tr>
            </table>
        `,
        width: 600,
        showCloseButton: true
    });
}

/**
 * Delete warga
 */
function hapusWarga(id, nama) {
    confirmDelete('Yakin ingin menghapus warga ' + nama + '? Data yang sudah terhapus tidak dapat dikembalikan.')
        .then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="id_pengguna" value="${id}">
                    <input type="hidden" name="delete_warga" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
}

// ============================================
// JENIS DOKUMEN MANAGEMENT
// ============================================

let fieldCounter = 0;

/**
 * Update upload fields based on selected quantity
 */
function updateUploadFields() {
    const jumlah = parseInt(document.getElementById('jumlah_upload').value);
    const container = document.getElementById('uploadFieldsContainer');

    let html = '<div class="row mt-3">';
    for (let i = 1; i <= jumlah; i++) {
        html += `
            <div class="col-md-6 mb-3">
                <label class="form-label">Label untuk Upload ${i} *</label>
                <input type="text" 
                       name="upload_labels[]" 
                       id="upload_label_${i}"
                       class="form-control form-control-sm" 
                       placeholder="Contoh: KTP, KK, Surat Pengantar RT"
                       required>
                <small class="text-muted">Label ini akan ditampilkan di form warga</small>
            </div>
        `;
    }
    html += '</div>';

    container.innerHTML = html;
}

/**
 * Open add jenis dokumen modal
 */
function openAddJenisModal() {
    document.getElementById('jenisModalTitle').textContent = 'Tambah Jenis Dokumen';
    document.getElementById('formJenis').reset();
    document.getElementById('jenis_id').value = '';
    document.getElementById('btnSubmitJenis').name = 'add_jenis';
    document.getElementById('fieldContainer').innerHTML = '';
    document.getElementById('jumlah_upload').value = '1';
    fieldCounter = 0;

    // Initialize upload fields
    updateUploadFields();

    // Add default field row
    addFieldRow();

    const modal = new bootstrap.Modal(document.getElementById('jenisModal'));
    modal.show();
}

/**
 * Edit jenis dokumen
 */
function editJenis(data) {
    document.getElementById('jenisModalTitle').textContent = 'Edit Jenis Dokumen';
    document.getElementById('jenis_id').value = data.id;
    document.getElementById('jenis_nama').value = data.nama;
    document.getElementById('jenis_deskripsi').value = data.data.deskripsi || '';
    document.getElementById('btnSubmitJenis').name = 'edit_jenis';

    // Set jumlah upload dan label
    const uploadConfig = data.data.upload_config || {
        jumlah: 1,
        labels: ['Dokumen Pendukung']
    };
    document.getElementById('jumlah_upload').value = uploadConfig.jumlah;
    updateUploadFields();

    // Use setTimeout to ensure fields are rendered
    setTimeout(() => {
        uploadConfig.labels.forEach((label, index) => {
            const labelInput = document.getElementById('upload_label_' + (index + 1));
            if (labelInput) {
                labelInput.value = label;
            }
        });
    }, 100);

    // Populate field config
    document.getElementById('fieldContainer').innerHTML = '';
    fieldCounter = 0;

    if (data.data.field_config && data.data.field_config.length > 0) {
        data.data.field_config.forEach(field => addFieldRow(field));
    } else {
        addFieldRow();
    }

    const modal = new bootstrap.Modal(document.getElementById('jenisModal'));
    modal.show();
}

/**
 * Add field row for form configuration
 */
function addFieldRow(fieldData = null) {
    fieldCounter++;
    const container = document.getElementById('fieldContainer');

    const fieldHtml = `
        <div class="field-row" id="field_${fieldCounter}" style="background: #f8f9fa; padding: 15px; margin-bottom: 10px; border-radius: 8px; border-left: 4px solid #007bff;">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label small">Label Field *</label>
                    <input type="text" name="field_labels[]" class="form-control form-control-sm" 
                           value="${fieldData ? fieldData.label : ''}" 
                           placeholder="Contoh: Nama Lengkap" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tipe Input *</label>
                    <select name="field_types[]" class="form-select form-select-sm" 
                            onchange="handleFieldTypeChange(this, ${fieldCounter})" required>
                        <option value="text" ${fieldData && fieldData.type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="textarea" ${fieldData && fieldData.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="number" ${fieldData && fieldData.type === 'number' ? 'selected' : ''}>Number</option>
                        <option value="date" ${fieldData && fieldData.type === 'date' ? 'selected' : ''}>Date</option>
                        <option value="email" ${fieldData && fieldData.type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="tel" ${fieldData && fieldData.type === 'tel' ? 'selected' : ''}>Telepon</option>
                        <option value="select" ${fieldData && fieldData.type === 'select' ? 'selected' : ''}>Select</option>
                        <option value="radio" ${fieldData && fieldData.type === 'radio' ? 'selected' : ''}>Radio</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Placeholder</label>
                    <input type="text" name="field_placeholders[]" class="form-control form-control-sm" 
                           value="${fieldData ? fieldData.placeholder : ''}" placeholder="Petunjuk">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Options (koma)</label>
                    <input type="text" name="field_options[]" 
                           class="form-control form-control-sm options-field-${fieldCounter}" 
                           value="${fieldData && fieldData.options ? fieldData.options.join(', ') : ''}" 
                           placeholder="A, B, C"
                           style="display: ${fieldData && (fieldData.type === 'select' || fieldData.type === 'radio') ? 'block' : 'none'}">
                </div>
                <div class="col-md-1 text-center">
                    <label class="form-label small d-block">Wajib?</label>
                    <input type="checkbox" name="field_required[]" class="form-check-input" 
                           ${fieldData && fieldData.required ? 'checked' : ''}>
                </div>
                <div class="col-md-1 text-center">
                    <label class="form-label small d-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFieldRow(${fieldCounter})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
}

/**
 * Remove field row
 */
function removeFieldRow(id) {
    const element = document.getElementById('field_' + id);
    if (element) {
        element.remove();
    }
}

/**
 * Handle field type change to show/hide options
 */
function handleFieldTypeChange(select, fieldId) {
    const optionsField = document.querySelector('.options-field-' + fieldId);
    if (select.value === 'select' || select.value === 'radio') {
        optionsField.style.display = 'block';
    } else {
        optionsField.style.display = 'none';
        optionsField.value = '';
    }
}

/**
 * Toggle jenis dokumen status
 */
function toggleJenisStatus(id, currentStatus, nama) {
    const newStatus = currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
    const statusText = newStatus === 'aktif' ? 'mengaktifkan' : 'menonaktifkan';

    Swal.fire({
        title: 'Konfirmasi',
        html: 'Yakin ingin ' + statusText + ' jenis dokumen <strong>' + nama + '</strong>?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="id_jenis" value="${id}">
                <input type="hidden" name="status" value="${currentStatus}">
                <input type="hidden" name="toggle_jenis_status" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/**
 * Delete jenis dokumen
 */
function hapusJenis(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: 'Yakin ingin menghapus jenis dokumen <strong>' + nama + '</strong>?<br><small class="text-danger">Data yang sudah terhapus tidak dapat dikembalikan!</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="id_jenis" value="${id}">
                <input type="hidden" name="delete_jenis" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// ============================================
// CHART INITIALIZATION
// ============================================

/**
 * Initialize charts with data from server
 */
function initializeCharts(chartData) {
    // Pie Chart - Status Pengajuan
    const ctxPie = document.getElementById('chartStatusPie');
    if (ctxPie) {
        new Chart(ctxPie.getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'Disetujui', 'Ditolak'],
                datasets: [{
                    data: [
                        chartData.stats.pending,
                        chartData.stats.disetujui,
                        chartData.stats.ditolak
                    ],
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Bar Chart - Jenis Dokumen
    const ctxBar = document.getElementById('chartJenisBar');
    if (ctxBar) {
        new Chart(ctxBar.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Jumlah Pengajuan',
                    data: chartData.data,
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// ============================================
// INITIALIZATION
// ============================================

/**
 * Initialize admin dashboard
 */
function initAdminDashboard() {
    // Initialize upload fields
    updateUploadFields();

    // Handle logout confirmation
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('logout')) {
        confirmLogout('Yakin ingin logout?').then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../includes/logout.php';
            }
        });
    }

    // Initialize charts if data is available
    if (typeof window.adminChartData !== 'undefined') {
        initializeCharts(window.adminChartData);
    }

    // Handle flash messages
    if (typeof window.flashMessage !== 'undefined' && window.flashMessage.message) {
        if (window.flashMessage.type === 'success') {
            showSuccess(window.flashMessage.message);
        } else {
            showError(window.flashMessage.message);
        }
    }
}

// Run initialization DOM 
document.addEventListener('DOMContentLoaded', initAdminDashboard);