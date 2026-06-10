<script>
    // ========================
    // = Permission Management =
    // ========================

    // Global variables
    let allPermissions = [];
    let originalSelections = [];

    // ===========================
    // = Initialize Page =
    // ===========================
    document.addEventListener('DOMContentLoaded', function() {
        initializePage();
    });

    function initializePage() {
        // Get role ID from URL
        const roleId = getRoleIdFromUrl();
        if (roleId) {
            loadRolePermissions(roleId);
        } else {
            showError('Role ID tidak ditemukan dalam URL.');
        }

        initializeEventListeners();
        initializeSearch();
    }

    // ===========================
    // = Get Role ID from URL =
    // ===========================
    function getRoleIdFromUrl() {
        const urlSegments = window.location.pathname.split('/');
        return urlSegments[urlSegments.length - 1];
    }

    // ===========================
    // = Initialize Event Listeners =
    // ===========================
    function initializeEventListeners() {
        // Form submission
        const form = document.getElementById('permissionForm');
        if (form) form.addEventListener('submit', handleFormSubmit);

        // Reset buttons
        const btnResetSel = document.getElementById('resetSelection');
        if (btnResetSel) btnResetSel.addEventListener('click', resetSelection);

        const btnResetForm = document.getElementById('resetForm');
        if (btnResetForm) btnResetForm.addEventListener('click', resetForm);

        // Search input
        const searchInput = document.getElementById('searchPermissions');
        if (searchInput) searchInput.addEventListener('input', handleSearch);

        // Delegated event listeners for dynamic content
        document.addEventListener('change', handleDelegatedEvents);
    }

    // ===========================
    // = Initialize Search =
    // ===========================
    function initializeSearch() {
        // Will be used for search functionality
    }

    // ===========================
    // = Delegated Event Handlers =
    // ===========================
    function handleDelegatedEvents(e) {
        // Group Select All functionality
        if (e.target.classList.contains('check-all-group')) {
            const group = e.target.getAttribute('data-group');
            document.querySelectorAll(`.permission-group-${group}`).forEach(cb => {
                cb.checked = e.target.checked;
            });
            updateUIStates();
            return;
        }

        // Module Select All functionality
        if (e.target.classList.contains('check-all-module')) {
            const module = e.target.getAttribute('data-module');
            document.querySelectorAll(`.permission-module-${module}`).forEach(cb => {
                cb.checked = e.target.checked;
            });
            updateUIStates();
        }

        // Individual permission checkbox change
        if (e.target.name === 'permissions[]') {
            updateUIStates();
        }
    }

    // ===========================
    // = Handle Form Submission =
    // ===========================
    function handleFormSubmit(e) {
        e.preventDefault();

        const roleId = document.getElementById('permissionForm').getAttribute('data-id');
        const selectedPermissions = getSelectedPermissions();

        if (!roleId) {
            ResponseHandler.handleError("Role ID tidak ditemukan.");
            return;
        }

        saveRolePermissions(roleId, selectedPermissions);
    }

    // ===========================
    // = Get Selected Permissions =
    // ===========================
    function getSelectedPermissions() {
        const selectedPermissions = [];
        const checkboxes = document.querySelectorAll(
            '#permissions_list input[type="checkbox"][name="permissions[]"]:checked'
        );

        checkboxes.forEach(checkbox => {
            selectedPermissions.push(checkbox.value);
        });

        return selectedPermissions;
    }

    // ===========================
    // = Save Role Permissions =
    // ===========================
    function saveRolePermissions(roleId, permissions) {
        const url = '{{ route('administrator.rbac.role.store-role-permission', ':id') }}'.replace(':id', roleId);
        const saveButton = document.getElementById('savePermissions');
        const originalText = saveButton.innerHTML;

        // Show loading state
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

        let formData = new FormData();
        permissions.forEach(permission => formData.append('permissions[]', permission));
        formData.append('_token', '{{ csrf_token() }}');

        AjaxHandler.sendRequest(
            url,
            'POST',
            formData,
            function(response) {
                ResponseHandler.handleSuccess("Role permissions berhasil disimpan.");

                // Update original selections
                originalSelections = permissions;
                updateUIStates();

                // Restore button
                saveButton.disabled = false;
                saveButton.innerHTML = originalText;
            },
            function(xhr) {
                let errors = xhr.responseJSON?.data || {};
                ResponseHandler.handleValidationErrors(errors, null);

                // Restore button
                saveButton.disabled = false;
                saveButton.innerHTML = originalText;
            }
        );
    }

    // ===========================
    // = Load Role Permissions =
    // ===========================
    function loadRolePermissions(roleId) {
        const url = '{{ route('administrator.rbac.role.list-role-permission', ':id') }}'.replace(':id', roleId);

        showLoadingState(true);

        AjaxHandler.sendGetRequest(url, function(response) {
            if (response.status === 200 && response.data) {
                const {
                    role,
                    permissions,
                    role_permissions
                } = response.data;

                // Store permissions globally
                allPermissions = permissions;
                originalSelections = role_permissions;

                // Display role information
                document.getElementById('permissionForm').setAttribute('data-id', roleId);
                
                const roleScopeEl = document.getElementById('detail_p_role_scope');
                if (roleScopeEl) roleScopeEl.value = role.role_scope || 'N/A';

                const roleNameEl = document.getElementById('detail_p_role_name');
                if (roleNameEl) roleNameEl.value = role.role_name || 'N/A';
                
                const roleDescEl = document.getElementById('detail_p_role_description');
                if (roleDescEl) roleDescEl.value = role.role_description || 'N/A';

                // Create structured permission list
                createStructuredPermissionList(permissions, role_permissions);

                // Show main content
                showMainContent();
            } else {
                showError("Data tidak ditemukan.");
            }

            showLoadingState(false);
        }, function(error) {
            showError("Gagal memuat data permissions.");
            showLoadingState(false);
        });
    }

    // ===========================
    // = Show/Hide States =
    // ===========================
    function showLoadingState(show) {
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (show) {
            loadingIndicator.classList.remove('d-none');
        } else {
            loadingIndicator.classList.add('d-none');
        }
    }

    function showMainContent() {
        document.getElementById('mainContent').classList.remove('d-none');
    }

    function showError(message) {
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        errorText.textContent = message;
        errorMessage.classList.remove('d-none');
    }

    // ===========================
    // = Create Structured Permission List =
    // ===========================
    function createStructuredPermissionList(permissions, rolePermissions) {
        const permissionsList = document.getElementById('permissions_list');
        permissionsList.innerHTML = '';
        
        // Remove accordion styling classes
        permissionsList.className = 'p-0'; 

        // permissions is now already a grouped object from backend
        // e.g. {"Master": {"Kecamatan": [{id: 1, name: "View", slug: "master.kecamatan.view"}]}}
        
        Object.keys(permissions).forEach(modul => {
            createModuleSection(modul, permissions[modul], permissionsList, rolePermissions);
        });

        // Update UI states
        updateUIStates();
    }

    // ===========================
    // = Create Module Section =
    // ===========================
    function createModuleSection(modul, submodules, container, rolePermissions) {
        const modulKey = modul.replace(/[^a-zA-Z0-9]/g, '-');
        
        const moduleContainer = document.createElement('div');
        moduleContainer.className = 'mb-4 module-card';

        moduleContainer.innerHTML = `
            <div class="bg-light-primary text-primary px-3 py-2 rounded-3 mb-3 fw-bold d-flex align-items-center justify-content-between">
                <div>Modul: ${modul}</div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input check-all-group" type="checkbox" 
                        data-group="${modulKey}" id="checkAll-${modulKey}">
                    <label class="form-check-label small" for="checkAll-${modulKey}">Pilih Semua</label>
                </div>
            </div>
            <div class="row g-3" id="subcategories-${modulKey}">
                <!-- Submodules will be injected here -->
            </div>
        `;

        const submodulesContainer = moduleContainer.querySelector(`#subcategories-${modulKey}`);

        Object.keys(submodules).forEach(submodul => {
            createSubmoduleSection(modulKey, submodul, submodules[submodul], submodulesContainer, rolePermissions);
        });

        container.appendChild(moduleContainer);
    }

    // ===========================
    // = Create Submodule Section =
    // ===========================
    function createSubmoduleSection(modulKey, submodul, actions, container, rolePermissions) {
        const submodulKey = `${modulKey}-${submodul.replace(/[^a-zA-Z0-9]/g, '-')}`;

        const colDiv = document.createElement('div');
        colDiv.className = 'col-12 col-md-6 col-lg-4';

        colDiv.innerHTML = `
            <div class="p-3 border rounded-3 bg-white h-100 shadow-sm submodule-card transition-all">
                <div class="fw-bold mb-3 border-bottom pb-2 small text-dark d-flex align-items-center justify-content-between">
                    <div class="text-capitalize"><i class="fas fa-layer-group me-2 text-muted"></i>${submodul}</div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input check-all-module" type="checkbox" 
                               data-module="${submodulKey}" id="checkAll-${submodulKey}">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-3" id="actions-${submodulKey}">
                    <!-- Action checkboxes will be injected here -->
                </div>
            </div>
        `;

        const actionsContainer = colDiv.querySelector(`#actions-${submodulKey}`);

        if (Array.isArray(actions)) {
            actions.forEach(actionObj => {
                const checkbox = createActionCheckbox(actionObj, modulKey, submodulKey, rolePermissions);
                actionsContainer.appendChild(checkbox);
            });
        }

        container.appendChild(colDiv);
    }

    // ===========================
    // = Create Action Checkbox =
    // ===========================
    function createActionCheckbox(actionObj, modulKey, submodulKey, rolePermissions) {
        const checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'form-check me-3 mb-1';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'permissions[]';
        checkbox.className = `form-check-input permission-group-${modulKey} permission-module-${submodulKey}`;
        checkbox.id = `check-${actionObj.id}`;
        checkbox.value = actionObj.id;

        if (rolePermissions.includes(actionObj.id)) {
            checkbox.checked = true;
        }

        const label = document.createElement('label');
        label.className = 'form-check-label small text-dark fw-medium';
        label.setAttribute('for', checkbox.id);

        label.textContent = actionObj.name;
        label.title = actionObj.slug;

        checkboxDiv.appendChild(checkbox);
        checkboxDiv.appendChild(label);

        return checkboxDiv;
    }

    // ===========================
    // = Search Function =
    // ===========================
    function handleSearch(e) {
        const searchTerm = e.target.value.toLowerCase();

        const moduleCards = document.querySelectorAll('.module-card');

        moduleCards.forEach(moduleCard => {
            const moduleHeader = moduleCard.querySelector('.card-header h5');
            const moduleText = moduleHeader ? moduleHeader.textContent.toLowerCase() : '';

            const submoduleCards = moduleCard.querySelectorAll('.col-12.col-md-6.col-lg-4');
            let hasVisibleSubmodules = false;

            submoduleCards.forEach(subCard => {
                const subCardHeader = subCard.querySelector('.card-header h6');
                const subCardText = subCardHeader ? subCardHeader.textContent.toLowerCase() : '';

                let subCardVisible = true;

                // Apply search filter
                if (searchTerm && !subCardText.includes(searchTerm) && !moduleText.includes(searchTerm)) {
                    // Check permissions within this submodule
                    const permissions = subCard.querySelectorAll('.form-check');
                    let hasVisiblePermissions = false;

                    permissions.forEach(permission => {
                        const label = permission.querySelector('label');
                        if (!label) return;
                        
                        const labelText = label.textContent.toLowerCase();
                        const labelTitle = label.title ? label.title.toLowerCase() : '';

                        if (labelText.includes(searchTerm) || labelTitle.includes(searchTerm)) {
                            hasVisiblePermissions = true;
                            permission.style.display = 'block';
                        } else {
                            // Only hide if it's not a master "Select All" switch (which we don't have inside form-check anymore, but just in case)
                            if (!permission.classList.contains('form-switch')) {
                                permission.style.display = 'none';
                            }
                        }
                    });

                    subCardVisible = hasVisiblePermissions;
                } else {
                    // If the submodule or module matches, show all permissions
                    subCard.querySelectorAll('.form-check').forEach(p => p.style.display = 'block');
                }

                subCard.style.display = subCardVisible ? 'block' : 'none';
                if (subCard.style.display === 'block') hasVisibleSubmodules = true;
            });

            // Show/hide module based on visible submodules
            moduleCard.style.display = hasVisibleSubmodules ? 'block' : 'none';
        });
    }

    // ===========================
    // = Update UI States =
    // ===========================
    function updateUIStates() {
        updateGroupSelectAllStates();
        updateSelectedCount();
    }

    function updateGroupSelectAllStates() {
        // Update group select all states
        document.querySelectorAll('.check-all-group').forEach(groupCheckbox => {
            const group = groupCheckbox.getAttribute('data-group');
            const groupCheckboxes = document.querySelectorAll(`.permission-group-${group}`);
            const checkedGroupCheckboxes = document.querySelectorAll(`.permission-group-${group}:checked`);

            groupCheckbox.checked = groupCheckboxes.length === checkedGroupCheckboxes.length;
            groupCheckbox.indeterminate = checkedGroupCheckboxes.length > 0 && checkedGroupCheckboxes.length <
                groupCheckboxes.length;
        });

        // Update module select all states
        document.querySelectorAll('.check-all-module').forEach(moduleCheckbox => {
            const module = moduleCheckbox.getAttribute('data-module');
            const moduleCheckboxes = document.querySelectorAll(`.permission-module-${module}`);
            const checkedModuleCheckboxes = document.querySelectorAll(`.permission-module-${module}:checked`);

            moduleCheckbox.checked = moduleCheckboxes.length === checkedModuleCheckboxes.length;
            moduleCheckbox.indeterminate = checkedModuleCheckboxes.length > 0 && checkedModuleCheckboxes
                .length < moduleCheckboxes.length;
        });
    }

    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('input[name="permissions[]"]:checked').length;
        const totalCount = document.querySelectorAll('input[name="permissions[]"]').length;

        document.getElementById('selectedCount').textContent = selectedCount;
        document.getElementById('totalCount').textContent = totalCount;
    }

    // ===========================
    // = Reset Functions =
    // ===========================
    function resetSelection() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.checked = false;
        });
        updateUIStates();
    }

    function resetForm() {
        resetSelection();
        document.getElementById('searchPermissions').value = '';
        // Re-apply search to show all items
        handleSearch({
            target: {
                value: ''
            }
        });
    }
</script>
