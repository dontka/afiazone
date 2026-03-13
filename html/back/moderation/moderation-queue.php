<?php
$pageTitle = 'KYC Moderation Queue - Admin | AfiaZone';
$pageStyles = [];
$additionalScripts = ['/assets/js/pages-kyc-moderation.js'];
ob_start();
?>
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Moderation /</span> KYC Queue</h4>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="content-left">
            <span>Pending Submissions</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2" id="pending-count">0</h3>
            </div>
            <small class="text-warning">Awaiting review</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-warning"
              ><i class="ti ti-clock-hour-3"></i></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="content-left">
            <span>Approved</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2" id="approved-count">0</h3>
            </div>
            <small class="text-success">This month</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-success"
              ><i class="ti ti-check"></i></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="content-left">
            <span>Rejected</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2" id="rejected-count">0</h3>
            </div>
            <small class="text-danger">Needs revision</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-danger"
              ><i class="ti ti-x"></i></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="content-left">
            <span>Revision Requested</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2" id="revision-count">0</h3>
            </div>
            <small class="text-info">Awaiting resubmission</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-info"
              ><i class="ti ti-alert-circle"></i></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row">
      <div class="col-lg-4">
        <div class="mb-3">
          <label class="form-label">Search by Email or Name</label>
          <input type="text" id="search-input" class="form-control" placeholder="Search...">
        </div>
      </div>
      <div class="col-lg-3">
        <div class="mb-3">
          <label class="form-label">Filter by Status</label>
          <select id="filter-status" class="form-select">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="revision_requested">Revision Requested</option>
          </select>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="mb-3">
          <label class="form-label">Sort by</label>
          <select id="sort-by" class="form-select">
            <option value="latest">Latest First</option>
            <option value="oldest">Oldest First</option>
            <option value="user">User Name A-Z</option>
          </select>
        </div>
      </div>
      <div class="col-lg-2">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary w-100" onclick="filterAndSort()">Apply</button>
      </div>
    </div>
  </div>
</div>

<!-- KYC Submissions Table -->
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>User</th>
          <th>Email</th>
          <th>Submitted</th>
          <th>Status</th>
          <th>Documents</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="kyc-submissions-tbody">
        <tr>
          <td colspan="6" class="text-center text-muted py-4">Loading submissions...</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal for KYC Review -->
<div class="modal fade" id="kycReviewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">KYC Application Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- User Info -->
        <div class="mb-4">
          <h6 class="mb-3">User Information</h6>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Name:</strong> <span id="modal-user-name"></span></p>
              <p><strong>Email:</strong> <span id="modal-user-email"></span></p>
              <p><strong>Phone:</strong> <span id="modal-user-phone"></span></p>
            </div>
            <div class="col-md-6">
              <p><strong>Date of Birth:</strong> <span id="modal-dob"></span></p>
              <p><strong>Nationality:</strong> <span id="modal-nationality"></span></p>
              <p><strong>Submitted:</strong> <span id="modal-submitted-date"></span></p>
            </div>
          </div>
          <p><strong>Address:</strong> <span id="modal-address"></span></p>
        </div>

        <!-- Documents -->
        <div class="mb-4">
          <h6 class="mb-3">Uploaded Documents</h6>
          <div id="modal-documents" class="row">
            <!-- Documents will be populated here -->
          </div>
        </div>

        <!-- Review Notes -->
        <div class="mb-4">
          <h6>Previous Review Notes</h6>
          <div id="modal-review-history" class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">
            <small class="text-muted">No previous reviews</small>
          </div>
        </div>

        <!-- Admin Review -->
        <div class="mb-4">
          <h6>Your Review</h6>
          <textarea id="modal-review-notes" class="form-control" placeholder="Add your review notes..." rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning" onclick="requestRevision()">Request Revision</button>
        <button type="button" class="btn btn-danger" onclick="rejectKyc()">Reject</button>
        <button type="button" class="btn btn-success" onclick="approveKyc()">Approve</button>
      </div>
    </div>
  </div>
</div>

<script>
let currentKycId = null;

// Load KYC submissions
async function loadKycSubmissions() {
  try {
    const response = await fetch('/admin/kyc?status=pending', {
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
      }
    });
    const data = await response.json();
    
    if (response.ok) {
      displaySubmissions(data.data);
      updateStats(data);
    }
  } catch (error) {
    console.error('Error loading KYC submissions:', error);
  }
}

// Display submissions in table
function displaySubmissions(submissions) {
  const tbody = document.getElementById('kyc-submissions-tbody');
  
  if (submissions.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No pending submissions</td></tr>';
    return;
  }
  
  tbody.innerHTML = submissions.map(submission => `
    <tr>
      <td>
        <div class="d-flex align-items-center">
          <img src="${submission.user.avatar || '/assets/img/avatars/default.png'}" alt="Avatar" class="rounded-circle" width="40" height="40" style="margin-right: 10px;">
          <div>
            <strong>${submission.user.first_name} ${submission.user.last_name}</strong>
          </div>
        </div>
      </td>
      <td>${submission.user.email}</td>
      <td>${new Date(submission.submission_date).toLocaleDateString()}</td>
      <td>
        <span class="badge bg-${getStatusColor(submission.status)}">${submission.status.replace('_', ' ')}</span>
      </td>
      <td>
        <span class="badge bg-info">${submission.documents_count} docs</span>
      </td>
      <td>
        <button class="btn btn-sm btn-primary" onclick="openReview(${submission.id})">
          Review
        </button>
      </td>
    </tr>
  `).join('');
}

// Open review modal
async function openReview(kycId) {
  currentKycId = kycId;
  
  try {
    const response = await fetch(`/admin/kyc/${kycId}`, {
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
      }
    });
    const data = await response.json();
    
    if (response.ok) {
      const kyc = data.data;
      
      // Fill form
      document.getElementById('modal-user-name').textContent = `${kyc.user.first_name} ${kyc.user.last_name}`;
      document.getElementById('modal-user-email').textContent = kyc.user.email;
      document.getElementById('modal-user-phone').textContent = kyc.user.phone || 'N/A';
      document.getElementById('modal-dob').textContent = new Date(kyc.date_of_birth).toLocaleDateString();
      document.getElementById('modal-nationality').textContent = kyc.nationality;
      document.getElementById('modal-address').textContent = kyc.residential_address;
      document.getElementById('modal-submitted-date').textContent = new Date(kyc.submission_date).toLocaleDateString();
      
      // Display documents
      const docsHtml = kyc.documents.map(doc => `
        <div class="col-md-6 mb-3">
          <div class="card">
            <img src="${doc.file_url}" alt="${doc.document_type}" class="card-img-top" style="height: 200px; object-fit: cover;">
            <div class="card-body p-2">
              <small><strong>${doc.document_type}</strong></small>
              <p class="mb-0 text-muted" style="font-size: 12px;">Uploaded: ${new Date(doc.uploaded_at).toLocaleDateString()}</p>
            </div>
          </div>
        </div>
      `).join('');
      document.getElementById('modal-documents').innerHTML = docsHtml;
      
      // Clear notes
      document.getElementById('modal-review-notes').value = '';
      
      // Show modal
      new bootstrap.Modal(document.getElementById('kycReviewModal')).show();
    }
  } catch (error) {
    console.error('Error loading KYC details:', error);
  }
}

// Approve KYC
async function approveKyc() {
  const notes = document.getElementById('modal-review-notes').value;
  
  try {
    const response = await fetch(`/admin/kyc/${currentKycId}/approve`, {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ notes })
    });
    
    if (response.ok) {
      alert('KYC approved successfully!');
      bootstrap.Modal.getInstance(document.getElementById('kycReviewModal')).hide();
      loadKycSubmissions();
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

// Reject KYC
async function rejectKyc() {
  const notes = document.getElementById('modal-review-notes').value;
  
  if (!notes) {
    alert('Please provide rejection reasons');
    return;
  }
  
  try {
    const response = await fetch(`/admin/kyc/${currentKycId}/reject`, {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ notes })
    });
    
    if (response.ok) {
      alert('KYC rejected');
      bootstrap.Modal.getInstance(document.getElementById('kycReviewModal')).hide();
      loadKycSubmissions();
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

// Request Revision
async function requestRevision() {
  const notes = document.getElementById('modal-review-notes').value;
  
  if (!notes) {
    alert('Please provide revision requests');
    return;
  }
  
  try {
    const response = await fetch(`/admin/kyc/${currentKycId}/request-revision`, {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ notes })
    });
    
    if (response.ok) {
      alert('Revision requested from user');
      bootstrap.Modal.getInstance(document.getElementById('kycReviewModal')).hide();
      loadKycSubmissions();
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

// Helper
function getStatusColor(status) {
  const colors = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    revision_requested: 'info'
  };
  return colors[status] || 'secondary';
}

// Update stats
function updateStats(data) {
  document.getElementById('pending-count').textContent = data.stats.pending || 0;
  document.getElementById('approved-count').textContent = data.stats.approved || 0;
  document.getElementById('rejected-count').textContent = data.stats.rejected || 0;
  document.getElementById('revision-count').textContent = data.stats.revision_requested || 0;
}

// Load on page load
document.addEventListener('DOMContentLoaded', loadKycSubmissions);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
