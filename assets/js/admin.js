// Admin functionality for Real Estate Listing System

document.addEventListener('DOMContentLoaded', function() {
    // Handle property deletion
    const deletePropertyBtns = document.querySelectorAll('.delete-property-btn');
    if (deletePropertyBtns.length) {
        deletePropertyBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const propertyId = this.getAttribute('data-property-id');
                const propertyTitle = this.getAttribute('data-property-title');
                
                if (confirm(`Are you sure you want to delete "${propertyTitle}"?`)) {
                    fetch('api/admin/property_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            property_id: propertyId,
                            action: 'delete'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.reload();
                        } else {
                            alert('Failed to delete property: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the property');
                    });
                }
            });
        });
    }

    // Handle seller application approval/rejection
    const approveSellerBtns = document.querySelectorAll('.approve-seller-btn');
    const rejectSellerBtns = document.querySelectorAll('.reject-seller-btn');

    if (approveSellerBtns.length) {
        approveSellerBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const applicationId = this.getAttribute('data-application-id');

                if (confirm('Are you sure you want to approve this seller application?')) {
                    fetch('api/admin/property_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'approve_seller',
                            user_id: userId,
                            application_id: applicationId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error approving seller application');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while approving the seller application');
                    });
                }
            });
        });
    }

    if (rejectSellerBtns.length) {
        rejectSellerBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const applicationId = this.getAttribute('data-application-id');

                if (confirm('Are you sure you want to reject this seller application?')) {
                    fetch('api/admin/property_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'reject_seller',
                            user_id: userId,
                            application_id: applicationId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error rejecting seller application');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while rejecting the seller application');
                    });
                }
            });
        });
    }

    // Handle user delete
    const deleteUserBtns = document.querySelectorAll('.delete-user-btn');

    if (deleteUserBtns.length) {
        deleteUserBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');

                if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                    // Send delete request
                    fetch('api/users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            action: 'delete'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove user from list or reload page
                            const userRow = this.closest('tr');
                            if (userRow) {
                                userRow.remove();
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert(data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the user.');
                    });
                }
            });
        });
    }

    // Handle report resolution
    const resolveReportBtns = document.querySelectorAll('.resolve-report-btn');
    const dismissReportBtns = document.querySelectorAll('.dismiss-report-btn');

    function handleReportAction(btn, action) {
        const reportId = btn.getAttribute('data-report-id');

        // Send update request
        fetch('api/properties.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                report_id: reportId,
                action: 'update_report',
                status: action === 'resolve' ? 'resolved' : 'dismissed'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update UI or reload page
                const reportRow = btn.closest('tr');
                if (reportRow) {
                    // Update status badge
                    const statusBadge = reportRow.querySelector('.report-status');
                    if (statusBadge) {
                        statusBadge.textContent = action === 'resolve' ? 'Resolved' : 'Dismissed';
                        statusBadge.className = 'report-status badge ' + 
                            (action === 'resolve' ? 'badge-success' : 'badge-secondary');
                    }

                    // Disable buttons
                    reportRow.querySelectorAll('button').forEach(button => {
                        button.disabled = true;
                    });
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the report.');
        });
    }

    if (resolveReportBtns.length) {
        resolveReportBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                if (confirm('Are you sure you want to mark this report as resolved?')) {
                    handleReportAction(this, 'resolve');
                }
            });
        });
    }

    if (dismissReportBtns.length) {
        dismissReportBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                if (confirm('Are you sure you want to dismiss this report?')) {
                    handleReportAction(this, 'dismiss');
                }
            });
        });
    }

    // Change user role
    const userRoleSelects = document.querySelectorAll('.user-role-select');

    if (userRoleSelects.length) {
        userRoleSelects.forEach(select => {
            select.addEventListener('change', function() {
                const userId = this.getAttribute('data-user-id');
                const newRole = this.value;

                if (confirm(`Are you sure you want to change this user's role to ${newRole}?`)) {
                    // Send update request
                    fetch('api/users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            role: newRole,
                            action: 'update_role'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message
                            alert(data.message || 'User role updated successfully.');
                        } else {
                            // Show error and reset select
                            alert(data.message || 'An error occurred');
                            this.value = this.getAttribute('data-current-role');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the user role.');
                        this.value = this.getAttribute('data-current-role');
                    });
                } else {
                    // Reset select if cancelled
                    this.value = this.getAttribute('data-current-role');
                }
            });
        });
    }

    // Property approval/rejection
    const approvePropertyBtns = document.querySelectorAll('.approve-property-btn');
    const rejectPropertyBtns = document.querySelectorAll('.reject-property-btn');

    function handlePropertyAction(btn, action) {
        const propertyId = btn.getAttribute('data-property-id');

        // Send update request
        fetch('api/properties.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                property_id: propertyId,
                action: action === 'approve' ? 'approve_property' : 'reject_property'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update UI or reload page
                const propertyRow = btn.closest('tr');
                if (propertyRow) {
                    if (action === 'reject') {
                        propertyRow.remove();
                    } else {
                        // Update status badge
                        const statusBadge = propertyRow.querySelector('.property-status');
                        if (statusBadge) {
                            statusBadge.textContent = 'Approved';
                            statusBadge.className = 'property-status badge badge-success';
                        }

                        // Disable buttons
                        propertyRow.querySelectorAll('button').forEach(button => {
                            if (button.classList.contains('approve-property-btn') || 
                                button.classList.contains('reject-property-btn')) {
                                button.disabled = true;
                            }
                        });
                    }
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the property.');
        });
    }

    if (approvePropertyBtns.length) {
        approvePropertyBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                if (confirm('Are you sure you want to approve this property?')) {
                    handlePropertyAction(this, 'approve');
                }
            });
        });
    }

    if (rejectPropertyBtns.length) {
        rejectPropertyBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                if (confirm('Are you sure you want to reject and delete this property?')) {
                    handlePropertyAction(this, 'reject');
                }
            });
        });
    }

    // Handle property deletion approval
    const approveDeleteBtns = document.querySelectorAll('.approve-delete-btn');
    if (approveDeleteBtns.length) {
        approveDeleteBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const propertyId = this.getAttribute('data-property-id');
                const propertyTitle = this.getAttribute('data-property-title');

                if (confirm(`Are you sure you want to approve deletion of "${propertyTitle}"? This cannot be undone.`)) {
                    fetch('api/properties.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            property_id: propertyId,
                            action: 'approve_deletion'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error approving deletion');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while approving the deletion');
                    });
                }
            });
        });
    }

    // Admin dashboard charts (if available)
    const propertiesChartCanvas = document.getElementById('properties-chart');
    const usersChartCanvas = document.getElementById('users-chart');

    if (typeof Chart !== 'undefined') {
        if (propertiesChartCanvas) {
            // Get data from data attributes
            const propertyTypes = JSON.parse(propertiesChartCanvas.getAttribute('data-types') || '[]');
            const propertyCounts = JSON.parse(propertiesChartCanvas.getAttribute('data-counts') || '[]');

            const propertiesChart = new Chart(propertiesChartCanvas, {
                type: 'doughnut',
                data: {
                    labels: propertyTypes,
                    datasets: [{
                        data: propertyCounts,
                        backgroundColor: [
                            '#4fc3f7',
                            '#0288d1',
                            '#01579b',
                            '#039be5',
                            '#b3e5fc'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        position: 'bottom'
                    }
                }
            });
        }

        if (usersChartCanvas) {
            // Get data from data attributes
            const userRoles = JSON.parse(usersChartCanvas.getAttribute('data-roles') || '[]');
            const userCounts = JSON.parse(usersChartCanvas.getAttribute('data-counts') || '[]');

            const usersChart = new Chart(usersChartCanvas, {
                type: 'pie',
                data: {
                    labels: userRoles,
                    datasets: [{
                        data: userCounts,
                        backgroundColor: [
                            '#4caf50',
                            '#f44336',
                            '#9c27b0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        position: 'bottom'
                    }
                }
            });
        }
    }
});

// Admin dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle property delete
    const deletePropertyBtns = document.querySelectorAll('.delete-property-btn');
    const featuredPropertyBtns = document.querySelectorAll('.featured-property-btn');

    if (deletePropertyBtns.length) {
        deletePropertyBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                const propertyId = this.getAttribute('data-property-id');
                const propertyTitle = this.getAttribute('data-property-title');

                if (confirm(`Are you sure you want to delete "${propertyTitle}"?`)) {
                    fetch('api/properties.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            property_id: propertyId,
                            action: 'delete'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error deleting property');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the property');
                    });
                }
            });
        });
    }

    // Handle featured property toggle
const toggleFeaturedBtns = document.querySelectorAll('.toggle-featured-btn');
if (toggleFeaturedBtns.length) {
    toggleFeaturedBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-property-id');
            const featured = this.getAttribute('data-featured') === '1';

            const action = featured ? 'unfeature' : 'feature';
            if (confirm('Are you sure you want to ' + (featured ? 'remove' : 'set') + ' this property as featured?')) {

            fetch('api/properties.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'toggle_featured',
                    property_id: propertyId,
                    featured: !featured
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update button state
                    this.setAttribute('data-featured', featured ? '0' : '1');
                    const icon = this.querySelector('i');
                    if (featured) {
                        this.innerHTML = '<i class="far fa-star"></i> Set Featured';
                    } else {
                        this.innerHTML = '<i class="fas fa-star"></i> Featured';
                    }
                } else {
                    alert(data.message || 'Error updating featured status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the property');
            });
        }
        });
    });
}

    // Dismiss report functionality
    const dismissReportBtns = document.querySelectorAll('.dismiss-report-btn');
    dismissReportBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reportId = this.getAttribute('data-report-id');

            fetch('api/properties.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'dismiss_report',
                    report_id: reportId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    console.error('Property action failed:', data);
                    alert('Failed to dismiss report. ' + (data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while dismissing the report');
            });
        });
    });
});