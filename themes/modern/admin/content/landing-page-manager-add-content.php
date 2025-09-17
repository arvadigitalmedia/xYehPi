<?php
/**
 * Landing Page Manager Add Content
 * Content untuk halaman tambah landing page
 */

// Variables sudah tersedia dari parent scope
?>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i data-feather="check-circle" width="16" height="16"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i data-feather="x-circle" width="16" height="16"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Landing Page Form -->
<form method="POST" enctype="multipart/form-data" class="landing-form">
    <!-- Basic Information -->
    <div class="form-section">
        <h2 class="form-section-title">Informasi Dasar</h2>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label required">Member</label>
                <select name="user_id" class="form-select" required onchange="updateUrlPreview()">
                    <option value="">Pilih Member</option>
                    <?php foreach ($users as $user_option): ?>
                        <option value="<?= $user_option['id'] ?>" 
                                data-referral="<?= htmlspecialchars($user_option['referral_code']) ?>"
                                <?= (isset($_POST['user_id']) && $_POST['user_id'] == $user_option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user_option['name']) ?> (<?= htmlspecialchars($user_option['referral_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-help">Pilih member yang akan memiliki landing page ini</div>
            </div>
            
            <div class="form-group">
                <label class="form-label required">Judul Landing Page</label>
                <input type="text" name="page_title" class="form-input" 
                       value="<?= htmlspecialchars($_POST['page_title'] ?? '') ?>" 
                       placeholder="Masukkan judul landing page" required>
                <div class="form-help">Judul akan ditampilkan di browser dan hasil pencarian</div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label required">Slug URL</label>
                <input type="text" name="page_slug" class="form-input" 
                       value="<?= htmlspecialchars($_POST['page_slug'] ?? '') ?>" 
                       placeholder="url-friendly-slug" required onchange="updateUrlPreview()">
                <div class="form-help">URL yang akan digunakan untuk mengakses landing page (hanya huruf, angka, dan tanda hubung)</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="page_description" class="form-textarea" 
                          placeholder="Deskripsi singkat tentang landing page"><?= htmlspecialchars($_POST['page_description'] ?? '') ?></textarea>
                <div class="form-help">Deskripsi untuk SEO dan media sosial</div>
            </div>
        </div>
        
        <!-- URL Preview -->
        <div class="form-group">
            <label class="form-label">Preview URL</label>
            <div class="url-preview" id="url-preview">
                <?= epic_url('[username]/[slug]') ?>
            </div>
        </div>
    </div>
    
    <!-- Landing Page Configuration -->
    <div class="form-section">
        <h2 class="form-section-title">Konfigurasi Landing Page</h2>
        
        <div class="form-group">
            <label class="form-label required">URL Landing Page</label>
            <input type="url" name="landing_url" class="form-input" 
                   value="<?= htmlspecialchars($_POST['landing_url'] ?? '') ?>" 
                   placeholder="https://example.com/landing-page" required>
            <div class="form-help">URL lengkap ke landing page yang akan ditampilkan</div>
        </div>
        
        <div class="form-group">
            <label class="form-label required">Metode Implementasi</label>
            <div class="method-options">
                <?php foreach ($method_options as $method_id => $method): ?>
                    <div class="method-option <?= (isset($_POST['method']) && $_POST['method'] == $method_id) ? 'selected' : '' ?>" 
                         onclick="selectMethod(<?= $method_id ?>)">
                        <input type="radio" name="method" value="<?= $method_id ?>" 
                               id="method_<?= $method_id ?>" class="method-radio"
                               <?= (isset($_POST['method']) && $_POST['method'] == $method_id) ? 'checked' : '' ?>
                               onchange="toggleFindReplace()">
                        <div class="method-content">
                            <div class="method-name"><?= htmlspecialchars($method['name']) ?></div>
                            <div class="method-description"><?= htmlspecialchars($method['description']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Find & Replace Section (only for Inject URL method) -->
        <div class="find-replace-section" id="find-replace-section" 
             <?= (isset($_POST['method']) && $_POST['method'] == '2') ? 'style="display: block;"' : '' ?>>
            <h3 class="form-section-title">Find & Replace</h3>
            <div id="find-replace-container">
                <?php
                $find_replace_data = [];
                $find_replace_count = 0;
                
                if (isset($_POST['find_replace']) && is_array($_POST['find_replace'])) {
                    foreach ($_POST['find_replace'] as $index => $item) {
                        if (!empty($item['find']) || !empty($item['replace'])) {
                            $find_replace_data[] = $item;
                            $find_replace_count++;
                            
                            echo '<div class="find-replace-row">';
                            echo '<input type="text" name="find_replace[' . $index . '][find]" placeholder="Teks yang dicari" class="form-input" value="' . htmlspecialchars($item['find']) . '">';
                            echo '<input type="text" name="find_replace[' . $index . '][replace]" placeholder="Teks pengganti" class="form-input" value="' . htmlspecialchars($item['replace']) . '">';
                            echo '<button type="button" class="topbar-btn secondary" onclick="removeFindReplace(this)"><i data-feather="trash-2" width="16" height="16"></i></button>';
                            echo '</div>';
                        }
                    }
                }
                
                // Add default rows if none exist
                if ($find_replace_count === 0) {
                    for ($i = 0; $i < 3; $i++) {
                        echo '<div class="find-replace-row">';
                        echo '<input type="text" name="find_replace[' . $i . '][find]" placeholder="Teks yang dicari" class="form-input">';
                        echo '<input type="text" name="find_replace[' . $i . '][replace]" placeholder="Teks pengganti" class="form-input">';
                        echo '<button type="button" class="topbar-btn secondary" onclick="removeFindReplace(this)"><i data-feather="trash-2" width="16" height="16"></i></button>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            <button type="button" class="topbar-btn secondary" onclick="addFindReplace()">
                <i data-feather="plus" width="16" height="16"></i>
                Tambah Find & Replace
            </button>
            <div class="form-help">Fitur ini memungkinkan Anda mengganti teks tertentu di landing page dengan teks lain. Berguna untuk personalisasi konten.</div>
        </div>
    </div>
    
    <!-- Image Upload -->
    <div class="form-section">
        <h2 class="form-section-title">Gambar Landing Page</h2>
        
        <div class="form-group">
            <label class="form-label">Upload Gambar</label>
            <div class="image-upload">
                <div class="image-preview" id="image-preview" onclick="document.getElementById('page_image').click()">
                    <div class="image-upload-text">
                        <i data-feather="upload" width="32" height="32"></i>
                        <div>Klik untuk upload gambar</div>
                        <small>PNG, JPG, GIF hingga 2MB</small>
                    </div>
                </div>
                <input type="file" name="page_image" id="page_image" 
                       accept="image/*" style="display: none;" onchange="previewImage(this)">
            </div>
            <div class="form-help">Gambar akan digunakan untuk preview di media sosial dan SEO</div>
        </div>
    </div>
    
    <!-- Status -->
    <div class="form-section">
        <h2 class="form-section-title">Status</h2>
        
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" name="is_active" value="1" class="checkbox-input" 
                       <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?>>
                <label class="form-label">Aktifkan Landing Page</label>
            </div>
            <div class="form-help">Landing page akan dapat diakses oleh pengunjung</div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <a href="<?= epic_url('admin/manage/landing-page-manager') ?>" class="topbar-btn secondary">
            <i data-feather="x" width="16" height="16"></i>
            Cancel
        </a>
        <button type="submit" class="topbar-btn">
            <i data-feather="save" width="16" height="16"></i>
            Save Landing Page
        </button>
    </div>
</form>

<script>
    let findReplaceIndex = 10; // Start from 10 to avoid conflicts
    
    // Page-specific functionality
    function initPageFunctionality() {
        // Update URL preview on page load
        updateUrlPreview();
        
        // Auto-generate slug from title
        const titleInput = document.querySelector('input[name="page_title"]');
        if (titleInput) {
            titleInput.addEventListener('input', function() {
                const slugInput = document.querySelector('input[name="page_slug"]');
                if (slugInput && !slugInput.value) {
                    const slug = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim('-');
                    slugInput.value = slug;
                    updateUrlPreview();
                }
            });
        }
    }
    
    function updateUrlPreview() {
        const userSelect = document.querySelector('select[name="user_id"]');
        const slugInput = document.querySelector('input[name="page_slug"]');
        const preview = document.getElementById('url-preview');
        const baseUrl = '<?= epic_url('') ?>';
        
        if (userSelect && slugInput && preview) {
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const username = selectedOption ? selectedOption.getAttribute('data-referral') : '[username]';
            const slug = slugInput.value || '[slug]';
            preview.textContent = baseUrl + username + '/' + slug;
        }
    }
    
    function selectMethod(methodId) {
        // Update radio button
        document.getElementById('method_' + methodId).checked = true;
        
        // Update visual selection
        document.querySelectorAll('.method-option').forEach(option => {
            option.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        
        // Toggle find & replace section
        toggleFindReplace();
    }
    
    function toggleFindReplace() {
        const selectedMethod = document.querySelector('input[name="method"]:checked');
        const findReplaceSection = document.getElementById('find-replace-section');
        
        if (selectedMethod && selectedMethod.value === '2') {
            findReplaceSection.style.display = 'block';
        } else {
            findReplaceSection.style.display = 'none';
        }
    }
    
    function addFindReplace() {
        const container = document.getElementById('find-replace-container');
        const newRow = document.createElement('div');
        newRow.className = 'find-replace-row';
        newRow.innerHTML = `
            <input type="text" name="find_replace[${findReplaceIndex}][find]" placeholder="Teks yang dicari" class="form-input">
            <input type="text" name="find_replace[${findReplaceIndex}][replace]" placeholder="Teks pengganti" class="form-input">
            <button type="button" class="topbar-btn secondary" onclick="removeFindReplace(this)"><i data-feather="trash-2" width="16" height="16"></i></button>
        `;
        container.appendChild(newRow);
        findReplaceIndex++;
        
        // Re-initialize feather icons
        feather.replace();
    }
    
    function removeFindReplace(button) {
        button.closest('.find-replace-row').remove();
    }
    
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>