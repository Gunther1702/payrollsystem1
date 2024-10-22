$(document).ready(function() {
    // Initialize DataTable without default search
    const table = $('#employeeTable').DataTable({
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "zeroRecords": "No records available",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No entries available",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            { "orderable": true, "targets": [0, 1] },
            { "orderable": false, "targets": [2, 3, 4, 5, 6] }
        ],
        "paging": true,
        "lengthChange": true,
        "searching": false, // Disable default search
        "info": true,
        "fixedHeader": true
    });

    // Bind filter function to search bar
    $('#searchBar').on('keyup', function() {
        filterEmployees();
    });

    function filterEmployees() {
        const input = $('#searchBar').val().toLowerCase();
        const rows = table.rows().nodes(); // Get all rows

        rows.each(function(row) {
            const fullname = $(row).find('td:nth-child(2)').text().toLowerCase(); // Fullname
            const empId = $(row).find('td:nth-child(1)').text().toLowerCase(); // Employee ID
            const contact = $(row).find('td:nth-child(3)').text().toLowerCase(); // Contact
            const email = $(row).find('td:nth-child(4)').text().toLowerCase(); // Email
            const gender = $(row).find('td:nth-child(5)').text().toLowerCase(); // Gender
            const department = $(row).find('td:nth-child(7)').text().toLowerCase(); // Department
            const address = $(row).find('td:nth-child(8)').text().toLowerCase(); // Address
            const employee_type = $(row).find('td:nth-child(6)').text().toLowerCase(); // Address

            // Check if input matches any of the fields
            if (
                fullname.indexOf(input) > -1 ||
                empId.indexOf(input) > -1 ||
                contact.indexOf(input) > -1 ||
                email.indexOf(input) > -1 ||
                gender.indexOf(input) > -1 ||
                department.indexOf(input) > -1 ||
                employee_type.indexOf(input) > -1 || // Correctly include employee type
                address.indexOf(input) > -1 // Correctly include address
            ) {
                $(row).show(); // Show the row if match
            } else {
                $(row).hide(); // Hide the row if no match
            }
        });
    }
});
