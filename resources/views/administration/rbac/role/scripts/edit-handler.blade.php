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
        document.getElementById('permissionForm').addEventListener('submit', handleFormSubmit);

        // Reset buttons
        document.getElementById('resetSelection').addEventListener('click', resetSelection);
        document.getElementById('resetForm').addEventListener('click', resetForm);

        // Search input
        document.getElementById('searchPermissions').addEventListener('input', handleSearch);

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
                document.getElementById('detail_p_role_name').textContent = role.role_name || 'N/A';
                document.getElementById('detail_p_role_description').textContent = role.role_description ||
                    'N/A';

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

        // Group permissions by category and module
        const structuredPermissions = structurePermissions(permissions);

        Object.keys(structuredPermissions).forEach(category => {
            createCategoryAccordion(category, structuredPermissions[category], permissionsList,
                rolePermissions);
        });

        // Update UI states
        updateUIStates();
    }

    // ===========================
    // = Structure Permissions =
    // ===========================
    function structurePermissions(permissions) {
        const structured = {};

        if (!Array.isArray(permissions)) {
            console.error('Permissions is not an array:', permissions);
            return structured;
        }

        permissions.forEach(permission => {
            const parts = permission.permission_name.split('.');

            if (parts.length < 2) {
                if (!structured.other) structured.other = {};
                if (!structured.other.general) structured.other.general = {};
                if (!structured.other.general.general) structured.other.general.general = [];
                structured.other.general.general.push(permission);
                return;
            }

            const category = parts[0];
            const subCategory = parts[1] || 'general';
            const module = parts[2] || 'general';
            const action = parts.slice(3).join('.') || 'general';

            if (!structured[category]) structured[category] = {};
            if (!structured[category][subCategory]) structured[category][subCategory] = {};
            if (!structured[category][subCategory][module]) structured[category][subCategory][module] = [];

            structured[category][subCategory][module].push({
                ...permission,
                action: action
            });
        });

        return structured;
    }

    // ===========================
    // = Create Category Accordion =
    // ===========================
    function createCategoryAccordion(category, subCategories, container, rolePermissions) {
        const categoryKey = category.replace(/[^a-zA-Z0-9]/g, '-');
        const categoryId = `category-${categoryKey}`;

        const accordionItem = document.createElement('div');
        accordionItem.className = 'accordion-item border-0';

        accordionItem.innerHTML = `
            <h2 class="accordion-header" id="heading-${categoryId}">
                <button class="accordion-button collapsed fw-bold fs-6 text-dark bg-light" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapse-${categoryId}" 
                        aria-expanded="false" aria-controls="collapse-${categoryId}">
                    <div class="d-flex justify-content-between align-items-center w-100 me-5">
                        <span class="text-uppercase">${category}</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input check-all-group" type="checkbox" 
                                data-group="${categoryKey}" id="checkAll-${categoryKey}">
                            <label class="form-check-label small" for="checkAll-${categoryKey}">Select All</label>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapse-${categoryId}" class="accordion-collapse collapse" 
                aria-labelledby="heading-${categoryId}">
                <div class="accordion-body p-3">
                    <div class="row g-3" id="subcategories-${categoryKey}">
                        <!-- Subcategories will be injected here -->
                    </div>
                </div>
            </div>
        `;

        const subcategoriesContainer = accordionItem.querySelector(`#subcategories-${categoryKey}`);

        Object.keys(subCategories).forEach(subCategory => {
            createSubCategorySection(category, categoryKey, subCategory, subCategories[subCategory],
                subcategoriesContainer, rolePermissions);
        });

        container.appendChild(accordionItem);
    }

    // ===========================
    // = Create SubCategory Section =
    // ===========================
    function createSubCategorySection(category, categoryKey, subCategory, modules, container, rolePermissions) {
        const subCategoryKey = `${categoryKey}-${subCategory.replace(/[^a-zA-Z0-9]/g, '-')}`;

        const subCategoryCol = document.createElement('div');
        subCategoryCol.className = 'col-12 col-md-6 col-lg-4';

        subCategoryCol.innerHTML = `
        <div class="card h-100 border-0 shadow-sm permission-card">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold text-capitalize text-primary">${subCategory}</h6>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="modules-container" id="modules-${subCategoryKey}">
                    <!-- Modules will be injected here -->
                </div>
            </div>
        </div>
    `;

        const modulesContainer = subCategoryCol.querySelector(`#modules-${subCategoryKey}`);

        Object.keys(modules).forEach(module => {
            createModuleSection(categoryKey, subCategoryKey, module, modules[module],
                modulesContainer, rolePermissions);
        });

        container.appendChild(subCategoryCol);
    }

    // ===========================
    // = Create Module Section =
    // ===========================
    function createModuleSection(categoryKey, subCategoryKey, module, permissions, container, rolePermissions) {
        if (!Array.isArray(permissions)) {
            console.warn(`Permissions for module ${module} is not an array:`, permissions);
            permissions = [];
        }

        const moduleKey = `${subCategoryKey}-${module.replace(/[^a-zA-Z0-9]/g, '-')}`;

        const moduleSection = document.createElement('div');
        moduleSection.className = 'mb-3';

        if (module !== 'general') {
            moduleSection.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <small class="fw-bold text-dark text-capitalize">${module}</small>
                <div class="form-check form-switch">
                    <input class="form-check-input check-all-module" type="checkbox" 
                           data-module="${moduleKey}" id="checkAll-${moduleKey}">
                    <label class="form-check-label" for="checkAll-${moduleKey}">
                        <small>All</small>
                    </label>
                </div>
            </div>
        `;
        }

        const permissionsList = document.createElement('div');
        permissionsList.className = 'ps-1';

        if (Array.isArray(permissions)) {
            permissions.forEach(permission => {
                const checkbox = createPermissionCheckbox(permission, categoryKey, moduleKey, rolePermissions);
                permissionsList.appendChild(checkbox);
            });
        }

        moduleSection.appendChild(permissionsList);
        container.appendChild(moduleSection);
    }

    // ===========================
    // = Create Permission Checkbox =
    // ===========================
    function createPermissionCheckbox(permission, categoryKey, moduleKey, rolePermissions) {
        const checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'form-check mb-2';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'permissions[]';
        checkbox.className = `form-check-input permission-group-${categoryKey} permission-module-${moduleKey}`;
        checkbox.id = `check-${permission.id_permission}`;
        checkbox.value = permission.id_permission;

        if (rolePermissions.includes(permission.id_permission)) {
            checkbox.checked = true;
        }

        const label = document.createElement('label');
        label.className = 'form-check-label small text-muted';
        label.setAttribute('for', checkbox.id);

        const displayName = permission.action && permission.action !== 'general' ?
            permission.action :
            permission.permission_name.split('.').pop();
        label.textContent = displayName;
        label.title = permission.permission_name;

        checkboxDiv.appendChild(checkbox);
        checkboxDiv.appendChild(label);

        return checkboxDiv;
    }

    // ===========================
    // = Search Function =
    // ===========================
    function handleSearch(e) {
        const searchTerm = e.target.value.toLowerCase();

        const accordionItems = document.querySelectorAll('.accordion-item');

        accordionItems.forEach(item => {
            const categoryHeader = item.querySelector('.accordion-button');
            const categoryText = categoryHeader.textContent.toLowerCase();

            const subCategories = item.querySelectorAll('.col-12.col-md-6.col-lg-4');
            let hasVisibleSubCategories = false;

            subCategories.forEach(subCat => {
                const subCatHeader = subCat.querySelector('.card-header h6');
                const subCatText = subCatHeader.textContent.toLowerCase();

                let subCatVisible = true;

                // Apply search filter
                if (searchTerm && !subCatText.includes(searchTerm)) {
                    // Check modules within this subcategory
                    const modules = subCat.querySelectorAll('.modules-container > div');
                    let hasVisibleModules = false;

                    modules.forEach(module => {
                        const permissions = module.querySelectorAll('.form-check');

                        let moduleVisible = false;

                        // Check if any permission matches search
                        permissions.forEach(permission => {
                            const label = permission.querySelector('label');
                            const labelText = label.textContent.toLowerCase();
                            const labelTitle = label.title.toLowerCase();

                            if (labelText.includes(searchTerm) || labelTitle.includes(
                                    searchTerm)) {
                                moduleVisible = true;
                                permission.style.display = 'block';
                            } else {
                                permission.style.display = 'none';
                            }
                        });

                        // Show/hide module based on visibility
                        module.style.display = moduleVisible ? 'block' : 'none';
                        if (module.style.display === 'block') hasVisibleModules = true;
                    });

                    subCatVisible = hasVisibleModules;
                }

                subCat.style.display = subCatVisible ? 'block' : 'none';
                if (subCat.style.display === 'block') hasVisibleSubCategories = true;
            });

            // Show/hide category based on visible subcategories
            item.style.display = hasVisibleSubCategories ? 'block' : 'none';
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
