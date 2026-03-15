<?php
$pageTitle = 'KYC Verification - AfiaZone';
$additionalStyles = ['/vendor/libs/sweetalert2/sweetalert2.css'];
$additionalScripts = ['/vendor/libs/sweetalert2/sweetalert2.js'];
ob_start();
?>

<div class="rts-navigation-area-breadcrumb">
    <div class="container-2">
        <div class="row">
            <div class="col-lg-12">
                <div class="navigator-breadcrumb-wrapper">
                    <a href="/">Home</a>
                    <i class="fa-regular fa-chevron-right"></i>
                    <a href="/me">Profile</a>
                    <i class="fa-regular fa-chevron-right"></i>
                    <a class="current" href="javascript:void(0)">KYC Verification</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="rts-section-gap" style="padding: 40px 0;">
    <div class="container-2">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- KYC Status Card -->
                <div class="card mb-4" id="kycStatusCard">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fa-light fa-id-card"></i>
                            Know Your Customer (KYC) Verification
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Loading State -->
                        <div id="loadingState" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading KYC status...</p>
                        </div>

                        <!-- Status Display -->
                        <div id="statusDisplay" style="display: none;">
                            <div class="alert alert-info" id="statusBadge"></div>

                            <!-- Pending or Revision Requested -->
                            <div id="submissionGuidance" class="mb-4">
                                <h6>Required Documents</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fa-solid fa-circle-check text-success"></i> Valid ID (Passport, National ID, Driver License)</li>
                                    <li><i class="fa-solid fa-circle-check text-success"></i> Proof of Address (Recent utility bill, bank statement)</li>
                                    <li><i class="fa-solid fa-circle-check text-success"></i> Selfie with ID (Optional but recommended)</li>
                                </ul>
                            </div>

                            <!-- Documents Section -->
                            <div id="documentsSection" class="mb-4">
                                <h6>Uploaded Documents</h6>
                                <div id="documentsList" class="list-group mb-3"></div>
                                <button type="button" class="btn btn-primary" onclick="openDocumentUploadModal()">
                                    <i class="fa-light fa-plus"></i> Upload Document
                                </button>
                            </div>

                            <!-- Action Buttons -->
                            <div id="actionButtons" class="mt-4 d-flex gap-2">
                                <!-- Submit button (shown when ready) -->
                                <button type="button" class="btn btn-success" id="submitBtn" onclick="submitKYC()" style="display: none;">
                                    <i class="fa-light fa-paper-plane"></i> Submit for Review
                                </button>

                                <!-- Revision Note (if revision requested) -->
                                <div id="revisionNote" class="alert alert-warning w-100" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- No Submission State -->
                        <div id="noSubmissionState" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fa-light fa-circle-info"></i>
                                You haven't submitted any KYC documents yet. Let's get started!
                            </div>
                            <button type="button" class="btn btn-primary" onclick="startKYCSubmission()">
                                <i class="fa-light fa-arrow-right"></i> Start KYC Verification
                            </button>
                        </div>

                        <!-- Approved State -->
                        <div id="approvedState" style="display: none;">
                            <div class="alert alert-success">
                                <h5 class="mb-2"><i class="fa-solid fa-circle-check"></i> KYC Verified!</h5>
                                <p class="mb-0">Your identity has been verified. You can now enjoy all platform features.</p>
                            </div>
                            <div class="alert alert-light">
                                <p><strong>Verified on:</strong> <span id="verifiedDate"></span></p>
                            </div>
                        </div>

                        <!-- Rejected State -->
                        <div id="rejectedState" style="display: none;">
                            <div class="alert alert-danger">
                                <h5 class="mb-2"><i class="fa-solid fa-circle-xmark"></i> Verification Rejected</h5>
                                <p id="rejectionReason" class="mb-0"></p>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="retryKYCSubmission()">
                                <i class="fa-light fa-arrow-right"></i> Resubmit Documents
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa-light fa-circle-info"></i> Why KYC Verification?</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Legal Compliance:</strong> We comply with financial regulations to ensure a safe marketplace.</p>
                        <p class="mb-2"><strong>Security:</strong> Verification protects you and other users from fraud.</p>
                        <p class="mb-0"><strong>Higher Limits:</strong> Verified users can perform more transactions and access premium features.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="documentUploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documentType" class="form-label">Document Type *</label>
                        <select class="form-control" id="documentType" name="document_type" required>
                            <option value="">Select Document Type</option>
                            <option value="id_card">ID Card/National ID</option>
                            <option value="passport">Passport</option>
                            <option value="driver_license">Driver License</option>
                            <option value="proof_of_address">Proof of Address</option>
                            <option value="business_license">Business License</option>
                            <option value="tax_certificate">Tax Certificate</option>
                        </select>
                        <small class="text-danger" id="documentTypeError"></small>
                    </div>

                    <div class="mb-3">
                        <label for="documentFile" class="form-label">Upload File *</label>
                        <div class="upload-area border border-dashed rounded p-4 text-center" id="uploadArea" style="cursor: pointer; background-color: #f8f9fa;">
                            <i class="fa-light fa-cloud-arrow-up fa-2x text-muted mb-2"></i>
                            <p class="mb-1"><strong>Click to upload</strong> or drag and drop</p>
                            <p class="text-muted small">PDF, JPG, PNG (Max 5MB)</p>
                            <input type="file" id="documentFile" name="document" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" required>
                        </div>
                        <small class="text-danger" id="documentFileError"></small>
                        <div id="filePreview" class="mt-3"></div>
                    </div>

                    <div class="mb-3">
                        <label for="documentDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="documentDescription" name="description" rows="3" placeholder="Any additional information about this document..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitDocumentUpload()" id="uploadSubmitBtn">
                    <i class="fa-light fa-upload"></i> Upload Document
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const API_URL = '/api';
const token = localStorage.getItem('auth_token');

// Load KYC status on page load
document.addEventListener('DOMContentLoaded', function() {
    loadKYCStatus();
    setupUploadDragDrop();
});

function loadKYCStatus() {
    if (!token) {
        window.location.href = '/login';
        return;
    }

    fetch(`${API_URL}/kyc`, {
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
        document.getElementById('loadingState').style.display = 'none';

        if (data.status === 'no_submission') {
            document.getElementById('noSubmissionState').style.display = 'block';
        } else if (data.status === 'approved') {
            displayApprovedState(data);
        } else if (data.status === 'rejected') {
            displayRejectedState(data);
        } else if (data.status === 'pending' || data.status === 'revision_requested') {
            displayPendingState(data);
        }
    })
    .catch(error => {
        console.error('Error loading KYC status:', error);
        Swal.fire('Error', 'Failed to load KYC status', 'error');
        document.getElementById('loadingState').innerHTML = '<p class="text-danger">Failed to load KYC status</p>';
    });
}

function displayPendingState(data) {
    const statusDisplay = document.getElementById('statusDisplay');
    const statusBadge = document.getElementById('statusBadge');
    const documentsList = document.getElementById('documentsList');
    const submitBtn = document.getElementById('submitBtn');
    const revisionNote = document.getElementById('revisionNote');

    statusDisplay.style.display = 'block';

    const statusText = data.status === 'revision_requested' 
        ? 'Revision Requested' 
        : 'Pending Review';
    const statusColor = data.status === 'revision_requested' ? 'warning' : 'info';

    statusBadge.innerHTML = `<span class="badge bg-${statusColor} text-dark">${statusText}</span>`;

    if (data.status === 'revision_requested') {
        revisionNote.style.display = 'block';
        revisionNote.innerHTML = `<strong>Revision Note:</strong> ${data.revision_reason || 'Please resubmit your documents'}`;
    }

    // Display documents
    if (data.documents && data.documents.length > 0) {
        documentsList.innerHTML = data.documents.map(doc => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${formatDocumentType(doc.document_type)}</strong>
                    <br>
                    <small class="text-muted">
                        Uploaded: ${new Date(doc.uploaded_at).toLocaleDateString()}
                        <span class="ms-2">Status: <span class="badge bg-${getVerificationStatusColor(doc.verification_status)}">${doc.verification_status}</span></span>
                    </small>
                </div>
                <a href="${doc.file_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fa-light fa-download"></i> View
                </a>
            </div>
        `).join('');
    } else {
        documentsList.innerHTML = '<p class="text-muted">No documents uploaded yet</p>';
    }

    const allVerified = data.documents && data.documents.every(d => d.verification_status === 'verified');
    if (allVerified && data.documents.length > 0) {
        submitBtn.style.display = 'block';
    }
}

function displayApprovedState(data) {
    document.getElementById('statusDisplay').style.display = 'block';
    document.getElementById('statusBadge').innerHTML = '<span class="badge bg-success">Verified</span>';
    document.getElementById('documentsSection').style.display = 'none';
    document.getElementById('actionButtons').style.display = 'none';
    document.getElementById('approvedState').style.display = 'block';
    document.getElementById('verifiedDate').textContent = new Date(data.verified_at).toLocaleDateString();
}

function displayRejectedState(data) {
    document.getElementById('statusDisplay').style.display = 'block';
    document.getElementById('statusBadge').innerHTML = '<span class="badge bg-danger">Rejected</span>';
    document.getElementById('documentsSection').style.display = 'none';
    document.getElementById('actionButtons').style.display = 'none';
    document.getElementById('rejectedState').style.display = 'block';
    document.getElementById('rejectionReason').textContent = data.rejection_reason || 'Please resubmit with correct documents';
}

function startKYCSubmission() {
    document.getElementById('noSubmissionState').style.display = 'none';
    document.getElementById('statusDisplay').style.display = 'block';
    openDocumentUploadModal();
}

function retryKYCSubmission() {
    loadKYCStatus();
}

function submitKYC() {
    if (!token) return;

    fetch(`${API_URL}/kyc/validate`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'KYC submission successful!', 'success').then(() => {
                loadKYCStatus();
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Failed to submit KYC', 'error');
    });
}

function openDocumentUploadModal() {
    const modal = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
    modal.show();
}

function setupUploadDragDrop() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('documentFile');

    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '#e9ecef';
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.backgroundColor = '#f8f9fa';
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '#f8f9fa';
        fileInput.files = e.dataTransfer.files;
        displayFilePreview();
    });

    fileInput.addEventListener('change', displayFilePreview);
}

function displayFilePreview() {
    const fileInput = document.getElementById('documentFile');
    const filePreview = document.getElementById('filePreview');
    const file = fileInput.files[0];

    if (!file) return;

    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        document.getElementById('documentFileError').textContent = 'File size must be less than 5MB';
        fileInput.value = '';
        filePreview.innerHTML = '';
        return;
    }

    document.getElementById('documentFileError').textContent = '';

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            filePreview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px;" />`;
        };
        reader.readAsDataURL(file);
    } else {
        filePreview.innerHTML = `<p><i class="fa-light fa-file"></i> ${file.name}</p>`;
    }
}

function submitDocumentUpload() {
    if (!token) return;

    const documentType = document.getElementById('documentType').value;
    const fileInput = document.getElementById('documentFile');
    const file = fileInput.files[0];

    if (!documentType) {
        document.getElementById('documentTypeError').textContent = 'Please select a document type';
        return;
    }

    if (!file) {
        document.getElementById('documentFileError').textContent = 'Please select a file';
        return;
    }

    const formData = new FormData();
    formData.append('document_type', documentType);
    formData.append('document', file);

    const uploadBtn = document.getElementById('uploadSubmitBtn');
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

    fetch(`${API_URL}/kyc/documents`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Document uploaded successfully!', 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('uploadDocumentModal')).hide();
                document.getElementById('documentUploadForm').reset();
                document.getElementById('filePreview').innerHTML = '';
                loadKYCStatus();
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Failed to upload document', 'error');
    })
    .finally(() => {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fa-light fa-upload"></i> Upload Document';
    });
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

function getVerificationStatusColor(status) {
    const map = {
        'pending': 'warning',
        'verified': 'success',
        'rejected': 'danger'
    };
    return map[status] || 'secondary';
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
