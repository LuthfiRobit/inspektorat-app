<script>
    // ========================
    // = Permission Management =
    // ========================

    // Global variables
    let allPermissions = [];

    // ===========================
    // = Handle Select All Toggle =
    // ===========================
    document.addEventListener('change', function(e) {
        // Select All functionality
        if (e.target.id === 'selectAllPermissions') {
            const isChecked = e.target.checked;
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.checked = isChecked;
            });
            return;
        }

        // Group Select All functionality
        if (e.target.classList.contains('check-all-group')) {
            const group = e.target.getAttribute('data-group');
            document.querySelectorAll(`.permission-group-${group}`).forEach(cb => {
                cb.checked = e.target.checked;
            });
            return;
        }

        // Module Select All functionality
        if (e.target.classList.contains('check-all-module')) {
            const module = e.target.getAttribute('data-module');
            document.querySelectorAll(`.permission-module-${module}`).forEach(cb => {
                cb.checked = e.target.checked;
            });
        }
    });

    // ===========================
    // = Show Permission Modal =
    // ===========================
    function handleActionPermission(roleId) {
        const url = '{{ route('administrator.rbac.role.list-role-permission', ':id') }}'.replace(':id', roleId);

        AjaxHandler.sendGetRequest(url, function(response) {
            if (response.status === 200 && response.data) {
                const {
                    role,
                    permissions,
                    role_permissions
                } = response.data;

                // Store permissions globally for select all functionality
                allPermissions = permissions;

                // Display role information
                document.getElementById('permissionForm').setAttribute('data-id', roleId);
                document.getElementById('detail_p_role_name').textContent = role.role_name || 'N/A';
                document.getElementById('detail_p_role_description').textContent = role.role_description ||
                    'N/A';

                // Create structured permission list
                createStructuredPermissionList(permissions, role_permissions);

                // Show modal
                $('#modalPermission').modal('show');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    }

    // ================================
    // = Create Structured Permission List =
    // ================================
    function createStructuredPermissionList(permissions, rolePermissions) {
        const permissionsList = document.getElementById('permissions_list');
        permissionsList.innerHTML = '';

        // Group permissions by category and module
        const structuredPermissions = structurePermissions(permissions);

        Object.keys(structuredPermissions).forEach(category => {
            createCategoryAccordion(category, structuredPermissions[category], permissionsList,
                rolePermissions);
        });
    }

    // ===========================
    // = Structure Permissions - FIXED VERSION =
    // ===========================
    function structurePermissions(permissions) {
        const structured = {};

        // Pastikan permissions adalah array
        if (!Array.isArray(permissions)) {
            console.error('Permissions is not an array:', permissions);
            return structured;
        }

        permissions.forEach(permission => {
            const parts = permission.permission_name.split('.');

            if (parts.length < 2) {
                // Handle permissions without proper structure
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
        accordionItem.className = 'accordion-item border-0 mb-3';

        accordionItem.innerHTML = `
        <h2 class="accordion-header" id="heading-${categoryId}">
            <button class="accordion-button collapsed fw-bold fs-6 text-dark bg-light" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#collapse-${categoryId}" 
                    aria-expanded="false" aria-controls="collapse-${categoryId}">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
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
        <div class="card h-100 border shadow-sm">
            <div class="card-header bg-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold text-capitalize">${subCategory}</h6>
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
    // = Create Module Section - FIXED VERSION =
    // ===========================
    function createModuleSection(categoryKey, subCategoryKey, module, permissions, container, rolePermissions) {
        // Validasi: pastikan permissions adalah array
        if (!Array.isArray(permissions)) {
            console.warn(`Permissions for module ${module} is not an array:`, permissions);
            permissions = []; // Set sebagai array kosong jika bukan array
        }

        const moduleKey = `${subCategoryKey}-${module.replace(/[^a-zA-Z0-9]/g, '-')}`;

        const moduleSection = document.createElement('div');
        moduleSection.className = 'mb-3';

        if (module !== 'general') {
            moduleSection.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-bold text-muted text-capitalize">${module}</small>
                <div class="form-check form-switch">
                    <input class="form-check-input check-all-module" type="checkbox" 
                           data-module="${moduleKey}" id="checkAll-${moduleKey}">
                </div>
            </div>
        `;
        }

        const permissionsList = document.createElement('div');
        permissionsList.className = 'ps-2';

        // Pastikan permissions adalah array sebelum menggunakan forEach
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
        label.className = 'form-check-label small';
        label.setAttribute('for', checkbox.id);

        // Tampilkan action yang lebih meaningful
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
    // = Existing Functions (Keep as is) =
    // ===========================
    function handleActionShow(url) {
        AjaxHandler.sendGetRequest(url, function(response) {
            if (response.status === 200 && response.data) {
                $('#detail_role_scope').text(response.data.role_scope || 'N/A');
                $('#detail_role_name').text(response.data.role_name || 'N/A');
                $('#detail_role_description').text(response.data.role_description || 'N/A');
                $('#modalDetail').modal('show');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    }

    function handleActionEdit(url) {
        AjaxHandler.sendGetRequest(url, function(response) {
            if (response.status === 200 && response.data) {
                $('#editForm').attr('data-id', response.data.id_role);
                $('.selectpicker').selectpicker('refresh');
                $('#modalEdit').modal('show');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    }

    // ========================
    // = Event Handlers =
    // ========================
    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');

        if (!dataId) {
            ResponseHandler.handleError("ID tidak ditemukan!");
            return;
        }

        switch (action) {
            case 'action_show':
                handleActionShow('{{ route('administrator.rbac.role.show', ':id') }}'.replace(':id', dataId));
                break;
            case 'action_edit':
                handleActionEdit('{{ route('administrator.rbac.role.show', ':id') }}'.replace(':id', dataId));
                break;
            case 'action_permission':
                handleActionPermission(dataId);
                break;
        }
    });
</script>
