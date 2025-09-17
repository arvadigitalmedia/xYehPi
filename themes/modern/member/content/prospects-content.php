<?php
/**
 * EPIC Hub Member Prospects Content
 * Konten halaman manajemen prospek member
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Data sudah disiapkan di prospects.php
?>

<!-- Prospects Management Section -->
<div class="prospects-management-section">
    <!-- Filter and Search -->
    <div class="prospects-filter-section">
        <div class="filter-search-container">
            <div class="search-box">
                <div class="search-input-container">
                    <i data-feather="search" width="18" height="18"></i>
                    <input type="text" class="search-input" placeholder="Cari berdasarkan nama, email, atau telepon..." id="prospectSearch">
                </div>
            </div>
            
            <div class="filter-controls">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">Semua</button>
                    <button class="filter-tab" data-filter="hot">Hot</button>
                    <button class="filter-tab" data-filter="warm">Warm</button>
                    <button class="filter-tab" data-filter="cold">Cold</button>
                </div>
                
                <div class="sort-dropdown">
                    <select class="sort-select" id="prospectSort">
                        <option value="recent">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="name_asc">Nama A-Z</option>
                        <option value="name_desc">Nama Z-A</option>
                        <option value="last_contact">Kontak Terakhir</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Prospects List -->
    <div class="prospects-list-section">
        <?php if (!empty($prospects)): ?>
            <div class="prospects-grid" id="prospectsContainer">
                <?php foreach ($prospects as $prospect): ?>
                    <div class="prospect-card" data-status="<?= $prospect['status'] ?>" data-search="<?= strtolower($prospect['name'] . ' ' . $prospect['email'] . ' ' . $prospect['phone']) ?>">
                        <div class="prospect-card-header">
                            <div class="prospect-avatar">
                                <div class="avatar-placeholder">
                                    <?= strtoupper(substr($prospect['name'], 0, 2)) ?>
                                </div>
                            </div>
                            
                            <div class="prospect-info">
                                <h4 class="prospect-name"><?= htmlspecialchars($prospect['name']) ?></h4>
                                <div class="prospect-contact">
                                    <div class="contact-item">
                                        <i data-feather="mail" width="14" height="14"></i>
                                        <span><?= htmlspecialchars($prospect['email']) ?></span>
                                    </div>
                                    <div class="contact-item">
                                        <i data-feather="phone" width="14" height="14"></i>
                                        <span><?= htmlspecialchars($prospect['phone']) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="prospect-status">
                                <span class="status-badge status-<?= $prospect['status'] ?>">
                                    <?php 
                                    $status_text = [
                                        'hot' => 'Hot',
                                        'warm' => 'Warm',
                                        'cold' => 'Cold'
                                    ];
                                    echo $status_text[$prospect['status']] ?? ucfirst($prospect['status']);
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="prospect-card-body">
                            <div class="prospect-meta">
                                <div class="meta-item">
                                    <i data-feather="calendar" width="14" height="14"></i>
                                    <span>Ditambahkan: <?= date('d M Y', strtotime($prospect['created_at'])) ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <i data-feather="clock" width="14" height="14"></i>
                                    <span>Kontak terakhir: <?= date('d M Y', strtotime($prospect['last_contact'])) ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <i data-feather="tag" width="14" height="14"></i>
                                    <span>Sumber: <?= htmlspecialchars($prospect['source']) ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($prospect['notes'])): ?>
                                <div class="prospect-notes">
                                    <div class="notes-header">
                                        <i data-feather="file-text" width="14" height="14"></i>
                                        <span>Catatan:</span>
                                    </div>
                                    <p class="notes-content"><?= htmlspecialchars($prospect['notes']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="prospect-card-footer">
                            <div class="prospect-actions">
                                <button class="action-btn action-btn-primary" onclick="contactProspect(<?= $prospect['id'] ?>)" title="Hubungi">
                                    <i data-feather="phone" width="16" height="16"></i>
                                </button>
                                
                                <button class="action-btn action-btn-secondary" onclick="emailProspect(<?= $prospect['id'] ?>)" title="Email">
                                    <i data-feather="mail" width="16" height="16"></i>
                                </button>
                                
                                <button class="action-btn action-btn-info" onclick="editProspect(<?= $prospect['id'] ?>)" title="Edit">
                                    <i data-feather="edit" width="16" height="16"></i>
                                </button>
                                
                                <div class="action-dropdown">
                                    <button class="action-btn action-btn-secondary dropdown-toggle" onclick="toggleProspectMenu(<?= $prospect['id'] ?>)">
                                        <i data-feather="more-horizontal" width="16" height="16"></i>
                                    </button>
                                    
                                    <div class="dropdown-menu" id="prospectMenu<?= $prospect['id'] ?>">
                                        <button class="dropdown-item" onclick="changeStatus(<?= $prospect['id'] ?>, 'hot')">
                                            <i data-feather="trending-up" width="14" height="14"></i>
                                            Mark as Hot
                                        </button>
                                        <button class="dropdown-item" onclick="changeStatus(<?= $prospect['id'] ?>, 'warm')">
                                            <i data-feather="minus" width="14" height="14"></i>
                                            Mark as Warm
                                        </button>
                                        <button class="dropdown-item" onclick="changeStatus(<?= $prospect['id'] ?>, 'cold')">
                                            <i data-feather="trending-down" width="14" height="14"></i>
                                            Mark as Cold
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item text-danger" onclick="deleteProspect(<?= $prospect['id'] ?>)">
                                            <i data-feather="trash-2" width="14" height="14"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="prospects-empty-state">
                <div class="empty-state-icon">
                    <i data-feather="users" width="64" height="64"></i>
                </div>
                <div class="empty-state-content">
                    <h3 class="empty-state-title">Belum Ada Prospek</h3>
                    <p class="empty-state-text">
                        Mulai tambahkan prospek untuk mengelola dan meningkatkan konversi penjualan Anda.
                    </p>
                    <div class="empty-state-actions">
                        <button class="btn btn-primary" onclick="openAddProspectModal()">
                            <i data-feather="user-plus" width="16" height="16"></i>
                            Tambah Prospek Pertama
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Prospect Modal -->
<div id="prospectModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeProspectModal()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="prospectModalTitle">Tambah Prospek</h3>
            <button class="modal-close" onclick="closeProspectModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form class="modal-body" id="prospectForm" method="POST" action="<?= epic_url('dashboard/member/prospects/save') ?>">
            <input type="hidden" name="prospect_id" id="prospectId">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap</label>
                    <input type="text" name="name" id="prospectName" class="form-input" 
                           placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" id="prospectEmail" class="form-input" 
                           placeholder="email@example.com" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="tel" name="phone" id="prospectPhone" class="form-input" 
                           placeholder="+62 812 3456 7890">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="prospectStatus" class="form-select">
                        <option value="cold">Cold</option>
                        <option value="warm">Warm</option>
                        <option value="hot">Hot</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Sumber</label>
                <input type="text" name="source" id="prospectSource" class="form-input" 
                       placeholder="Facebook Ads, Instagram, Website, dll.">
            </div>
            
            <div class="form-group">
                <label class="form-label">Catatan</label>
                <textarea name="notes" id="prospectNotes" class="form-textarea" rows="4" 
                          placeholder="Tambahkan catatan tentang prospek ini..."></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProspectModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Simpan Prospek
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Prospect management functions
let currentProspects = <?= json_encode($prospects) ?>;

// Modal functions
function openAddProspectModal() {
    document.getElementById('prospectModalTitle').textContent = 'Tambah Prospek';
    document.getElementById('prospectForm').reset();
    document.getElementById('prospectId').value = '';
    document.getElementById('prospectModal').style.display = 'flex';
}

function closeProspectModal() {
    document.getElementById('prospectModal').style.display = 'none';
}

function editProspect(id) {
    const prospect = currentProspects.find(p => p.id == id);
    if (!prospect) return;
    
    document.getElementById('prospectModalTitle').textContent = 'Edit Prospek';
    document.getElementById('prospectId').value = prospect.id;
    document.getElementById('prospectName').value = prospect.name;
    document.getElementById('prospectEmail').value = prospect.email;
    document.getElementById('prospectPhone').value = prospect.phone;
    document.getElementById('prospectStatus').value = prospect.status;
    document.getElementById('prospectSource').value = prospect.source;
    document.getElementById('prospectNotes').value = prospect.notes;
    
    document.getElementById('prospectModal').style.display = 'flex';
}

// Action functions
function contactProspect(id) {
    const prospect = currentProspects.find(p => p.id == id);
    if (prospect && prospect.phone) {
        window.open(`tel:${prospect.phone}`);
    }
}

function emailProspect(id) {
    const prospect = currentProspects.find(p => p.id == id);
    if (prospect && prospect.email) {
        window.open(`mailto:${prospect.email}`);
    }
}

function changeStatus(id, newStatus) {
    // Send AJAX request to update status
    fetch('<?= epic_url('dashboard/member/prospects/update-status') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast('Gagal mengubah status', 'error');
        }
    })
    .catch(error => {
        showToast('Terjadi kesalahan', 'error');
    });
}

function deleteProspect(id) {
    if (confirm('Apakah Anda yakin ingin menghapus prospek ini?')) {
        // Send AJAX request to delete
        fetch('<?= epic_url('dashboard/member/prospects/delete') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast('Gagal menghapus prospek', 'error');
            }
        })
        .catch(error => {
            showToast('Terjadi kesalahan', 'error');
        });
    }
}

function toggleProspectMenu(id) {
    const menu = document.getElementById(`prospectMenu${id}`);
    const allMenus = document.querySelectorAll('.dropdown-menu');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    
    // Toggle current menu
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('prospectSearch');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const sortSelect = document.getElementById('prospectSort');
    const prospectCards = document.querySelectorAll('.prospect-card');
    
    let currentFilter = 'all';
    let currentSort = 'recent';
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterAndSort();
        });
    }
    
    // Filter functionality
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            currentFilter = this.dataset.filter;
            
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            filterAndSort();
        });
    });
    
    // Sort functionality
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentSort = this.value;
            filterAndSort();
        });
    }
    
    function filterAndSort() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        let visibleCards = [];
        
        prospectCards.forEach(card => {
            const status = card.dataset.status;
            const searchData = card.dataset.search;
            
            // Apply filter
            const matchesFilter = currentFilter === 'all' || status === currentFilter;
            const matchesSearch = !searchTerm || searchData.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                card.style.display = 'block';
                visibleCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });
        
        // Apply sort
        const container = document.getElementById('prospectsContainer');
        if (container && visibleCards.length > 0) {
            visibleCards.sort((a, b) => {
                const aName = a.querySelector('.prospect-name').textContent;
                const bName = b.querySelector('.prospect-name').textContent;
                
                switch (currentSort) {
                    case 'name_asc':
                        return aName.localeCompare(bName);
                    case 'name_desc':
                        return bName.localeCompare(aName);
                    case 'oldest':
                        // Implement based on created_at data
                        return 0;
                    case 'last_contact':
                        // Implement based on last_contact data
                        return 0;
                    default: // recent
                        return 0;
                }
            });
            
            // Reorder DOM elements
            visibleCards.forEach(card => {
                container.appendChild(card);
            });
        }
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    // Implementation depends on your toast system
    console.log(`${type.toUpperCase()}: ${message}`);
}
</script>