<?php
/**
 * EPIC Hub Member Prospects Page
 * Halaman manajemen prospek untuk member area (EPIC & EPIS only)
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout system
require_once __DIR__ . '/components/page-layout.php';

$user = $user ?? epic_current_user();
$access_level = $access_level ?? epic_get_member_access_level($user);

// Get prospects data (dummy data for now)
$prospects = [
    [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+62 812 3456 7890',
        'status' => 'hot',
        'source' => 'Facebook Ads',
        'last_contact' => '2024-01-15 10:30:00',
        'notes' => 'Tertarik dengan produk digital marketing',
        'created_at' => '2024-01-10 09:00:00'
    ],
    [
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'phone' => '+62 813 9876 5432',
        'status' => 'warm',
        'source' => 'Instagram',
        'last_contact' => '2024-01-14 15:45:00',
        'notes' => 'Membutuhkan informasi lebih lanjut tentang harga',
        'created_at' => '2024-01-08 14:20:00'
    ],
    [
        'id' => 3,
        'name' => 'Bob Wilson',
        'email' => 'bob@example.com',
        'phone' => '+62 814 1111 2222',
        'status' => 'cold',
        'source' => 'Website',
        'last_contact' => '2024-01-12 11:15:00',
        'notes' => 'Belum merespon follow up terakhir',
        'created_at' => '2024-01-05 16:30:00'
    ]
];

// Get statistics
$stats = [
    'total_prospects' => count($prospects),
    'hot_prospects' => count(array_filter($prospects, fn($p) => $p['status'] === 'hot')),
    'warm_prospects' => count(array_filter($prospects, fn($p) => $p['status'] === 'warm')),
    'cold_prospects' => count(array_filter($prospects, fn($p) => $p['status'] === 'cold')),
    'conversion_rate' => '15%'
];
?>

<?php
// Render consistent page header
render_page_header([
    'title' => 'Manajemen Prospek',
    'subtitle' => 'Kelola dan follow up prospek Anda untuk meningkatkan konversi',
    'breadcrumb' => [
        ['text' => 'Dashboard', 'url' => epic_url('dashboard/member')],
        ['text' => 'Prospek']
    ],
    'actions' => [
        [
            'text' => 'Tambah Prospek',
            'url' => '#',
            'class' => 'btn-primary',
            'icon' => 'user-plus',
            'onclick' => 'openAddProspectModal()'
        ]
    ]
]);
?>

<?php
// Render consistent statistics section
$prospect_stats = [
    [
        'title' => 'Total Prospek',
        'value' => $stats['total_prospects'],
        'icon' => 'users',
        'change' => [
            'type' => 'neutral',
            'text' => 'Semua status'
        ]
    ],
    [
        'title' => 'Hot Prospects',
        'value' => $stats['hot_prospects'],
        'icon' => 'trending-up',
        'variant' => 'danger',
        'change' => [
            'type' => 'positive',
            'text' => 'Prioritas tinggi'
        ]
    ],
    [
        'title' => 'Warm Prospects',
        'value' => $stats['warm_prospects'],
        'icon' => 'thermometer',
        'variant' => 'warning',
        'change' => [
            'type' => 'neutral',
            'text' => 'Follow up aktif'
        ]
    ],
    [
        'title' => 'Conversion Rate',
        'value' => $stats['conversion_rate'],
        'icon' => 'target',
        'variant' => 'success',
        'change' => [
            'type' => 'positive',
            'text' => 'Bulan ini'
        ]
    ]
];
render_stats_section($prospect_stats);

// Include content
require_once __DIR__ . '/content/prospects-content.php';
?>

<!-- Legacy content cleanup -->

<!-- Filters and Search -->
<div class="card mb-6">
    <div class="card-body">
        <div class="filters-section">
            <div class="search-box">
                <div class="search-input-wrapper">
                    <i data-feather="search" width="16" height="16" class="search-icon"></i>
                    <input type="text" class="search-input" placeholder="Cari prospek..." id="searchProspects">
                </div>
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-status="all">Semua</button>
                <button class="filter-btn" data-status="hot">Hot</button>
                <button class="filter-btn" data-status="warm">Warm</button>
                <button class="filter-btn" data-status="cold">Cold</button>
            </div>
            
            <div class="sort-dropdown">
                <select class="form-select" id="sortProspects">
                    <option value="created_desc">Terbaru</option>
                    <option value="created_asc">Terlama</option>
                    <option value="name_asc">Nama A-Z</option>
                    <option value="name_desc">Nama Z-A</option>
                    <option value="last_contact_desc">Last Contact</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Prospects Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Prospek</h3>
        <div class="card-actions">
            <button class="btn btn-secondary btn-sm" onclick="exportProspects()">
                <i data-feather="download" width="16" height="16"></i>
                Export
            </button>
        </div>
    </div>
    
    <div class="table-container">
        <table class="table" id="prospectsTable">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th>Sumber</th>
                    <th>Last Contact</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prospects as $prospect): ?>
                    <tr data-status="<?= $prospect['status'] ?>" class="prospect-row">
                        <td>
                            <div class="prospect-info">
                                <div class="prospect-name"><?= htmlspecialchars($prospect['name']) ?></div>
                                <div class="prospect-notes"><?= htmlspecialchars($prospect['notes']) ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div class="contact-email">
                                    <i data-feather="mail" width="14" height="14"></i>
                                    <?= htmlspecialchars($prospect['email']) ?>
                                </div>
                                <div class="contact-phone">
                                    <i data-feather="phone" width="14" height="14"></i>
                                    <?= htmlspecialchars($prospect['phone']) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $prospect['status'] ?>">
                                <?= ucfirst($prospect['status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="source-tag"><?= htmlspecialchars($prospect['source']) ?></span>
                        </td>
                        <td>
                            <div class="last-contact">
                                <div class="contact-date"><?= date('d M Y', strtotime($prospect['last_contact'])) ?></div>
                                <div class="contact-time"><?= date('H:i', strtotime($prospect['last_contact'])) ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" onclick="viewProspect(<?= $prospect['id'] ?>)" title="Lihat Detail">
                                    <i data-feather="eye" width="16" height="16"></i>
                                </button>
                                <button class="btn-icon" onclick="editProspect(<?= $prospect['id'] ?>)" title="Edit">
                                    <i data-feather="edit" width="16" height="16"></i>
                                </button>
                                <button class="btn-icon" onclick="contactProspect(<?= $prospect['id'] ?>)" title="Kontak">
                                    <i data-feather="message-circle" width="16" height="16"></i>
                                </button>
                                <button class="btn-icon danger" onclick="deleteProspect(<?= $prospect['id'] ?>)" title="Hapus">
                                    <i data-feather="trash-2" width="16" height="16"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Prospect Modal -->
<div class="modal" id="addProspectModal" style="display: none;">
    <div class="modal-overlay" onclick="closeAddProspectModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Prospek Baru</h3>
            <button class="modal-close" onclick="closeAddProspectModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form class="modal-body" onsubmit="addProspect(event)">
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" class="form-input" name="name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-input" name="email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="tel" class="form-input" name="phone">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="cold">Cold</option>
                        <option value="warm">Warm</option>
                        <option value="hot">Hot</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sumber</label>
                    <select class="form-select" name="source">
                        <option value="Website">Website</option>
                        <option value="Facebook Ads">Facebook Ads</option>
                        <option value="Instagram">Instagram</option>
                        <option value="Google Ads">Google Ads</option>
                        <option value="Referral">Referral</option>
                        <option value="Other">Lainnya</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea class="form-textarea" name="notes" rows="3" placeholder="Catatan tentang prospek ini..."></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddProspectModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Prospek</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Page Header */
.page-header {
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
}

/* Filters Section */
.filters-section {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
}

.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    width: 100%;
    padding: 0.75rem 0.75rem 0.75rem 2.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
}

.filter-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    background: white;
    color: #374151;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: #f3f4f6;
}

.filter-btn.active {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
}

.sort-dropdown {
    min-width: 150px;
}

/* Prospects Table */
.prospect-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.prospect-name {
    font-weight: 600;
    color: #374151;
}

.prospect-notes {
    font-size: 0.8125rem;
    color: #6b7280;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.contact-email,
.contact-phone {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-hot {
    background: #fee2e2;
    color: #991b1b;
}

.status-warm {
    background: #fef3c7;
    color: #92400e;
}

.status-cold {
    background: #e0e7ff;
    color: #3730a3;
}

.source-tag {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    color: #374151;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.last-contact {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.contact-date {
    font-size: 0.8125rem;
    color: #374151;
    font-weight: 500;
}

.contact-time {
    font-size: 0.75rem;
    color: #6b7280;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.btn-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: #e5e7eb;
    color: #374151;
}

.btn-icon.danger:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    z-index: 1001;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .filters-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-buttons {
        justify-content: center;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .table {
        min-width: 800px;
    }
    
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
    
    .grid.grid-cols-2 {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Prospects management functions
function openAddProspectModal() {
    document.getElementById('addProspectModal').style.display = 'flex';
}

function closeAddProspectModal() {
    document.getElementById('addProspectModal').style.display = 'none';
}

function addProspect(event) {
    event.preventDefault();
    
    // Get form data
    const formData = new FormData(event.target);
    const prospectData = Object.fromEntries(formData);
    
    // Here you would normally send data to server
    console.log('Adding prospect:', prospectData);
    
    // Show success message
    alert('Prospek berhasil ditambahkan!');
    
    // Close modal and reset form
    closeAddProspectModal();
    event.target.reset();
    
    // Refresh table (in real app, you'd reload data from server)
    // location.reload();
}

function viewProspect(id) {
    console.log('Viewing prospect:', id);
    // Implement view prospect functionality
}

function editProspect(id) {
    console.log('Editing prospect:', id);
    // Implement edit prospect functionality
}

function contactProspect(id) {
    console.log('Contacting prospect:', id);
    // Implement contact prospect functionality
}

function deleteProspect(id) {
    if (confirm('Apakah Anda yakin ingin menghapus prospek ini?')) {
        console.log('Deleting prospect:', id);
        // Implement delete prospect functionality
    }
}

function exportProspects() {
    console.log('Exporting prospects');
    // Implement export functionality
}

// Filter and search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchProspects');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const sortSelect = document.getElementById('sortProspects');
    const table = document.getElementById('prospectsTable');
    const rows = table.querySelectorAll('.prospect-row');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Filter functionality
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const status = this.dataset.status;
            
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Sort functionality
    sortSelect.addEventListener('change', function() {
        const sortBy = this.value;
        console.log('Sorting by:', sortBy);
        // Implement sorting logic
    });
});
</script>