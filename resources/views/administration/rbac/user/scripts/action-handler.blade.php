<script>
    $('.selectpicker').selectpicker();

    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');
        const url = '{{ route('administrator.rbac.user.show', ':id') }}'.replace(':id', dataId);

        if (!dataId) return ResponseHandler.handleError("ID tidak ditemukan!");

        const handlers = {
            action_show: handleShow,
            action_edit: handleEdit,
            action_role: handleRole
        };

        if (handlers[action]) {
            AjaxHandler.sendGetRequest(url, res => {
                if (res.status === 200 && res.data) {
                    handlers[action](res.data, dataId);
                } else {
                    ResponseHandler.handleError("Data tidak ditemukan.");
                }
            });
        }
    });

    function handleShow(data) {
        $('#detail_name').text(data.name || 'N/A');
        $('#detail_username').text(data.username || 'N/A');
        $('#detail_email').text(data.email || 'N/A');
        $('#detail_status').text(data.status || 'N/A');
        const roles = Array.isArray(data.roles) && data.roles.length ?
            data.roles.map(r => r.role_name).join(', ') :
            'Tidak ada role';
        $('#detail_roles').text(roles);
        $('#modalDetail').modal('show');
    }

    function handleEdit(data) {
        $('#editForm').attr('data-id', data.id_user);
        $('.selectpicker').selectpicker('refresh');
        $('#modalEdit').modal('show');
    }

    function handleRole(_, dataId) {
        const url = '{{ route('administrator.rbac.user.list-user-role', ':id') }}'.replace(':id', dataId);

        AjaxHandler.sendGetRequest(url, res => {
            if (res.status === 200 && res.data) {
                const {
                    user,
                    roles,
                    user_roles
                } = res.data;
                $('#user_name_display').text(user.name || 'N/A');
                $('#userRoleForm').attr('data-id', user.id_user);
                renderRoleCheckboxes(roles, user_roles);
                $('#modalUserRole').modal('show');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    }

    function renderRoleCheckboxes(roles, selectedRoles) {
        const container = document.getElementById('roles_list');
        container.innerHTML = '';

        roles.forEach(role => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-2';

            const checkDiv = document.createElement('div');
            checkDiv.className = 'form-check';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'form-check-input';
            input.name = 'roles[]';
            input.value = role.id_role;
            input.id = `role_${role.id_role}`;
            if (selectedRoles.includes(role.id_role)) input.checked = true;

            const label = document.createElement('label');
            label.className = 'form-check-label';
            label.setAttribute('for', input.id);
            label.textContent = role.role_name;

            checkDiv.appendChild(input);
            checkDiv.appendChild(label);
            col.appendChild(checkDiv);
            container.appendChild(col);
        });
    }
</script>
