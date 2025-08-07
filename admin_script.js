document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', async (e) => {
        if (e.target.classList.contains('action-btn')) {
            const button = e.target;
            const action = button.dataset.action;
            const id = button.dataset.id;
            const rowId = (action.includes('user') ? 'user-row-' : 'order-row-') + id;
            const row = document.getElementById(rowId);

            if (!confirm(`Are you sure you want to perform this action?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', action);
            formData.append('id', id);

            try {
                const response = await fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    if (action === 'delete_user') {
                        row.style.transition = 'opacity 0.3s ease-out';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    } else if (action === 'suspend_user') {
                        row.querySelector('.status-cell').innerHTML = '<span class="text-red-400 font-bold">Suspended</span>';
                        button.textContent = 'Unsuspend';
                        button.dataset.action = 'unsuspend_user';
                        button.classList.remove('text-yellow-400');
                        button.classList.add('text-green-400');
                    } else if (action === 'unsuspend_user') {
                        row.querySelector('.status-cell').innerHTML = '<span class="text-green-400 font-bold">Active</span>';
                        button.textContent = 'Suspend';
                        button.dataset.action = 'suspend_user';
                        button.classList.remove('text-green-400');
                        button.classList.add('text-yellow-400');
                    } else if (action === 'complete_order') {
                        row.querySelector('.status-cell').innerHTML = '<span class="font-bold text-green-400">completed</span>';
                        button.remove();
                    }
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('An unexpected error occurred.');
                console.error(error);
            }
        }
    });
});