<?php
$pageTitle = 'KYC Moderation Dashboard - AfiaZone Admin';
$vendorScripts = ['/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'];
$additionalScripts = ['/assets/js/admin/kyc-moderation.js'];
$pageStyles = ['/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css', '/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css'];
ob_start();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="menu-icon tf-icons ti ti-file-check"></i> KYC Moderation</h4>
            <p class="text-muted mb-0">Review and approve Know Your Customer submissions</p>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="pending">
                Pending <span class="badge bg-warning ms-1" id="pendingCount">0</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="approved">
                Approved <span class="badge bg-success ms-1" id="approvedCount">0</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="rejected">
                Rejected <span class="badge bg-danger ms-1" id="rejectedCount">0</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="all">
                All
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Search by Email or User ID</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" id="sortBy">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100" onclick="loadKYCSubmissions('pending')">
                        <i class="ti ti-refresh"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Submissions Table -->
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="kycTable">
                <thead class="table-light">
                    <tr>
                        <th>Submission ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Documents</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="kycTableBody">
                    <tr id="loadingRow">
                        <td colspan="7" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center p-3">
            <small class="text-muted" id="totalCount">No submissions</small>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- KYC Detail Modal -->
<div class="modal fade" id="kycDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">KYC Submission Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="kycDetailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="requestRevisionBtn" onclick="requestRevision()" style="display: none;">
                    <i class="ti ti-alert-circle"></i> Request Revision
                </button>
                <button type="button" class="btn btn-danger" id="rejectBtn" onclick="showRejectForm()" style="display: none;">
                    <i class="ti ti-x"></i> Reject
                </button>
                <button type="button" class="btn btn-success" id="approveBtn" onclick="approveSubmission()" style="display: none;">
                    <i class="ti ti-check"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject KYC Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason *</label>
                    <textarea class="form-control" id="rejectionReason" rows="4" required></textarea>
                </div>
                <div class="alert alert-info">
                    <i class="ti ti-info-circle"></i> The user will be notified and can resubmit their documents.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="ti ti-x"></i> Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Request Revision Modal -->
<div class="modal fade" id="requestRevisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Revision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Revision Note *</label>
                    <textarea class="form-control" id="revisionNote" rows="4" required></textarea>
                </div>
                <div class="alert alert-info">
                    <i class="ti ti-info-circle"></i> The user will be notified and asked to resubmit their documents.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmRequestRevision()">
                    <i class="ti ti-alert-circle"></i> Request Revision
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const API_URL = '/api';
const token = localStorage.getItem('auth_token');
let currentSubmissionId = null;
let currentStatus = 'pending';
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // Filter buttons
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentStatus = this.getAttribute('data-filter') === 'all' ? '' : this.getAttribute('data-filter');
            currentPage = 1;
            loadKYCSubmissions();
        });
    });

    // Search
    document.getElementById('searchInput').addEventListener('debounce:input', debounce(() => {
        currentPage = 1;
        loadKYCSubmissions();
    }, 300));

    // Sort
    document.getElementById('sortBy').addEventListener('change', function() {
        currentPage = 1;
        loadKYCSubmissions();
    });

    loadKYCSubmissions();
    // Refresh every 30 seconds
    setInterval(() => loadKYCSubmissions(), 30000);
});

function loadKYCSubmissions(page = 1) {
    if (!token) return;

    currentPage = page;
    const params = new URLSearchParams();
    if (currentStatus) params.append('status', currentStatus);
    params.append('page', page);
    params.append('per_page', 15);
    
    const searchTerm = document.getElementById('searchInput').value;
    if (searchTerm) params.append('search', searchTerm);

    const sortBy = document.getElementById('sortBy').value;
    params.append('sort', sortBy === 'newest' ? 'desc' : 'asc');

    fetch(`${API_URL}/admin/kyc?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderTable(data.data);
            updateStats(data.stats);
            renderPagination(data.pagination);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loadingRow').innerHTML = '<tr><td colspan="7" class="text-danger text-center">Error loading submissions</td></tr>';
    });
}

function renderTable(submissions) {
    const tbody = document.getElementById('kycTableBody');
    
    if (submissions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No submissions found</td></tr>';
        return;
    }

    tbody.innerHTML = submissions.map(sub => `
        <tr>
            <td><code>${sub.id}</code></td>
            <td>
                <div class="d-flex align-items-center">
                    <img src="${sub.user.avatar_url || '/assets/img/avatars/default.jpg'}" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                    <span>${sub.user.first_name} ${sub.user.last_name}</span>
                </div>
            </td>
            <td><small>${sub.user.email}</small></td>
            <td>
                <span class="badge bg-${getStatusColor(sub.status)}">
                    ${formatStatus(sub.status)}
                </span>
            </td>
            <td>
                <span class="badge bg-info">${sub.documents.length} doc(s)</span>
            </td>
            <td><small>${formatDate(sub.submission_date)}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${sub.id})">
                    <i class="ti ti-eye"></i> View
                </button>
            </td>
        </tr>
    `).join('');
}

function viewDetails(submissionId) {
    currentSubmissionId = submissionId;
    const modal = new bootstrap.Modal(document.getElementById('kycDetailModal'));
    
    fetch(`${API_URL}/admin/kyc/${submissionId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderDetailModal(data.data);
            modal.show();
        }
    });
}

function renderDetailModal(submission) {
    const content = document.getElementById('kycDetailContent');
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const requestRevisionBtn = document.getElementById('requestRevisionBtn');

    // Show/hide action buttons based on status
    approveBtn.style.display = submission.status === 'pending' || submission.status === 'revision_requested' ? 'block' : 'none';
    rejectBtn.style.display = submission.status === 'pending' || submission.status === 'revision_requested' ? 'block' : 'none';
    requestRevisionBtn.style.display = submission.status === 'pending' ? 'block' : 'none';

    content.innerHTML = `
        <div class="mb-4">
            <h6>User Information</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Name:</strong> ${submission.user.first_name} ${submission.user.last_name}</p>
                    <p class="mb-0"><strong>Email:</strong> ${submission.user.email}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Phone:</strong> ${submission.user.phone || 'N/A'}</p>
                    <p class="mb-0"><strong>Status:</strong> <span class="badge bg-${getStatusColor(submission.status)}">${formatStatus(submission.status)}</span></p>
                </div>
            </div>
        </div>

        <hr>

        <div class="mb-4">
            <h6>Documents Submitted</h6>
            <div class="list-group">
                ${submission.documents.map(doc => `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${formatDocumentType(doc.document_type)}</h6>
                                <p class="text-muted small mb-1">Uploaded: ${formatDate(doc.uploaded_at)}</p>
                                <p class="text-muted small mb-0">Status: <span class="badge bg-${getVerificationStatusColor(doc.verification_status)}">${doc.verification_status}</span></p>
                            </div>
                            <a href="${doc.file_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-download"></i> View
                            </a>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>

        ${submission.status === 'revision_requested' || submission.status === 'rejected' ? `
            <div class="alert alert-info">
                <strong>Note:</strong> ${submission.revision_reason || submission.rejection_reason}
            </div>
        ` : ''}

        ${submission.review_date ? `
            <div class="alert alert-light">
                <p class="mb-0"><strong>Reviewed on:</strong> ${formatDate(submission.review_date)}</p>
            </div>
        ` : ''}
    `;
}

function approveSubmission() {
    if (!currentSubmissionId) return;

    fetch(`${API_URL}/admin/kyc/${currentSubmissionId}/approve`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            notes: 'Approved through moderation dashboard'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'KYC submission approved!', 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('kycDetailModal')).hide();
                loadKYCSubmissions(currentPage);
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function showRejectForm() {
    new bootstrap.Modal(document.getElementById('rejectReasonModal')).show();
}

function confirmReject() {
    const reason = document.getElementById('rejectionReason').value;
    if (!reason.trim()) {
        alert('Please provide a rejection reason');
        return;
    }

    fetch(`${API_URL}/admin/kyc/${currentSubmissionId}/reject`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            rejection_reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'KYC submission rejected!', 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('rejectReasonModal')).hide();
                bootstrap.Modal.getInstance(document.getElementById('kycDetailModal')).hide();
                document.getElementById('rejectionReason').value = '';
                loadKYCSubmissions(currentPage);
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function requestRevision() {
    new bootstrap.Modal(document.getElementById('requestRevisionModal')).show();
}

function confirmRequestRevision() {
    const note = document.getElementById('revisionNote').value;
    if (!note.trim()) {
        alert('Please provide a revision note');
        return;
    }

    fetch(`${API_URL}/admin/kyc/${currentSubmissionId}/request-revision`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            revision_reason: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Revision requested!', 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('requestRevisionModal')).hide();
                bootstrap.Modal.getInstance(document.getElementById('kycDetailModal')).hide();
                document.getElementById('revisionNote').value = '';
                loadKYCSubmissions(currentPage);
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function updateStats(stats) {
    document.getElementById('pendingCount').textContent = stats.pending || 0;
    document.getElementById('approvedCount').textContent = stats.approved || 0;
    document.getElementById('rejectedCount').textContent = stats.rejected || 0;
}

function renderPagination(pagination) {
    const container = document.getElementById('pagination');
    container.innerHTML = '';

    if (pagination.total_pages <= 1) return;

    // Previous
    if (pagination.current_page > 1) {
        container.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadKYCSubmissions(${pagination.current_page - 1})">Previous</a></li>`;
    }

    // Pages
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === pagination.current_page) {
            container.innerHTML += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            container.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadKYCSubmissions(${i})">${i}</a></li>`;
        }
    }

    // Next
    if (pagination.current_page < pagination.total_pages) {
        container.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadKYCSubmissions(${pagination.current_page + 1})">Next</a></li>`;
    }

    document.getElementById('totalCount').textContent = `Showing ${pagination.from} - ${pagination.to} of ${pagination.total} submissions`;
}

function getStatusColor(status) {
    const map = {
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'revision_requested': 'info'
    };
    return map[status] || 'secondary';
}

function getVerificationStatusColor(status) {
    const map = {
        'pending': 'warning',
        'verified': 'success',
        'rejected': 'danger'
    };
    return map[status] || 'secondary';
}

function formatStatus(status) {
    const map = {
        'pending': 'Pending Review',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'revision_requested': 'Revision Requested'
    };
    return map[status] || status;
}

function formatDocumentType(type) {
    const map = {
        'id_card': 'ID Card',
        'passport': 'Passport',
        'driver_license': 'Driver License',
        'proof_of_address': 'Proof of Address',
        'business_license': 'Business License',
        'tax_certificate': 'Tax Certificate'
    };
    return map[type] || type;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
