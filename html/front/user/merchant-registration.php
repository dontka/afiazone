<?php
$pageTitle = 'Become a Merchant - AfiaZone';
$additionalStyles = [];
$additionalScripts = [];
ob_start();
?>

<div class="rts-navigation-area-breadcrumb">
    <div class="container-2">
        <div class="row">
            <div class="col-lg-12">
                <div class="navigator-breadcrumb-wrapper">
                    <a href="/">Home</a>
                    <i class="fa-regular fa-chevron-right"></i>
                    <a class="current" href="javascript:void(0)">Become a Merchant</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="rts-section-gap" style="padding: 60px 0;">
    <div class="container-2">
        <div class="row mb-5">
            <div class="col-lg-12">
                <div class="text-center">
                    <h2 class="mb-3">Grow Your Business with AfiaZone</h2>
                    <p class="lead text-muted">Join thousands of successful merchants selling medical products on our platform</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Benefits -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="card h-100">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0"><i class="fa-light fa-star text-warning"></i> Why Sell with Us?</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fa-solid fa-circle-check text-success me-2"></i>
                                <strong>Wide Reach</strong><br>
                                <small class="text-muted">Access to thousands of customers</small>
                            </li>
                            <li class="mb-3">
                                <i class="fa-solid fa-circle-check text-success me-2"></i>
                                <strong>Secure Payments</strong><br>
                                <small class="text-muted">Fast and reliable payment processing</small>
                            </li>
                            <li class="mb-3">
                                <i class="fa-solid fa-circle-check text-success me-2"></i>
                                <strong>Commerce Tools</strong><br>
                                <small class="text-muted">Analytics, inventory management, more</small>
                            </li>
                            <li class="mb-3">
                                <i class="fa-solid fa-circle-check text-success me-2"></i>
                                <strong>Dedicated Support</strong><br>
                                <small class="text-muted">24/7 seller support team</small>
                            </li>
                            <li>
                                <i class="fa-solid fa-circle-check text-success me-2"></i>
                                <strong>Growth Opportunities</strong><br>
                                <small class="text-muted">Promotions, advertising, tier upgrades</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column - Registration Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Merchant Registration</h5>
                        <small class="text-muted">Complete this form to become a merchant</small>
                    </div>

                    <div class="card-body">
                        <!-- Step Indicator -->
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <ul class="nav nav-pills justify-content-between" role="tablist">
                                    <li class="nav-item flex-grow-1">
                                        <a class="nav-link active text-center rounded-0" data-bs-toggle="pill" href="#basicInfo">
                                            <span class="badge bg-primary rounded-circle me-2">1</span>
                                            <span class="d-none d-md-inline">Basic Info</span>
                                        </a>
                                    </li>
                                    <li class="nav-item flex-grow-1">
                                        <a class="nav-link text-center rounded-0" data-bs-toggle="pill" href="#businessInfo">
                                            <span class="badge bg-secondary rounded-circle me-2">2</span>
                                            <span class="d-none d-md-inline">Business</span>
                                        </a>
                                    </li>
                                    <li class="nav-item flex-grow-1">
                                        <a class="nav-link text-center rounded-0" data-bs-toggle="pill" href="#shippingInfo">
                                            <span class="badge bg-secondary rounded-circle me-2">3</span>
                                            <span class="d-none d-md-inline">Shipping</span>
                                        </a>
                                    </li>
                                    <li class="nav-item flex-grow-1">
                                        <a class="nav-link text-center rounded-0" data-bs-toggle="pill" href="#reviewInfo">
                                            <span class="badge bg-secondary rounded-circle me-2">4</span>
                                            <span class="d-none d-md-inline">Review</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <form id="merchantRegistrationForm">
                            <div class="tab-content">
                                <!-- Step 1: Basic Info -->
                                <div id="basicInfo" class="tab-pane fade show active">
                                    <h5 class="mb-4">Business Owner Information</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Business Name *</label>
                                            <input type="text" class="form-control" name="business_name" required>
                                            <small class="text-danger" id="business_nameError"></small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Business Type *</label>
                                            <select class="form-select" name="business_type" required>
                                                <option value="">Select Type</option>
                                                <option value="wholesaler">Wholesaler</option>
                                                <option value="producer">Producer/Manufacturer</option>
                                                <option value="retailer">Retailer</option>
                                            </select>
                                            <small class="text-danger" id="business_typeError"></small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Business Description *</label>
                                        <textarea class="form-control" name="description" rows="4" placeholder="Describe your business..." required></textarea>
                                        <small class="text-danger" id="descriptionError"></small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Contact Person *</label>
                                            <input type="text" class="form-control" name="contact_person" required>
                                            <small class="text-danger" id="contact_personError"></small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Contact Phone *</label>
                                            <input type="tel" class="form-control" name="contact_phone" required>
                                            <small class="text-danger" id="contact_phoneError"></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Business Info -->
                                <div id="businessInfo" class="tab-pane fade">
                                    <h5 class="mb-4">Business Details</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Company Registration Number</label>
                                        <input type="text" class="form-control" name="registration_number">
                                        <small class="text-muted">Optional: Registration/License number</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Tax ID / VAT Number</label>
                                        <input type="text" class="form-control" name="tax_id">
                                        <small class="text-muted">Optional: Your tax identification number</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Logo URL</label>
                                        <input type="url" class="form-control" name="logo_url" placeholder="https://example.com/logo.png">
                                        <small class="text-muted">Square image (200x200px recommended)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Cover Image URL</label>
                                        <input type="url" class="form-control" name="cover_image_url" placeholder="https://example.com/cover.png">
                                        <small class="text-muted">Wide image (1200x400px recommended)</small>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" name="medical_certified" id="medicalCertified">
                                        <label class="form-check-label" for="medicalCertified">
                                            Certified Medical Supplier (Optional)
                                        </label>
                                        <small class="d-block text-muted">Check if you have medical supplier certification</small>
                                    </div>
                                </div>

                                <!-- Step 3: Shipping Info -->
                                <div id="shippingInfo" class="tab-pane fade">
                                    <h5 class="mb-4">Shipping Information</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Warehouse Address *</label>
                                        <input type="text" class="form-control" name="warehouse_address" required>
                                        <small class="text-danger" id="warehouse_addressError"></small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">City *</label>
                                            <input type="text" class="form-control" name="warehouse_city" required>
                                            <small class="text-danger" id="warehouse_cityError"></small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Country *</label>
                                            <input type="text" class="form-control" name="warehouse_country" value="Democratic Republic of Congo" required>
                                            <small class="text-danger" id="warehouse_countryError"></small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Return Policy</label>
                                        <textarea class="form-control" name="return_policy" rows="3" placeholder="Describe your return policy..."></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Processing Time (days) *</label>
                                            <input type="number" class="form-control" name="processing_time_days" min="1" value="1" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Commission Rate (%)</label>
                                            <input type="number" class="form-control" name="commission_percent" min="0" max="50" value="15" step="0.01">
                                            <small class="text-muted">Typical: 10-25%</small>
                                        </div>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="accepts_cod" id="acceptsCod" checked>
                                        <label class="form-check-label" for="acceptsCod">
                                            Accept Cash on Delivery
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="accepts_wallet" id="acceptsWallet" checked>
                                        <label class="form-check-label" for="acceptsWallet">
                                            Accept Wallet Payment
                                        </label>
                                    </div>
                                </div>

                                <!-- Step 4: Review -->
                                <div id="reviewInfo" class="tab-pane fade">
                                    <h5 class="mb-4">Review Your Information</h5>
                                    <div id="reviewContent" class="alert alert-light">
                                        Loading...
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fa-light fa-circle-info"></i>
                                        <strong>Important:</strong> After registration, your business will require KYC verification before going live.
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" name="agree_terms" id="agreeTerms" required>
                                        <label class="form-check-label" for="agreeTerms">
                                            I agree to the <a href="#" target="_blank">Terms & Conditions</a> and <a href="#" target="_blank">Merchant Agreement</a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="previousStep()">
                                            <i class="fa-light fa-arrow-left"></i> Previous
                                        </button>
                                        <div>
                                            <button type="button" class="btn btn-outline-primary" onclick="nextStep()" id="nextBtn">
                                                Next <i class="fa-light fa-arrow-right"></i>
                                            </button>
                                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                                <i class="fa-light fa-check"></i> Complete Registration
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API_URL = '/api';
const token = localStorage.getItem('auth_token');
let currentStep = 0;
const steps = ['basicInfo', 'businessInfo', 'shippingInfo', 'reviewInfo'];

document.addEventListener('DOMContentLoaded', function() {
    if (!token) {
        window.location.href = '/login';
        return;
    }

    document.getElementById('merchantRegistrationForm').addEventListener('submit', submitForm);
});

function nextStep() {
    if (validateCurrentStep()) {
        currentStep++;
        if (currentStep === steps.length - 1) {
            updateReview();
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'block';
        }
        document.querySelector(`[href="#${steps[currentStep]}"]`).click();
    }
}

function previousStep() {
    if (currentStep > 0) {
        currentStep--;
        document.getElementById('submitBtn').style.display = 'none';
        document.getElementById('nextBtn').style.display = 'block';
        document.querySelector(`[href="#${steps[currentStep]}"]`).click();
    }
}

function validateCurrentStep() {
    const form = document.getElementById('merchantRegistrationForm');
    const inputs = form.querySelectorAll(`#${steps[currentStep]} [required]`);
    let valid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return valid;
}

function updateReview() {
    const form = document.getElementById('merchantRegistrationForm');
    const formData = new FormData(form);
    
    const review = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6>Business Information</h6>
                <p><strong>Name:</strong> ${formData.get('business_name')}</p>
                <p><strong>Type:</strong> ${formData.get('business_type')}</p>
                <p><strong>Contact:</strong> ${formData.get('contact_person')}</p>
                <p><strong>Phone:</strong> ${formData.get('contact_phone')}</p>
            </div>
            <div class="col-md-6 mb-3">
                <h6>Shipping Details</h6>
                <p><strong>Address:</strong> ${formData.get('warehouse_address')}</p>
                <p><strong>City:</strong> ${formData.get('warehouse_city')}</p>
                <p><strong>Processing Time:</strong> ${formData.get('processing_time_days')} days</p>
                <p><strong>Commission:</strong> ${formData.get('commission_percent')}%</p>
            </div>
        </div>
    `;

    document.getElementById('reviewContent').innerHTML = review;
}

function submitForm(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registering...';

    fetch(`${API_URL}/merchants`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire(
                'Success!',
                'Your merchant account has been created. You can now start adding products!',
                'success'
            ).then(() => {
                window.location.href = '/merchant/dashboard';
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Failed to register merchant account', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-light fa-check"></i> Complete Registration';
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
