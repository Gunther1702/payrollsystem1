document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const addEmployeeModal = document.getElementById('addEmployeeModal');
    const closeModal = document.querySelector('.modal-close');

    // Toggle sidebar function
    window.toggleSidebar = function() {
        sidebar.classList.toggle('open'); // Toggle the 'open' class on the sidebar
        mainContent.classList.toggle('expanded'); // Toggle the 'expanded' class on the main content
    };

    // Event listener for clicks outside the sidebar
    document.addEventListener('click', function (event) {
        const menuBtn = document.querySelector('.menu-btn');
        if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
            sidebar.classList.remove('open');
            mainContent.classList.remove('expanded');
        }
    });

    // Setup edit and delete buttons
    setupEditButtons();
    setupDeleteButtons();

    function setupEditButtons() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Edit Employee',
                    text: `Edit functionality for Employee ID: ${employeeId} to be implemented!`,
                    icon: 'info',
                });
            });
        });
    }

    function setupDeleteButtons() {
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You won't be able to revert this! Employee ID: ${employeeId}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteEmployee(employeeId);
                    }
                });
            });
        });
    }

    function deleteEmployee(employeeId) {
        fetch(`delete_employee.php?id=${employeeId}`, {
            method: 'DELETE'
        })
        .then(response => {
            if (response.ok) {
                Swal.fire('Deleted!', `Employee ID: ${employeeId} has been deleted.`, 'success');
                // Remove the employee row from the table
                const row = document.querySelector(`tr[data-id="${employeeId}"]`);
                if (row) {
                    row.remove();
                }
            } else {
                Swal.fire('Error!', 'There was an error deleting the employee. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'There was an error. Please try again.', 'error');
        });
    }

    // Modal handling code
    const addEmployeeBtn = document.querySelector('.btn[onclick="openModal()"]');
    if (addEmployeeBtn) {
        addEmployeeBtn.addEventListener('click', function() {
            addEmployeeModal.style.display = 'flex';
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', function() {
            addEmployeeModal.style.display = 'none';
        });
    }

    // Close the modal when clicking anywhere outside of it
    window.onclick = function(event) {
        if (event.target === addEmployeeModal) {
            addEmployeeModal.style.display = 'none';
        }
    };
});
